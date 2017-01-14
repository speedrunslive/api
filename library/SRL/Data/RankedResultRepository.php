<?php
class SRL_Data_RankedResultRepository extends SRL_Data_BaseRepository
{
    private $playerRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    private function GetRankedResultsHelper($where)
    {
        $raceResults = array();
        
        $results = $this->Select("SELECT player_id, old_rating, new_rating FROM season_race_results $where");
        foreach ($results as $result)
        {
            $player = $this->playerRepo->GetPlayerById($result["player_id"]);
            
            $raceResult = new SRL_Core_RankedResult($player, $result["old_rating"] * 40, $result["new_rating"] * 40);
            array_push($raceResults, $raceResult);
        }
        
        return $raceResults;
    }
    
    public function GetRankedResults($race_id)
    {
        return $this->GetRankedResultsHelper("WHERE race_id = '$race_id'");
    }
}