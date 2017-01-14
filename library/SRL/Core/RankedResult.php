<?php
class SRL_Core_RankedResult
{
    private $player;
    private $old_rating;
    private $new_rating;
    
    function __construct($player, $old_rating, $new_rating)
    {
        $this->player = $player;
        $this->old_rating = $old_rating;
        $this->new_rating = $new_rating;
    }

    public function Player()
    {
        return $this->player;
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
}