<?php
class SRL_Data_CountryRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function GetCountries()
    {
        $countries = array();

        $results = $this->Select("SELECT country_id, country_name from countries;");
        foreach ($results as $result)
        {
            array_push($countries, $result["country_name"]);
        }
        
        return $countries;
    }
    
    public function GetCountry($player)
    {
        $player = mysql_real_escape_string($player);
        $results = $this->Select("SELECT country_name FROM flags f INNER JOIN players p ON p.player_id = f.player_id INNER JOIN countries c on c.country_id = f.country_id WHERE p.player_name = '$player';");
        if (count($results) > 0)
            return $results[0]["country_name"];
        
        return "None";
    }
    
    public function SetCountry($player, $country)
    {
        $playerRepo = new SRL_Data_PlayerRepository();
        $realPlayer = $playerRepo->GetPlayer($player);
        $country = $this->DoesCountryExist($country);
        
        if ($realPlayer->Id() != 0
            && isset($country)) {
            $player_id = $realPlayer->Id();
            $conn = $this->GetPreparedConnection();
            $stmt = $conn->prepare("INSERT INTO flags (player_id, country_id) VALUES ((?), (?)) ON DUPLICATE KEY UPDATE country_id = (?);");
            $stmt->bind_param('sss', $player_id, $country["country_id"], $country["country_id"]);
            
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
    
    private function DoesCountryExist($country)
    {
        $country = mysql_real_escape_string($country);
        
        $results = $this->Select("SELECT country_id FROM countries where country_name = '$country';");
        
        return (count($results) > 0) ? $results[0] : null;
    }
}