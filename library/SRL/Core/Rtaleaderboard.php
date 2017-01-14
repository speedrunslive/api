<?php
class SRL_Core_Rtaleaderboard
{
    private $id;
    private $name;
    private $game;
    private $timing_start;
    private $timing_end;
    private $rules;
    private $description;
    private $milestones;
    private $tags;
    
    function __construct($id, $name, $game, $timing_start, $timing_end, $rules, $description, $milestones, $tags)
    {
        $this->id = $id;
        $this->name = $name;
        $this->game = $game;
        $this->timing_start = $timing_start;
        $this->timing_end = $timing_end;
        $this->rules = $rules;
        $this->description = $description;
        $this->milestones = $milestones;
        $this->tags = $tags;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Name()
    {
        return $this->name;
    }
    
    public function Game()
    {
        return $this->game;
    }

    public function TimingStart()
    {
        return $this->timing_start;
    }
    
    public function TimingEnd()
    {
        return $this->timing_end;
    }
    
    public function Rules()
    {
        return $this->rules;
    }
    
    public function Description()
    {
        return $this->description;
    }
    
    public function Milestones()
    {
        return $this->milestones;
    }
    
    public function TagOptions()
    {
        return $this->tags;
    }
}