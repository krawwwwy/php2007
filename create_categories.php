<?php
session_start();

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'dental_clinic';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

// Настройки для PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

echo "<h1>Создание таблицы категорий</h1>";

try {
    // Подключаемся к БД
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование таблицы categories
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу категорий
        $pdo->exec("CREATE TABLE `categories` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        echo "<p>Таблица категорий успешно создана.</p>";
        
        // Вставляем три категории
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
        
        echo "<p>Три категории успешно добавлены.</p>";
    } else {
        echo "<p>Таблица категорий уже существует.</p>";
    }
    
    // Проверяем существование поля category_id в таблице services
    try {
        $stmt = $pdo->query("SELECT category_id FROM services LIMIT 1");
        echo "<p>Поле category_id уже существует в таблице services.</p>";
    } catch (PDOException $e) {
        // Поля нет - добавляем его
        $pdo->exec("ALTER TABLE `services` ADD COLUMN `category_id` INT(11) UNSIGNED DEFAULT 1");
        echo "<p>Поле category_id добавлено в таблицу services.</p>";
        
        // Назначаем категории по умолчанию
        $pdo->exec("UPDATE services SET category_id = 1 WHERE title_value IN ('Лечение зубов', 'Отбеливание')");
        $pdo->exec("UPDATE services SET category_id = 2 WHERE title_value IN ('Имплантация', 'Протезирование')");
        $pdo->exec("UPDATE services SET category_id = 3 WHERE title_value IN ('Рентгенология', 'Ортодонтия')");
        
        echo "<p>Категории назначены существующим услугам.</p>";
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
        echo "<li><strong>{$service['title_value']}</strong> - {$service['price']}</li>";
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