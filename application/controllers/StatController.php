<?php
class StatController extends Zend_Rest_Controller
{
    private $statRepo;
    private $gameRepo;
    private $playerRepo;
    private $raceRepo;
    private $seasonRepo;
    
    public function init()
    {
        $this->statRepo = new SRL_Data_StatRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
        $this->raceRepo = new SRL_Data_PastRaceRepository();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
    }
    
    public function indexAction()
    {
        if (isset($_GET["game"]) && isset($_GET["player"]) && isset($_GET["season"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            
            $gameId = $game->Id();
            $playerId = $player->Id();
            
            $this->view->player = $player;
            $this->view->game = $game;
            $this->view->stat = $this->statRepo->GetPlayerGameStatsForSeason($playerId, $gameId, $_GET["season"]);
            
            $this->view->type = "playergame";
        }
        else if (isset($_GET["game"]) && isset($_GET["player"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            
            $gameId = $game->Id();
            $playerId = $player->Id();
            
            $this->view->player = $player;
            $this->view->game = $game;
            $this->view->stat = $this->statRepo->GetPlayerGameStats($playerId, $gameId);
            
            $this->view->type = "playergame";
        }
        else if (isset($_GET["game"]) && isset($_GET["season"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $gameId = $game->Id();
            $stat = $this->statRepo->GetGameStatsForSeason($gameId, $_GET["season"]);
            
            $this->view->game = $game;
            $this->view->stat = $stat;
            
            $this->view->type = "game";
        }
        else if (isset($_GET["game"])) {
            $game = $this->gameRepo->GetGame($_GET["game"]);
            $gameId = $game->Id();
            $stat = $this->statRepo->GetGameStats($gameId);
            
            $this->view->game = $game;
            $this->view->stat = $stat;
            
            $this->view->type = "game";
        }
        else if (isset($_GET["player"]) && isset($_GET["season"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            $playerId = $player->Id();
            $stat = $this->statRepo->GetPlayerStatsForSeason($playerId, $_GET["season"]);
            
            $this->view->player = $player;
            $this->view->rank = 0;
            $this->view->stat = $stat;
                       
            $this->view->type = "player";
        }
        else if (isset($_GET["player"]) && isset($_GET["seasongoal"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            $playerId = $player->Id();
            $stat = $this->statRepo->GetPlayerStatsForSeasonGoal($playerId, $_GET["seasongoal"]);
            
            $this->view->player = $player;
            $this->view->rank = 0;
            $this->view->stat = $stat;
                       
            $this->view->type = "player";
        }
        else if (isset($_GET["player"])) {
            $player = $this->playerRepo->GetPlayer($_GET["player"]);
            $playerId = $player->Id();
            $stat = $this->statRepo->GetPlayerStats($playerId);
            
            $this->view->player = $player;
            $this->view->rank = 0;
            $this->view->stat = $stat;
                       
            $this->view->type = "player";
        }
        else if (isset($_GET["season"])) {
            $stat = $this->statRepo->GetOverallStatsForSeason($_GET["season"]);
            $this->view->stat = $stat;
            $this->view->type = "overall";
        }
        else if (isset($_GET["seasongoal"])) {
            $stat = $this->statRepo->GetOverallStatsForSeasonGoal($_GET["seasongoal"]);
            $this->view->stat = $stat;
            $this->view->type = "overall";
        }
        else {
            $stat = $this->statRepo->GetOverallStats();
            $this->view->stat = $stat;
            $this->view->type = "overall";
        }
    }
    
    public function getAction()
    {
        $this->view->stat = $this->statRepo->GetAllMonthlyStats();
    }
    
    public function putAction()
    {
    
    }
    
    public function postAction()
    {
    
    }
    
    public function deleteAction()
    {
    
    }
}
