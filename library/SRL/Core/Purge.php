<?php
class SRL_Core_Purge
{
    private $user;
    private $num_warnings;
    private $remaining;
    private $adminLogRepo;
    
    function __construct($user, $num_warnings, $remaining)
    {
        $this->user = $user;
        $this->num_warnings = $num_warnings;
        $this->remaining = $remaining;
        $this->adminLogRepo = new SRL_Data_AdminLogRepository();
    }
    
    public function User()
    {
        return $this->user;
    }
    
    public function Num_Warnings()
    {
        return $this->num_warnings;
    }
    
    public function Remaining()
    {
        return $this->remaining;
    }

    public function LastPurge()
    {
        return $this->adminLogRepo->GetLastPurgeEntry($this->user);
    }
}