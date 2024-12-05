<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/server/config/database.php';

try {
    error_log("Received request: " . file_get_contents("php://input"));
    
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->name) || !isset($data->email) || !isset($data->message)) {
        throw new Exception("Brak wymaganych danych");
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO contact_messages (name, email, phone, message, created_at) 
              VALUES (:name, :email, :phone, :message, NOW())";
              
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":phone", $data->phone);
    $stmt->bindParam(":message", $data->message);
    
    error_log("Executing query with data: " . json_encode($data));
    
    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Wiadomość została wysłana pomyślnie!"
        ]);
    } else {
        error_log("Database error: " . json_encode($stmt->errorInfo()));
        throw new Exception("Błąd podczas wysyłania wiadomości");
    }
    
} catch (Exception $e) {
    error_log("Error in submit_form.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?> 