<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== Database Check ===\n\n";
    
    // Проверяем подключение
    echo "Database connection: OK\n\n";
    
    // Проверяем таблицы
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database:\n";
    print_r($tables);
    echo "\n";
    
    // Проверяем пользователей
    $users = $db->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
    echo "Users in database:\n";
    print_r($users);
    echo "\n";
    
    // Пробуем добавить тестовую тренировку
    $trainerId = $db->query("SELECT id FROM users WHERE role = 'trainer' LIMIT 1")->fetchColumn();
    $userId = $db->query("SELECT id FROM users WHERE role = 'user' LIMIT 1")->fetchColumn();
    
    echo "Trainer ID: " . ($trainerId ?: 'Not found') . "\n";
    echo "User ID: " . ($userId ?: 'Not found') . "\n\n";
    
    if ($trainerId && $userId) {
        $stmt = $db->prepare("
            INSERT INTO trainings (user_id, trainer_id, type, date, time, status) 
            VALUES (?, ?, ?, ?, ?, 'scheduled')
        ");
        
        $testData = [
            $userId,
            $trainerId,
            'Test Training',
            date('Y-m-d'),
            '10:00:00'
        ];
        
        echo "Attempting to insert training with data:\n";
        print_r($testData);
        
        $result = $stmt->execute($testData);
        
        if ($result) {
            $newId = $db->lastInsertId();
            echo "\nTraining added successfully with ID: " . $newId . "\n";
            
            $training = $db->query("SELECT * FROM trainings WHERE id = " . $newId)->fetch(PDO::FETCH_ASSOC);
            echo "Added training details:\n";
            print_r($training);
        } else {
            echo "\nFailed to add training\n";
            echo "Error info:\n";
            print_r($stmt->errorInfo());
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 