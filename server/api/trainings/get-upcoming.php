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
    
    $query = "SELECT t.*, u.name as trainer_name 
              FROM trainings t 
              JOIN users u ON t.trainer_id = u.id 
              WHERE t.user_id = ? 
              AND t.date >= CURDATE() 
              AND t.status = 'scheduled'
              ORDER BY t.date ASC, t.time ASC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'trainings' => $trainings
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 