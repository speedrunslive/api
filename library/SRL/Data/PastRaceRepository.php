<?php
class SRL_Data_PastRaceRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    private function GetRacesHelper($where, $page, $pageSize)
    {
        $races = array();
        $page = intval($page);
        $pageSize = intval($pageSize);
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT r.race_id, game_game_id, goal_goal_id, race_goal, race_date FROM races r $where ORDER BY race_date DESC LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $raceResultRepo = new SRL_Data_PastRaceResultRepository();
            $raceResults = $raceResultRepo->GetRaceResults($result["race_id"]);
            
            $rankedRepo = new SRL_Data_RankedResultRepository();
            $rankedResults = $rankedRepo->GetRankedResults($result["race_id"]);
            
            $gameRepo = new SRL_Data_GameRepository();
            $game = $gameRepo->GetGameById($result["game_game_id"]);
            
            $race = new SRL_Core_PastRace($result["race_id"], $game, $result["goal_goal_id"], $result["race_goal"], $result["race_date"], $raceResults, $rankedResults);
            array_push($races, $race);
        }
        
        return $races;
    }
    
    public function GetRaces($page, $pageSize)
    {
        return $this->GetRacesHelper("", $page, $pageSize);
    }
    
    public function GetRacesForGame($game_id, $page, $pageSize)
    {
        $game_id = intval($game_id);
        return $this->GetRacesHelper("WHERE game_game_id = $game_id", $page, $pageSize);
    }
    
    public function GetRacesForSeasonAndGame($season, $game_id, $page, $pageSize)
    {
        $season = intval($season);
        $game_id = intval($game_id);
        return $this->GetRacesHelper("WHERE game_game_id = $game_id AND season_id = $season ", $page, $pageSize);
    }
    
    public function GetRacesForPlayer($player_id, $page, $pageSize)
    {
        $player_id = intval($player_id);
        return $this->GetRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id", $page, $pageSize);
    }
    
    public function GetRacesForSeasonAndPlayer($season, $player_id, $page, $pageSize)
    {
        $season = intval($season);
        $player_id = intval($player_id);
        return $this->GetRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND season_id = $season ", $page, $pageSize);
    }
    
    public function GetRacesForSeasonAndPlayerAndGame($season, $player_id, $game_id, $page, $pageSize)
    {
        $season = intval($season);
        $player_id = intval($player_id);
        $game_id = intval($game_id);
        return $this->GetRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND season_id = $season AND game_game_id = $game_id ", $page, $pageSize);
    }
    
    public function GetRacesForPlayerAndGame($player_id, $game_id, $page, $pageSize)
    {
        $player_id = intval($player_id);
        $game_id = intval($game_id);
        return $this->GetRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND game_game_id = $game_id", $page, $pageSize);
    }
    
    public function GetRacesForSeasonGoal($season_goal_id, $page, $pageSize)
    {
        $season_goal_id = intval($season_goal_id);
        return $this->GetRacesHelper(" INNER JOIN season_race s ON r.race_id = s.race_id WHERE season_goal_id = $season_goal_id ", $page, $pageSize);
    }
    
    public function GetRacesForSeason($season, $page, $pageSize)
    {
        $season = intval($season);
        return $this->GetRacesHelper(" WHERE season_id = $season ", $page, $pageSize);
    }
    
    public function GetRacesForSeasonGoalAndPlayer($season_goal_id, $player_id, $page, $pageSize)
    {
        $season_goal_id = intval($season_goal_id);
        $player_id = intval($player_id);
        return $this->GetRacesHelper(" INNER JOIN season_race s ON r.race_id = s.race_id INNER JOIN race_link on r.race_id = races_race_id WHERE season_goal_id = $season_goal_id AND players_player_id = $player_id", $page, $pageSize);
    }
    
    public function CountRaces()
    {
        return $this->CountRacesHelper("");
    }
    
    public function CountRacesForGame($game_id)
    {
        $game_id = intval($game_id);
        return $this->CountRacesHelper("WHERE game_game_id = $game_id");
    }
    
    public function CountRacesForSeasonAndGame($season, $game_id)
    {
        $season = intval($season);
        $game_id = intval($game_id);
        return $this->CountRacesHelper("WHERE game_game_id = $game_id AND season_id = $season ");
    }
    
    public function CountRacesForPlayer($player_id)
    {
        $player_id = intval($player_id);
        return $this->CountRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id");
    }
    
    public function CountRacesForSeasonAndPlayer($season, $player_id)
    {
        $season = intval($season);
        $player_id = intval($player_id);
        return $this->CountRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND season_id = $season ");
    }
    
    public function CountRacesForSeasonAndPlayerAndGame($season, $player_id, $game_id)
    {
        $season = intval($season);
        $player_id = intval($player_id);
        $game_id = intval($game_id);
        return $this->CountRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND season_id = $season AND game_game_id = $game_id ");
    }
    
    public function CountRacesForPlayerAndGame($player_id, $game_id)
    {
        $player_id = intval($player_id);
        $game_id = intval($game_id);
        return $this->CountRacesHelper("INNER JOIN race_link ON races_race_id = race_id WHERE players_player_id = $player_id AND game_game_id = $game_id");
    }
    
    public function CountRacesForSeasonGoal($season_goal_id)
    {
        $season_goal_id = intval($season_goal_id);
        return $this->CountRacesHelper("INNER JOIN season_race s ON r.race_id = s.race_id WHERE season_goal_id = $season_goal_id");
    }
    
    public function CountRacesForSeason($season)
    {
        $season = intval($season);
        return $this->CountRacesHelper("WHERE season_Id = $season");
    }
    
    public function CountRacesForSeasonGoalAndPlayer($season_goal_id, $player_id)
    {
        $season_goal_id = intval($season_goal_id);
        $player_id = intval($player_id);
        return $this->CountRacesHelper("INNER JOIN season_race s ON r.race_id = s.race_id INNER JOIN race_link on r.race_id = races_race_id WHERE season_goal_id = $season_goal_id AND players_player_id = $player_id");
    }
    
    private function CountRacesHelper($where)
    {
        $results = $this->Select("SELECT count(*) as count FROM races r $where;");
        return $results[0]["count"];
    }
    
    public function GetRace($race_id)
    {
        $race_id = intval($race_id);
        $race = $this->GetRacesHelper("WHERE race_id = $race_id", 1, 1);
        if (count($race) < 1)
        {
            return null;
        }
        
        return $race[0];
    }
}