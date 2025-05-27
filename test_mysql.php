<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<pre>";
echo "Проверка подключения к MySQL...\n";

$host = 'localhost';
$user = 'root';
$pass = '1234';

try {
    // Пытаемся подключиться к MySQL без указания базы данных
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "Подключение к MySQL успешно!\n";
    
    // Выводим доступные базы данных
    echo "Список доступных баз данных:\n";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($databases as $database) {
        echo "- $database\n";
    }
    
    // Проверяем существование базы данных dental_clinic
    $dental_clinic_exists = in_array('dental_clinic', $databases);
    echo "База данных 'dental_clinic' " . ($dental_clinic_exists ? "существует" : "не существует") . "\n";
    
    if ($dental_clinic_exists) {
        try {
            // Пытаемся подключиться к базе данных dental_clinic
            $pdo2 = new PDO("mysql:host=$host;dbname=dental_clinic", $user, $pass);
            echo "Подключение к базе данных 'dental_clinic' успешно!\n";
            
            // Проверяем наличие таблицы reviews
            $stmt = $pdo2->query("SHOW TABLES LIKE 'reviews'");
            $table_exists = $stmt->fetch();
            echo "Таблица 'reviews' " . ($table_exists ? "существует" : "не существует") . "\n";
            
            if ($table_exists) {
                // Проверяем содержимое таблицы reviews
                $stmt = $pdo2->query("SELECT COUNT(*) FROM reviews");
                $count = $stmt->fetchColumn();
                echo "Количество записей в таблице 'reviews': $count\n";
            }
        } catch (PDOException $e) {
            echo "Ошибка при подключении к базе данных 'dental_clinic': " . $e->getMessage() . "\n";
        }
    } else {
        echo "Создаем базу данных 'dental_clinic'...\n";
        $pdo->exec("CREATE DATABASE `dental_clinic` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "База данных 'dental_clinic' успешно создана!\n";
    }
    
} catch (PDOException $e) {
    echo "Ошибка при подключении к MySQL: " . $e->getMessage() . "\n";
}

echo "Завершение проверки.\n";
echo "</pre>";
?> 