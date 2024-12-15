<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();

    // Получаем данные запроса
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->note_id)) {
        throw new Exception("Не указан ID заметки");
    }

    $noteId = $data->note_id;

    $database = new Database();
    $db = $database->getConnection();

    // Проверяем, принадлежит ли заметка пользователю
    $checkQuery = "SELECT id FROM progress_notes WHERE id = ? AND user_id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$noteId, $userId]);

    if ($checkStmt->rowCount() === 0) {
        throw new Exception("Заметка не найдена или не принадлежит вам");
    }

    // Удаляем заметку
    $deleteQuery = "DELETE FROM progress_notes WHERE id = ?";
    $deleteStmt = $db->prepare($deleteQuery);

    if ($deleteStmt->execute([$noteId])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Заметка успешно удалена'
        ]);
    } else {
        throw new Exception("Не удалось удалить заметку");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 