<?php
class SRL_Data_StreamRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function GetStream($player)
    {
        $player = mysql_real_escape_string($player);       
        $results = $this->Select("SELECT channel, api FROM streams WHERE user = '$player';");
        if (count($results) > 0) {
            return $results[0];
    }
        
        return "";
    }
    public function SetStream($player, $twitch, $api)
    {
        $this->playerRepo = new SRL_Data_PlayerRepository();
        $conn = $this->GetPreparedConnection();
        $player = mysql_real_escape_string($player);
        $player_id = $this->playerRepo->GetPlayerId($player);
        $query1 = "SELECT player_id from streams where player_id = " . $player_id;
        $resultStreamCheck = $this->Select($query1);
        if (count($resultStreamCheck) > 0)
        {
                // user exists, update.
                $query2 = "UPDATE IGNORE streams set channel='" . mysql_real_escape_string($twitch) . "', api='" . mysql_real_escape_string($api) . "' where player_id= " . $player_id;
                $this->Execute($query2);
        }
        else
        {
                // user does not exist, insert.
                //$stmt = $conn->prepare("INSERT INTO streams (user, channel, api, player_id, success) VALUES ((?), (?), (?), (?), 0)");
                //$stmt->bind_param('sssss', $player, $twitch, $api, $player_id);
                //$stmt->execute();
                //$stmt->close();
                $query3 = "INSERT IGNORE INTO streams (user, channel, api, player_id, success) VALUES ('". mysql_real_escape_string($player) ."','". mysql_real_escape_string($twitch)."','".mysql_real_escape_string($api)."','".$player_id."',0)";
                $this->Execute($query3);
        }
        $conn->close();

	$this->UpdatePlayerCacheStream($player_id, $twitch, $api);
    }

    
    public function RemoveStream($player)
    {
        $player = mysql_real_escape_string($player);
        
        $this->Execute("DELETE FROM streams WHERE user = '$player' LIMIT 1;");
    }
    
    public function GetPlayerByStream($channel)
    {
        $channel = mysql_real_escape_string($channel);
        $results = $this->Select("SELECT user FROM streams WHERE channel = '$channel';");
        if (count($results) > 0)
            return $results[0]["user"];
        
        return "";
    }
    
    public function CountStreams($api)
    {
        if ( $api ) {
            $results = $this->Select("SELECT COUNT(distinct channel) AS c FROM streams WHERE api = \"$api\" AND (whitelisted = 1 OR met_race_req = 1)");
        }
        else {
            $results = $this->Select("SELECT COUNT(distinct channel) AS c FROM streams WHERE whitelisted = 1 OR met_race_req = 1;");
        }
        return $results[0]["c"];
    }
    
    public function GetStreamsPage($page, $api = "")
    {
        $page_size = 100;
        $page = mysql_escape_string($page);
        $api = mysql_escape_string($api);
        $offset = $page_size*($page-1);
        //$min_races = 35;
        //$min_races = 1;
        //$two_weeks_ago = 1209600;
        $thirty_days_ago = 2592000;
        $three_weeks_ago = 1814400;
        $banned_game_ids = "397,219,85,14,59,23,1079";

        //if ($page == 1)
        //{
            //$query = "truncate table qualifiers;";
            //$this->Execute($query);
            /*$query = "
                insert into qualifiers
                select player_name as p, 1 as met_race_req, player_id
                from players
                inner join race_link
                    on player_id = players_player_id
                inner join races on race_id = races_race_id
                where
                    place < 9000
                    and game_game_id not in ($banned_game_ids)
                group by player_name
                having
                    count(race_id) >= $min_races
                    and max(race_date) > UNIX_TIMESTAMP(NOW())-$two_weeks_ago
                    and min(race_date) < UNIX_TIMESTAMP(NOW())-$three_weeks_ago
                    and sum(time) >= 54000";*/
                /*$query = "CREATE TEMPORARY TABLE qualifierstwo (id INT)";
                $this->Execute($query);
                $query = "UPDATE streams SET met_race_req = 0";
                $this->Execute($query);
                $query = "INSERT INTO qualifierstwo (
                select player_id
                from players
                inner join race_link
                    on player_id = players_player_id
                inner join races on race_id = races_race_id
                where
                    place < 9000
                    and game_game_id not in ($banned_game_ids)
                group by player_name
                having
                    max(race_date) > UNIX_TIMESTAMP(NOW())-$thirty_days_ago
                    and sum(time) >= 3600)";
                $this->Execute($query);
                $query = "UPDATE streams AS s
                    INNER JOIN qualifierstwo AS q ON s.player_id = q.id
                    SET s.met_race_req = 1";
                $this->Execute($query);
                $query = "DROP TABLE qualifierstwo";
                $this->Execute($query);*/

        //}

        /*$query =
            "select distinct channel, api, max(whitelisted) as whitelisted, frontpage_pref, max(CASE WHEN met_race_req is null THEN 0 ELSE met_race_req END) as met_race_req
            from streams s
            left join qualifiers q
                on s.player_id = q.qualifying_player_id
            where whitelisted in (0,1)";*/
            $query =
                "select distinct user, channel, api, max(whitelisted) as whitelisted, frontpage_pref, met_race_req
                from streams s
                where whitelisted in (0,1) or met_race_req in (0,1)";
        if ( $api != "" ) {
            $query .= " AND api = \"$api\"";
        }
        $query .= " group by channel
            having (whitelisted = 1 or met_race_req = 1) and (frontpage_pref != 1) and (whitelisted != 2)
            limit $page_size offset $offset;";

        $results = $this->Select($query);
        foreach ( $results as &$stream ) {
            $stream["is_racing"] = $this->isRacing($stream["user"]);
        }
        return $results;
    }

    public function UpdatePlayerCacheQualifier($player_id, $whitelisted, $qualified)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://api.speedrunslive.com/es/players/player/'.$player_id
        ));

        $res = curl_exec($curl);

        $source = json_decode($res, true)["_source"];
        $source["whitelisted"] = $whitelisted;
        $source["streamQualified"] = $qualified;

        curl_setopt_array($curl, array(
            CURLOPT_POST => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($source),
            CURLOPT_URL => 'http://api.speedrunslive.com/es/players/player/'.$player_id
        ));

        curl_exec($curl);
        curl_close($curl);
    }
    public function UpdatePlayerCacheStream($player_id, $stream, $api)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://api.speedrunslive.com/es/players/player/'.$player_id
        ));

        $res = curl_exec($curl);

        $source = json_decode($res, true)["_source"];
        $source["stream"] = $stream;
        $source["api"] = $api;

        curl_setopt_array($curl, array(
            CURLOPT_POST => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($source),
            CURLOPT_URL => 'http://api.speedrunslive.com/es/players/player/'.$player_id
        ));

        curl_exec($curl);
        curl_close($curl);
    }
    
    public function WhitelistStream($player)
    {
        $conn = $this->GetPreparedConnection();
        $stmt = $conn->prepare("UPDATE streams SET whitelisted = 1 WHERE user = (?);");
        $stmt->bind_param('s', $conn->real_escape_string($player));
        
        $stmt->execute();
        $stmt->close();
        $conn->close();

        $player = mysql_real_escape_string($player);
        $results = $this->Select("SELECT player_id FROM players WHERE player_name = '$player';");
        $this->UpdatePlayerCacheQualifier($results[0]["player_id"], 1, 1);
    }
    
    public function BlacklistStream($player)
    {
        $conn = $this->GetPreparedConnection();
        $stmt = $conn->prepare("UPDATE streams SET whitelisted = 2 WHERE user = (?);");
        $stmt->bind_param('s', $conn->real_escape_string($player));
        
        $stmt->execute();
        $stmt->close();
        $conn->close();

        $player = mysql_real_escape_string($player);
        $results = $this->Select("SELECT player_id FROM players WHERE player_name = '$player';");
        $this->UpdatePlayerCacheQualifier($results[0]["player_id"], 2, 0);
    }

    public function UnwhitelistStream($player)
    {
        $conn = $this->GetPreparedConnection();
        $stmt = $conn->prepare("UPDATE streams SET whitelisted = 0 WHERE user = (?);");
        $stmt->bind_param('s', $conn->real_escape_string($player));
        
        $stmt->execute();
        $stmt->close();
        $conn->close();

        $player = mysql_real_escape_string($player);
        $results = $this->Select(
            "SELECT p.player_id, (CASE WHEN (SUM(CASE WHEN time > 0 THEN time ELSE 0 END) AND ((UNIX_TIMESTAMP(NOW())-11209600) < p.last_seen)) THEN 1 ELSE 0 END) AS streamQualified
            FROM race_link r
            INNER JOIN players p ON r.players_player_id = p.player_id
            WHERE p.player_name = '$player';"
            );
        $this->UpdatePlayerCacheQualifier($results[0]["player_id"], 0, intval($results[0]["streamQualified"]));
    }
    public function GetFrontpagePref($player) {
        $player = mysql_real_escape_string($player);       
        $results = $this->Select("SELECT frontpage_pref FROM streams WHERE user = '$player';");
        if (count($results) > 0) {
            return $results[0]["frontpage_pref"];
        }
        
        return 0;
    }
    public function GetStreamInformation($api) {
        $api = mysql_real_escape_string($api);
        $results = $this->Select(
            "SELECT
              user,
              frontpage_pref,
              (CASE WHEN r.count >= 1 AND r.time < -1 THEN 1 ELSE 0 END) AS is_racing
            FROM streams LEFT JOIN (
              SELECT current_race_player_name, COUNT(current_race_player_name) as count, time
              FROM current_races_link
              GROUP BY current_race_player_name
            ) r ON user = r.current_race_player_name
            WHERE api='$api';"
        );
        return $results;
    }
    public function SetFrontpagePref($player, $value)
    {
        $conn = $this->GetPreparedConnection();
        $stmt = $conn->prepare("UPDATE streams SET frontpage_pref = (?) WHERE user = (?);");
        $stmt->bind_param('ss', $conn->real_escape_string($value), $conn->real_escape_string($player));
        
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
    public function isRacing($player)
    {
        $player = mysql_escape_string($player);
        $results = $this->Select("SELECT COUNT(current_race_player_name) AS count FROM current_races_link WHERE current_race_player_name = '$player' AND time < -1");
        return $results[0]["count"];
    }
    public function getWarningCount($stream)
    {
        $results = $this->Select("SELECT num_warnings FROM streams WHERE user = '$stream'");
        return $results[0]["num_warnings"];
    }
    public function getLastWarned($stream)
    {
        $results = $this->Select("SELECT UNIX_TIMESTAMP(last_warning) AS last_warning FROM streams WHERE user = '$stream'");
        return $results[0]["last_warning"];
    }
    public function incrementWarningCount($stream)
    {
        $ninety_days_ago = 7776000;
        $resetCount = $this->Select("SELECT (UNIX_TIMESTAMP(NOW())-$ninety_days_ago > UNIX_TIMESTAMP(last_warning)) AS ok FROM `streams` WHERE user = '$stream'")[0]["ok"];
        Zend_Debug::dump($resetCount);
        if ( $resetCount == 1 ) {
            $this->Execute("UPDATE streams SET num_warnings = 1, last_warning = CURRENT_TIMESTAMP WHERE user = '$stream'");
        }
        else {
            $this->Execute("UPDATE streams SET num_warnings = num_warnings + 1, last_warning = CURRENT_TIMESTAMP WHERE user = '$stream'");
        }
        return $this->Select("SELECT num_warnings FROM streams WHERE user = '$stream'")[0]['num_warnings'];
    }
    public function isBlacklisted($stream)
    {
        $results = $this->Select("SELECT whitelisted FROM streams WHERE user = '$stream'");
        if ( $results[0]["whitelisted"] == 2 ) {
            return true;
        }
        else {
            return false;
        }
    }
    public function recentlyPurged($user) {
        $user = mysql_escape_string($user);
        $results = $this->Select("SELECT UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(last_warning) AS diff FROM streams WHERE user = '$user'");
        if ( $results[0]["diff"] < 360 ) {
            return true;
        }
        else {
            return false;
        }
    }
}
