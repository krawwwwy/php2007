<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'dental_clinic';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Начало выполнения скрипта\n";

try {
    // Подключение к базе данных
    echo "Подключение к базе данных...\n";
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Подключение успешно\n";
    
    // Проверяем существование таблицы reviews
    echo "Проверка существования таблицы reviews...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Таблица 'reviews' не существует\n";
    } else {
        echo "Таблица 'reviews' найдена\n";
        
        // Получаем все отзывы
        echo "Получение отзывов из таблицы...\n";
        $stmt = $pdo->query("SELECT * FROM reviews ORDER BY date_added DESC");
        $reviews = $stmt->fetchAll();
        
        echo "Количество отзывов: " . count($reviews) . "\n";
        
        if (count($reviews) > 0) {
            echo "Содержимое таблицы reviews:\n";
            
            foreach ($reviews as $review) {
                echo "ID: " . $review['id'] . "\n";
                echo "Имя: " . $review['name'] . "\n";
                echo "Email: " . $review['email'] . "\n";
                echo "Услуга: " . $review['service'] . "\n";
                echo "Рейтинг: " . $review['rating'] . "\n";
                echo "Отзыв: " . $review['review'] . "\n";
                echo "Одобрен: " . ($review['approved'] ? 'Да' : 'Нет') . "\n";
                echo "Дата: " . $review['date_added'] . "\n";
                echo "------------------------------\n";
            }
        } else {
            echo "Таблица 'reviews' пуста\n";
        }
    }
} catch (PDOException $e) {
    echo "Ошибка PDO: " . $e->getMessage() . "\n";
}
echo "Завершение выполнения скрипта\n";
echo "</pre>";
?> 