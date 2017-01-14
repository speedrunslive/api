<?php
class SRL_Core_Player
{
    private $id;
    private $name;
    private $channel;
    private $twitter;
    private $youtube;
    private $role;
    private $country;
    private $api;
    
    function __construct($id, $name, $channel, $twitter, $youtube, $role, $country, $api)
    {
        $this->id = $id;
        $this->name = $name;
        $this->channel = $channel;
        $this->twitter = $twitter;
        $this->youtube = $youtube;
        $this->role = $role;
        $this->country = $country;
        @$this->api = $api;
    }
    
    public function Id()
    {
        return $this->id;
    }
    
    public function Name()
    {
        return $this->name;
    }
    
    public function Channel()
    {
        return $this->channel;
    }
    
    public function Twitter()
    {
        return $this->twitter;
    }
    
    public function Youtube()
    {
        return $this->youtube;
    }
    
    public function Role()
    {
        return SRL_Core_Role::GetRole($this->role);
    }
    
    public function Country()
    {
        return $this->country;
    }

    public function Api()
    {
        return $this->api;
    }
}
