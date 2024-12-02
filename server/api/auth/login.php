<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../../config/database.php';

try {
    error_log("\n=== Login attempt started ===");
    
    $database = new Database();
    $db = $database->getConnection();
    
    $rawData = file_get_contents("php://input");
    error_log("Raw request data: " . $rawData);
    
    $data = json_decode($rawData);
    error_log("Login attempt for email: " . $data->email);
    
    if(!isset($data->email) || !isset($data->password)) {
        throw new Exception("Brakujące dane logowania");
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$user) {
        error_log("User not found with email: " . $data->email);
        throw new Exception("Nieprawidłowy email lub hasło");
    }

    if(password_verify($data->password, $user['password'])) {
        error_log("Password verified successfully for user ID: " . $user['id']);
        
        // Генерируем новый токен
        $token = bin2hex(random_bytes(32));
        error_log("Generated new token: " . $token);
        
        // Сохраняем токен в базе данных
        $updateStmt = $db->prepare("UPDATE users SET token = ? WHERE id = ?");
        if (!$updateStmt->execute([$token, $user['id']])) {
            error_log("Failed to update token. Error: " . implode(", ", $updateStmt->errorInfo()));
            throw new Exception("Błąd podczas aktualizacji tokenu");
        }
        error_log("Token saved successfully");

        // Проверяем сохранение токена
        $checkStmt = $db->prepare("SELECT token FROM users WHERE id = ?");
        $checkStmt->execute([$user['id']]);
        $savedToken = $checkStmt->fetchColumn();
        
        if ($savedToken !== $token) {
            error_log("Token verification failed. Saved token doesn't match generated token");
            throw new Exception("Token verification failed");
        }
        
        error_log("Token verified successfully");

        $response = [
            "status" => "success",
            "message" => "Zalogowano pomyślnie",
            "token" => $token,
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "role" => $user['role']
            ]
        ];
        
        error_log("Sending response: " . json_encode($response));
        echo json_encode($response);
        
    } else {
        error_log("Invalid password for user ID: " . $user['id']);
        throw new Exception("Nieprawidłowy email lub hasło");
    }
} catch(Exception $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

error_log("=== Login attempt finished ===\n");
?> 