<?php
class RtamilestonesController extends Zend_Rest_Controller
{
    private $rtalbRepo;
    
    public function init()
    {
        $this->rtalbRepo = new SRL_Data_RtaleaderboardRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
    
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $lb = $this->rtalbRepo->GetLeaderboard($id);
        
        if ($lb->Id() != 0
            && isset($json->time))
        {
            $this->rtalbRepo->AddMilestone($id, $json->time);
        }
    }
    
    public function postAction()
    {
    
    }
    
    public function deleteAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $lb = $this->rtalbRepo->GetLeaderboard($id);
        
        if ($lb->Id() != 0
            && isset($json->time))
        {
            $this->rtalbRepo->RemoveMilestone($id, $json->time);
        }
    }
    
    public function optionsAction()
    {
        
    }
}