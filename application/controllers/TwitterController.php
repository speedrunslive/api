<?php
class TwitterController extends Zend_Rest_Controller
{
    private $twitterRepo;
    
    public function init()
    {
        $this->twitterRepo = new SRL_Data_TwitterRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        $twitter = $this->twitterRepo->GetTwitter($player);
        
        $this->view->player = $player;
        $this->view->twitter = $twitter;
    }
    
    public function postAction()
    {
    
    }
    
    public function putAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($player) && isset($json->twitter)) {
            $this->twitterRepo->SetTwitter($player, $json->twitter);
            
            $this->view->player = $player;
            $this->view->twitter = $json->twitter;
        }
    }
    
    public function deleteAction()
    {
    
    }
    
    public function optionsAction()
    {
        
    }
}