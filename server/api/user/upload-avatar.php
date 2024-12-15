<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Файл не загружен или произошла ошибка при загрузке");
    }

    $fileTmpPath = $_FILES['avatar']['tmp_name'];
    $fileSize = $_FILES['avatar']['size'];
    $fileType = $_FILES['avatar']['type'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Неподдерживаемый тип файла");
    }

    // Ограничение размера файла до 2MB
    if ($fileSize > 2 * 1024 * 1024) {
        throw new Exception("Файл слишком большой");
    }

    // Чтение файла как бинарные данные
    $imageData = file_get_contents($fileTmpPath);

    $database = new Database();
    $db = $database->getConnection();

    // Обновление аватара пользователя
    $query = "UPDATE users SET avatar = ? WHERE id = ?";
    $stmt = $db->prepare($query);

    if ($stmt->execute([$imageData, $userId])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Аватар успешно обновлен'
        ]);
    } else {
        throw new Exception("Не удалось обновить аватар");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 