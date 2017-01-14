<?php

class GoalsController extends Zend_Controller_Action
{
    private $gameRepo;
    private $goalRepo;
    private $seasonRepo;
    
    public function init()
    {
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->goalRepo = new SRL_Data_GoalRepository();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
    }
    
    public function indexAction()
    {
        
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $game = $this->gameRepo->GetGame($id);
        
        $season = $this->seasonRepo->GetActiveSeason();
        if (isset($_GET["season"])) {
            $season = $_GET["season"];
        }
        
        $topgoals = $this->goalRepo->GetTopGoalsAndTimesForGame($game->Id(), $season);
        
        $this->view->game = $game;
        $this->view->topgoals = $topgoals;
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        $game = $this->gameRepo->GetGame($id);
        
        if ($game->Id() != 0 && isset($json->goal)) {
            $this->goalRepo->CreateGoal($game->Id(), trim($json->goal));
        }
        
        $this->view->game = $game;
        $this->view->goal = $json->goal;
    }
    
    public function postAction()
    {
        
    }
    
    public function deleteAction()
    {
        
    }
    
    public function optionsAction()
    {
        
    }
}

