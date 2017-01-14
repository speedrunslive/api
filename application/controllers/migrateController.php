<?php
class MigrateController extends Zend_Rest_Controller
{
    private $ratingRepo;
    private $pastRaceRepo;
    private $gameRepo;
    private $playerRepo;
    private $statRepo;
    
    public function init()
    {
        $this->ratingRepo = new SRL_Data_RatingRepository();
        $this->pastRaceRepo = new SRL_Data_PastRaceRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
        $this->statRepo = new SRL_Data_StatRepository();
    }
    
    public function indexAction()
    {
        if (!isset($_GET["fishy"]))
            return;
         
        $games = $this->gameRepo->GetGames(1);
        foreach ($games as $game)
        {
            $gameId = $game->Id();
        }
        
        $players = $this->playerRepo->GetPlayers(1);
        foreach ($players as $player)
        {
            $playerId = $player->Id();
        }
        
        $invalidRaces = array(10302, 10406, 10474, 10476, 10515, 10547, 10564, 10642, 10690, 10724, 10727, 10787, 10917, 10970, 11046, 11163, 11244, 11285, 11512, 11513, 11514, 11515, 11575, 11576, 11854, 11963, 12059, 12121, 12125, 12191, 12388, 12479, 12496, 12558, 12737, 12843, 12856, 12940, 12980, 13018, 13026, 13040, 13042, 13063, 13078, 13111, 13129, 13144, 13151, 13152, 13179, 13180, 13307, 13350, 13390, 13431, 13473, 13483, 13679, 13682, 13683, 13742, 13743, 13744, 13746, 13748, 13749, 13751, 13753, 13754, 13759, 13811, 13817, 14000, 14088, 14090, 14094, 14097, 14155, 14300, 14553, 14554, 14556, 14626, 14645, 14647, 15210, 15214, 15288, 15361, 15417, 15679, 16061, 16064, 16530, 16700, 16702, 16703, 16757, 17130, 17136, 17563, 17751, 17759, 18447, 19496, 11574);
        
        set_time_limit(999999999);
        for ($i = 999999999; $i <= 18987; $i++)
        {
            if (in_array($i, $invalidRaces)) //empty sotn race+deleted misc races
                continue;
            
            $pastRace = $this->pastRaceRepo->GetRace($i);

            $currentRatings = $this->ratingRepo->GetCurrentRatings($pastRace->Game(), $pastRace->RaceResults());
            
            $orderedRatings = array();
            foreach ($pastRace->RaceResults() as $raceResult) {
                $name = strtolower($raceResult->Player()->Name());
                $currentRating = $currentRatings["$name"];
                $currentRating->place = $raceResult->Place();
                $currentRating->name = $name;
                
                $orderedRatings[] = $currentRating;
                
                unset($currentRating);
                unset($entrant);
            }
            
            $newRatings = $this->ratingRepo->CalculateNewRatings($orderedRatings);
            
            $this->ratingRepo->MigrateRaceToTrueSkill($i, $orderedRatings, $newRatings);
            
            $gameId = $pastRace->Game()->Id();
            $goal = $pastRace->RaceGoal();
            $this->ratingRepo->MigrateGoal($i, $gameId, $goal);
        }
        
        $this->UpdateMonthy(2009);
        $this->UpdateMonthy(2010);
        $this->UpdateMonthy(2011);
        $this->UpdateMonthy(2012);
    }
    
    public function getAction()
    {
        
    }
    
    public function postAction()
    {
        
    }
    
    public function putAction()
    {
        
    }
    
    public function deleteAction()
    {
        
    }
    
    private function UpdateMonthy($year)
    {
        $month = 1;
        while ($month <= 12)
        {
            $this->statRepo->UpdateMonthlyStats($month, $year);
            
            $month++;
        }
    }
}