<?php
class YoutubeController extends Zend_Rest_Controller
{
    private $youtubeRepo;
    
    public function init()
    {
        $this->youtubeRepo = new SRL_Data_YoutubeRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        $youtube = $this->youtubeRepo->GetYoutube($player);
        
        $this->view->player = $player;
        $this->view->youtube = $youtube;
    }
    
    public function postAction()
    {
    
    }
    
    public function putAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($player) && isset($json->youtube)) {
            $this->youtubeRepo->SetYoutube($player, $json->youtube);
            
            $this->view->player = $player;
            $this->view->youtube = $json->youtube;
        }
    }
    
    public function deleteAction()
    {
    
    }
    
    public function optionsAction()
    {
        
    }
}