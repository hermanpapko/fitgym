<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    // Проверяем токен
    $userId = authenticate();
    error_log("API called for user ID: $userId");

    $database = new Database();
    $db = $database->getConnection();
    
    // Прямой запрос для проверки
    $checkQuery = "SELECT membership FROM users WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$userId]);
    $directMembership = $checkStmt->fetchColumn();
    error_log("Direct membership query result: $directMembership");

    // Убеждаемся, что берем актуальные данные
    $db->query('FLUSH QUERY CACHE');
    $db->query('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');

    // Основной запрос
    $query = "SELECT id, name, email, avatar, membership FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Проверяем соответствие данных
    if ($user['membership'] !== $directMembership) {
        error_log("WARNING: Membership mismatch detected!");
        error_log("Direct query: $directMembership, User query: {$user['membership']}");
        // Используем значение из прямого запроса
        $user['membership'] = $directMembership;
    }

    error_log("Full user data: " . print_r($user, true));

    // Проверяем данные перед отправкой
    $response = [
        'status' => 'success',
        'user' => $user,
        'debug' => [
            'userId' => $userId,
            'directMembership' => $directMembership,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ];
    
    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 