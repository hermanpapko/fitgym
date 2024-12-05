-- Создание таблицы пользователей
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'trainer', 'admin') DEFAULT 'user',
    avatar VARCHAR(255),
    membership VARCHAR(20) DEFAULT 'standard',
    token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Создание таблицы тренеров
CREATE TABLE trainers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    specialization VARCHAR(100),
    experience_years INT,
    description TEXT,
    rating DECIMAL(3,2),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Создание таблицы тренировок
CREATE TABLE trainings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    trainer_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id)
);

-- Создание таблицы расписания тренеров
CREATE TABLE trainer_schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trainer_id INT NOT NULL,
    day_of_week ENUM('Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'),
    start_time TIME,
    end_time TIME,
    FOREIGN KEY (trainer_id) REFERENCES users(id)
);

-- Создание таблицы тренировочных программ
CREATE TABLE workouts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    type VARCHAR(50),
    duration INT, -- в минутах
    calories INT,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Добавляем таблицу для заметок о прогрессе
CREATE TABLE progress_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Вставка тестовых данных для тренеров
INSERT INTO users (name, email, password, role) VALUES 
('Anna Kowalska', 'anna.kowalska@fitgym.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('Marek Nowak', 'marek.nowak@fitgym.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('Karolina Wiśniewska', 'karolina.wisniewska@fitgym.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('Piotr Kowalczyk', 'piotr.kowalczyk@fitgym.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('Magdalena Lis', 'magdalena.lis@fitgym.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer');

-- Добавление информации о тренерах
INSERT INTO trainers (user_id, specialization, experience_years, description) VALUES
(1, 'Yoga', 5, 'Certyfikowany instruktor jogi z 5-letnim doświadczeniem'),
(2, 'Crossfit', 7, 'Trener personalny i instruktor CrossFit'),
(3, 'Zumba', 4, 'Instruktor Zumby i fitness'),
(4, 'Box', 6, 'Trener boksu z licencją PZB'),
(5, 'Pilates', 5, 'Instruktor Pilates i stretching');

-- Добавление расписания тренеров
INSERT INTO trainer_schedule (trainer_id, day_of_week, start_time, end_time) VALUES
(1, 'Poniedziałek', '08:00', '16:00'),
(1, 'Środa', '08:00', '16:00'),
(1, 'Piątek', '08:00', '16:00'),
(2, 'Wtorek', '10:00', '18:00'),
(2, 'Czwartek', '10:00', '18:00'),
(3, 'Poniedziałek', '14:00', '22:00'),
(3, 'Środa', '14:00', '22:00'),
(4, 'Wtorek', '16:00', '22:00'),
(4, 'Czwartek', '16:00', '22:00'),
(5, 'Poniedziałek', '09:00', '17:00'),
(5, 'Piątek', '09:00', '17:00');

-- Создание таблицы сообщений
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 