<?php
// Файл для настройки категорий и назначения услуг в каталоге
session_start();

// Подключение к базе данных
$host = 'localhost';
$dbname = 'dental_clinic';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

echo "<h1>Настройка категорий услуг</h1>";

try {
    // Проверяем соединение с базой данных
    echo "<p>Подключение к базе данных...</p>";
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование базы данных
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        // Создаем базу данных, если она не существует
        echo "<p>База данных '$dbname' не найдена. Создание...</p>";
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>База данных '$dbname' успешно создана!</p>";
    }
    
    // Подключаемся к созданной/существующей базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    echo "<p>Успешное подключение к базе данных $dbname.</p>";
    
    // Проверяем существование таблицы categories
    echo "<p>Проверка наличия таблицы категорий...</p>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    $categoriesTableExists = $stmt->fetch();
    
    if (!$categoriesTableExists) {
        // Создаем таблицу категорий
        echo "<p>Создание таблицы категорий...</p>";
        $pdo->exec("CREATE TABLE `categories` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Вставляем категории
        echo "<p>Вставка категорий...</p>";
        $categories = [
            ['name' => 'Терапевтические услуги', 'description' => 'Лечебные процедуры для оздоровления зубов и десен'],
            ['name' => 'Хирургические услуги', 'description' => 'Процедуры по хирургическому вмешательству в полости рта'],
            ['name' => 'Диагностические услуги', 'description' => 'Диагностика состояния полости рта и зубов']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO `categories` (name, description) VALUES (:name, :description)");
        foreach ($categories as $category) {
            $stmt->execute([
                'name' => $category['name'],
                'description' => $category['description']
            ]);
        }
        echo "<p>Категории успешно добавлены.</p>";
    } else {
        echo "<p>Таблица категорий уже существует.</p>";
    }
    
    // Проверяем существование таблицы services
    echo "<p>Проверка наличия таблицы услуг...</p>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    $servicesTableExists = $stmt->fetch();
    
    if (!$servicesTableExists) {
        // Создаем таблицу services с полем category_id
        echo "<p>Создание таблицы услуг...</p>";
        $pdo->exec("CREATE TABLE `services` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT(11) UNSIGNED NOT NULL,
            `title_value` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `price` VARCHAR(100) NOT NULL,
            `duration` VARCHAR(100) NOT NULL,
            `doctor` VARCHAR(255) NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            `first_letter` CHAR(1) NOT NULL,
            FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Вставляем данные услуг
        echo "<p>Вставка услуг...</p>";
        $services = [
            [
                'title_value' => 'Лечение зубов',
                'content' => 'Лечение кариеса, пульпита, периодонтита и других заболеваний зубов с использованием современных материалов и оборудования.',
                'price' => 'от 1000 руб.',
                'duration' => '30-90 мин.',
                'doctor' => 'стоматолог-терапевт',
                'image' => 'img/product1.jpg',
                'first_letter' => 'Л',
                'category_id' => 1 // Терапевтические услуги
            ],
            [
                'title_value' => 'Протезирование',
                'content' => 'Восстановление утраченных зубов с помощью современных протезов и коронок из различных материалов.',
                'price' => 'от 2000 руб.',
                'duration' => '60-120 мин.',
                'doctor' => 'стоматолог-ортопед',
                'image' => 'img/product2.jpg',
                'first_letter' => 'П',
                'category_id' => 1 // Терапевтические услуги
            ],
            [
                'title_value' => 'Имплантация',
                'content' => 'Установка дентальных имплантов для замещения утраченных зубов с последующим протезированием.',
                'price' => 'от 5000 руб.',
                'duration' => '60-180 мин.',
                'doctor' => 'стоматолог-хирург',
                'image' => 'img/product3.jpg',
                'first_letter' => 'И',
                'category_id' => 2 // Хирургические услуги
            ],
            [
                'title_value' => 'Отбеливание',
                'content' => 'Профессиональное отбеливание зубов по современным технологиям для достижения белоснежной улыбки.',
                'price' => 'от 3000 руб.',
                'duration' => '60-90 мин.',
                'doctor' => 'стоматолог-терапевт',
                'image' => 'img/tooth-whitening.jpg',
                'first_letter' => 'О',
                'category_id' => 1 // Терапевтические услуги
            ],
            [
                'title_value' => 'Ортодонтия',
                'content' => 'Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров.',
                'price' => 'от 4000 руб.',
                'duration' => '30-60 мин.',
                'doctor' => 'ортодонт',
                'image' => 'img/banner1.jpg',
                'first_letter' => 'О',
                'category_id' => 1 // Терапевтические услуги
            ],
            [
                'title_value' => 'Рентгенология',
                'content' => 'Диагностика состояния зубов и челюстей с помощью рентгенологического оборудования.',
                'price' => 'от 500 руб.',
                'duration' => '10-20 мин.',
                'doctor' => 'рентгенолог',
                'image' => 'img/banner3.jpg',
                'first_letter' => 'Р',
                'category_id' => 3 // Диагностические услуги
            ]
        ];
        
        // Подготавливаем запрос на вставку
        $stmt = $pdo->prepare("INSERT INTO `services` 
            (category_id, title_value, content, price, duration, doctor, image, first_letter) 
            VALUES (:category_id, :title_value, :content, :price, :duration, :doctor, :image, :first_letter)");
        
        // Вставляем данные
        foreach ($services as $service) {
            $stmt->execute([
                'category_id' => $service['category_id'],
                'title_value' => $service['title_value'],
                'content' => $service['content'],
                'price' => $service['price'],
                'duration' => $service['duration'],
                'doctor' => $service['doctor'],
                'image' => $service['image'],
                'first_letter' => $service['first_letter']
            ]);
        }
        echo "<p>Услуги успешно добавлены.</p>";
    } else {
        echo "<p>Таблица услуг уже существует. Проверка наличия поля category_id...</p>";
        
        // Проверяем наличие поля category_id в таблице services
        try {
            $stmt = $pdo->query("SELECT category_id FROM services LIMIT 1");
            echo "<p>Поле category_id уже существует.</p>";
        } catch (PDOException $e) {
            // Добавляем поле category_id в существующую таблицу, если его нет
            echo "<p>Добавление поля category_id в таблицу услуг...</p>";
            $pdo->exec("ALTER TABLE `services` ADD COLUMN `category_id` INT(11) UNSIGNED DEFAULT 1");
            
            // Назначаем категории для услуг
            echo "<p>Назначение категорий для существующих услуг...</p>";
            $pdo->exec("UPDATE services SET category_id = 1 WHERE title_value IN ('Лечение зубов', 'Протезирование', 'Отбеливание', 'Ортодонтия')");
            $pdo->exec("UPDATE services SET category_id = 2 WHERE title_value = 'Имплантация'");
            $pdo->exec("UPDATE services SET category_id = 3 WHERE title_value = 'Рентгенология'");
            
            // Добавляем внешний ключ
            try {
                $pdo->exec("ALTER TABLE `services` ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)");
                echo "<p>Добавлен внешний ключ для связи с категориями.</p>";
            } catch (PDOException $e) {
                echo "<p>Внешний ключ уже существует или произошла ошибка: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Выводим все категории
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    
    echo "<h2>Созданные категории:</h2>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li><strong>{$category['name']}</strong> - {$category['description']}</li>";
    }
    echo "</ul>";
    
    // Выводим все услуги с категориями
    $stmt = $pdo->query("SELECT s.*, c.name as category_name FROM services s JOIN categories c ON s.category_id = c.id ORDER BY c.id, s.title_value");
    $services = $stmt->fetchAll();
    
    echo "<h2>Услуги по категориям:</h2>";
    
    $currentCategory = null;
    foreach ($services as $service) {
        if ($currentCategory != $service['category_id']) {
            if ($currentCategory !== null) {
                echo "</ul>";
            }
            $currentCategory = $service['category_id'];
            echo "<h3>{$service['category_name']}</h3>";
            echo "<ul>";
        }
        echo "<li><strong>{$service['title_value']}</strong> - {$service['content']} ({$service['price']})</li>";
    }
    if ($currentCategory !== null) {
        echo "</ul>";
    }
    
    echo "<p>Настройка категорий успешно завершена!</p>";
    echo "<p><a href='catalog.php'>Перейти в каталог услуг</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Ошибка при работе с базой данных</h2>";
    echo "<p>Произошла ошибка: " . $e->getMessage() . "</p>";
}
?> 