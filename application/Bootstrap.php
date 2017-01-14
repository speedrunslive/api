<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRestRoute()
    {
        $this->bootstrap('frontController');
        $frontController = Zend_Controller_Front::getInstance();
        $restRoute = new Zend_Rest_Route($frontController);
        $frontController->getRouter()->addRoute('default', $restRoute);
    }
    
    protected function _initPlugins()
    {
        Zend_Registry::set('acl', new Common_Acl());
        
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Common_GameVerification());
        $front->registerPlugin(new Common_PlayerVerification());
        $front->registerPlugin(new Common_Authentication());
        $front->registerPlugin(new Common_Authorization());
        $front->registerPlugin(new Common_Minify());
        $front->registerPlugin(new Common_AuditLog());
    }
}

