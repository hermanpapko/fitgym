<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, name, email, avatar FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'user' => $user
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 