<?php
define("Secret", "");

class SRL_Data_TokenRepository extends SRL_Data_BaseRepository
{
    private $botKey = "";

    function __construct()
    {
        parent::__construct();
    }

    public function GenerateToken($username, $password)
    {
        $username = strtolower (mysql_real_escape_string($username));
        $results = $this->Select("SELECT hash FROM players WHERE player_name = '$username';");

        if (count($results) < 1)
            return '';

        $hash = $results[0]['hash'];
        if (crypt($password, "$hash") == $hash)
        {
            $token = uniqid('', true);

            $redis = new Predis\Client();
            $redis->set("$token", $username);
            $redis->expire("$token", 600);

            return $token;
        }
        else
        {
            return '';
        }
    }

    public function DoLogin() {
        $redis = new Predis\Client();
        if (isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
            $loginInfo = json_decode(base64_decode($_COOKIE['login']), true);
            $loginTimestamp = $redis->get("timestamp-".$loginInfo['username']) or 0;
            if (md5($loginInfo['id'].$loginInfo['username'].$loginInfo['timestamp'].constant('Secret')) == $loginInfo['signature'] && $loginInfo['timestamp'] > $loginTimestamp) {
                $redis->set("token-".$loginInfo['username'], $loginInfo['signature']);
                $redis->set("timestamp-".$loginInfo['username'], $loginInfo['timestamp']);
            }
        } else if (!isset($_COOKIE['login']) && isset($_SESSION)) {
            session_unset();
            session_destroy();
        }
        die("");
    }

    public function InvalidateSessions($username) {
        $redis = new Predis\Client();
        $redis->set("token-$username", null);
        $redis->set("timestamp-$username", time());
        die("");
    }

    public function GetLoggedInUser($token)
    {
        if ($token == $this->botKey)
        {
            return 'racebot';
        }
        else {
            if (!isset($_SESSION['username']) && isset($_COOKIE['login']) && !empty($_COOKIE['login'])) {
                $loginInfo = json_decode(base64_decode($_COOKIE['login']), true);
                $redis = new Predis\Client();
                if (md5($loginInfo['id'].$loginInfo['username'].$loginInfo['timestamp'].constant('Secret')) == $loginInfo['signature'] && $redis->get("token-".$loginInfo['username']) == $loginInfo['signature']) {
                    $_SESSION['id'] = $loginInfo['id'];
                    $_SESSION['username'] = $loginInfo['username'];
                    return $_SESSION['username'];
                }
            } else if (!isset($_COOKIE['login']) && isset($_SESSION)) {
                session_unset();
                session_destroy();
            }
        }

        return 'anon';
    }

    public function SavePassword($username, $oldPassword, $newPassword)
    {
        $username = strtolower(mysql_real_escape_string($username));

        if (!$this->GenerateToken($username, $oldPassword))
            return false;

        $salt = $this->MakeSalt($username);
        $hash = crypt("$newPassword", '$2a$10$' . $salt . '$');
        $this->Execute("UPDATE players SET hash = '$hash' WHERE player_name = '$username';");
        return true;
    }

    private function MakeSalt($base)
    {
        $salt = substr($base, 0, 22);
        $salt = preg_replace("/[^a-zA-Z0-9\']/", "", $salt);
        while (strlen($salt) < 22)
        {
            $salt .= '4';
        }

        return $salt;
    }
}
