<?php
class TestController extends Zend_Rest_Controller

{
	private $testRepo;
	
	public function init()
	{
		$this->testRepo = new SRL_Data_TestRepository();
	}
    
	public function indexAction()
	{
	}
	public function getAction()
    {
		return True;
    }
    
    public function postAction()
    {
		return true;
    }
    
    public function putAction()
    {
		return true;
    }
    
    public function deleteAction()
    {
		return true;
    }
}

?>
