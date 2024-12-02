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
    // Проверяем входящие данные
    $rawData = file_get_contents("php://input");
    error_log("Raw input data: " . $rawData);
    
    $data = json_decode($rawData);
    if (!$data) {
        error_log("JSON decode error: " . json_last_error_msg());
        throw new Exception("Invalid JSON data");
    }
    
    // Проверяем подключение к базе данных
    $database = new Database();
    $db = $database->getConnection();
    
    // Проверяем авторизацию
    $userId = authenticate();
    error_log("User ID: " . $userId);
    
    // Проверяем тренера
    $checkTrainer = $db->prepare("SELECT COUNT(*) FROM users WHERE id = ? AND role = 'trainer'");
    $checkTrainer->execute([$data->trainer_id]);
    $trainerExists = $checkTrainer->fetchColumn();
    error_log("Trainer exists: " . ($trainerExists ? 'Yes' : 'No'));
    
    if (!$trainerExists) {
        throw new Exception("Trainer not found");
    }
    
    // Добавляем тренировку
    $query = "INSERT INTO trainings (user_id, trainer_id, type, date, time, status) 
              VALUES (?, ?, ?, ?, ?, 'scheduled')";
              
    $params = [$userId, $data->trainer_id, $data->type, $data->date, $data->time];
    error_log("Query params: " . json_encode($params));
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute($params);
    
    if (!$result) {
        error_log("Database error: " . json_encode($stmt->errorInfo()));
        throw new Exception("Failed to create training");
    }
    
    $newId = $db->lastInsertId();
    error_log("New training ID: " . $newId);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Trening został zaplanowany',
        'training_id' => $newId
    ]);
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

error_log("=== Training creation finished ===\n");
?> 