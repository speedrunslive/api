<?php

class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
    
    public function getAction()
    {
        $this->errorHandler();
    }
    
    public function putAction()
    {
        $this->errorHandler();
    }
    
    public function postAction()
    {
        $this->errorHandler();
    }
    
    public function deleteAction()
    {
        $this->errorHandler();
    }
    
    public function indexAction()
    {
        $this->errorHandler();
    }
    
    private function errorHandler()
    {
        $error = $id = $this->GetRequest()->GetParam("id");
        if ($error == "notfound")
        {
            $this->view->errorCode = 404;
            $this->view->errorText = "Not Found";
            $this->getResponse()->setHttpResponseCode(404);
        }
        else if ($error == "noauth")
        {
            $this->view->errorCode = 401;
            $this->view->errorText = "Unauthorized";
            $this->getResponse()->setHttpResponseCode(401);
        }
        else
        {
            $this->view->errorCode = 500;
            $this->view->errorText = "Internal Server Error";
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
    
    public function optionsAction()
    {
        
    }
}

