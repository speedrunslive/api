<?php
class SRL_Core_PastRace
{
    private $id;
    private $game;
    private $goal_id;
    private $race_goal;
    private $race_date;
    private $raceResults;
    private $rankedResults;
    
    function __construct($id, $game, $goal_id, $race_goal, $race_date, $raceResults, $rankedResults)
    {
        $this->id = $id;
        $this->game = $game;
        $this->goal_id = $goal_id;
        $this->race_goal = $race_goal;
        $this->race_date = $race_date;
        $this->raceResults = $raceResults;
        $this->rankedResults = $rankedResults;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Game()
    {
        return $this->game;
    }
    
    public function GoalId()
    {
        return $this->goal_id;
    }
    
    public function RaceGoal()
    {
        return $this->race_goal;
    }
    
    public function RaceDate()
    {
        return date("M d, Y", $this->race_date);
    }
    
    public function RaceMonth()
    {
        return date("n", $this->race_date);
    }
    
    public function RaceYear()
    {
        return date("Y", $this->race_date);
    }
    
    public function RaceTime()
    {
        return date("G:i:s", $this->race_date);
    }
    
    public function RawTime()
    {
        return $this->race_date;
    }
    
    public function RaceResults()
    {
        return $this->raceResults;
    }
    
    public function RankedResults()
    {
        return $this->rankedResults;
    }
}