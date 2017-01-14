<?php
class SRL_Data_TagRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
        $this->pageSize = 9999;
    }
    
    public function AddTagGroup($name)
    {
        $name = mysql_real_escape_string($name);
        if (!$this->TagGroupExists($name))
        {
            $this->Execute("INSERT INTO tag_groups (tag_group_name) VALUES ('$name');");
        }
    }
    
    private function TagGroupExists($name)
    {
        $name = mysql_real_escape_string($name);
        $results = $this->Select("SELECT * FROM tag_groups WHERE tag_group_name = '$name';");
        return count($results) > 0;
    }
    
    public function GetTagGroupsHelper($where, $page)
    {
        $tagGroups = array();
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        
        $results = $this->Select("SELECT tag_group_id, tag_group_name FROM tag_groups $where LIMIT $offset, $pageSize;");
        foreach ($results as $result)
        {
            $tagGroupId = $result["tag_group_id"];
            $tagsResult = $this->Select("SELECT tag_id, tag_value FROM tags WHERE tag_group_id = $tagGroupId;");
            
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
    
    public function GetTagGroups()
    {
        return $this->GetTagGroupsHelper("", 1);
    }
    
    public function GetTagGroup($name)
    {
        $name = mysql_real_escape_string($name);
        $results = $this->GetTagGroupsHelper(" WHERE tag_group_name = '$name' ", 1);
        if (count($results) > 0)
            return $results[0];
        else
            return null;
    }
    
    public function AddTag($groupId, $name)
    {
        $name = mysql_real_escape_string($name);
        
        $this->Execute("INSERT INTO tags (tag_group_id, tag_value) VALUES ($groupId, '$name');");
    }
    
    public function DeleteTag($id)
    {
        $this->Execute("DELETE FROM tags WHERE tag_id = $id;");
    }
}