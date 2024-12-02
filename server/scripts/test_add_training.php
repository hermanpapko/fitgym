<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connected to database\n";
    
    // Проверяем существование пользователя и тренера
    $userId = 1; // ID существующего пользователя
    $trainerId = 2; // ID существующего тренера
    
    $checkUsers = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE id = ?) as user_exists,
            (SELECT COUNT(*) FROM users WHERE id = ? AND role = 'trainer') as trainer_exists
    ");
    $checkUsers->execute([$userId, $trainerId]);
    $result = $checkUsers->fetch(PDO::FETCH_ASSOC);
    
    echo "User exists: " . ($result['user_exists'] ? 'Yes' : 'No') . "\n";
    echo "Trainer exists: " . ($result['trainer_exists'] ? 'Yes' : 'No') . "\n";
    
    // Пробуем добавить тренировку
    $query = "INSERT INTO trainings (user_id, trainer_id, type, date, time, status) 
              VALUES (?, ?, ?, ?, ?, 'scheduled')";
              
    $stmt = $db->prepare($query);
    
    $testData = [
        $userId,
        $trainerId,
        'Test Training',
        date('Y-m-d'),
        '10:00:00'
    ];
    
    echo "Trying to insert training...\n";
    $result = $stmt->execute($testData);
    
    if ($result) {
        $newId = $db->lastInsertId();
        echo "Training added successfully with ID: " . $newId . "\n";
        
        // Проверяем добавленную запись
        $check = $db->query("SELECT * FROM trainings WHERE id = " . $newId);
        print_r($check->fetch(PDO::FETCH_ASSOC));
    } else {
        echo "Failed to add training\n";
        print_r($stmt->errorInfo());
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 