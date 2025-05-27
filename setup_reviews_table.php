<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'dental_clinic';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

try {
    // Подключение к базе данных
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Создаем таблицу отзывов, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS `reviews` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        `service` VARCHAR(255) NOT NULL,
        `rating` INT(1) NOT NULL,
        `review` TEXT NOT NULL,
        `approved` TINYINT(1) NOT NULL DEFAULT 0,
        `date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    
    // Добавляем несколько тестовых отзывов
    $reviews = [
        [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'service' => 'Лечение кариеса',
            'rating' => 5,
            'review' => 'Отличная клиника! Лечение прошло безболезненно, врач все подробно объяснил.',
            'approved' => 1
        ],
        [
            'name' => 'Мария Сидорова',
            'email' => 'maria@example.com',
            'service' => 'Отбеливание',
            'rating' => 4,
            'review' => 'Результатом довольна, зубы стали заметно белее. Единственный минус - немного завышенная цена.',
            'approved' => 1
        ],
        [
            'name' => 'Алексей Иванов',
            'email' => 'alex@example.com',
            'service' => 'Удаление зуба',
            'rating' => 5,
            'review' => 'Боялся удалять зуб мудрости, но процедура прошла быстро и почти безболезненно. Спасибо врачу за профессионализм!',
            'approved' => 1
        ]
    ];
    
    // Проверяем, есть ли уже отзывы в таблице
    $stmt = $pdo->query("SELECT COUNT(*) FROM reviews");
    $count = $stmt->fetchColumn();
    
    // Если отзывов нет, добавляем тестовые
    if ($count == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (name, email, service, rating, review, approved) 
            VALUES (:name, :email, :service, :rating, :review, :approved)
        ");
        
        foreach ($reviews as $review) {
            $stmt->execute($review);
        }
        
        echo "Тестовые отзывы успешно добавлены.";
    } else {
        echo "В таблице уже есть отзывы. Тестовые отзывы не добавлены.";
    }
    
    echo "Таблица 'reviews' успешно создана.";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 