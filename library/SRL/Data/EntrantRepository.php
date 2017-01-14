<?php
class SRL_Data_EntrantRepository extends SRL_Data_BaseRepository
{
    private $playerRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->playerRepo = new SRL_Data_PlayerRepository();        
    }
    
    private function GetEntrantsHelper($where)
    {
        $entrants = array();
        $query = "SELECT current_race_player_name, place, time, message, r.current_race_game_id, p.player_name, g.rating, s.channel, p.player_id, s.api
        FROM current_races_link l
        INNER JOIN current_races r
        ON r.current_race_id = l.current_race_race_id
        LEFT JOIN players p
        ON l.current_race_player_id = p.player_id
        LEFT JOIN game_rating g
        ON g.players_player_id = p.player_id AND g.game_game_id = r.current_race_game_id
        LEFT JOIN streams s
        ON p.player_id = s.player_id
        $where
        ORDER BY place ASC, rating DESC;";      
        $results = $this->Select($query);
        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result["player_id"];
        }
        $players_by_id = array();
        if (count($ids)  > 0)  {
            $players = $this->playerRepo->GetPlayersByID($ids);
            foreach ($players as $playerobj) {
               $players_by_id[$playerobj->Id()] = $playerobj;
            }
        }
        foreach ($results as $result)
        {
            //$player = $this->playerRepo->GetPlayer($result["current_race_player_name"]);
            if (count($players_by_id) > 0) {
                $player = $players_by_id[$result["player_id"]];
            }
            if ($player == null) {
                $player = $this->playerRepo->GetPlayer($result["player_name"]);
            }
            if ($player == null) {            
                $id = $this->raceRepo->GetPlayerId($result["player_name"]);
                $player = $this->playerRepo->GetPlayerById($id);
            }
            $entrant = new SRL_Core_Entrant($player, $result["place"], $result["time"], $result["message"], $result["rating"], $result["channel"], $result["api"]);
            array_push($entrants, $entrant);
        }

        return $entrants;
    }
    public function GetEntrants($race_id)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        
        return $this->GetEntrantsHelper("WHERE current_race_race_id = '$race_id'");
    }
    
    public function GetEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
    $id = $this->playerRepo->GetPlayerId($entrant);
        $results = $this->GetEntrantsHelper("WHERE current_race_race_id = '$race_id' AND current_race_player_id = '$id'");
        if (count($results > 0)) {
            return $results[0];
        }
        
        return null;
    }
    
    public function CountEntrants($race_id)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        
        $result = $this->Select("SELECT COUNT(*) AS count FROM current_races_link WHERE current_race_race_id = '$race_id';");
        return $result[0]["count"];
    }
    
    public function AddEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        $id = $this->playerRepo->GetPlayerId($entrant);
        $this->Execute("INSERT INTO current_races_link (current_race_race_id, current_race_player_id, current_race_player_name, place, time) VALUES ('$race_id', '$id', '$entrant', 9995, 0);");
    }
    
    public function RemoveEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("DELETE FROM current_races_link WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
        $this->ReorderEntrants($race_id);
    }
    
    public function ReadyEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("UPDATE current_races_link SET place = 9994, time = -3, message = '' WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
    }
    
    public function UnreadyEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("UPDATE current_races_link SET place = 9995, time = 0 WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
    }
    
    public function DoneEntrant($race_id, $entrant, $place, $time, $message)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $entrantLink = $this->GetEntrant($race_id, $entrant);
        if ($entrantLink->Place() == SRL_Core_EntrantState::Ready) {
            if ($time < 1)
                $time = 1;
            $this->Execute("UPDATE current_races_link SET place = $place, time = $time WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
            $this->CommentEntrant($race_id, $entrant, $message);
        }
        $this->ReorderEntrants($race_id);
    }
    
    public function UndoneEntrant($race_id, $entrant)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->ReadyEntrant($race_id, $entrant);
        $this->ReorderEntrants($race_id);
    }
    
    public function CommentEntrant($race_id, $entrant, $message)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $realEntrant = $this->GetEntrant($race_id, $entrant);
        if (!is_null($message) && $realEntrant->Place() != SRL_Core_EntrantState::Ready) {
            $message = mysql_real_escape_string($message);
            $this->Execute("UPDATE current_races_link SET message = '$message' WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
        }
    }
    
    public function ForfeitEntrant($race_id, $entrant, $message)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("UPDATE current_races_link SET place = 9998, time = -1 WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
        $this->CommentEntrant($race_id, $entrant, $message);
        $this->ReorderEntrants($race_id);
    }
    
    public function DisqualifyEntrant($race_id, $entrant, $message)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("UPDATE current_races_link SET place = 9999, time = -2 WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
        $this->CommentEntrant($race_id, $entrant, $message);
        $this->ReorderEntrants($race_id);
    }
    
    private function UpdateEntrantPlace($race_id, $entrant, $place)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $entrant = mysql_real_escape_string($entrant);
        
        $this->Execute("UPDATE current_races_link SET place = $place WHERE current_race_race_id = '$race_id' AND current_race_player_name = '$entrant';");
    }
    
    private function ReorderEntrants($race_id)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        $oldEntrants = $this->GetEntrants($race_id);
        $finishedEntrants = array();
        
        foreach ($oldEntrants as $entrant) {
            if ($entrant->Place() > 0 && $entrant->Place() < 9994)
                $finishedEntrants[$entrant->Player()->Name()] = $entrant->Time();
        }
        
        if (count($finishedEntrants) > 0) {
            asort($finishedEntrants);
            $place = 0;
            $order = 0;
            $lastTime = -1;
            foreach ($finishedEntrants as $entrant => $time) {
                $order++;
                
                if ($time != $lastTime)
                {
                    $place = $order;
                }
                    
                $this->UpdateEntrantPlace($race_id, $entrant, $place);
                $lastTime = $time;
                unset($time);
            }
        }
    }
    
    public function RemoveAllEntrants($race_id)
    {
        parent::EstablishConnection();
        $race_id = mysql_real_escape_string($race_id);
        
        $this->Execute("DELETE FROM current_races_link WHERE current_race_race_id = '$race_id';");
    }
}
