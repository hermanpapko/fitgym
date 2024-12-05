<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Проверяем наличие тренеров
    $result = $db->query("SELECT id, name, email, role FROM users WHERE role = 'trainer'");
    echo "Available trainers:\n";
    $trainers = $result->fetchAll(PDO::FETCH_ASSOC);
    print_r($trainers);
    
    if (empty($trainers)) {
        throw new Exception("No trainers found in database");
    }
    
    // Берем первого тренера для теста
    $trainer = $trainers[0];
    
    // Тестовые данные тренировки
    $testTraining = [
        'user_id' => 1, // ID тестового пользователя
        'trainer_id' => $trainer['id'],
        'type' => 'Yoga',
        'date' => date('Y-m-d'), // Сегодняшняя дата
        'time' => '10:00',
        'status' => 'scheduled'
    ];
    
    // Пробуем добавить тренировку
    $query = "INSERT INTO trainings (user_id, trainer_id, type, date, time, status) 
              VALUES (:user_id, :trainer_id, :type, :date, :time, :status)";
    
    $stmt = $db->prepare($query);
    
    if ($stmt->execute($testTraining)) {
        $newId = $db->lastInsertId();
        echo "\nTraining added successfully with ID: " . $newId . "\n";
        
        // Проверяем добавленную тренировку
        $checkQuery = "SELECT * FROM trainings WHERE id = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$newId]);
        $training = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nAdded training details:\n";
        print_r($training);
    } else {
        $error = $stmt->errorInfo();
        throw new Exception("Failed to add training: " . $error[2]);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 