<?php
class EntrantsController extends Zend_Rest_Controller
{
    private $raceRepo;
    private $entrantRepo;
    
    public function init()
    {
        $this->raceRepo = new SRL_Data_RaceRepository();
        $this->entrantRepo = new SRL_Data_EntrantRepository();
    }
    
    public function indexAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        $this->returnEntrants($id);
    }
    
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        $race = $this->raceRepo->GetRace($id);
        
        $message = isset($json->message) ? $json->message : "";
        
        if (isset($json->enter) 
            && !$race->IsPlayerInRace($json->enter)
            && $race->State() == SRL_Core_RaceState::EntryOpen) {
            
            $this->entrantRepo->AddEntrant($id, $json->enter);
        }
        else if (isset($json->ready)
            && $race->IsPlayerInRace($json->ready)
            && $race->State() == SRL_Core_RaceState::EntryOpen) {
            
            $this->entrantRepo->ReadyEntrant($id, $json->ready);
        }
        else if (isset($json->unready)
            && $race->IsPlayerInRace($json->unready)
            && $race->State() == SRL_Core_RaceState::EntryOpen) {
            
            $this->entrantRepo->UnreadyEntrant($id, $json->unready);
        }
        else if (isset($json->done)
            && $race->IsPlayerInRace($json->done)
            && $race->State() == SRL_Core_RaceState::InProgress) {
            
            $time = time() - $race->Time();
            $this->entrantRepo->DoneEntrant($id, $json->done, $race->FinishedEntrants() + 1, $time, $message);
        }
        else if (isset($json->undone)
            && $race->IsPlayerInRace($json->undone)
            && ($race->State() == SRL_Core_RaceState::InProgress
                || $race->State() == SRL_Core_RaceState::Complete)) {
            
            $this->entrantRepo->UndoneEntrant($id, $json->undone);
        }
        else if (isset($json->forfeit)
            && $race->IsPlayerInRace($json->forfeit)
            && ($race->State() == SRL_Core_RaceState::InProgress
                || $race->State() == SRL_Core_RaceState::Complete)) {
            
            $this->entrantRepo->ForfeitEntrant($id, $json->forfeit, $message);
        }
        else if (isset($json->comment) && isset($json->entrant)
            && $race->State() != SRL_Core_RaceState::EntryOpen
            && $race->State() != SRL_Core_RaceState::EntryClosed) {
            
            $this->entrantRepo->CommentEntrant($id, $json->entrant, $json->comment);
        }
        else if (isset($json->disqualify)
            && $race->IsPlayerInRace($json->disqualify)
            && ($race->State() == SRL_Core_RaceState::InProgress
                || $race->State() == SRL_Core_RaceState::Complete)) {
            
            $this->entrantRepo->DisqualifyEntrant($id, $json->disqualify, $message);
        }
        
        $this->returnEntrants($id);
    }
    
    public function deleteAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        if (isset($json->entrant))
            $this->entrantRepo->RemoveEntrant($id, $json->entrant);
        
        $this->returnEntrants($id);
    }
    
    private function returnEntrants($id)
    {
        $entrants = $this->entrantRepo->GetEntrants($id);
        $entrantCount = $this->entrantRepo->CountEntrants($id);
        
        $this->view->entrants = $entrants;
        $this->view->entrantCount = $entrantCount;
        
        $this->view->lower = !is_null($this->GetRequest()->GetParam("lower"));
    }
    
    public function optionsAction()
    {
        
    }
}