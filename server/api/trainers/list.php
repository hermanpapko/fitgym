<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

try {
    $userId = authenticate();
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT u.id, u.name, t.specialization 
              FROM users u 
              LEFT JOIN trainers t ON u.id = t.user_id 
              WHERE u.role = 'trainer'";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'trainers' => $trainers
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 