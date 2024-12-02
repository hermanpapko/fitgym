<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

try {
    $conn = new PDO("mysql:host=localhost;dbname=fitgym", "root", "");
    
    $data = json_decode(file_get_contents("php://input"));
    
    // Проверяем существование email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    
    if($stmt->rowCount() > 0) {
        echo json_encode(["message" => "Email już istnieje"]);
        exit();
    }
    
    // Создаем пользователя
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
    
    if($stmt->execute([$data->name, $data->email, $password_hash])) {
        echo json_encode([
            "message" => "OK",
            "user" => ["name" => $data->name, "email" => $data->email]
        ]);
    }
} catch(PDOException $e) {
    echo json_encode(["message" => $e->getMessage()]);
}
?> 