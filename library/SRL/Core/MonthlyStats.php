<?php
class SRL_Core_MonthlyStats
{
    private $stats;
    private $month;
    private $year;
    
    function __construct($stats, $month, $year)
    {
        $this->stats = $stats;
        $this->month = $month;
        $this->year = $year;
    }
    
    public function Stats()
    {
        return $this->stats;
    }
    
    public function Month()
    {
        return $this->month;
    }
    
    public function Year()
    {
        return $this->year;
    }
}