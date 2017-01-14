<?php
class Common_Minify extends Zend_Controller_Plugin_Abstract
{
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $body = $this->getResponse()->getBody();
        $this->getResponse()->clearBody();
        $body = str_replace('    ', '', $body);
        $body = str_replace("\r\n", '', $body);
        
        $this->getResponse()->setBody($body);
    }
}