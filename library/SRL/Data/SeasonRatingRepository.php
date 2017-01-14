<?php
class SRL_Data_SeasonRatingRepository extends SRL_Data_BaseRepository
{
    private $seasonRepo;
    private $ratingRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->seasonRepo = new SRL_Data_SeasonRepository();
        $this->ratingRepo = new SRL_Data_RatingRepository();
    }
    
    public function GetSeasonLeaderboardForGame($season_id, $game_id)
    {
        $results = null;
        
        if ($season_id == $this->seasonRepo->GetActiveSeason()) {
            $results = $this->ratingRepo->GetGameLeaderboard($game_id);
        }
        else {
            $results = $this->Select("SELECT p.player_name as player_name, (sr.rating - sr.sigma * 3) * 40 as rating, sr.mu, sr.sigma, sr.rank FROM players p INNER JOIN past_season_game_rating sr ON sr.players_player_id = p.player_id AND sr.season_id = $season_id WHERE rating > 0 AND sr.game_game_id = $game_id ORDER BY rating DESC;");
        }
        return $results;
    }
    
    public function GetSeasonUnrankedLeaderboardForGame($season_id, $game_id)
    {
        $results = null;
        
        if ($season_id == $this->seasonRepo->GetActiveSeason()) {
            $results = $this->ratingRepo->GetGameUnrankedLeaderboard($game_id);
        }
        else {
            $results = $this->Select("SELECT p.player_name as player_name, (sr.rating - sr.sigma * 3) * 40 as rating, sr.mu, sr.sigma, sr.rank FROM players p INNER JOIN past_season_game_rating sr ON sr.players_player_id = p.player_id AND sr.season_id = $season_id WHERE rating <= 0 AND sr.game_game_id = $game_id ORDER BY player_name;");
        }
        return $results;
    }
    
    
}
