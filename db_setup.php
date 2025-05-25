<?php
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

try {
    // Сначала подключимся без указания базы данных
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование базы данных
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        // Создаем базу данных
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "База данных '$dbname' успешно создана!<br>";
    }
    
    // Подключаемся к созданной/существующей базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование таблицы users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу users
        $pdo->exec("CREATE TABLE `users` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `name` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        echo "Таблица 'users' успешно создана!";
    } else {
        echo "Таблица 'users' уже существует.";
    }
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 