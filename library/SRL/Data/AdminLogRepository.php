<?php
class SRL_Data_AdminLogRepository extends SRL_Data_BaseRepository
{
	public function GetCount($search = "")
	{
		if ( $search == "" ) {
			$searchQuery = "";
		}
		else {
			$search = mysql_escape_string($search);
			$searchQuery = " WHERE source LIKE '%$search%' OR action LIKE '%$search%' OR target LIKE '%$search%' OR comment LIKE '%$search%'";
		}
		return $this->Select("SELECT count(*) AS count FROM admin_log".$searchQuery)[0]["count"];
	}
	public function GetPurgeCount($search = "")
	{
		if ( $search == "" ) {
			$searchQuery = " WHERE action = 'purged'";
		}
		else {
			$search = mysql_escape_string($search);
			$searchQuery = " WHERE (source LIKE '%$search%' OR action LIKE '%$search%' OR target LIKE '%$search%' OR comment LIKE '%$search%') AND action = 'purged'";
		}
		return $this->Select("SELECT count(*) AS count FROM admin_log".$searchQuery)[0]["count"];
	}
	public function GetEntries($page, $search = "") {
		$entries = array();
		$offset = $page * 20;
		if ( $search == "" ) {
			$searchQuery = "";
		}
		else {
			$search = mysql_escape_string($search);
			$searchQuery = " WHERE source LIKE '%$search%' OR action LIKE '%$search%' OR target LIKE '%$search%' OR comment LIKE '%$search%'";
		}
		$logEntries = $this->Select("SELECT timestamp, source, action, target, comment FROM admin_log".$searchQuery." ORDER BY id DESC LIMIT 20 OFFSET $offset");
		foreach ( $logEntries as $logEntry ) {
			$entry = new SRL_Core_LogEntry($logEntry["timestamp"], $logEntry["source"], $logEntry["action"], $logEntry["target"], str_replace("\n", "\\n", $logEntry["comment"]));
			array_push($entries, $entry);
		}
		return $entries;
	}
	public function GetPurgeEntries($page, $search = "") {
		$entries = array();
		$offset = $page * 20;
		if ( $search == "" ) {
			$searchQuery = " WHERE action = 'purged'";
		}
		else {
			$search = mysql_escape_string($search);
			$searchQuery = " WHERE (source LIKE '%$search%' OR action LIKE '%$search%' OR target LIKE '%$search%' OR comment LIKE '%$search%') AND action = 'purged'";
		}
		$logEntries = $this->Select("SELECT timestamp, source, action, target, comment FROM admin_log".$searchQuery." ORDER BY id DESC LIMIT 20 OFFSET $offset");
		foreach ( $logEntries as $logEntry ) {
			$entry = new SRL_Core_LogEntry($logEntry["timestamp"], $logEntry["source"], $logEntry["action"], $logEntry["target"], str_replace("\n", "\\n", $logEntry["comment"]));
			array_push($entries, $entry);
		}
		return $entries;
	}
	public function GetLastPurgeEntry($user) {
		$user = mysql_escape_string($user);
		$logEntry = $this->Select("SELECT timestamp, source, action, target, comment FROM admin_log WHERE action = 'purged' AND target = '$user' ORDER BY id DESC LIMIT 1");
		if ( count($logEntry) == 0 ) { return new SRL_Core_LogEntry("", "", "", "", ""); }
		else { return new SRL_Core_LogEntry($logEntry[0]["timestamp"], $logEntry[0]["source"], $logEntry[0]["action"], $logEntry[0]["target"], str_replace("\n", "\\n", $logEntry[0]["comment"])); }
	}
	public function LogAction($source, $action, $target, $comment = "")
	{
		$source = mysql_escape_string($source);
		$action = mysql_escape_string($action);
		$target = mysql_escape_string($target);
		$comment = mysql_escape_string($comment);
		$this->Execute("INSERT INTO admin_log VALUES (NULL, NULL, '$source', '$action', '$target', '$comment')");
	}
}
?>