<?php
class SRL_Data_YoutubeRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function GetYoutube($player)
    {
        $player = mysql_real_escape_string($player);
        $results = $this->Select("SELECT youtube FROM youtube t INNER JOIN players p ON p.player_id = t.player_id WHERE p.player_name = '$player';");
        if (count($results) > 0)
            return $results[0]["youtube"];
        
        return "";
    }
    
    public function SetYoutube($player, $youtube)
    {
        $playerRepo = new SRL_Data_PlayerRepository();
        $realPlayer = $playerRepo->GetPlayer($player);
        
        if ($realPlayer->Id() != 0) {
            $player_id = $realPlayer->Id();
            $conn = $this->GetPreparedConnection();
            $stmt = $conn->prepare("INSERT INTO youtube (player_id, youtube) VALUES ((?), (?)) ON DUPLICATE KEY UPDATE youtube = (?);");
            $stmt->bind_param('sss', $player_id, $youtube, $youtube);

            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
}