<?php
class LeaderboardController extends Zend_Rest_Controller
{
    private $ratingRepo;
    private $gameRepo;
    private $statRepo;
    private $seasonsRatingRepo;
    
    public function init()
    {
        $this->ratingRepo = new SRL_Data_RatingRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->statRepo = new SRL_Data_StatRepository();
        $this->seasonsRatingRepo = new SRL_Data_SeasonRatingRepository();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
    }
    
    public function indexAction()
    {
        // $page = $this->GetRequest()->GetParam("page");
        // if (empty($page))
        //     $page = 1;
            
        // $pageSize = $this->GetRequest()->GetParam("pageSize");
        // if (empty($pageSize))
        //     $pageSize = 100;
            
        $sortField = $this->GetRequest()->GetParam("sortField");
        if (empty($sortField))
            $sortField = 1;
            
        // $order = $this->GetRequest()->GetParam("order");
        // if (empty($order))
        //     $order = "DESC";
        
        // $this->view->stats = $this->statRepo->GetAllPlayerStats($sortField, $order, $page, $pageSize);

        $this->view->stattype = $sortField;
    }
    
    public function getAction()
    {
        $gameAbbrev = $this->GetRequest()->GetParam("id");
        
        $game = $this->gameRepo->GetGame($gameAbbrev);
        $this->view->game = $game;
        
        if (isset($_GET["season"]) && $_GET["season"] != 0)
        {
            $game_id = $game->Id();
            $this->view->leaders = $this->seasonsRatingRepo->GetSeasonLeaderboardForGame($_GET["season"], $game_id);
            $this->view->leadersCount = count($this->view->leaders);
            $this->view->unranked = $this->seasonsRatingRepo->GetSeasonUnrankedLeaderboardForGame($_GET["season"], $game_id);
            $this->view->unrankedCount = count($this->view->unranked);
        }
        else
        {
            $game_id = $game->Id();
            $this->view->leaders = $this->ratingRepo->GetGameLeaderboard($game_id);
            $this->view->leadersCount = count($this->view->leaders);
            $this->view->unranked = $this->ratingRepo->GetGameUnrankedLeaderboard($game_id);
            $this->view->unrankedCount = count($this->view->unranked);
        }
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
