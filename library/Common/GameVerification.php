<?php
class Common_GameVerification extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $gameRepo = new SRL_Data_GameRepository();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        if (($controller == "leaderboard" && $action != "index")
            || ($controller == "games" && $action != "put" && $action != "index")
            || $controller == "rules")
        {
            $gameAbbrev = $this->getRequest()->getParam("id");
            $game = $gameRepo->GetGame($gameAbbrev);
            
            if ($game->Id() == 0)
            {
                $this->_redirectNotFound($request);
            }
        }
        
        $gameAbbrev = $this->getRequest()->getParam("game");
        $game = $gameRepo->GetGame($gameAbbrev);
        
        if ($gameAbbrev == null)
            return;
        
        if ($game->Id() == 0)
        {
            $this->_redirectNotFound($request);
        }
    }
    
    protected function _redirectNotFound(Zend_Controller_Request_Abstract $request)
    {
        if ($request->getParam('id') == 'notfound')
        {
            return;
        }
        $redir = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
        $redir->setGotoRoute(array(), 'notfound', true);
        $redir->redirectAndExit();
    }
}