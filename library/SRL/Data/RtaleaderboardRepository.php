<?php
class SRL_Data_RtaleaderboardRepository extends SRL_Data_BaseRepository
{
    private $gameRepo;
    private $tagRepo;
    
    function __construct()
    {
        $this->gameRepo = new SRL_Data_GameRepository();
        $this->tagRepo = new SRL_Data_TagRepository();
        parent::__construct();
    }
    
    public function CreateLeaderboard($name, $game_id, $rules, $timingStart, $timingEnd, $description)
    {
        $name = mysql_real_escape_string($name);
        $rules = mysql_real_escape_string($rules);
        $timingStart = mysql_real_escape_string($timingStart);
        $timingEnd = mysql_real_escape_string($timingEnd);
        $description = mysql_real_escape_string($description);
        
        $this->Execute("INSERT INTO rtaleaderboards (name, rules, timing_start, timing_end, description, game_id) VALUES ('$name', '$rules', '$timingStart', '$timingEnd', '$description', $game_id);");
    }
    
    private function GetLeaderboardsHelper($where, $page)
    {
        $lbs = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT lb_id, name, rules, timing_start, timing_end, description, game_id FROM rtaleaderboards $where LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $game = $this->gameRepo->GetGameById($result["game_id"]);
            $lbid = $result["lb_id"];
            
            $milestones = $this->Select("SELECT time FROM rtamilestones WHERE lb_id = $lbid ORDER BY time ASC;");
            $ma = array();
            foreach ($milestones as $milestone)
            {
                array_push($ma, $milestone["time"]);
            }
            $m = join(",", $ma);
            
            $tags = $this->GetTagOptionsForLeaderbord($result["lb_id"]);
            
            $lb = new SRL_Core_Rtaleaderboard($result["lb_id"], $result["name"], $game, $result["timing_start"], $result["timing_end"], $result["rules"], $result["description"], $m, $tags);
            array_push($lbs, $lb);
        }
        
        return $lbs;
    }
    
    public function GetLeaderboardsForGame($game_id)
    {
        return $this->GetLeaderboardsHelper(" WHERE game_id = $game_id ", 1);
    }
    
    public function GetLeaderboard($id)
    {
        $lbs = $this->GetLeaderboardsHelper(" WHERE lb_id = $id ", 1);
        
        if (count($lbs) > 0)
            return $lbs[0];
        else
            return null;
    }
    
    public function AddMilestone($id, $time)
    {
        $results = $this->Select("SELECT * FROM rtamilestones WHERE lb_id = $id AND time = $time;");
        
        if (count($results) == 0)
            $this->Execute("INSERT INTO rtamilestones (lb_id, time) VALUES ($id, $time);");
    }
    
    public function RemoveMilestone($id, $time)
    {
        $this->Execute("DELETE FROM rtamilestones WHERE lb_id = $id and time = $time;");
    }
    
    public function AssignTag($lb_id, $tagGroupId, $tagId)
    {
        $results = $this->Select("SELECT * FROM lb_tags WHERE lb_id = $lb_id AND tag_group_id = $tagGroupId AND tag_id = $tagId;");
        
        if (count($results) == 0)
            $this->Execute("INSERT INTO lb_tags (lb_id, tag_group_id, tag_id) VALUES ($lb_id, $tagGroupId, $tagId);");
    }
    
    public function RemoveTag($lb_id, $tagGroupId, $tagId)
    {
        $this->Execute("DELETE FROM lb_tags WHERE lb_id = $lb_id AND tag_group_id = $tagGroupId AND tag_id = $tagId;");
    }
    
    public function GetTagOptionsForLeaderbord($lb_id)
    {
        $tagGroups = array();
        
        $results = $this->Select("SELECT DISTINCT g.tag_group_id, tag_group_name FROM tag_groups g INNER JOIN lb_tags lb ON lb.tag_group_id = g.tag_group_id WHERE lb.lb_id = $lb_id;");
        foreach ($results as $result)
        {
            $tagGroupId = $result["tag_group_id"];
            $tagsResult = $this->Select("SELECT t.tag_id, tag_value FROM tags t INNER JOIN lb_tags lb ON lb.tag_id = t.tag_id WHERE t.tag_group_id = $tagGroupId AND lb.lb_id = $lb_id;");
            
            $tags = array();
            foreach ($tagsResult as $tagResult)
            {
                $tag = new SRL_Core_Tag($tagResult["tag_id"], $tagResult["tag_value"]);
                array_push($tags, $tag);
            }
            
            $tagGroup = new SRL_Core_TagGroup($tagGroupId, $result["tag_group_name"], $tags);
            array_push($tagGroups, $tagGroup);
        }
        
        return $tagGroups;
    }
}