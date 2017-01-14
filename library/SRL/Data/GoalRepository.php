<?php
class SRL_Data_GoalRepository extends SRL_Data_BaseRepository
{
    private $resultRepo;
    private $gameRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->resultRepo = new SRL_Data_PastRaceResultRepository();
        $this->gameRepo = new SRL_Data_GameRepository();
    }
    
    private function GetGoalsHelper($where)
    {
        $goals = array();
        
        $results = $this->Select("SELECT goal_id, game_game_id, tracked_goal FROM tracked_goals $where ORDER BY tracked_goal;");
        foreach ($results as $result)
        {
            $game = $this->gameRepo->GetGameById($result["game_game_id"]);
            $goal = new SRL_Core_Goal($result["goal_id"], $result["tracked_goal"], $game);
            array_push($goals, $goal);
        }
        
        return $goals;
    }
    
    public function GetGoals($gameId)
    {
        return $this->GetGoalsHelper("WHERE game_game_id = $gameId");
    }
    
    public function GetGoal($goalId)
    {
        $results = $this->GetGoalsHelper("WHERE goal_id = $goalId");
        return count($results) > 0 ? $results[0] : null;
    }
    
    public function CountGoals($gameId)
    {
        $results = $this->Select("SELECT COUNT(*) as COUNT FROM goals WHERE game_game_id = $gameId;");
        return $results[0]["count"];
    }
    
    public function CreateGoal($gameId, $goal)
    {
        $goal = mysql_real_escape_string($goal);
        
        $this->Execute("INSERT INTO tracked_goals (game_game_id, tracked_goal) VALUES ($gameId, '$goal');");
    }
    
    public function GetTopGoalsForGame($gameId, $limit, $season)
    {
        $results = $this->Select("SELECT goal_id, tracked_goal, count(*) AS count FROM tracked_goals t INNER JOIN races ON goal_id = goal_goal_id WHERE t.game_game_id = $gameId AND season_id = $season GROUP BY goal_id, tracked_goal ORDER BY count desc LIMIT $limit;");
        return $results;
    }
    
    public function GetTopGoalsAndTimesForGame($gameId, $season)
    {
        $topGoals = $this->GetTopGoalsForGame($gameId, 8, $season);
        
        $allTopGoals = array();
        foreach ($topGoals as $topGoal)
        {
            $goal = $this->GetGoal($topGoal["goal_id"]);
            $realTopGoal = new SRL_Core_TopGoalTimes($goal);
            $goalId = $goal->Id();
            
            $results = $this->Select("SELECT min(rl.race_link_id) as race_link_id, z.players_player_id, z.lowestTime FROM (SELECT players_player_id, MIN(time) AS lowestTime FROM race_link INNER JOIN races ON race_id = races_race_id WHERE goal_goal_id = $goalId AND time > 0 AND game_game_id = $gameId and season_id = $season GROUP BY players_player_id ORDER BY lowestTime ASC LIMIT 10) AS z INNER JOIN race_link rl ON rl.players_player_id = z.players_player_id AND z.lowestTime = rl.time INNER JOIN races r on rl.races_race_id = r.race_id WHERE game_game_id = $gameId GROUP BY z.players_player_id, z.lowestTime ORDER BY lowestTime;");
            foreach ($results as $result)
            {
                $raceResult = $this->resultRepo->GetRaceResult($result["race_link_id"]);
                $realTopGoal->AddTopTime($raceResult);
            }
            
            array_push($allTopGoals, $realTopGoal);
            unset($realTopGoal);
            unset($topGoal);
        }
        
        return $allTopGoals;
    }
    
    public function ReassignGoal($oldGoal, $newGoal)
    {
        $oldGoal = mysql_real_escape_string($oldGoal);
        $newGoal = mysql_real_escape_string($newGoal);
        
        $results = $this->Select("SELECT game_game_id FROM tracked_goals WHERE goal_id in ($oldGoal, $newGoal) GROUP BY game_game_id;");
        if (count($results) == 1)
        {
            $this->Execute("UPDATE races SET goal_goal_id = $newGoal WHERE goal_goal_id = '$oldGoal';");
            return $this->GetTopGoalsForGame($results[0]["game_game_id"], 9999);
        }
        
        return null;
    }
}