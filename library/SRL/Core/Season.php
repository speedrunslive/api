<?php
class SRL_Core_Season
{
    private $id;
    private $name;
    private $start_date;
    private $end_date;
    private $goals;
    
    function __construct($id, $name, $start_date, $end_date, $goals)
    {
        $this->id = $id;
        $this->name = $name;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->goals = $goals;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Name()
    {
        return $this->name;
    }
    
    public function StartDate()
    {
        return $this->start_date;
    }

    public function EndDate()
    {
        return $this->end_date;
    }
    
    public function Goals()
    {
        return $this->goals;
    }
}