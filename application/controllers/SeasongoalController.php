<?php
class SeasongoalController extends Zend_Rest_Controller
{
    private $seasonGoalRepo;
    private $seasonRatingRepo;
    
    public function init()
    {
        $this->seasonGoalRepo = new SRL_Data_SeasonGoalRepository();
        $this->seasonRatingRepo = new SRL_Data_SeasonGoalsRatingRepository();
    }
    
    public function indexAction()
    {
    
    }
    
    public function getAction()
    {
        $seasonGoalId = $this->GetRequest()->GetParam("id");
        
        $goal = $this->seasonGoalRepo->GetSeasonGoal($seasonGoalId);
        
        $this->view->goal = $goal;
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($json->season_id) && isset($json->goal_id)) {
            $this->seasonGoalRepo->AddGoalToSeason($json->season_id, $json->goal_id);
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