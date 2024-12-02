<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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
    
    // Получаем ID тренировки из запроса
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->trainingId)) {
        throw new Exception("Training ID is required");
    }
    
    // Проверяем, существует ли тренировка и принадлежит ли она пользователю
    $checkQuery = "SELECT id FROM trainings WHERE id = ? AND user_id = ? AND status = 'scheduled'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$data->trainingId, $userId]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception("Training not found or already cancelled");
    }
    
    // Отменяем тренировку
    $updateQuery = "UPDATE trainings SET status = 'cancelled' WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    
    if (!$updateStmt->execute([$data->trainingId])) {
        throw new Exception("Failed to cancel training");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Trening został anulowany'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 