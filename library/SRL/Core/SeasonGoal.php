<?php
class SRL_Core_SeasonGoal
{
    private $id;
    private $goal;
    private $ranks;
    private $unranks;
    
    function __construct($id, $goal, $ranks, $unranks)
    {
        $this->id = $id;
        $this->goal = $goal;
        $this->ranks = $ranks;
        $this->unranks = $unranks;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Goal()
    {
        return $this->goal;
    }
    
    public function Ranks()
    {
        return $this->ranks;
    }
    
    public function Unranks()
    {
        return $this->unranks;
    }
}