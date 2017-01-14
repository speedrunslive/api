<?php
class Common_AuditLog extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $action = $request->getActionName();
        if (strcmp($action, "get") == 0
            || strcmp($action, "index") == 0
            || strcmp($action, "options") == 0)
        {
            return;
        }
        
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        
        $username = $identity->username;
        $role = $identity->role;
        $controller = $request->getControllerName();
        $body = $request->getRawBody();
        $uri = $request->getRequestUri();
        
        $q = strrpos($uri, '?');
        
        if ($q !== false)
        {
            $uri = substr($uri, 0, $q);
        }
        
        if ($controller == "token")
        {
            $body = "";
        }
        
        $logRepo = new SRL_Data_AuditLogRepository();
        $logRepo->Log($username, $role, $controller, $action, $body, $uri);
    }
}