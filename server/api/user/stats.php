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
    
    // Получаем статистику пользователя
    $query = "SELECT 
        COUNT(*) as workouts,
        COALESCE(SUM(duration), 0) as total_hours,
        COALESCE(SUM(calories), 0) as total_calories
        FROM workouts 
        WHERE user_id = ? AND MONTH(date) = MONTH(CURRENT_DATE())";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'workouts' => $stats['workouts'],
        'hours' => round($stats['total_hours'] / 60, 1), // Конвертируем минуты в часы
        'calories' => $stats['total_calories']
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 