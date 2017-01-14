<?php
class RulesController extends Zend_Rest_Controller

{
	private $gameRepo;
	
	public function init()
	{
		$this->gameRepo = new SRL_Data_GameRepository();
	}
    
	public function indexAction()
	{
        
	}
	public function getAction()
    {
		$id = $this->GetRequest()->GetParam("id");
        $game = $this->gameRepo->GetGame($id);
        
        $this->view->game = $game;
    }
    
    public function postAction()
    {
		
    }
    
    public function putAction()
    {
		
    }
    
    public function deleteAction()
    {
		
    }
}

?>