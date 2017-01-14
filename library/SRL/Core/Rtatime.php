<?php
class SRL_Core_Rtatime
{
    private $id;
    private $lb;
    private $player;
    private $time;
    private $rank;
    private $video;
    private $notes;
    private $date;
    private $day;
    private $month;
    private $year;
    private $tags;
    
    function __construct($id, $lb, $player, $time, $rank, $video, $notes, $date, $day, $month, $year, $tags)
    {
        $this->id = $id;
        $this->lb = $lb;
        $this->player = $player;
        $this->time = $time;
        $this->rank = $rank;
        $this->video = $video;
        $this->notes = $notes;
        $this->date = $date;
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
        $this->tags = $tags;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Leaderboard()
    {
        return $this->lb;
    }
    
    public function Player()
    {
        return $this->player;
    }

    public function Time()
    {
        return $this->time;
    }
    
    public function Rank()
    {
        return $this->rank;
    }
    
    public function Video()
    {
        return $this->video;
    }
    
    public function Notes()
    {
        return $this->notes;
    }
    
    public function Date()
    {
        return $this->date;
    }
    
    public function Day()
    {
        return $this->day;
    }
    
    public function Month()
    {
        return $this->month;
    }
    
    public function Year()
    {
        return $this->year;
    }
    
    public function Tags()
    {
        return $this->tags;
    }
}