<?php
class SRL_Data_SeasonGoalRepository extends SRL_Data_BaseRepository
{
    private $goalRepo;
    private $ratingRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->pageSize = 9000;
        $this->goalRepo = new SRL_Data_GoalRepository();
        $this->ratingRepo = new SRL_Data_SeasonGoalsRatingRepository();
    }
    
    private function GetSeasonGoalHelper($where, $page, $lbLimit)
    {
        $seasons = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT season_goal_id, goal_id FROM season_goals $where ORDER BY season_goal_id ASC LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $tracked_goal = $this->goalRepo->GetGoal($result["goal_id"]);
            $ranks = $this->ratingRepo->GetSeasonGoalLeaderboard($result["season_goal_id"], $lbLimit);
            $unranks = $this->ratingRepo->GetSeasonGoalUnrankedLeaderboard($result["season_goal_id"], $lbLimit);
            $season = new SRL_Core_SeasonGoal($result["season_goal_id"], $tracked_goal, $ranks, $unranks);
            array_push($seasons, $season);
        }
        
        return $seasons;
    }
    
    public function GetSeasonGoals($season_id)
    {
        return $this->GetSeasonGoalHelper(" WHERE season_id = $season_id ", 1, 5);
    }
    
    public function GetSeasonGoal($season_goal_id)
    {
        $result = $this->GetSeasonGoalHelper(" WHERE season_goal_id = $season_goal_id ", 1, 9999);
        return $result[0];
    }
    
    public function AddGoalToSeason($season_id, $goal_id)
    {
        $tracked_goal = $this->goalRepo->GetGoal($goal_id);
        if ($tracked_goal != null)
        {
            $this->Execute("INSERT INTO season_goals (season_id, goal_id) VALUES ($season_id, $goal_id);");
        }
    }
    
    public function GetSeasonGoalBySeason($season_id, $goal_id)
    {
        $results = $this->GetSeasonGoalHelper(" WHERE season_id = $season_id AND goal_id = $goal_id ", 1, 9999);
        return count($results) > 0 ? $results[0] : null;
    }
    
    public function GetSeasonGoalRating($season_goal_id, $player_id)
    {
        $results = $this->Select("SELECT player_id, rating, sigma, mu FROM season_rating WHERE player_id = $player_id and season_goal_id = $season_goal_id;");
        
        if (count($results) > 0)
        {
            $rating->name = $results[0]["player_id"];
            $rating->rating = $results[0]["rating"];
            $rating->sigma = $results[0]["sigma"];
            $rating->mu = $results[0]["mu"];
            
            return $rating;
        }
        else
        {
            $rating->name = $player_id;
            $rating->rating = 0;
            $rating->sigma = 8.3333333;
            $rating->mu = 25;
            
            return $rating;
        }
    }
    
    public function RerankSeasonGoal($season_goal_id)
    {
        $ranks = $this->Select("SELECT player_id, rating FROM season_rating WHERE season_goal_id = $season_goal_id ORDER BY rating DESC;");
        $currentRank = 0;
        $prevRank = 1;
        $prevRating = 0;
        
        foreach ($ranks as $rank)
        {
            $currentRank++;
            $playerId = $rank["player_id"];
            $rating = floor($rank["rating"] * 40);
            
            $playerRank = $currentRank;
            if ($prevRating == $rating) // ties in rank
            {
                $playerRank = $prevRank;
            }
            else
            {
                $prevRating = $rating;
                $prevRank = $currentRank;
            }
            
            $this->Execute("UPDATE season_rating SET rank = $playerRank WHERE season_goal_id = $season_goal_id AND player_id = $playerId;");
        }
    }
}