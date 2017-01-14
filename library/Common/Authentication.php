<?php
class Common_Authentication extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        
        $apiKeyParam = $this->getRequest()->getParam("X-SRL-API-KEY");
        $apiKeyHeader = $this->getRequest()->getHeader("X-SRL-API-KEY");
        
        $apiKey = "";
        
        if (!empty($apiKeyParam))
        {
            $apiKey = $apiKeyParam;
        }
        
        if (!empty($apiKeyHeader))
        {
            $apiKey = $apiKeyHeader;
        }
        
        $tokenRepo = new SRL_Data_TokenRepository();
        $username = $tokenRepo->GetLoggedInUser($apiKey);
        
        $playerRepo = new SRL_Data_PlayerRepository();
        $player = $playerRepo->GetPlayer($username);
        
        $role = 'anon';
        if ($player->Id() != 0)
        {
            $role = $player->Role();
            $playerRepo->UpdateLastSeen($username);
        }
        
        $identity = new stdClass();
        $identity->username = $username;
        $identity->role = $role;
        $auth->getStorage()->write($identity);
    }
    
    protected function _redirectNoAuth(Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam('id') == 'noauth')
        {
            return;
        }
        $redir = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
        $redir->setGotoRoute(array(), 'noauth', true);
        $redir->redirectAndExit();
    }
}