<?php
class SRL_Data_StreamprefsRepository extends SRL_Data_BaseRepository
{
	private $streamRepo;

	function __construct()
	{
		parent::__construct();
		$this->streamRepo = new SRL_Data_StreamRepository();
	}

	function getCustomStreams($username) {
		return [];
	}

	function clearPrefs($username) {
		$username = mysql_escape_string($username);
		$this->Execute("DELETE FROM stream_prefs WHERE username = '$username'");
	}

	function getPrefsHelper($username, $type) {
		$username = mysql_escape_string($username);
		$type = mysql_escape_string($type);
		$query = "SELECT streamname FROM stream_prefs WHERE username = '$username' AND type = $type";
		$temp = $this->Select($query);
		foreach ( $temp as &$thing ) {
			$thing["streamname"] = str_replace('"', '\"', $thing["streamname"]);
		}
		return $temp;
	}

	function setPrefsHelper($username, $type, $stream) {
		$username = mysql_escape_string($username);
		$type = mysql_escape_string($type);
		$stream = mysql_escape_string($stream);
		$this->Execute("INSERT IGNORE INTO stream_prefs VALUES ('$username', $type, '$stream')");
	}

	function delPrefsHelper($username, $type, $stream) {
		$username = mysql_escape_string($username);
		$type = mysql_escape_string($type);
		$stream = mysql_escape_string($stream);
		$this->Execute("DELETE FROM stream_prefs WHERE username = '$username' AND type = $type AND streamname = '$stream'");
	}

	function isLocked($username) {
		$wc = $this->streamRepo->getWarningCount($username);
		$twenty_four_hours_ago = 86400;
		$one_week_ago = 604800;
		if ( $wc <= 1 ) { return false; }
		else if ( $wc <= 3 && $this->streamRepo->getLastWarned($username)+$twenty_four_hours_ago < time() ) { return false; }
		else if ( $this->streamRepo->getLastWarned($username)+$one_week_ago < time() ) { return false; }
		return true;
	}

	function getLocks() {
		$entries = array();
		$purges = $this->Select("SELECT user, num_warnings, (CASE WHEN num_warnings > 3 THEN UNIX_TIMESTAMP(last_warning) + 604800 - UNIX_TIMESTAMP(NOW()) ELSE UNIX_TIMESTAMP(last_warning) + 86400 - UNIX_TIMESTAMP(NOW()) END ) AS remaining FROM streams WHERE ( num_warnings > 1 AND (UNIX_TIMESTAMP(last_warning) + 86400 > UNIX_TIMESTAMP(NOW())) ) OR ( num_warnings > 3 AND (UNIX_TIMESTAMP(last_warning) + 604800 > UNIX_TIMESTAMP(NOW())) ) ORDER BY user");
		foreach ( $purges as $purge ) {
			$entry = new SRL_Core_Purge($purge["user"], $purge["num_warnings"], $purge["remaining"]);
			array_push($entries, $entry);
		}
		return $entries;
	}

	function getDefaultSortPreference($username) {
		$username = mysql_escape_string($username);
		$temp = $this->Select("SELECT streamname FROM stream_prefs WHERE username = '$username' AND type = 6 LIMIT 1");
		if ( count($temp) == 0 ) {
			return 0;
		}
		else {
			return $temp[0]["streamname"];
		}
	}

	function setDefaultSortPreference($username, $sort) {
		$username = mysql_escape_string($username);
		$sort = mysql_escape_string($sort);
		$temp = $this->Select("SELECT streamname FROM stream_prefs WHERE username = '$username' AND type = 6 LIMIT 1");
		if ( count($temp) > 0 ) {
			$this->Execute("UPDATE stream_prefs SET streamname = '$sort' WHERE username = '$username' AND type = 6");
		}
		else {
			$this->Execute("INSERT INTO stream_prefs VALUES ('$username', 6, '$sort')");
		}
	}

	function getPinnedStreams($username)
	{
		return $this->getPrefsHelper($username, 1);
	}

	function getHiddenStreams($username)
	{
		return $this->getPrefsHelper($username, 2);
	}

	function getPinnedGames($username)
	{
		return $this->getPrefsHelper($username, 4);
	}

	function getHiddenGames($username)
	{
		return $this->getPrefsHelper($username, 3);
	}

	function pinStream($username, $stream)
	{
		$this->setPrefsHelper($username, 1, $stream);
	}

	function hideStream($username, $stream)
	{
		$this->setPrefsHelper($username, 2, $stream);
	}

	function pinGame($username, $game)
	{
		$this->setPrefsHelper($username, 4, $game);
	}

	function unpinGame($username, $game)
	{
		$this->delPrefsHelper($username, 4, $game);
	}

	function hideGame($username, $game)
	{
		$this->setPrefsHelper($username, 3, $game);
	}

	function unpinStream($username, $stream)
	{
		$this->delPrefsHelper($username, 1, $stream);
	}

	function unhideStream($username, $stream)
	{
		$this->delPrefsHelper($username, 2, $stream);
	}
	
	function unhideGame($username, $game)
	{
		$this->delPrefsHelper($username, 3, $game);
	}

	function SetImportPreference($username, $stream)
	{
		$stream = intval($stream);
		if ( $stream >= 0 && $stream <= 2 ) {
			$this->Execute("DELETE FROM stream_prefs WHERE type = 5 AND username = '$username'");
			$this->setPrefsHelper($username, 5, $stream);
		}
	}

	function GetImportPreference($username)
	{
		$temp = $this->getPrefsHelper($username, 5);
		if ( count($temp) == 0 ) {
			return 0;
		}
		else {
			if ( $temp[0]["streamname"] >= 0 && $temp[0]["streamname"] <= 2 ) {
				return $temp[0]["streamname"];
			}
			else {
				return 0;
			}
		}
	}

}