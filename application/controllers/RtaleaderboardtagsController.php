<?php
class RtaleaderboardtagsController extends Zend_Rest_Controller
{
    private $rtalbRepo;
    
    public function init()
    {
        $this->rtalbRepo = new SRL_Data_RtaleaderboardRepository();
    }
    
    public function indexAction()
    {
        
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        
    }
    
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->tag_group)
            && isset($json->tag_id))
        {
            $this->rtalbRepo->AssignTag($id, $json->tag_group, $json->tag_id);
        }
    }
    
    public function deleteAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->tag_group)
            && isset($json->tag_id))
        {
            $this->rtalbRepo->RemoveTag($id, $json->tag_group, $json->tag_id);
        }
    }
    
    public function optionsAction()
    {
    
    }
}