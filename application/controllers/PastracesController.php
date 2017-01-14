<?php
class PastracesController extends Zend_Rest_Controller
{
    private $pastraceRepo;
    private $gameRepo;
    private $playerRepo;
    
    public function init()
    {
        $this->pastraceRepo = new SRL_Data_PastRaceRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    public function indexAction()
    {
        $page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        $pageSize = 20;
        if (isset($_GET["pageSize"])) {
            $pageSize = $_GET["pageSize"];
        }
        
        $pastraces = array();
        $pastracesCount = 0;
        
        if (isset($_GET["season"]) && isset($_GET["game"]) && isset($_GET["player"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            
            $pastraces = $this->pastraceRepo->GetRacesForSeasonAndPlayerAndGame($_GET["season"], $player->Id(), $game->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeasonAndPlayerAndGame($_GET["season"], $player->Id(), $game->Id());
        }
        else if (isset($_GET["game"]) && isset($_GET["player"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            
            $pastraces = $this->pastraceRepo->GetRacesForPlayerAndGame($player->Id(), $game->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForPlayerAndGame($player->Id(), $game->Id());
        }
        else if (isset($_GET["seasongoal"]) && isset($_GET["player"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            $pastraces = $this->pastraceRepo->GetRacesForSeasonGoalAndPlayer($_GET["seasongoal"], $player->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeasonGoalAndPlayer($_GET["seasongoal"], $player->Id());
        }
        else if (isset($_GET["season"]) && isset($_GET["player"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            
            $pastraces = $this->pastraceRepo->GetRacesForSeasonAndPlayer($_GET["season"], $player->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeasonAndPlayer($_GET["season"], $player->Id());
        }
        else if (isset($_GET["season"]) && isset($_GET["game"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            
            $pastraces = $this->pastraceRepo->GetRacesForSeasonAndGame($_GET["season"], $game->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeasonAndGame($_GET["season"], $game->Id());
        }
        else if (isset($_GET["game"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $pastraces = $this->pastraceRepo->GetRacesForGame($game->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForGame($game->Id());
        }
        else if (isset($_GET["player"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            $pastraces = $this->pastraceRepo->GetRacesForPlayer($player->Id(), $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForPlayer($player->Id());
        }
        else if (isset($_GET["seasongoal"])) {
            $pastraces = $this->pastraceRepo->GetRacesForSeasonGoal($_GET["seasongoal"], $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeasonGoal($_GET["seasongoal"]);
        }
        else if (isset($_GET["season"])) {
            $pastraces = $this->pastraceRepo->GetRacesForSeason($_GET["season"], $page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRacesForSeason($_GET["season"]);
        }
        else {
            $pastraces = $this->pastraceRepo->GetRaces($page, $pageSize);
            $pastracesCount = $this->pastraceRepo->CountRaces($page);
        }
        
        $this->view->pastraces = $pastraces;
        $this->view->pastracesCount = $pastracesCount;
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        $this->view->pastrace = $this->pastraceRepo->GetRace($id);
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
}