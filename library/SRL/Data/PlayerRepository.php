<?php
class SRL_Data_PlayerRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
        // $this->pageSize = 10000;
    }
    private function GetPlayersHelper($query, $page, $pageSize, $test = false)
    {
        $players = array();
        // $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;        
        
        $wholequery = "SELECT p.player_id, p.player_name, p.role_id,
            y.youtube, c.country_name, t.twitter, s.api, s.channel
            FROM players p 
            LEFT JOIN flags f ON f.player_id = p.player_id
            LEFT JOIN countries c on f.country_id = c.country_id
            LEFT JOIN twitters t ON t.player_id = p.player_id
            LEFT JOIN youtube y ON y.player_id = p.player_id
            LEFT JOIN streams s ON s.player_id=p.player_id
            $query LIMIT $offset, $pageSize;";
            $results = $this->Select($wholequery);        
        foreach ($results as $result)
        {
            
            $twitter = $result["twitter"];
            
            
            $youtube = $result["youtube"];
                        
            $country = $result["country_name"];
            
            $player = new SRL_Core_Player($result["player_id"], $result["player_name"], $result["channel"], $twitter, $youtube, $result["role_id"], $country, $result["api"]);
            array_push($players, $player);
        }
        return $players;
    }
    public function GetPlayersByID($ids) { 
    $wherein = "WHERE p.player_id IN (";
    $idcount = 0;
    foreach($ids as $id) {
      if ($idcount > 0)
        $wherein .= ", ";
      $wherein .= $id;
      $idcount++;
    }
    $wherein .= ")";
    return $this->GetPlayersHelper($wherein,1,655,true);
    }
    public function GetPlayersByName($names){
        $wherein = "WHERE p.player_name IN (";
        $namecount = 0;
        foreach($names as $name)
        {
            $name = mysql_real_escape_string($name);
            if($namecount > 0)
                $wherein .= ",";
            $wherein .= "'" . $name . "'";
            $namecount++;
        }        
        $wherein .= ")";
        
        return $this->GetPlayersHelper($wherein,1,500);
    }
    public function GetPlayerById($id)
    {
                
        parent::EstablishConnection();
        //$name = mysql_real_escape_string($name);
        //$players = $this->GetPlayersHelper("WHERE player_name = '$name'", 1, 1);
        $players = $this->GetPlayersHelper("WHERE p.player_id = '$id'", 1, 1);
        if (count($players) > 0)
        {
            return $players[0];
        }
        return null;
    }
    
    public function GetPlayer($name)
    {
        parent::EstablishConnection();
        $name = mysql_real_escape_string($name);
        $players = $this->GetPlayersHelper("WHERE player_name = '$name'", 1, 1);
        if (count($players) > 0)
        {
            return $players[0];
        }
        return new SRL_Core_Player(0, $name, "", "", "", SRL_Core_Role::Anon, "", "");
    }
    
    public function GetPlayerSearch($search)
    {
        parent::EstablishConnection();
        $search = mysql_real_escape_string($search);
        return $this->GetPlayersHelper(
            "WHERE player_name LIKE '$search%' " .
            "GROUP BY player_name " .
            "ORDER BY LENGTH(player_name) ASC, CASE " .
                "WHEN player_name like '$search%' THEN 0 " .
               "WHEN player_name like '%$search' THEN 2 " .
               "WHEN player_name like '% %$search% %' THEN 1 " .
               "ELSE 3 " .
            "END, player_name", 1, 3
            );
    }
    
    public function GetPlayers($page)
    {
        return $this->GetPlayersHelper("", $page, 100);
    }
    
    public function CountPlayers()
    {
        $results = $this->Select("SELECT count(*) as count FROM players;");
        return $results[0]["count"]; 
    }
    
    public function RenamePlayer($oldName, $newName)
    {
        $oldName = mysql_real_escape_string($oldName);
        $newName = mysql_real_escape_string($newName);
        
        if ($this->GetPlayer($newName)->Id() == 0)
        {
            $this->Execute("UPDATE players SET player_name = '$newName' WHERE player_name = '$oldName' LIMIT 1;");
            return $this->GetPlayer($newName);
        }
        
        return $this->GetPlayer($oldName);
    }
    
    public function GetPlayersByStream($channels)
    {
        
       $wholequery = "SELECT p.player_id, p.player_name, s.channel, s.api, p.role_id,
            y.youtube, c.country_name, t.twitter 
            FROM players p 
            LEFT JOIN streams s ON s.player_id = p.player_id
            LEFT JOIN flags f ON f.player_id = p.player_id
            LEFT JOIN countries c on f.country_id = c.country_id
            LEFT JOIN twitters t ON t.player_id = p.player_id
            LEFT JOIN youtube y ON y.player_id = p.player_id
            WHERE s.channel in ('$channels') ORDER BY player_name;";
            //die($wholequery);
        $results = $this->Select($wholequery);        
        $players = array();
        foreach ($results as $result)
        {            
            
            
            $twitter = $result["twitter"];
            
            
            $youtube = $result["youtube"];
                        
            $country = $result["country_name"];
            
            $player = new SRL_Core_Player($result["player_id"], $result["player_name"], $result["channel"], $twitter, $youtube, $result["role_id"], $country, $result["api"]);            
            array_push($players, $player);          
            
        }           
        return $players;
        
    }
    
    public function RecasePlayer($oldName, $newName)
    {
        if (strcasecmp($oldName, $newName) == 0)
        {
            $conn = $this->GetPreparedConnection();
            $stmt = $conn->prepare("UPDATE players SET player_name = '$newName' WHERE player_name = '$oldName' LIMIT 1;");
            //$stmt->bind_param('ss', $newName, $oldName);
            
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
    public function GetPlayerId($name)
    {
        parent::EstablishConnection();
        $name = mysql_real_escape_string($name);
        $results = $this->Select("SELECT player_id FROM players WHERE player_name = '$name';");
        if (count($results) > 0) {
            return $results[0]["player_id"];
        }
        else {
            $this->Execute("INSERT INTO players (player_name, role_id) VALUES ('$name', 5);");
            $playerId = $this->GetLastInsertId();
            return $playerId;
        }
    }
    public function UpdateLastSeen($name)
    {
        parent::EstablishConnection();
        $name = mysql_real_escape_string($name);
        $this->Execute("UPDATE players SET last_seen = CURRENT_TIMESTAMP WHERE player_name = '$name';");
    }

}
