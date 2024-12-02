<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

error_log("\n=== Token check started ===");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

try {
    $headers = getallheaders();
    error_log("Headers received: " . json_encode($headers));
    
    if (!isset($headers['Authorization'])) {
        throw new Exception("No Authorization header present");
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    error_log("Checking token: " . $token);
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name FROM users WHERE token = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Invalid token");
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Token valid for user: " . $user['name']);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Token is valid'
    ]);
    
} catch (Exception $e) {
    error_log("Token check error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

error_log("=== Token check finished ===\n");
?> 