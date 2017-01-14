<?php
class SRL_Data_AuditLogRepository extends SRL_Data_BaseRepository
{
    function __construct()
    {
        parent::__construct();
    }
    
    public function Log($username, $role, $controller, $action, $body, $uri)
    {
        $conn = $this->GetPreparedConnection();
        $stmt = $conn->prepare("INSERT INTO log (time, username, role, controller, action, body, uri) VALUES ((?),(?),(?),(?),(?),(?),(?));");
        $stmt->bind_param('issssss', time(), $username, $role, $controller, $action, $body, $uri);
        
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}