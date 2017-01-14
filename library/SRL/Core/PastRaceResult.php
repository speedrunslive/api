<?php
class SRL_Core_PastRaceResult
{
    private $race_link_id;
    private $races_race_id;
    private $player;
    private $place;
    private $time;
    private $message;
    private $old_rating;
    private $new_rating;
    private $season_old_rating;
    private $season_new_rating;
    
    function __construct($race_link_id, $races_race_id, $player, $place, $time, $message, $old_rating, $new_rating, $season_old_rating, $season_new_rating)
    {
        $this->race_link_id = $race_link_id;
        $this->races_race_id = $races_race_id;
        $this->player = $player;
        $this->place = $place;
        $this->time = $time;
        $this->message = $message;
        $this->old_rating = $old_rating;
        $this->new_rating = $new_rating;
        $this->season_old_rating = $season_old_rating;
        $this->season_new_rating = $season_new_rating;
    }
    
    public function Id()
    {
        return $this->race_link_id;
    }
    
    public function RaceId()
    {
        return $this->races_race_id;
    }
    
    public function Player()
    {
        return $this->player;
    }
    
    public function Place()
    {
        return $this->place;
    }
    
    public function Time()
    {
        return $this->time;
    }
    
    public function Message()
    {
        return $this->message;
    }
    
    public function OldTrueskill()
    {
        return max(0, round($this->old_rating));
    }
    
    public function NewTrueskill()
    {
        return max(0, round($this->new_rating));
    }
    
    public function TrueskillChange()
    {
        return $this->NewTrueskill() - $this->OldTrueskill();
    }
    
    public function Finished()
    {
        return $this->Place() < SRL_Core_EntrantState::Ready;
    }
    
    public function OldSeasonTrueskill()
    {
        return max(0, round($this->season_old_rating));
    }
    
    public function NewSeasonTrueskill()
    {
        return max(0, round($this->season_new_rating));
    }
    
    public function SeasonTrueskillChange()
    {
        return $this->NewSeasonTrueskill() - $this->OldSeasonTrueskill();
    }
}