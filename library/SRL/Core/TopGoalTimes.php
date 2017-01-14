<?php
class SRL_Core_TopGoalTimes
{
    private $goal;
    private $topTimes;
    
    function __construct($goal)
    {
        $this->goal = $goal;
        $this->topTimes = array();
    }
    
    public function AddTopTime($topTime)
    {
        array_push($this->topTimes, $topTime);
    }
    
    public function Goal()
    {
        return $this->goal;
    }
    
    public function TopTimes()
    {
        return $this->topTimes;
    }
}