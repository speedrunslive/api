<?php
class SRL_Data_RatingRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
        $this->pageSize = 1000;
    }
    
    public function GetGameLeaderboard($gameId)
    {
        $results = $this->Select("SELECT p.player_name as player_name, gr.rating * 40 as rating, gr.mu, gr.sigma, gr.rank FROM players p INNER JOIN game_rating gr ON gr.players_player_id = p.player_id AND gr.game_game_id = $gameId WHERE rating > 0 ORDER BY rating DESC;");
        return $results;
    }
    
    public function GetGameUnrankedLeaderboard($gameId)
    {
        $results = $this->Select("SELECT p.player_name as player_name, gr.rating * 40 as rating, gr.mu, gr.sigma, gr.rank FROM players p INNER JOIN game_rating gr ON gr.players_player_id = p.player_id AND gr.game_game_id = $gameId WHERE rating <= 0 ORDER BY player_name;");
        return $results;
    }
    
    public function GetCurrentRatings($game, $entrants)
    {
        $gameId = $game->Id();
        
        $names = array();
        foreach ($entrants as $entrant) {
            array_push($names, "'" . mysql_real_escape_string($entrant->Player()->Name()) . "'");
        }
        $names = join(",", $names);
        
        $results = $this->Select("SELECT lcase(p.player_name) as player_name, gr.mu, gr.sigma FROM players p LEFT JOIN game_rating gr ON gr.players_player_id = p.player_id WHERE p.player_name IN ($names) AND gr.game_game_id = $gameId;");
    
        $currentRatings = array();
        foreach ($results as $result) {
            $newR->mu = ($result["mu"] == null) ? 25 : $result["mu"];
            $newR->sigma = ($result["sigma"] == null) ? 8.333333 : $result["sigma"];
            
            $currentRatings[$result["player_name"]] = $newR;
            
            unset($newR);
            unset($result);
        }
        
        foreach ($entrants as $entrant) {
            $playerName = strtolower($entrant->Player()->Name());
            if (!array_key_exists($playerName, $currentRatings)) {
                $newRating->mu = 25;
                $newRating->sigma = 8.333333;
                $currentRatings[$playerName] = $newRating;
                
                unset($newRating);
                unset($entrant);
            }
        }
        
        return $currentRatings;
    }
    
    public function CalculateNewRatings($oldRatings)
    {
        $calculator = new Moserware\Skills\TrueSkill\FactorGraphTrueSkillCalculator();
        $gameInfo = new Moserware\Skills\GameInfo();
        
        $players = array();
        $teams = array();
        $places = array();
        foreach ($oldRatings as $oldRating) {
            $player = new Moserware\Skills\Player($oldRating->name);
            $players[$oldRating->name] = $player;
            
            $team = new Moserware\Skills\Team($player, new Moserware\Skills\Rating($oldRating->mu, $oldRating->sigma));
            array_push($teams, $team);
            
            $place = min($oldRating->place, 9996);
            array_push($places, $place);
            // apparently you gotta do these unsets
            unset($player);
            unset($team);
            unset($oldRating);
            unset($place);
        }
        
        $newRatings = $calculator->calculateNewRatings($gameInfo, $teams, $places);
        
        $reallyNewRatings = array();
        foreach ($players as $name => $player) {
            $rating = $newRatings->getRating($player);
            
            $newRating->name = $name;
            $newRating->mu = $rating->getMean();
            $newRating->sigma = $rating->getStandardDeviation();
            
            $reallyNewRatings[] = $newRating;
            
            // apparently you gotta do these unsets
            unset($player);
            unset($rating);
            unset($newRating);
        }
        
        return $reallyNewRatings;
    }
    
    public function MigrateRaceToTrueSkill($race, $oldRatings, $newRatings)
    {
        $game = $this->Select("SELECT game_game_id FROM races WHERE race_id = $race;");
        $game = $game[0]["game_game_id"];
        
        foreach ($newRatings as $newRating)
        {
            $player = mysql_real_escape_string($newRating->name);
            $playerId = $this->Select("SELECT player_id FROM players WHERE player_name = '$player';");
            $playerId = $playerId[0]["player_id"];
            
            $newMu = $newRating->mu;
            $newSigma = $newRating->sigma;
            $newSkill = $newMu - (3*$newSigma);
            
            $olderRating = null;
            foreach ($oldRatings as $oldRating)
            {
                if (strtolower($oldRating->name) == strtolower($newRating->name))
                {
                    $olderRating = $oldRating;
                }
            }
            
            $oldMu = $olderRating->mu;
            $oldSigma = $olderRating->sigma;
            $oldSkill = $oldMu - (3*$oldSigma);
            
            $this->Execute("UPDATE game_rating SET rating = $newSkill, mu = $newMu, sigma = $newSigma WHERE game_game_id = $game AND players_player_id = $playerId;");
            $this->Execute("UPDATE race_link SET old_rating = $oldSkill, old_mu = $oldMu, old_sigma = $oldSigma, new_rating = $newSkill, new_mu = $newMu, new_sigma = $newSigma WHERE races_race_id = $race AND players_player_id = $playerId;"); 
        }
        
        $this->Execute("UPDATE game_rating SET sigma = CASE WHEN sigma + 0.00333 > 8.333333 THEN 8.333333 ELSE sigma + 0.00333 END WHERE game_game_id = $game;");
        $this->Execute("UPDATE game_rating SET rating = mu-(3*sigma) WHERE game_game_id = $game;");
        
        $statRepo = new SRL_Data_StatRepository();
        $statRepo->RerankPlayersInGame($game);
        $statRepo->UpdateStats($race);
    }
    
    public function MigrateGoal($raceId, $gameId, $goal)
    {
        $goalRepo = new SRL_Data_GoalRepository();
        $goal = trim($goal);
        
        $trackedGoals = $goalRepo->GetGoals($gameId);
        $trackedGoalId = null;
        foreach ($trackedGoals as $trackedGoal)
        {
            if (strcasecmp($goal, $trackedGoal->Goal()) == 0)
            {
                $trackedGoalId = $trackedGoal->Id();
                break;
            }
        }
        
        if ($trackedGoalId == null)
        {
            $goal = mysql_real_escape_string($goal);
            $this->Execute("INSERT INTO tracked_goals (game_game_id, tracked_goal) VALUES ($gameId, '$goal');");
            $trackedGoalId = $this->GetLastInsertId();
        }
        
        $this->Execute("UPDATE races SET goal_goal_id = $trackedGoalId WHERE race_id = $raceId;");
    }
    
    public function GetPlayerRatings($playerId)
    {
        $results = $this->Select("select game_name, game_abbrev, count(*) as count, game_poprank, rank, (1000/(game_poprank*rank))+count(*) as personalpop from (select game_game_id from race_link rl inner join races r on r.race_id = rl.races_race_id where players_player_id = $playerId order by race_link_id desc) as z inner join game on game_id = z.game_game_id inner join game_rating gr on z.game_game_id = gr.game_game_id where gr.players_player_id = $playerId group by z.game_game_id order by personalpop desc;");
        return $results;
    }
    
    public function GetPlayerRatingsForSeason($playerId, $season_id)
    {
        $results = $this->Select("select     g.game_name,      g.game_abbrev,      count(*) as count,      p.game_poprank,      rank,      (1000/(p.game_poprank*rank))+count(*) as personalpop from      (select          game_game_id, rank     from past_season_game_rating rl     where players_player_id = $playerId     and season_id = $season_id) as z  inner join game g     on g.game_id = z.game_game_id  inner join past_game_pop p     on z.game_game_id = p.game_id where p.season_id = $season_id group by z.game_game_id order by personalpop desc;");
        return $results;
    }
    
    public function GetRandomChampion()
    {
        $games = $this->Select("SELECT distinct game_game_id from game_rating;");
        
        $game = null;
        while ($game == null) {
            $index = rand(0, count($games)-1);
            $game = $games[$index]["game_game_id"];
        }
        
        $statRepo = new SRL_Data_StatRepository();
        $player = $statRepo->GetRankOnePlayerForGame($game);
        
        $champion->player = $player;
        $gameRepo = new SRL_Data_GameRepository();
        $champion->game = $gameRepo->GetGameById($game);
        return $champion;
    }
}
