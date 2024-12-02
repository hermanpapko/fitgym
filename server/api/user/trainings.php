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
    
    // Добавляем проверку статуса в запрос
    $query = "SELECT t.*, u.name as trainer_name 
        FROM trainings t
        LEFT JOIN users u ON t.trainer_id = u.id
        WHERE t.user_id = ? 
        AND t.date >= CURRENT_DATE()
        AND t.status = 'scheduled'  /* Добавлена проверка статуса */
        ORDER BY t.date ASC, t.time ASC
        LIMIT 5";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $trainings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'trainings' => array_map(function($training) {
            return [
                'id' => $training['id'],
                'type' => $training['type'],
                'date' => $training['date'],
                'time' => $training['time'],
                'trainer' => $training['trainer_name']
            ];
        }, $trainings)
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 