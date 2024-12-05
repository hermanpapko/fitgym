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
    
    error_log('Updating membership for user ' . $userId . ' to: ' . $data->membership);
    
    if (!isset($data->membership) || !in_array($data->membership, ['standard', 'premium', 'vip'])) {
        throw new Exception("Invalid membership type");
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "UPDATE users SET membership = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    error_log('Executing query with params: ' . $data->membership . ', ' . $userId);
    if ($stmt->execute([$data->membership, $userId])) {
        $checkQuery = "SELECT membership FROM users WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$userId]);
        $newMembership = $checkStmt->fetchColumn();
        error_log('New membership value: ' . $newMembership);
        
        echo json_encode([
            'status' => 'success',
            'membership' => $newMembership,
            'message' => 'Membership updated successfully'
        ]);
    } else {
        throw new Exception("Failed to update membership");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 