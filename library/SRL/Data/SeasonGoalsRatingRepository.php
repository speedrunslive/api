<?php
class SRL_Data_SeasonGoalsRatingRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function GetSeasonGoalLeaderboard($season_goal_id, $lbLimit)
    {
        $results = $this->Select("SELECT p.player_name as player_name, sr.rating * 40 as rating, sr.mu, sr.sigma, sr.rank FROM players p INNER JOIN season_rating sr ON sr.player_id = p.player_id AND sr.season_goal_id = $season_goal_id WHERE rating > 0 ORDER BY sr.rating DESC limit $lbLimit;");
        return $results;
    }
    
    public function GetSeasonGoalUnrankedLeaderboard($season_goal_id, $lbLimit)
    {
        $results = $this->Select("SELECT p.player_name as player_name, sr.rating * 40 as rating, sr.mu, sr.sigma, sr.rank FROM players p INNER JOIN season_rating sr ON sr.player_id = p.player_id AND sr.season_goal_id = $season_goal_id WHERE rating <= 0 ORDER BY player_name limit $lbLimit;");
        return $results;
    }
}