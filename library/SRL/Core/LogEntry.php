<?php
class SRL_Core_LogEntry
{
    private $timestamp;
    private $source;
    private $action;
    private $target;
    private $comment;
    
    function __construct($timestamp, $source, $action, $target, $comment)
    {
        $this->timestamp = $timestamp;
        $this->source = $source;
        $this->action = $action;
        $this->target = $target;
        $this->comment = $comment;
    }
    
    public function Timestamp()
    {
        return $this->timestamp;
    }
    
    public function Source()
    {
        return $this->source;
    }
    
    public function Action()
    {
        return $this->action;
    }

    public function Target()
    {
        return $this->target;
    }

    public function Comment()
    {
        return $this->comment;
    }
}