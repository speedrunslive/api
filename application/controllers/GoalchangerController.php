<?php

class GoalchangerController extends Zend_Controller_Action
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
        $topgoals = $this->goalRepo->GetTopGoalsForGame($game->Id(), 9999, $this->seasonRepo->GetActiveSeason());
        
        $this->view->game = $game;
        $this->view->topgoals = $topgoals;
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->oldGoal, $json->newGoal)) {
            $this->goalRepo->ReassignGoal($json->oldGoal, $json->newGoal);
        }
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

