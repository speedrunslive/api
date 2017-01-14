<?php
class SRL_Data_RaceRepository extends SRL_Data_BaseRepository
{
    private $gameRepo;
    private $entrantRepo;
    private $statRepo;
    private $playerRepo;
    private $goalRepo;
    private $seasonRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->entrantRepo = new SRL_Data_EntrantRepository();
        $this->statRepo = new SRL_Data_StatRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
        $this->goalRepo = new SRL_Data_GoalRepository();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
    }
    
    private function GetRacesHelper($where, $page)
    {
        $races = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        //
        //Old race helper query was not very efficient, 10,000 requests took over a minute.
        // $results = $this->Select("SELECT current_race_id, current_race_game_id, current_race_goal, current_race_time, current_race_state, count(*) as numentrants, current_race_filename
        // FROM current_races r
        // LEFT JOIN current_races_link l
        // ON r.current_race_id = l.current_race_race_id $where
        // GROUP BY current_race_id, current_race_game_id, current_race_goal, current_race_time, current_race_state 
        // ORDER BY current_race_state ASC, numentrants DESC LIMIT $offset, $pageSize;");
        // 
        // 10,000 requests took under 20 seconds for me with this one, so let's use it instead.
        $results = $this->Select("SELECT current_race_id, current_race_game_id, current_race_goal, current_race_time, current_race_state, (select count(*) from current_races_link where current_race_race_id=r.current_race_id) as numentrants, current_race_filename
        FROM current_races r $where
        ORDER BY current_race_state ASC, numentrants DESC LIMIT $offset, $pageSize;");
        parent::WriteLogExtensive("RacesHelper called");
        foreach ($results as $result)
        {
            $game = $this->gameRepo->GetGameById($result["current_race_game_id"]);
            $results = $this->entrantRepo->GetEntrants($result["current_race_id"]);
            $race = new SRL_Core_Race($result["current_race_id"], $game, $result["current_race_goal"], $result["current_race_time"], $result["current_race_state"], $results, $result["current_race_filename"]);
            array_push($races, $race);
        }
        
        return $races;
    }
    
    public function GetRaces($page)
    {
        return $this->GetRacesHelper("", $page);
    }
    
    public function GetRace($id)
    {
        $id = mysql_real_escape_string($id);
        $results = $this->GetRacesHelper("WHERE current_race_id = '$id'", 1);
        if (count($results) > 0){
            return $results[0];
        }
        else{
            return null;
        }
    }
    
    private function CountRacesHelper($where)
    {
        $results = $this->Select("SELECT count(*) AS count FROM current_races $where;");
        return $results[0]["count"];
    }
    
    public function CountRaces()
    {
        return $this->CountRacesHelper("");
    }
    
    public function CreateRace($game)
    {
        $game = mysql_real_escape_string($game);
        
        $id = $this->RandomString(5);
        $potentialGame = $this->gameRepo->GetGame($game);
        
        if ($potentialGame->Id() != 0)
            $game = $potentialGame->Id();
        else
            $game = 0;
        
        $this->Execute("INSERT INTO current_races (current_race_id, current_race_game_id, current_race_state, current_race_filename) VALUES ('$id', '$game', 1, 0);");
        
        return $this->GetRace($id);
    }
    
    public function SetFilename($id)
    {
        $id = mysql_real_escape_string($id);
        
        $this->Execute("UPDATE current_races SET current_race_filename = 1 WHERE current_race_id = '$id';");
    }
    
    public function RemoveFilename($id)
    {
        $id = mysql_real_escape_string($id);
        
        $this->Execute("UPDATE current_races SET current_race_filename = 0 WHERE current_race_id = '$id';");
    }
    
    private function RandomString($length)
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
        $string = "";    
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        return $string;
    }
    
    public function SetRaceGame($id, $game)
    {
        $id = mysql_real_escape_string($id);
        $game = mysql_real_escape_string($game);
        $potentialGame = $this->gameRepo->GetGame($game);
        
        if ($potentialGame->Id() != 0)
            $game = $potentialGame->Id();
        else
            $game = 0;
            
        $this->Execute("UPDATE current_races SET current_race_game_id = $game WHERE current_race_id = '$id';");
    }
    
    public function SetRaceGoal($id, $goal)
    {
        $id = mysql_real_escape_string($id);
        $goal = mysql_real_escape_string($goal);
        
        $this->Execute("UPDATE current_races SET current_race_goal = '$goal' WHERE current_race_id = '$id';");
    }
    
    public function SetRaceInProgress($id)
    {
        $id = mysql_real_escape_string($id);
        $race = $this->GetRace($id);
        if (($race->State() == SRL_Core_RaceState::EntryOpen && $race->IsEveryoneReady())
            || ($race->State() == SRL_Core_RaceState::EntryClosed && $race->IsEveryoneReady())) {
            $startTime = time();
            $this->Execute("UPDATE current_races SET current_race_state = 3, current_race_time = $startTime WHERE current_race_id = '$id';");
        }
        else if ($race->State() == SRL_Core_RaceState::Complete) {
            $this->Execute("UPDATE current_races SET current_race_state = 3 WHERE current_race_id = '$id';");
        }
    }
    
    public function SetRaceEntryClosed($id)
    {
        $id = mysql_real_escape_string($id);
        $race = $this->GetRace($id);
        if ($race->State() == SRL_Core_RaceState::EntryOpen) {
        
            $this->Execute("UPDATE current_races SET current_race_state = 2 WHERE current_race_id = '$id';");
        }
    }
    
    public function SetRaceComplete($id)
    {
        $id = mysql_real_escape_string($id);
        $race = $this->GetRace($id);
        if ($race->State() == SRL_Core_RaceState::InProgress
            && $race->IsEveryoneDone()) {
        
            $this->Execute("UPDATE current_races SET current_race_state = 4 WHERE current_race_id = '$id';");
        }
    }
    
    public function SetRaceOver($id)
    {
        $id = mysql_real_escape_string($id);
        
        $this->Execute("UPDATE current_races SET current_race_state = 5 WHERE current_race_id = '$id';");
    }
    
    private function SetRaceEntryOpen($id)
    {
        $id = mysql_real_escape_string($id);
        
        $race = $this->GetRace($id);
        if ($race->State() == SRL_Core_RaceState::Complete
            || $race->State() == SRL_Core_RaceState::RaceOver) {
            
            $this->Execute("UPDATE current_races SET current_race_state = 1, current_race_time = NULL WHERE current_race_id = '$id';");
        }
    }
    
    public function EndRace($id)
    {
        $id = mysql_real_escape_string($id);
        
        $this->Execute("DELETE FROM current_races WHERE current_race_id = '$id';");
    }
    
    public function RematchRace($id)
    {
        $id = mysql_real_escape_string($id);
        
        $race = $this->GetRace($id);
        if ($race->State() == SRL_Core_RaceState::Complete
            || $race->State() == SRL_Core_RaceState::RaceOver) {
            
            $this->entrantRepo->RemoveAllEntrants($race->Id());
            $this->SetRaceEntryOpen($race->Id());
            $this->RemoveFilename($race->Id());
        }
    }
    
    private function GetPlayerId($name)
    {
        $name = mysql_real_escape_string($name);
        
        $results = $this->Select("SELECT player_id FROM players WHERE player_name = '$name';");
        if (count($results) > 0) {
            return $results[0]["player_id"];
        }
        else {
            $this->Execute("INSERT INTO players (player_name) VALUES ('$name');");
            $playerId = $this->GetLastInsertId();
            return $playerId;
        }
    }
    
    public function RecordRace($id, $oldRatings, $newRatings)
    {
        $id = mysql_real_escape_string($id);
        
        $race = $this->GetRace($id);
        $gameId = $race->Game()->Id();
        $goal = trim($race->Goal());
        $recordedTime = time();
        
        $trackedGoals = $this->goalRepo->GetGoals($gameId);
        $trackedGoalId = null;

        foreach ($trackedGoals as $trackedGoal)
        {
            if (strcasecmp($goal, $trackedGoal->Goal()) == 0)
            {
                $trackedGoalId = $trackedGoal->Id();
                break;
            }
        }
        
        $goal = mysql_real_escape_string($goal);

        if ($trackedGoalId == null)
        {
            $this->Execute("INSERT INTO tracked_goals (game_game_id, tracked_goal) VALUES ($gameId, '$goal');");
            $trackedGoalId = $this->GetLastInsertId();
        }
        
        $season_id = 0;
        
        $this->Execute("INSERT INTO races (game_game_id, goal_goal_id, race_goal, race_date, season_id) VALUES ($gameId, $trackedGoalId, '$goal', $recordedTime, $season_id);");
        $savedRaceId = $this->GetLastInsertId();
        
        $bonuspop = 0.5;
        $currentpop = 1.5;
        $flatpop = 0;
        foreach ($oldRatings as $oldRating) {
            $player = $oldRating->name;
            $playerId = $this->GetPlayerId($player);

            $playernewrating = $oldRating;
            // see if we got ranked in the new results or if we got omitted, if we get omitted, just copy the score from before.
            foreach ($newRatings as $newRating) {
                parent::WriteLogExtensive(print_r($newRating['name'], true) . " / " . print_r($oldRating->name, true), print_r($newRating['name'] == $oldRating->name, true));
                if ($newRating['name'] == $oldRating->name) {
                    $playernewrating = new stdClass;
                    $playernewrating->place = $oldRating->place;
                    $playernewrating->name = $oldRating->name;
                    $playernewrating->mu = $newRating['mu'];
                    $playernewrating->sigma = $newRating['sigma'];
                }
            }
            $newMu = $playernewrating->mu;
            $newSigma = $playernewrating->sigma;
            $newSkill = $newMu - (3*$newSigma);
            parent::WriteLogExtensive("New mu/sigma: " . $newMu . " / " . $newSigma . " Skill: " . $newSkill);
            $oldMu = $oldRating->mu;
            $oldSigma = $oldRating->sigma;
            $oldSkill = $oldMu - (3*$oldSigma);
            parent::WriteLogExtensive("Old mu/sigma: " . $oldMu . " / " . $oldSigma . " Skill: " . $oldSkill);
            $this->Execute("INSERT INTO game_rating (game_game_id, players_player_id, rating, sigma, mu) VALUES ($gameId, $playerId, $newSkill, $newSigma, $newMu) ON DUPLICATE KEY UPDATE rating = $newSkill, sigma = $newSigma, mu = $newMu;");
            parent::WriteLogAll("Completed inserting game rating.");
            $this->Execute("UPDATE race_link SET old_rating = $oldSkill, old_sigma = $oldSigma, old_mu = $oldMu, new_rating = $newSkill, new_sigma = $newSigma, new_mu = $newMu WHERE races_race_id = '$id' AND players_player_id = $playerId;");
            parent::WriteLogAll("Completed updating race link.");
            $entrant = $this->entrantRepo->GetEntrant($id, $player);
            $place = $entrant->Place();
            $time = $entrant->Time();
            $message = mysql_real_escape_string($entrant->Message());
            $message = !empty($message) ? "'$message'" : "NULL";
            $this->Execute("INSERT INTO race_link
                (races_race_id, players_player_id, place, time, message, old_rating, old_sigma, old_mu, new_rating, new_sigma, new_mu) VALUES
                ($savedRaceId, $playerId, $place, $time, $message, $oldSkill, $oldSigma, $oldMu, $newSkill, $newSigma, $newMu);");
            parent::WriteLogAll("Finished inserting race link.");
            if ($place > 9995) {
                $flatpop += 1;
            }
            else {
                $flatpop += 3;
                $bonuspop *= .92;
                $currentpop *= ($bonuspop + 1);
            }

        }        
        parent::WriteLogAll("Updating user and decaying all other users.");
        $this->Execute("UPDATE game_rating SET sigma = CASE WHEN sigma + 0.00333 > 8.333333 THEN 8.333333 ELSE sigma + 0.00333 END WHERE game_game_id = $gameId;");
        $this->Execute("UPDATE game_rating SET rating = mu-(3*sigma) WHERE game_game_id = $gameId;");
        parent::WriteLogAll("Reranking players.");
        $this->statRepo->RerankPlayersInGame($gameId);
        parent::WriteLogAll("Updating statistics.");
        $this->statRepo->UpdateStats($savedRaceId);
        parent::WriteLogAll("Adjusting game popularity.");
        $gamepop = $this->Select("SELECT game_popularity FROM game WHERE game_id = $gameId;");
        $gamepop = $gamepop[0]["game_popularity"];
        $gamepop = $gamepop + $flatpop + $currentpop;
        $this->statRepo->AdjustGamePopularity($gameId, $gamepop);
        
        //$this->seasonRepo->RecordSeasonRace($savedRaceId);
        parent::WriteLogExtensive("Flagging race as having ended.");
        $this->SetRaceOver($id);
    }
}
