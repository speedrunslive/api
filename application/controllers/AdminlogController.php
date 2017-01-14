<?php
class AdminlogController extends Zend_Rest_Controller

{
	private $adminLogRepo;
	private $streamprefsRepo;
	
	public function init()
	{
		$this->adminLogRepo = new SRL_Data_AdminLogRepository();
		$this->streamprefsRepo = new SRL_Data_StreamprefsRepository();
	}
    
	public function indexAction()
	{
		$page = 1;
        if (isset($_GET["page"])) {
            $page = $_GET["page"];
        }
        $search = "";
        if ( isset($_GET['search']) ) {
        	$search = $_GET['search'];
        }
        $role = Zend_Auth::getInstance()->getIdentity()->role;
        if ( $role == "admin" ) {
			$this->view->logCount = $this->adminLogRepo->GetCount($search);
			$this->view->entries = $this->adminLogRepo->GetEntries($page - 1, $search);
		}
		else {
			$this->view->logCount = $this->adminLogRepo->GetPurgeCount($search);
			$this->view->entries = $this->adminLogRepo->GetPurgeEntries($page - 1, $search);
		}
	}
	public function getAction()
    {
		$id = $this->GetRequest()->GetParam("id");
		if ( $id == "purges" ) {
			$this->view->purges = $this->streamprefsRepo->GetLocks();
		}
		return true;
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
