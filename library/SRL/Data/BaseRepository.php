<?php

abstract class SRL_Data_BaseRepository
{
    private $conn;
    protected $pageSize = 50;
    private $mysql_database = "srl_data";
    private $mysql_hostname = "localhost";
    private $mysql_username = "";
    private $mysql_password = "";
    private $logname = "";
    private $debugcount = 0;
    private $debugid = "";
    private $isDebugging = false;
    // 0 = none
    // 3 = everything (this will include stack traces so yeah enjoy that)
    // 2 = extensive (default value for logging, will also log queries)
    // 1 = minimal, only things explicitly set to log to minimal
    private $debuglevel = 2;
    /*
     * The constructor used to create a connection.
     * We've removed this so that it only creates
     * a connection when the queries are executed
     * or when explicitly calling EstablishConnection().
     */
    function __construct()
    {
        $this->logname = "api-debugginglog-" . get_class($this) . ".log";
        if ($_SERVER["SERVER_NAME"] == "api-beta.speedrunslive.com") {
            $this->isDebugging = true;
            $this->debugid = md5(time());
        }
    }

    /*
     * Check for connection and open one if
     * the connection is not currently established.
     */
    function EstablishConnection() {
        if ($this->conn == null) {
            $this->conn = mysql_connect($this->mysql_hostname, $this->mysql_username, $this->mysql_password);
            $this->DebuggingConnection();
            if($this->conn === FALSE) {
                die("mysql connection error: " . mysql_error() . "\n");
            }//if($this->conn === FALSE)
           mysql_select_db($this->mysql_database, $this->conn);

           mysql_set_charset('utf8',$this->conn);
        }//if ($this->conn == null)
        if($this->conn === FALSE) {
            die("mysql connection error: " . mysql_error() . "\n");
        }//if($this->conn === FALSE)
    }//function EstablishConnection()

    public function PageSize()

    {
        return $this->pageSize;
    }
    /*
     * Return resultset of a query.
     */
    function Select($query)
    {
        $results = array();

        $rows = $this->Execute($query);
        while ($row = mysql_fetch_assoc($rows)) {
            array_push($results, $row);
        }

        return $results;
    }
    /*
     * Execute a query, establishing a connection first.
     */
    function Execute($query)
    {
        // Establish connection when query is executed instead of when
        // repository constructor is called.
        $this->EstablishConnection();
        if($this->conn === FALSE) {
            die("mysql connection error: " . mysql_error() . "\n");
        }
        $results = mysql_query($query) or die("Error: " . mysql_error() . "\n on query: " . $query);
        $this->DebuggingQuery($query);
        return $results;
    }

    function GetLastInsertId()
    {
        return mysql_insert_id($this->conn);
    }

    function GetPreparedConnection()
    {
        return mysqli_connect($this->mysql_hostname, $this->mysql_username, $this->mysql_password, $this->mysql_database);
    }
    /*
     * Debugging messages
     */
    function DebuggingConnection() {
        if ($this->isDebugging) {
            //error_log("Class " . get_class($this) . " is establishing a connection to the database.");
        }
    }
    function DebuggingQuery($query) {
        if ($this->isDebugging) {
            $this->WriteLogAll("[" . get_class($this) . "] Executing query " . $query . ".");
        }
    }
    function DebuggingMessage($message) {
        if ($this->isDebugging) {
            $this->WriteLogExtensive($message);
        }
    }
    private function WriteLog($message, $level = 2) {
        if ($this->debuglevel >= $level) {
            //$this->WriteLogMessage($message);
        }
    }

    function WriteLogMinimal($message) {
        $this->WriteLog($message, 1);
    }
    function WriteLogExtensive($message) {
        $this->WriteLog($message, 2);
    }
    function WriteLogAll($message) {
        $this->WriteLog($message, 3);
    }
    private function WriteLogMessage($message) {
        $stringprint = "[ID#: " . substr($this->debugid,0,12) . "] [" . date('d/m/y G:i:s', microtime()) . "] ";
        $stringprint .= $message  . "\r\n";
        file_put_contents($this->logname, $stringprint, FILE_APPEND);

    }
}
