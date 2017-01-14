<?php
class RtatimesController extends Zend_Rest_Controller
{
    private $rtalbRepo;
    private $rtatimeRepo;
    private $playerRepo;
    
    public function init()
    {
        $this->rtalbRepo = new SRL_Data_RtaleaderboardRepository();
        $this->rtatimeRepo = new SRL_Data_RtatimesRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $tags = $this->GetRequest()->GetParam("tags");
        
        $this->view->times = $this->rtatimeRepo->GetLeaderboardTimesWithTags($id, $tags);
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $lb = $this->rtalbRepo->GetLeaderboard($id);
        $player = $this->playerRepo->GetPlayer($json->player);
        
        if ($lb->Id() != 0
            && $player->Id() != 0
            && isset($json->time)
            && isset($json->video)
            && isset($json->notes)
            && isset($json->day)
            && isset($json->month)
            && isset($json->year)
            && isset($json->tags))
        {
            $this->rtatimeRepo->AddRtaTime($id, $player->Id(), $json->time, $json->video, $json->notes, $json->day, $json->month, $json->year, $json->tags);
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