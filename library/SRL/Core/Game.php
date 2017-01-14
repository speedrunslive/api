<?php
class SRL_Core_Game
{
    private $id;
    private $name;
    private $abbrev;
    private $popularity;
    private $recentpop;
    private $poprank;
    private $rules;
    
    function __construct($id, $name, $abbrev, $popularity, $recentpop, $poprank, $rules)
    {
        $this->id = $id;
        $this->name = $name;
        $this->abbrev = $abbrev;
        $this->popularity = $popularity;
        $this->recentpop = $recentpop;
        $this->poprank = $poprank;
        $this->rules = $rules;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Name()
    {
        return $this->name;
    }
    
    public function Abbrev()
    {
        return strtolower($this->abbrev);
    }
    
    public function Popularity()
    {
        return $this->popularity;
    }
    
    public function RecentPopularity()
    {
        return $this->recentpop;
    }
    
    public function PopularityRank()
    {
        return $this->poprank;
    }
    
    public function Rules()
    {
        if (!empty($this->rules))
            return $this->rules;
        else
            return "There are no rules defined for this game yet; only standard SRL rules apply.";
    }
}