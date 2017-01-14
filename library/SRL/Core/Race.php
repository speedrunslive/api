<?php
class SRL_Core_Race
{
    private $id;
    private $game;
    private $goal;
    private $time;
    private $state;
    private $entrants;
    private $filename;
    
    function __construct($id, $game, $goal, $time, $state, $entrants, $filename)
    {
        $this->id = $id;
        $this->game = $game;
        $this->goal = $goal;
        $this->time = $time;
        $this->state = $state;
        $this->entrants = $entrants;
        $this->filename = $filename;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Game()
    {
        return $this->game;
    }
    
    public function Goal()
    {
        return $this->goal;
    }
    
    public function Time()
    {
        return intval($this->time);
    }
    
    public function State()
    {
        return intval($this->state);
    }
    
    public function StateText()
    {
        switch ($this->state) {
            case SRL_Core_RaceState::EntryOpen:
                 return "Entry Open";
            case SRL_Core_RaceState::EntryClosed:
                return "Entry Closed";
            case SRL_Core_RaceState::InProgress:
                return "In Progress";
            case SRL_Core_RaceState::Complete:
                return "Complete";
            case SRL_Core_RaceState::RaceOver:
                return "Race Over";
            default:
                return "Unknown";
        }
    }
    
    public function Entrants()
    {
        return $this->entrants;
    }
    
    public function FinishedEntrants()
    {
        $numFinished = 0;
        
        foreach ($this->entrants as $entrant)
            if ($entrant->Time() > 0)
                $numFinished++;
        
        return $numFinished;
    }
    
    public function IsPlayerInRace($name)
    {
        foreach ($this->entrants as $entrant)
            if (strtolower ($entrant->Player()->Name()) == strtolower ($name))
                return true;
                
        return false;
    }
    
    public function IsEveryoneReady()
    {
        if (count($this->entrants) < 2)
            return false;
            
        foreach ($this->entrants as $entrant)
            if ($entrant->Place() != SRL_Core_EntrantState::Ready)
                return false;
                
        return true;
    }
    
    public function IsEveryoneDone()
    {
        foreach ($this->entrants as $entrant)
            if ($entrant->Place() == SRL_Core_EntrantState::Ready
                || $entrant->Place() == SRL_Core_EntrantState::Entered)
                return false;
                
        return true;
    }
    
    public function Filename()
    {
        return ($this->filename == 0) ? "" : "true";
    }
}