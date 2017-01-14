<?php
class RtaleaderboardsController extends Zend_Rest_Controller
{
    private $rtaRepo;
    private $gameRepo;
    
    public function init()
    {
        $this->rtaRepo = new SRL_Data_RtaleaderboardRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        $game = $this->gameRepo->GetGame($id);
        
        $lbs = $this->rtaRepo->GetLeaderboardsForGame($game->Id());
        
        $this->view->lbs = $lbs;
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $game = $this->gameRepo->GetGame($id);
        
        if ($game->Id() != 0
            && isset($json->name)
            && isset($json->rules)
            && isset($json->description)
            && isset($json->timing_start)
            && isset($json->timing_end))
        {
            $this->rtaRepo->CreateLeaderboard($json->name, $game->Id(), $json->rules, $json->timing_start, $json->timing_end, $json->description);
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