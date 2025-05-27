<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Проверка подключения к MySQL с использованием mysqli...\n";

$host = 'localhost';
$user = 'root';
$pass = '1234';

// Пробуем подключиться к MySQL
$mysqli = new mysqli($host, $user, $pass);

// Проверяем соединение
if ($mysqli->connect_error) {
    echo "Ошибка подключения к MySQL: " . $mysqli->connect_error . "\n";
} else {
    echo "Подключение к MySQL успешно!\n";
    
    // Выводим доступные базы данных
    echo "Список доступных баз данных:\n";
    $result = $mysqli->query("SHOW DATABASES");
    $databases = [];
    
    while ($row = $result->fetch_row()) {
        $databases[] = $row[0];
        echo "- " . $row[0] . "\n";
    }
    
    // Проверяем существование базы данных dental_clinic
    $dental_clinic_exists = in_array('dental_clinic', $databases);
    echo "База данных 'dental_clinic' " . ($dental_clinic_exists ? "существует" : "не существует") . "\n";
    
    if ($dental_clinic_exists) {
        // Выбираем базу данных
        if ($mysqli->select_db('dental_clinic')) {
            echo "Подключение к базе данных 'dental_clinic' успешно!\n";
            
            // Проверяем наличие таблицы reviews
            $result = $mysqli->query("SHOW TABLES LIKE 'reviews'");
            $table_exists = $result->num_rows > 0;
            echo "Таблица 'reviews' " . ($table_exists ? "существует" : "не существует") . "\n";
            
            if ($table_exists) {
                // Проверяем содержимое таблицы reviews
                $result = $mysqli->query("SELECT COUNT(*) FROM reviews");
                $count = $result->fetch_row()[0];
                echo "Количество записей в таблице 'reviews': $count\n";
                
                // Если таблица существует, но пуста, создаем тестовые записи
                if ($count == 0) {
                    echo "Создание тестовых записей в таблице 'reviews'...\n";
                    
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
                    
                    foreach ($reviews as $review) {
                        $stmt = $mysqli->prepare("INSERT INTO reviews (name, email, service, rating, review, approved) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssis", $review['name'], $review['email'], $review['service'], $review['rating'], $review['review'], $review['approved']);
                        if ($stmt->execute()) {
                            echo "Успешно добавлен отзыв от: " . $review['name'] . "\n";
                        } else {
                            echo "Ошибка при добавлении отзыва: " . $mysqli->error . "\n";
                        }
                        $stmt->close();
                    }
                    
                    // Проверяем количество записей после добавления
                    $result = $mysqli->query("SELECT COUNT(*) FROM reviews");
                    $count = $result->fetch_row()[0];
                    echo "Количество записей в таблице 'reviews' после добавления: $count\n";
                }
            } else {
                // Создаем таблицу reviews
                echo "Создание таблицы 'reviews'...\n";
                
                $sql = "CREATE TABLE `reviews` (
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
                
                if ($mysqli->query($sql) === TRUE) {
                    echo "Таблица 'reviews' успешно создана!\n";
                } else {
                    echo "Ошибка при создании таблицы: " . $mysqli->error . "\n";
                }
            }
        } else {
            echo "Ошибка при подключении к базе данных 'dental_clinic': " . $mysqli->error . "\n";
        }
    } else {
        echo "Создание базы данных 'dental_clinic'...\n";
        if ($mysqli->query("CREATE DATABASE `dental_clinic` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci") === TRUE) {
            echo "База данных 'dental_clinic' успешно создана!\n";
        } else {
            echo "Ошибка при создании базы данных: " . $mysqli->error . "\n";
        }
    }
    
    $mysqli->close();
}

echo "Завершение проверки.\n";
echo "</pre>";
?> 