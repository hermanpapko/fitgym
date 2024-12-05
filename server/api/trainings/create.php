<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Обрабатываем preflight запрос
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

// Включаем отображение всех ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Записываем все входящие данные в лог
error_log("=== New request to create.php ===");
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Raw input: " . file_get_contents("php://input"));
error_log("Headers: " . json_encode(getallheaders()));

try {
    // Проверяем метод запроса
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Получаем и проверяем ID пользователя
    $userId = authenticate();
    error_log("Authenticated user ID: " . $userId);

    // Получаем данные запроса
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }

    error_log("Decoded data: " . print_r($data, true));

    // Проверяем наличие всех необходимых данных
    if (!isset($data->type) || !isset($data->date) || !isset($data->time) || !isset($data->trainer_id)) {
        throw new Exception("Missing required fields");
    }

    $database = new Database();
    $db = $database->getConnection();

    // Проверяем существование тренера
    $trainerCheck = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'trainer'");
    $trainerCheck->execute([$data->trainer_id]);
    if ($trainerCheck->rowCount() === 0) {
        throw new Exception("Trainer not found");
    }

    // Проверяем занятость времени
    $checkQuery = "SELECT COUNT(*) FROM trainings 
                  WHERE trainer_id = ? AND date = ? AND time = ? 
                  AND status = 'scheduled'";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$data->trainer_id, $data->date, $data->time]);
    
    if ($checkStmt->fetchColumn() > 0) {
        throw new Exception("This time slot is already taken");
    }

    // Добавляем тренировку
    $query = "INSERT INTO trainings (user_id, trainer_id, type, date, time, status) 
              VALUES (?, ?, ?, ?, ?, 'scheduled')";
    
    $stmt = $db->prepare($query);
    $params = [$userId, $data->trainer_id, $data->type, $data->date, $data->time];
    
    error_log("Executing query with params: " . print_r($params, true));
    
    if ($stmt->execute($params)) {
        $newId = $db->lastInsertId();
        error_log("Training created successfully with ID: " . $newId);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Trening został zaplanowany',
            'training_id' => $newId
        ]);
    } else {
        $error = $stmt->errorInfo();
        error_log("Database error: " . print_r($error, true));
        throw new Exception("Database error: " . $error[2]);
    }

} catch (Exception $e) {
    error_log("Error in create.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 