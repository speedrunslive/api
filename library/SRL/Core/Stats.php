<?php
class SRL_Core_Stats
{
    private $stats;
    function __construct()
    {
        $this->stats = array();
    }
    
    public function AddStat($statId, $statValue)
    {
        $this->stats[$statId] = $statValue;
    }
    
    public function GetStat($statId)
    {
        if (array_key_exists($statId, $this->stats))
        {
            return $this->stats[$statId];
        }
        
        return 0;
    }
}