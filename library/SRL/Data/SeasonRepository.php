<?php
class SRL_Data_SeasonRepository extends SRL_Data_BaseRepository
{
    private $seasonGoalRepo;
    private $pastRaceRepo;
    private $ratingRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->pageSize = 9000;
        $this->seasonGoalRepo = new SRL_Data_SeasonGoalRepository();
        $this->pastRaceRepo = new SRL_Data_PastRaceRepository();
        $this->ratingRepo = new SRL_Data_RatingRepository();
    }
    
    private function GetSeasonsHelper($where, $page)
    {
        $seasons = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT season_id, season_name, start_date, end_date FROM seasons $where ORDER BY season_id ASC LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $seasonGoals = $this->seasonGoalRepo->GetSeasonGoals($result["season_id"]);
            $season = new SRL_Core_Season($result["season_id"], $result["season_name"], $result["start_date"], $result["end_date"], $seasonGoals);
            array_push($seasons, $season);
        }
        
        return $seasons;
    }
    
    public function GetSeasons()
    {
        return $this->GetSeasonsHelper("", 1);
    }
    
    public function GetSeason($id)
    {
        $result = $this->GetSeasonsHelper(" WHERE season_id = $id ", 1);
        return $result[0];
    }
    
    public function GetActiveSeason()
    {
        $results = $this->Select("SELECT MAX(season_id) AS season_id FROM seasons;");
        return $results[0]["season_id"];
    }
    
    public function RecordSeasonRace($raceId)
    {
        $race = $this->pastRaceRepo->GetRace($raceId);
        $season_goal = $this->seasonGoalRepo->GetSeasonGoalBySeason($this->GetActiveSeason(), $race->GoalId());
        
        if ($season_goal != null)
        {
            $season_goal_id = $season_goal->Id();
            $race_id = $race->Id();
            $this->Execute("INSERT INTO season_race (season_goal_id, race_id) VALUES ($season_goal_id, $race_id);");
        
            $current_ratings = array();
           
            foreach ($race->RaceResults() as $raceResult)
            {
                $player_id = $raceResult->Player()->Id();
                $rating = $this->seasonGoalRepo->GetSeasonGoalRating($season_goal_id, $player_id);
                $rating->place = $raceResult->Place();
                
                array_push($current_ratings, $rating);
            }
            
            $new_ratings = $this->ratingRepo->CalculateNewRatings($current_ratings);
            
            foreach ($new_ratings as $new_rate)
            {
                $player_id = $new_rate->name;
                $newMu = $new_rate->mu;
                $newSigma = $new_rate->sigma;
                $newSkill = $newMu - (3*$newSigma);
                
                $oldRating = null;
                foreach ($current_ratings as $curr_rating)
                {
                    if (strtolower($curr_rating->name) == strtolower($new_rate->name))
                    {
                        $oldRating = $curr_rating;
                    }
                }
                
                $oldMu = $oldRating->mu;
                $oldSigma = $oldRating->sigma;
                $oldSkill = $oldMu - (3*$oldSigma);
                
                $this->Execute("INSERT INTO season_rating (season_goal_id, player_id, rating, sigma, mu) VALUES ($season_goal_id, $player_id, $newSkill, $newSigma, $newMu) ON DUPLICATE KEY UPDATE rating = $newSkill, sigma = $newSigma, mu = $newMu;");
                
                $this->Execute("INSERT INTO season_race_results (race_id, player_id, old_rating, new_rating, old_sigma, new_sigma, old_mu, new_mu) VALUES ($raceId, $player_id, $oldSkill, $newSkill, $oldSigma, $newSigma, $oldMu, $newMu);");
            
                $this->Execute("UPDATE season_rating SET sigma = CASE WHEN sigma + 0.00333 > 8.333333 THEN 8.333333 ELSE sigma + 0.00333 END WHERE season_goal_id = $season_goal_id;");
                $this->Execute("UPDATE season_rating SET rating = mu-(3*sigma) WHERE season_goal_id = $season_goal_id;");
            }
            
            $this->seasonGoalRepo->RerankSeasonGoal($season_goal_id);
        }
    }
    
    public function NewSeason($season_name)
    {
        $season_name = mysql_real_escape_string($season_name);
        $active_season_id = $this->GetActiveSeason();
        $now = time();
        
        $this->Execute("UPDATE seasons SET end_date = $now WHERE season_id = $active_season_id;");
        
        $this->Execute("INSERT INTO seasons (season_name, start_date, end_date) VALUES ('$season_name', $now, 0);");
        
        $results = $this->Select("SELECT game_id, game_popularity, game_recentpop, game_poprank FROM game;");
        
        foreach ($results as $result) {
            $game_id = $result["game_id"];
            $game_popularity = $result["game_popularity"];
            $game_recentpop = $result["game_recentpop"];
            $game_poprank = $result["game_poprank"];
            
            $this->Execute("INSERT INTO past_game_pop (game_id, game_popularity, game_recentpop, game_poprank, season_id) VALUES ($game_id, $game_popularity, $game_recentpop, $game_poprank, $active_season_id);");
        }
        
        $this->Execute("UPDATE game SET game_popularity = 0, game_recentpop = 0, game_poprank = 9999;");
        
        $results = $this->Select("SELECT game_game_id, players_player_id, rating, sigma, mu, rank FROM game_rating;");
        
        foreach ($results as $result) {
            $game_game_id = $result["game_game_id"];
            $players_player_id = $result["players_player_id"];
            $rating = $result["rating"];
            $sigma = $result["sigma"];
            $mu = $result["mu"];
            $rank = $result["rank"] ?: 999;
            
            $this->Execute("INSERT INTO past_season_game_rating (season_id, game_game_id, players_player_id, rating, sigma, mu, rank) VALUES ($active_season_id, $game_game_id, $players_player_id, $rating, $sigma, $mu, $rank);");
        }
        
        $this->Execute("DELETE FROM game_rating;");
    }
}