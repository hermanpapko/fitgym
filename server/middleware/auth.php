<?php
function authenticate() {
    $headers = getallheaders();
    error_log("Auth headers: " . json_encode($headers));
    
    if (!isset($headers['Authorization'])) {
        throw new Exception('No authorization token provided');
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    error_log("Auth token: " . $token);
    
    $database = new Database();
    $db = $database->getConnection();
    
    $stmt = $db->prepare("SELECT id FROM users WHERE token = ?");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Invalid or expired token');
    }
    
    $userId = $stmt->fetchColumn();
    error_log("Authenticated user ID: " . $userId);
    return $userId;
}
?> 