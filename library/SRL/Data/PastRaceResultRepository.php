<?php
class SRL_Data_PastRaceResultRepository extends SRL_Data_BaseRepository
{
    private $playerRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    private function GetRaceResultsHelper($where)
    {
        $raceResults = array();
        
        $results = $this->Select("SELECT race_link_id, races_race_id, players_player_id, place, time, message, rl.old_rating, rl.new_rating, srr.old_rating as season_old_rating, srr.new_rating as season_new_rating FROM race_link rl LEFT JOIN season_race_results srr on srr.race_id = races_race_id and srr.player_id = players_player_id $where ORDER BY place ASC");
        foreach ($results as $result)
        {
            $player = $this->playerRepo->GetPlayerById($result["players_player_id"]);
            
            $raceResult = new SRL_Core_PastRaceResult($result["race_link_id"], $result["races_race_id"], $player, $result["place"], $result["time"], $result["message"], $result["old_rating"] * 40, $result["new_rating"] * 40, $result["season_old_rating"] * 40, $result["season_new_rating"] * 40);
            array_push($raceResults, $raceResult);
        }
        
        return $raceResults;
    }
    
    public function GetRaceResults($race_id)
    {
        return $this->GetRaceResultsHelper("WHERE races_race_id = '$race_id'");
    }
    
    public function GetRaceResult($race_link_id)
    {
        $results = $this->GetRaceResultsHelper("WHERE race_link_id = $race_link_id");
        return $results[0];
    }
}