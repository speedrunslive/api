<?php
class TagsController extends Zend_Rest_Controller
{
    private $tagRepo;
    
    public function init()
    {
        $this->tagRepo = new SRL_Data_TagRepository();
    }
    
    public function indexAction()
    {
        $this->view->tagGroups = $this->tagRepo->GetTagGroups();
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        $this->view->tagGroup = $this->tagRepo->GetTagGroup($id);
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->name))
        {
            $this->tagRepo->AddTagGroup($json->name);
        }
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $tagGroup = $this->tagRepo->GetTagGroup($id);
        if (!is_null($tagGroup)
            && isset($json->name))
        {
            $this->tagRepo->AddTag($tagGroup->Id(), $json->name);
        }
    }
    
    public function deleteAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->id))
        {
            $this->tagRepo->DeleteTag($json->id);
        }
    }
    
    public function optionsAction()
    {
    
    }
}