<?php
class SRL_Data_GameRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
        // $this->pageSize = 10000;
    }
    
    private function GetGamesHelper($query, $page, $pageSize)
    {
        $games = array();
        // $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT game_id, game_name, game_abbrev, game_popularity, game_recentpop, game_poprank, game_rules FROM game $query LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $game = new SRL_Core_Game($result["game_id"], $result["game_name"], $result["game_abbrev"], $result["game_popularity"], $result["game_recentpop"], $result["game_poprank"], $result["game_rules"]);
            array_push($games, $game);
        }
        
        return $games;
    }

    public function GetGameSearch($search)
    {
        $search = mysql_real_escape_string($search);
        return $this->GetGamesHelper(
            "WHERE game_name LIKE '%$search%' or game_abbrev LIKE '%$search%' " .
            "GROUP BY game_abbrev, game_name " .
            "ORDER BY CASE " .
                "WHEN game_abbrev like '$search' THEN 0 " .
                "WHEN game_name like '$search' THEN 1 " .
                "WHEN game_abbrev like '$search%' THEN 2 " .
                "WHEN game_name like '$search%' THEN 3 " .
                "WHEN game_name like '%$search' THEN 4 " .
                "WHEN game_abbrev like '%$search' THEN 5 " .
                "WHEN game_name like '% %$search% %' THEN 6 " .
                "WHEN game_abbrev like '% %$search% %' THEN 7 " .
                "ELSE 8 " .
            "END ASC, game_poprank ASC, game_abbrev, game_name" .
            "", 1, 3);
    }
    
    public function GetGames($page)
    {
        return $this->GetGamesHelper("WHERE game_id != 218 and game_abbrev != 'pkmnrandom' and game_id != '2945' ORDER BY game_poprank ASC", $page, 10000);
    }
    
    public function CountGames()
    {
        $results = $this->Select("SELECT count(*) as count FROM game;");
        return $results[0]["count"];
    }

    public function CountPlayers($game)
    {
        parent::EstablishConnection();
        $game = mysql_real_escape_string($game);
        
        $results = $this->Select("SELECT COUNT(players_player_id) as count FROM game_rating, game WHERE game_abbrev='$game' AND game_game_id=game_id AND rating > 0;");
        return $results[0]["count"];
    }
    
    public function GetGame($abbrev)
    {
        parent::EstablishConnection();
        $abbrev = mysql_real_escape_string($abbrev);
        $games = $this->GetGamesHelper("WHERE game_abbrev = '$abbrev'", 1, 1);
        if (count($games) > 0)
        {
            return $games[0];
        }
        
        return new SRL_Core_Game(0, "New Game", "newgame", 0, 0, 0, "");
    }
    
    public function GetGameById($id)
    {
        $games = $this->GetGamesHelper("WHERE game_id = $id", 1, 1);
        if (count($games) > 0)
        {
            return $games[0];
        }
        
        return new SRL_Core_Game(0, "New Game", "newgame", 0, 0, 0, "");
    }
    
    public function CreateGame($abbrev, $name)
    {
        parent::EstablishConnection();
        $abbrev = mysql_real_escape_string($abbrev);
        $name = mysql_real_escape_string($name);
        
        $this->Execute("INSERT INTO game (game_name, game_abbrev, game_popularity, game_recentpop, game_poprank, game_rules) VALUES ('$name', '$abbrev', 0, 0, 9999, '') ON DUPLICATE KEY UPDATE game_name = '$name';");   
        
        $gameId = $this->GetLastInsertId();
        
        return $this->GetGame($abbrev);
    }
}
