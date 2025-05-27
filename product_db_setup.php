<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'product_db';
$user = 'root';
$pass = '1234'; // Оставим пустым или укажите свой пароль, если он есть
$charset = 'cp1251';

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
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET cp1251 COLLATE cp1251_general_ci");
        echo "База данных '$dbname' успешно создана!<br>";
    } else {
        echo "База данных '$dbname' уже существует.<br>";
    }
    
    // Подключаемся к созданной/существующей базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование таблицы product
    $stmt = $pdo->query("SHOW TABLES LIKE 'product'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу product
        $pdo->exec("CREATE TABLE IF NOT EXISTS product (
            id int(11) NOT NULL AUTO_INCREMENT,
            manufacturer_id smallint(6) NOT NULL,
            name varchar(255) NOT NULL,
            alias varchar(255) NOT NULL,
            short_description text NOT NULL,
            description text NOT NULL,
            price decimal(20,2) NOT NULL,
            image varchar(255) NOT NULL,
            available smallint(1) NOT NULL DEFAULT '1',
            meta_keywords varchar(255) NOT NULL,
            meta_description varchar(255) NOT NULL,
            meta_title varchar(255) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1");
        
        echo "Таблица 'product' успешно создана!<br>";
    } else {
        echo "Таблица 'product' уже существует.<br>";
    }
    
    // Проверяем существование таблицы product_properties
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_properties'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу product_properties
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_properties (
            id int(11) NOT NULL AUTO_INCREMENT,
            product_id int(11) NOT NULL,
            property_name varchar(255) NOT NULL,
            property_value varchar(255) NOT NULL,
            property_price decimal(20,2) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1");
        
        echo "Таблица 'product_properties' успешно создана!<br>";
    } else {
        echo "Таблица 'product_properties' уже существует.<br>";
    }
    
    // Проверяем существование таблицы product_images
    $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу product_images
        $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
            id int(11) NOT NULL AUTO_INCREMENT,
            product_id int(11) NOT NULL,
            image varchar(255) NOT NULL,
            title varchar(255) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1");
        
        echo "Таблица 'product_images' успешно создана!<br>";
    } else {
        echo "Таблица 'product_images' уже существует.<br>";
    }
    
    echo "Все таблицы успешно созданы или уже существуют!";
    
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?> 