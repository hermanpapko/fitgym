<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->training_id)) {
        throw new Exception("Training ID is required");
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Проверяем, существует ли тренировка и принадлежит ли она пользователю
    $checkQuery = "SELECT id FROM trainings 
                  WHERE id = ? AND user_id = ? AND status = 'scheduled'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$data->training_id, $userId]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("Training not found or already cancelled");
    }
    
    // От��еняем тренировку
    $query = "UPDATE trainings SET status = 'cancelled' WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$data->training_id])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Trening został anulowany'
        ]);
    } else {
        throw new Exception("Failed to cancel training");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 