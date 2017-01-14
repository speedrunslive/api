<?php
class SRL_Core_Goal
{
    private $id;
    private $goal;
    private $game;
    
    function __construct($id, $goal, $game)
    {
        $this->id = $id;
        $this->goal = $goal;
        $this->game = $game;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Goal()
    {
        return $this->goal;
    }
    
    public function Game()
    {
        return $this->game;
    }
}