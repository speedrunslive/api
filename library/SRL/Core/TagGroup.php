<?php
class SRL_Core_TagGroup
{
    private $id;
    private $name;
    private $tags;
    
    function __construct($id, $name, $tags)
    {
        $this->id = $id;
        $this->name = $name;
        $this->tags = $tags;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Name()
    {
        return $this->name;
    }
    
    public function Tags()
    {
        return $this->tags;
    }
    
    public function Contains($tag_id)
    {
        foreach ($this->tags as $tag) {
            if ($tag_id == $tag->Id())
                return true;
        }
        
        return false;
    }
}