<?php
class SeasonsController extends Zend_Rest_Controller
{
    private $seasonRepo;
    private $seasonGoalRepo;
    
    public function init()
    {
        $this->seasonRepo = new SRL_Data_SeasonRepository();
        $this->seasonGoalRepo = new SRL_Data_SeasonGoalRepository();
    }
    
    public function indexAction()
    {
        $this->view->seasons = $this->seasonRepo->GetSeasons();
        $this->view->currentSeasonId = $this->seasonRepo->GetActiveSeason();
    }
    
    public function getAction()
    {
        $seasonId = $this->GetRequest()->GetParam("id");
        
        $this->view->season = $this->seasonRepo->GetSeason($seasonId);
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($json->season_name)) {
            $this->seasonRepo->NewSeason($json->season_name);
        }
    }
    
    public function putAction()
    {

    }
    
    public function deleteAction()
    {
        
    }
    
    public function optionsAction()
    {
        
    }
}