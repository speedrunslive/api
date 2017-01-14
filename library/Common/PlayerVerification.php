<?php
class Common_PlayerVerification extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $playerRepo = new SRL_Data_PlayerRepository();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        
        if ($controller == "ratings" && $action != "index")
        {
            $playerName = $this->getRequest()->getParam("id");
            $player = $playerRepo->GetPlayer($playerName);
            
            if ($player->Id() == 0)
            {
                $this->_redirectNotFound($request);
            }
        }
        
        $playerName = $this->getRequest()->getParam("player");
        $player = $playerRepo->GetPlayer($playerName);
        
        if ($playerName == null)
            return;
        
        if ($player->Id() == 0)
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