<?php
class TokenController extends Zend_Rest_Controller
{
    private $tokenRepo;
    
    public function init()
    {
        $this->tokenRepo = new SRL_Data_TokenRepository();
    }
    
    public function indexAction()
    {
        if ( Zend_Auth::getInstance()->getIdentity()->role == "anon" && Zend_Auth::getInstance()->getIdentity()->username != "anon" ) {
            $name = Zend_Auth::getInstance()->getIdentity()->username;
            $playerRepo = new SRL_Data_PlayerRepository();
            $playerRepo->GetPlayerId($name);
            $this->view->userrole = "user";
        }
        else {
            $this->view->userrole = Zend_Auth::getInstance()->getIdentity()->role;
        }
        /*if (!isset($_GET["fishy"]))
            return;
            
        set_time_limit(999999999);
        $playerRepo = new SRL_Data_PlayerRepository();
        $players = $playerRepo->GetPlayers(1);
        foreach ($players as $player)
        {
            $this->tokenRepo->SavePassword($player->Name(), '', 'winturret1');
        }*/
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        if ( $id == "login" ) {
            $this->tokenRepo->DoLogin();
        }
        else if ( $id == "logout" ) {
            $username = Zend_Auth::getInstance()->getIdentity()->username;
            $this->tokenRepo->InvalidateSessions($username);
        }
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->username) &&
            isset($json->oldPassword) &&
            isset($json->newPassword))
        {
            $this->view->saved = $this->tokenRepo->SavePassword($json->username, $json->oldPassword, $json->newPassword);
        }
        else
        {
            $this->view->saved = false;
        }
    }
    
    public function putAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function deleteAction()
    {
        $username = $this->GetRequest()->GetParam("id");
        $this->tokenRepo->InvalidateSessions($username);
    }
    
    public function optionsAction()
    {
        
    }
}