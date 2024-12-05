<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    // Создаем соединение без выбора базы данных
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создаем базу данных если она не существует
    $sql = "CREATE DATABASE IF NOT EXISTS fitgym";
    $pdo->exec($sql);
    echo "Database 'fitgym' created successfully\n";
    
    // Выбираем базу данных
    $pdo->exec("USE fitgym");
    
    // Создаем таблицу пользователей если её нет
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'trainer', 'admin') DEFAULT 'user',
        token VARCHAR(255),
        membership ENUM('basic', 'premium', 'vip') DEFAULT 'basic',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table 'users' created successfully\n";
    
    // Создаем таблицу тренировок
    $sql = "CREATE TABLE IF NOT EXISTS trainings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        trainer_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (trainer_id) REFERENCES users(id)
    )";
    $pdo->exec($sql);
    echo "Table 'trainings' created successfully\n";
    
    // Добавляем тестового тренера, если его нет
    $sql = "INSERT IGNORE INTO users (name, email, password, role) 
            VALUES ('John Trainer', 'trainer@example.com', ?, 'trainer')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([password_hash('trainerpass', PASSWORD_DEFAULT)]);
    echo "Test trainer added successfully\n";
    
    // Выводим список тренеров
    $result = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'trainer'");
    echo "\nAvailable trainers:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>