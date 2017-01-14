<?php
class SRL_Data_RtatimesRepository extends SRL_Data_BaseRepository
{
    private $rtalbRepo;
    private $playerRepo;
    private $tagRepo;
    
    function __construct()
    {
        parent::__construct();
        $this->pageSize = 9999;
        $this->rtalbRepo = new SRL_Data_RtaleaderboardRepository();
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    public function AddRtaTime($lb_id, $player_id, $time, $video, $notes, $day, $month, $year, $tags)
    {
        if ($time < 1) {
            return;
        }
        
        $tagOptions = $this->rtalbRepo->GetTagOptionsForLeaderbord($lb_id);
        foreach ($tags as $tag_group_id => $tag_id) {
            $tagFound = false;
            
            foreach ($tagOptions as $tagKey => $tagOption) {
                if ($tagOption->Id() == $tag_group_id
                    && $tagOption->Contains($tag_id))
                {
                    $tagFound = true;
                    unset($tagOptions[$tagKey]);
                    break;
                }
            }
            
            if (!$tagFound)
            {
                return;
            }
        }
        
        if (count($tagOptions) > 0)
        {
            return;
        }
        
        $video = mysql_real_escape_string($video);
        $notes = mysql_real_escape_string($notes);
        
        $date = time();
        $this->Execute("INSERT INTO rtatimes (lb_id, player_id, time, rank, video, notes, date, day, month, year) VALUES ($lb_id, $player_id, $time, 0, '$video', '$notes', $date, $day, $month, $year);");
        
        $rta_id = $this->GetLastInsertId();
        foreach ($tags as $tag_group_id => $tag_id) {
            $this->Execute("INSERT INTO rta_tags (rta_id, tag_group_id, tag_id) VALUES ($rta_id, $tag_group_id, $tag_id);");
        }
        
        $this->RerankTimes($lb_id);
    }
    
    private function GetTimeForPlayerForLeaderboard($player_id, $lb_id)
    {
        $results = $this->GetLeaderboardTimesHelper(" WHERE lb_id = $lb_id AND player_id = $player_id ", 1);
        
        if (count($results) > 0)
        {
            return $results[0];
        }
        else
        {
            return null;
        }
    }
    
    private function GetLeaderboardTimesHelper($where, $page)
    {
        $rtatimes = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT DISTINCT r.rta_id, r.lb_id, player_id, time, rank, video, notes, date, day, month, year FROM rtatimes r $where ORDER BY time ASC LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $lb = $this->rtalbRepo->GetLeaderboard($result["lb_id"]);
            $player = $this->playerRepo->GetPlayerById($result["player_id"]);
            
            $tags = $this->GetTagsForRtatime($result["rta_id"]);
            
            $rtatime = new SRL_Core_Rtatime($result["rta_id"], $lb, $player, $result["time"], $result["rank"], $result["video"], $result["notes"], $result["date"], $result["day"], $result["month"], $result["year"], $tags);
            array_push($rtatimes, $rtatime);
        }
        
        return $rtatimes;
    }
    
    public function GetLeaderboardTimes($lb_id)
    {
        return $this->GetLeaderboardTimesHelper(" WHERE lb_id = $lb_id ", 1);
    }
    
    public function GetLeaderboardTimesWithTags($lb_id, $tags)
    {
        $tags = mysql_real_escape_string($tags);
        if (empty($tags))
        {
            return $this->GetLeaderboardTimesHelper(" WHERE lb_id = $lb_id AND rank < 9000", 1);
        }
        else
        {
            return $this->GetLeaderboardTimesHelper(" WHERE lb_id = $lb_id AND rta_id = (SELECT r2.rta_id FROM rtatimes r2 INNER JOIN rta_tags t ON t.rta_id = r2.rta_id WHERE r.player_id = r2.player_id AND lb_id = $lb_id AND t.tag_id IN ($tags) ORDER BY r2.time ASC LIMIT 1) ", 1);
        }
    }
    
    public function GetTagsForRtatime($rta_id)
    {
        $tagGroups = array();
        
        $results = $this->Select("SELECT g.tag_group_id, tag_group_name FROM tag_groups g INNER JOIN rta_tags r ON r.tag_group_id = g.tag_group_id WHERE r.rta_id = $rta_id;");
        foreach ($results as $result)
        {
            $tagGroupId = $result["tag_group_id"];
            $tagsResult = $this->Select("SELECT t.tag_id, tag_value FROM tags t INNER JOIN rta_tags r ON r.tag_id = t.tag_id WHERE t.tag_group_id = $tagGroupId AND r.rta_id = $rta_id;");
            
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
    
    private function RerankTimes($lb_id)
    {
        $this->Execute("UPDATE rtatimes SET rank = 9000 WHERE lb_id = $lb_id;");
        
        $times = $this->Select("SELECT rta_id, time FROM rtatimes r WHERE lb_id = $lb_id AND rta_id = (SELECT r2.rta_id FROM rtatimes r2 WHERE r.player_id = r2.player_id AND lb_id = $lb_id ORDER BY r2.time ASC LIMIT 1) ORDER BY time ASC;");
        
        $place = 0;
        $order = 0;
        $lastTime = -1;
        foreach ($times as $time) {
            $order++;
            
            if ($time["time"] != $lastTime)
            {
                $place = $order;
            }
            
            $rtaid = $time["rta_id"];
            $this->Execute("UPDATE rtatimes SET rank = $place WHERE rta_id = $rtaid;");
            $lastTime = $time["time"];
        }
    }
}