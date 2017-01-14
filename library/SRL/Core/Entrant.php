<?php
class SRL_Core_Entrant
{
    private $player;
    private $place;
    private $time;
    private $message;
    private $trueskill;
    private $twitch;
    private $api;
    
    function __construct($player, $place, $time, $message, $trueskill, $twitch, $api)
    {
        $this->player = $player;
        $this->place = $place;
        $this->time = $time;
        $this->message = $message;
        $this->twitch = $twitch;
        $this->trueskill = max(0, round($trueskill * 40));
        $this->api = $api;
    }
    
    public function Player()
    {
        return $this->player;
    }
    
    public function Place()
    {
        return intval($this->place);
    }
    
    public function Time()
    {
        return intval($this->time);
    }

    public function Message()
    {
        return $this->message;
    }
    
    public function StateText()
    {
        switch ($this->place) {
            case SRL_Core_EntrantState::Entered:
                 return "Entered";
            case SRL_Core_EntrantState::Forfeit:
                return "Forfeit";
            case SRL_Core_EntrantState::Disqualified:
                return "Disqualified";
            case SRL_Core_EntrantState::Ready:
                return "Ready";
            case 0:
                return "Entered";
            default:
                return "Finished";
        }
    }
    
    public function Twitch()
    {
        if ( $this->api == "twitch" ) {
            return $this->twitch;
        }
        else {
            return "";
        }
    }

    public function HitBox()
    {
        if ( $this->api == "hitbox" ) {
            return $this->twitch;
        }
        else {
            return "";
        }
    }
    
    public function Trueskill()
    {
        return $this->trueskill;
    }
}