<?php
class SRL_Data_TwitterRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function GetTwitter($player)
    {
        $player = mysql_real_escape_string($player);
        $results = $this->Select("SELECT twitter FROM twitters t INNER JOIN players p ON p.player_id = t.player_id WHERE p.player_name = '$player';");
        if (count($results) > 0)
            return $results[0]["twitter"];
        
        return "";
    }
    
    public function SetTwitter($player, $twitter)
    {
        $playerRepo = new SRL_Data_PlayerRepository();
        $realPlayer = $playerRepo->GetPlayer($player);
        
        if ($realPlayer->Id() != 0) {
            $player_id = $realPlayer->Id();
            $conn = $this->GetPreparedConnection();
            $stmt = $conn->prepare("INSERT INTO twitters (player_id, twitter) VALUES ((?), (?)) ON DUPLICATE KEY UPDATE twitter = (?);");
            $stmt->bind_param('sss', $player_id, $twitter, $twitter);
            
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
}