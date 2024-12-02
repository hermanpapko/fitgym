<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();
    
    if (!isset($_FILES['avatar'])) {
        throw new Exception("No file uploaded");
    }
    
    $file = $_FILES['avatar'];
    
    // Проверяем ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload error: " . $file['error']);
    }
    
    // Проверяем тип файла
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Invalid file type: " . $mimeType);
    }
    
    // Определяем расширение файла
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Генерируем новое имя файла
    $newFileName = uniqid('avatar_') . '.' . $extension;
    
    // Путь для сохранения
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/fitgym/uploads/avatars/';
    
    // Создаем директорию, если её нет
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("Failed to create upload directory");
        }
        chmod($uploadDir, 0777);
    }
    
    $uploadPath = $uploadDir . $newFileName;
    
    // Перемещаем файл
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("Upload error. File: " . $file['tmp_name'] . " to " . $uploadPath);
        error_log("PHP error: " . error_get_last()['message']);
        throw new Exception("Failed to save file");
    }
    
    // Устанавливаем права на файл
    chmod($uploadPath, 0644);
    
    // Путь для сохранения в базе данных и отображения
    $avatarUrl = '/fitgym/uploads/avatars/' . $newFileName;
    
    // Обновляем базу данных
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE users SET avatar = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if (!$stmt->execute([$avatarUrl, $userId])) {
        unlink($uploadPath);
        throw new Exception("Failed to update database");
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Avatar uploaded successfully',
        'avatarUrl' => $avatarUrl
    ]);
    
} catch (Exception $e) {
    error_log("Avatar upload error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 