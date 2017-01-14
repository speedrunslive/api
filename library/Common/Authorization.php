<?php
class Common_Authorization extends Zend_Controller_Plugin_Abstract
{
    protected $defaultRole = "anon";
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        $acl = Zend_Registry::get('acl');
        $mysession = new Zend_Session_Namespace('mysession');
        
        $identity = $auth->getIdentity();
        $actionHash = $request->getControllerName() . '::' . $request->getActionName();
        
        if (!$acl->isAllowed($identity->role, $actionHash))
        {
            $this->_redirectNoAuth($request);
        }
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