<?php
class RatingsController extends Zend_Rest_Controller
{
    private $ratingRepo;
    private $playerRepo;
    
    public function init()
    {
        $this->ratingRepo = new SRL_Data_RatingRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    public function indexAction()
    {
        $this->view->champion = $this->ratingRepo->GetRandomChampion();
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $player = $this->playerRepo->GetPlayer($id);
        $games = NULL;
        
        if (isset($_GET["season"])) {
            $games = $this->ratingRepo->GetPlayerRatingsForSeason($player->Id(), $_GET["season"]);
        }
        else {
            $games = $this->ratingRepo->GetPlayerRatings($player->Id());
        }
        
        $this->view->games = $games;
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        $this->view->ratings = $this->ratingRepo->CalculateNewRatings($json);
    }
    
    public function putAction()
    {
        
    }
    
    public function deleteAction()
    {
        
    }
    
    public function optionsAction()
    {
        
    }
}