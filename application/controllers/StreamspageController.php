<?php
class StreamspageController extends Zend_Rest_Controller
{
    private $adminLogRepo;
    private $streamRepo;
    
    public function init()
    {
        $this->adminLogRepo = new SRL_Data_AdminLogRepository();
        $this->streamRepo = new SRL_Data_StreamRepository();
    }
    
    public function indexAction()
    {
        $page = $this->GetRequest()->GetParam("page") ?: 1;
        $api = $this->GetRequest()->GetParam("api");
        $this->view->count = $this->streamRepo->CountStreams($api);
        if ( $api != "" ) {
            $this->view->streams = $this->streamRepo->GetStreamsPage($page, $api);
        }
        else {
            $this->view->streams = $this->streamRepo->GetStreamsPage($page);
        }
    }
    
    public function getAction()
    {
        
    }
    
    public function postAction()
    {

    }
    
    public function putAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        switch ( $_SERVER['REQUEST_METHOD'] ) { // Zend interperts any POST with an ID as a PUT - override this
            case "PUT":
                $this->streamRepo->WhitelistStream($player);
                $username = Zend_Auth::getInstance()->getIdentity()->username;
                if ( $username == "racebot" ) {
                    $username = json_decode($this->GetRequest()->GetRawBody())->source;
                }
                $this->adminLogRepo->LogAction($username, 'whitelisted', $player);
                break;
            case "POST":
                $this->streamRepo->UnwhitelistStream($player);
                $username = Zend_Auth::getInstance()->getIdentity()->username;
                if ( $username == "racebot" ) {
                    $username = json_decode($this->GetRequest()->GetRawBody())->source;
                }
                $this->adminLogRepo->LogAction($username, 'unwhitelisted', $player);
        }
    }
    
    public function deleteAction()
    {
        $player = $this->GetRequest()->GetParam("id");
        $this->streamRepo->BlacklistStream($player);
        $username = Zend_Auth::getInstance()->getIdentity()->username;
        if ( $username == "racebot" ) {
            $username = json_decode($this->GetRequest()->GetRawBody())->source;
        }
        $this->adminLogRepo->LogAction($username, 'blacklisted', $player);
    }
    
    public function optionsAction()
    {
        
    }
}