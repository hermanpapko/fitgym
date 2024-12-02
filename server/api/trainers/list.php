<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

error_log("\n=== Trainers list request started ===");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/auth.php';

try {
    // Проверяем все входящие данные
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    
    $headers = getallheaders();
    error_log("Headers received: " . json_encode($headers));
    
    // Проверяем токен
    if (!isset($headers['Authorization'])) {
        throw new Exception("No Authorization header present");
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    error_log("Token from request: " . $token);
    
    // Проверяем токен в базе данных
    $database = new Database();
    $db = $database->getConnection();
    
    // Сначала проверим токен напрямую
    $checkToken = $db->prepare("SELECT id, name FROM users WHERE token = ?");
    $checkToken->execute([$token]);
    $tokenResult = $checkToken->fetch(PDO::FETCH_ASSOC);
    error_log("Direct token check result: " . json_encode($tokenResult));
    
    // Теперь выполняем аутентификацию
    $userId = authenticate();
    error_log("User authenticated with ID: " . $userId);
    
    // Получаем список тренеров
    $query = "SELECT id, name, specialization FROM users WHERE role = 'trainer'";
    error_log("Executing query: " . $query);
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Found trainers: " . json_encode($trainers));
    
    if (empty($trainers)) {
        error_log("No trainers found in database");
        echo json_encode([
            'status' => 'success',
            'trainers' => [],
            'message' => 'No trainers found'
        ]);
    } else {
        error_log("Sending response with " . count($trainers) . " trainers");
        echo json_encode([
            'status' => 'success',
            'trainers' => $trainers
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error in trainers/list.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

error_log("=== Trainers list request finished ===\n");
?> 