<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../../middleware/auth.php';

try {
    $userId = authenticate();
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->note) || empty(trim($data->note))) {
        throw new Exception("Note text is required");
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO progress_notes (user_id, note) VALUES (?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$userId, $data->note])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Note saved successfully'
        ]);
    } else {
        throw new Exception("Failed to save note");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 