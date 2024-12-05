<?php
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Проверяем существование таблицы
    $result = $db->query("SHOW TABLES LIKE 'trainings'");
    if ($result->rowCount() == 0) {
        echo "Table 'trainings' does not exist! Creating...\n";
        
        // Создаем таблицу
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
        
        $db->exec($sql);
        echo "Table 'trainings' created successfully!\n";
    } else {
        echo "Table 'trainings' exists.\n";
        
        // Про��еряем структуру таблицы
        $result = $db->query("DESCRIBE trainings");
        echo "\nTable structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }

    // Проверяем наличие тренеров
    $result = $db->query("SELECT id, name, role FROM users WHERE role = 'trainer'");
    echo "\nAvailable trainers:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?> 