<?php
class SRL_Data_StatRepository extends SRL_Data_BaseRepository
{
    private $pastRaceRepo;
    private $playerRepo;
    private $seasonRepo;
    private $currentSeason;
    
    function __construct()
    {
        parent::__construct();
        $this->pastRaceRepo = new SRL_Data_PastRaceRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
        $this->currentSeason = $this->seasonRepo->GetActiveSeason();
    }
    
    public function RerankPlayersInGame($gameId)
    {
        $ranks = $this->Select("SELECT game_rating_id, rating FROM game_rating WHERE game_game_id = $gameId ORDER BY rating DESC;");
        $currentRank = 0;
        $prevRank = 1;
        $prevRating = 0;
        
        foreach ($ranks as $rank)
        {
            $currentRank++;
            $ratingId = $rank["game_rating_id"];
            $rating = $rank["rating"];
            
            $playerRank = $currentRank;
            if ($prevRating == $rating) // ties in rank
            {
                $playerRank = $prevRank;
            }
            else
            {
                $prevRating = $rank["rating"];
                $prevRank = $currentRank;
            }
            
            $this->Execute("UPDATE game_rating SET rank = $playerRank WHERE game_rating_id = $ratingId;");
        }
    }
    
    public function GetOverallStats()
    {
        $results = $this->Select("select (select count(*) from races) as totalRaces, (select count(*) from players) as totalPlayers, (select count(*) from game) as totalGames, (select race_id from races inner join race_link on race_id = races_race_id group by race_id order by COUNT(*) desc limit 1) as largestRaceId, (select count(*) from races inner join race_link on race_id = races_race_id group by race_id order by COUNT(*) desc limit 1) as largestRaceSize, (select sum(lastTime) from (select races_race_id, max(time) as lastTime from race_link where place < 9000 group by races_race_id) as z) as totalRaceTime, (select sum(time) from race_link where place < 9000) as totalPlayedTime;");
        return $results[0];
    }
    
    public function GetOverallStatsForSeason($season_id)
    {
        $season_id = mysql_real_escape_string($season_id);
        $results = $this->Select("select (select count(*) from races where season_id = $season_id) as totalRaces, (select count(distinct player_id) from players inner join race_link on players_player_id = player_id inner join races on race_id = races_race_id where season_id = $season_id) as totalPlayers, (select count(distinct game_id) from game inner join races on game_id = game_game_id where season_id = $season_id) as totalGames, (select race_id from races inner join race_link on race_id = races_race_id where season_id = $season_id group by race_id order by COUNT(*) desc limit 1) as largestRaceId, (select count(*) from races inner join race_link on race_id = races_race_id where season_id = $season_id group by race_id order by COUNT(*) desc limit 1) as largestRaceSize, (select sum(lastTime) from (select races_race_id, max(time) as lastTime from race_link inner join races on race_id = races_race_id where season_id = $season_id and place < 9000 group by races_race_id) as z) as totalRaceTime, (select sum(time) from race_link inner join races on race_id = races_race_id where season_id = $season_id and place < 9000) as totalPlayedTime;");
        return $results[0];
    }
    
    public function GetOverallStatsForSeasonGoal($seasongoal)
    {
        $seasongoal = mysql_real_escape_string($seasongoal);
        $results = $this->Select("select  (select count(*) from races r inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal) as totalRaces,  (select count(distinct player_id) from players inner join race_link on players_player_id = player_id inner join races r on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal) as totalPlayers, (select count(distinct game_id) from game inner join races r on game_id = game_game_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal) as totalGames, (select r.race_id from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal group by r.race_id order by COUNT(*) desc limit 1) as largestRaceId, (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal group by r.race_id order by COUNT(*) desc limit 1) as largestRaceSize, (select sum(lastTime) from (select races_race_id, max(time) as lastTime from race_link inner join races r on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal and place < 9000 group by races_race_id) as z) as totalRaceTime, (select sum(time) from race_link inner join races r on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where season_goal_id = $seasongoal and place < 9000) as totalPlayedTime;");
        return $results[0];
    }
    
    public function GetGameStats($gameId)
    {
        $gameId = mysql_real_escape_string($gameId);
        $results = $this->Select("select (select count(*) from races where game_game_id = $gameId) as totalRaces, (select count(distinct players_player_id) from races inner join race_link on race_id = races_race_id where game_game_id = $gameId group by game_game_id) as totalPlayers, (select race_id from races inner join race_link on race_id = races_race_id where game_game_id = $gameId group by race_id order by COUNT(*) desc limit 1) as largestRaceId, (select count(*) from races inner join race_link on race_id = races_race_id where game_game_id = $gameId group by race_id order by COUNT(*) desc limit 1) as largestRaceSize, (select sum(lastTime) from (select races_race_id, max(time) as lastTime from race_link inner join races on race_id = races_race_id where place < 9000 and game_game_id = $gameId group by races_race_id) as z) as totalRaceTime, (select sum(time) from race_link inner join races on race_id = races_race_id where place < 9000 and game_game_id = $gameId) as totalPlayedTime;");
        return $results[0];
    }
    
    public function GetGameStatsForSeason($gameId, $season_id)
    {
        $gameId = mysql_real_escape_string($gameId);
        $season_id = mysql_real_escape_string($season_id);
        $results = $this->Select("select (select count(*) from races where game_game_id = $gameId and season_id = $season_id) as totalRaces, (select count(distinct players_player_id) from races inner join race_link on race_id = races_race_id where game_game_id = $gameId and season_id = $season_id group by game_game_id) as totalPlayers, (select race_id from races inner join race_link on race_id = races_race_id where game_game_id = $gameId and season_id = $season_id group by race_id order by COUNT(*) desc limit 1) as largestRaceId, (select count(*) from races inner join race_link on race_id = races_race_id where game_game_id = $gameId and season_id = $season_id group by race_id order by COUNT(*) desc limit 1) as largestRaceSize, (select sum(lastTime) from (select races_race_id, max(time) as lastTime from race_link inner join races on race_id = races_race_id where place < 9000 and game_game_id = $gameId and season_id = $season_id group by races_race_id) as z) as totalRaceTime, (select sum(time) from race_link inner join races on race_id = races_race_id where place < 9000 and game_game_id = $gameId and season_id = $season_id) as totalPlayedTime;");
        return $results[0];
    }
    
    public function GetPlayerStats($playerId)
    {
        $playerId = mysql_real_escape_string($playerId);
        $results = $this->Select("select (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId) as totalRaces, (select count(distinct game_game_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId) as totalGames, (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId) as firstRaceId, (select race_date from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and race_id = (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId)) as firstRaceDate, (select sum(time) from race_link where players_player_id = $playerId) as totalPlayedTime, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 1) as totalFirsts, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 2) as totalSeconds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 3) as totalThirds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 9998) as totalQuits, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 9999) as totalDQs;");
        return $results[0];
    }
    
    public function GetPlayerStatsForSeason($playerId, $season_id)
    {
        $playerId = mysql_real_escape_string($playerId);
        $season_id = mysql_real_escape_string($season_id);
        $results = $this->Select("select  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and season_id = $season_id) as totalRaces,  (select count(distinct game_game_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and season_id = $season_id) as totalGames,  (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and season_id = $season_id) as firstRaceId,  (select race_date from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and race_id = (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and season_id = $season_id)) as firstRaceDate,  (select sum(time) from race_link inner join races on race_id = races_race_id where place < 9000 and players_player_id = $playerId and season_id = $season_id) as totalPlayedTime,  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = $season_id and season_id = $season_id) as totalFirsts,  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 2 and season_id = $season_id) as totalSeconds,  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 3 and season_id = $season_id) as totalThirds,  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 9998 and season_id = $season_id) as totalQuits,  (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and place = 9999 and season_id = $season_id) as totalDQs;");
        return $results[0];
    }
    
    public function GetPlayerStatsForSeasonGoal($playerId, $seasongoal)
    {
        $playerId = mysql_real_escape_string($playerId);
        $seasongoal = mysql_real_escape_string($seasongoal);
        $results = $this->Select("select  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on  s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal) as totalRaces,  (select 1) as totalGames,  (select min(r.race_id) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal) as firstRaceId,  (select race_date from races r inner join race_link on r.race_id = races_race_id where players_player_id = $playerId and race_id = (select min(r.race_id) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal)) as firstRaceDate,  (select sum(time) from race_link inner join races r on races_race_id = r.race_id inner join season_race s on r.race_id = s.race_id where place < 9000 and season_goal_id = $seasongoal and players_player_id = $playerId) as totalPlayedTime,  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal and place = 1) as totalFirsts,  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal and place = 2) as totalSeconds,  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal and place = 3) as totalThirds,  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal and place = 9998) as totalQuits,  (select count(*) from races r inner join race_link on r.race_id = races_race_id inner join season_race s on s.race_id = r.race_id where players_player_id = $playerId and season_goal_id = $seasongoal and place = 9999) as totalDQs;");
        return $results[0];
    }
    
    public function GetPlayerGameStats($playerId, $gameId)
    {
        $playerId = mysql_real_escape_string($playerId);
        $gameId = mysql_real_escape_string($gameId);
        $results = $this->Select("select (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId) as totalRaces, (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId) as firstRaceId, (select race_date from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and race_id = (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId)) as firstRaceDate, (select sum(time) from race_link inner join races on race_id = races_race_id where place < 9000 and players_player_id = $playerId and game_game_id = $gameId) as totalPlayedTime, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 1) as totalFirsts, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 2) as totalSeconds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 3) as totalThirds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 9998) as totalQuits, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 9999) as totalDQs, (select rank from game_rating where game_game_id = $gameId and players_player_id = $playerId) as gameRank;");
        return $results[0];
    }
    
    public function GetPlayerGameStatsForSeason($playerId, $gameId, $season_id)
    {
        $playerId = mysql_real_escape_string($playerId);
        $gameId = mysql_real_escape_string($gameId);
        $season_id = mysql_real_escape_string($season_id);
        
        $results = $this->Select("select (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and season_id = $season_id) as totalRaces, (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and season_id = $season_id) as firstRaceId, (select race_date from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and race_id = (select min(race_id) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and season_id = $season_id)) as firstRaceDate, (select sum(time) from race_link inner join races on race_id = races_race_id where place < 9000 and players_player_id = $playerId and game_game_id = $gameId and season_id = $season_id) as totalPlayedTime, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 1 and season_id = $season_id) as totalFirsts, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 2 and season_id = $season_id) as totalSeconds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 3 and season_id = $season_id) as totalThirds, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 9998 and season_id = $season_id) as totalQuits, (select count(*) from races inner join race_link on race_id = races_race_id where players_player_id = $playerId and game_game_id = $gameId and place = 9999 and season_id = $season_id) as totalDQs, (select rank from past_season_game_rating where game_game_id = $gameId and players_player_id = $playerId and season_id = $season_id) as gameRank;");
        return $results[0];
    }
    
    public function GetMonthlyStats($month, $year)
    {
        return $this->GetStatsHelper("SELECT stat_type, stat_value FROM monthly_stats WHERE month = $month and year = $year;");
    }
    
    public function GetAllMonthlyStats()
    {
        $firstMonth = 10;
        $firstYear = 2009;

        $lastMonth = date("n");
        $lastYear = date("Y");

        $allStats = array();

        while ($lastMonth >= 1) {
            $mStats = $this->GetMonthlyStats($lastMonth, $lastYear);
            $monthlyStats = new SRL_Core_MonthlyStats($mStats, $lastMonth, $lastYear);

            array_push($allStats, $monthlyStats);
            $lastMonth--;
        }

        $year = $lastYear-1;
        $month = 12;
        while ($year > $firstYear) {
            $mStats = $this->GetMonthlyStats($month, $year);
            $monthlyStats = new SRL_Core_MonthlyStats($mStats, $month, $year);

            array_push($allStats, $monthlyStats);
            $month--;
            if ($month < 1 ) {
                $month = 12;
                $year--;
            }
        }

        $month = 12;
        while ($firstMonth <= $month) {
            $mStats = $this->GetMonthlyStats($month, $firstYear);
            $monthlyStats = new SRL_Core_MonthlyStats($mStats, $month, $firstYear);

            array_push($allStats, $monthlyStats);
            $month--;
        }
        return $allStats;
    }
    
    private function GetStatsHelper($select)
    {
        $stats = new SRL_Core_Stats();
        $results = $this->Select("$select");
        
        foreach($results as $result)
        {
            $stats->AddStat($result["stat_type"], $result["stat_value"]);
        }
        
        return $stats;
    }
    
    public function UpdateStats($lastRaceId)
    {
        $lastRace = $this->pastRaceRepo->GetRace($lastRaceId);
        
        $raceMonth = $lastRace->RaceMonth();
        $raceYear = $lastRace->RaceYear();
        $this->UpdateMonthlyStats($raceMonth, $raceYear);
    }
    
    public function UpdateMonthlyStats($raceMonth, $raceYear)
    {
        $minUnixTime = mktime(0, 0, 0, $raceMonth, 1, $raceYear);
        
        $nextRaceMonth = ($raceMonth+1 > 12) ? 1 : $raceMonth+1;
        $nextRaceYear = ($nextRaceMonth == 1) ? $raceYear+1 : $raceYear;
        $maxUnixTime = mktime(0, 0, 0, $nextRaceMonth, 1, $nextRaceYear);
        if ($this->HasThereBeenARaceThisMonth($raceMonth, $raceYear))
        {
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT count(*) FROM races WHERE race_date >= $minUnixTime and race_date < $maxUnixTime) WHERE stat_type = 1 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT count(*) FROM (SELECT DISTINCT players_player_id FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime) as z) WHERE stat_type = 2 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT count(*) FROM (SELECT DISTINCT game_game_id FROM races WHERE race_date >= $minUnixTime and race_date < $maxUnixTime) as z) WHERE stat_type = 3 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT race_id FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime GROUP BY race_id ORDER BY COUNT(*) DESC limit 1) WHERE stat_type = 4 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT count(*) as count FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime GROUP BY race_id ORDER BY COUNT(*) DESC limit 1) WHERE stat_type = 17 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT SUM(time) FROM (select max(time) AS time FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime AND race_date < $maxUnixTime and place < 9000 GROUP BY race_id) AS z) WHERE stat_type = 5 and month = $raceMonth and year = $raceYear;");
            $this->Execute("UPDATE monthly_stats SET stat_value = (SELECT SUM(time) FROM races r INNER JOIN race_link rl on r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime AND race_date < $maxUnixTime AND place < 9000) WHERE stat_type = 6 and month = $raceMonth and year = $raceYear;");
        }
        else
        {
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 1, (SELECT count(*) FROM races WHERE race_date >= $minUnixTime and race_date < $maxUnixTime));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 2, (SELECT count(*) FROM (SELECT DISTINCT players_player_id FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime) as z));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 3, (SELECT count(*) FROM (SELECT DISTINCT game_game_id FROM races WHERE race_date >= $minUnixTime and race_date < $maxUnixTime) as z));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 4, (SELECT race_id FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime GROUP BY race_id ORDER BY COUNT(*) DESC limit 1));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 17, (SELECT count(*) as count FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime and race_date < $maxUnixTime GROUP BY race_id ORDER BY COUNT(*) DESC limit 1));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 5, (SELECT SUM(time) FROM (select max(time) AS time FROM races r INNER JOIN race_link rl ON r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime AND race_date < $maxUnixTime and place < 9000 GROUP BY race_id) AS z));");
            $this->Execute("INSERT INTO monthly_stats values ($raceMonth, $raceYear, 6, (SELECT SUM(time) FROM races r INNER JOIN race_link rl on r.race_id = rl.races_race_id WHERE race_date >= $minUnixTime AND race_date < $maxUnixTime AND place < 9000));");
        }
    }
    
    private function HasThereBeenARaceThisMonth($month, $year)
    {
        $result = $this->Select("SELECT * FROM monthly_stats WHERE month = $month AND year = $year;");
        return count($result) > 0;
    }
    
    private function GetGameRecentPop($gameId)
    {
        $results = $this->Select("SELECT game_recentpop FROM game WHERE game_id = $gameId;");
        return $results[0]["game_recentpop"];
    }
    
    public function AdjustGamePopularity($gameId, $recentPop)
    {
        $this->Execute("UPDATE game SET game_recentpop = $recentPop WHERE game_id = $gameId;");
        $this->Execute("UPDATE game SET game_recentpop = game_recentpop * 0.9975 WHERE game_id != $gameId;");
        
        $highestRecentPop = $this->GetHighestRecentPop();
        $highestTimePlayed = $this->GetMostTotalTimePlayed();
        $highestPlayers = $this->GetMostPlayers();
        $highestRaces = $this->GetMostRaces();
        
        $gameRepo = new SRL_Data_GameRepository();
        $games = $gameRepo->GetGames(1);
        $sortedGames = array();
        foreach ($games as $game)
        {
            $stats = $this->GetGameStatsForSeason($game->Id(), $this->currentSeason);
            $gameTimePlayed = $stats["totalPlayedTime"];
            $gamePlayers = $stats["totalPlayers"];
            $gameRaces = $stats["totalRaces"];
            $gameRecentPop = $this->GetGameRecentPop($game->Id());
            
            $actualPop = 1000 *
                ((($gameRecentPop / $highestRecentPop) * (3 / 4) +
                (($gameTimePlayed / $highestTimePlayed) / 12) +
                (($gamePlayers / $highestPlayers) / 12) +
                (($gameRaces / $highestRaces) / 12)));
                
            $actualPop = floor($actualPop);
            
            $sortedGames[$game->Id()] = $actualPop;
        }
        
        arsort($sortedGames);
        $rank = 1;
        foreach ($sortedGames as $gid => $pop)
        {
            $this->Execute("UPDATE game SET game_popularity = $pop, game_poprank = $rank WHERE game_id = $gid;");
            $rank++;
        }
    }
    
    private function GetHighestRecentPop()
    {
        $result = $this->Select("SELECT MAX(game_recentpop) AS pop FROM game;");
        return $result[0]["pop"];
    }
    
    private function GetMostTotalTimePlayed()
    {
        $season_id = $this->currentSeason;
        $result = $this->Select("select sum(time) as totalTime from race_link inner join races on race_id = races_race_id where season_id = $season_id group by game_game_id order by totalTime DESC limit 1;");
        return $result[0]["totalTime"];
    }
    
    private function GetMostPlayers()
    {
        $season_id = $this->currentSeason;
        $result = $this->Select("select count(distinct players_player_id) as totalPlayers from race_link inner join races on race_id = races_race_id where season_id = $season_id group by game_game_id order by totalPlayers DESC limit 1;");
        return $result[0]["totalPlayers"];
    }
    
    private function GetMostRaces()
    {
        $season_id = $this->currentSeason;
        $result = $this->Select("select count(distinct race_id) as totalRaces from races where season_id = $season_id group by game_game_id order by totalRaces DESC limit 1;");
        return $result[0]["totalRaces"];
    }
    
    public function GetAllPlayerStats($sortField, $order, $page, $pageSize)
    {
        $sortField = mysql_real_escape_string($sortField);
        $sortColName = "numRaces";
        
        switch ($sortField)
        {
            case 8:
                $sortColName = "numWins";
                break;
            case 6:
                $sortColName = "timePlayed";
                break;
            case 3:
                $sortColName = "numGames";
                break;
            default:
                $sortColName = "numRaces";
        }
        
        $order = mysql_real_escape_string($order);
        $page = mysql_real_escape_string($page);
        $pageSize = mysql_real_escape_string($pageSize);
        $pageSize = min($pageSize, 200);
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("select player_id, player_name, channel, count(*) as numRaces, SUM(CASE WHEN place = 1 THEN 1 ELSE 0 END) as numWins, SUM(CASE WHEN time > 0 THEN time ELSE 0 END) as timePlayed, count(distinct game_game_id) as numGames from race_link inner join players     on players_player_id = player_id inner join races     on race_id = races_race_id left join streams on user = player_name group by player_id, player_name, channel order by $sortColName $order limit $offset, $pageSize;");
        
        return $results;
    }
    
    public function GetAllPlayerStatsForSeason($sortField, $order, $page, $pageSize, $season)
    {
        $sortField = mysql_real_escape_string($sortField);
        $sortColName = "numRaces";
        
        switch ($sortField)
        {
            case 8:
                $sortColName = "numWins";
                break;
            case 6:
                $sortColName = "timePlayed";
                break;
            case 3:
                $sortColName = "numGames";
                break;
            default:
                $sortColName = "numRaces";
        }
        
        $order = mysql_real_escape_string($order);
        $page = mysql_real_escape_string($page);
        $pageSize = mysql_real_escape_string($pageSize);
        $pageSize = min($pageSize, 200);
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("select player_id, player_name, channel, count(*) as numRaces, SUM(CASE WHEN place = 1 THEN 1 ELSE 0 END) as numWins, SUM(CASE WHEN time > 0 THEN time ELSE 0 END) as timePlayed, count(distinct game_game_id) as numGames from race_link inner join players     on players_player_id = player_id inner join races     on race_id = races_race_id left join streams on user = player_name where season_id = $season group by player_id, player_name, channel order by $sortColName $order limit $offset, $pageSize;");
        
        return $results;
    }
    
    public function GetRankOnePlayerForGame($gameId)
    {
        $results = $this->Select("SELECT players_player_id FROM game_rating WHERE game_game_id = $gameId AND rank = 1;");
        
        return $this->playerRepo->GetPlayerById($results[0]["players_player_id"]);
    }
}