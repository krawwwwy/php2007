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
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Создаем таблицу корзины, если она не существует - без внешних ключей
    $sql = "CREATE TABLE IF NOT EXISTS `cart` (
        `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT(11) NOT NULL,
        `service_id` INT(11) NOT NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Таблица корзины успешно создана!";
    
} catch (PDOException $e) {
    die("Ошибка при создании таблицы корзины: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Настройка корзины - Стоматологическая клиника Жемчуг</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #0066cc;
            text-align: center;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            color: green;
            background: #e8f5e9;
        }
        .error {
            color: red;
            background: #ffebee;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Настройка модуля корзины</h1>
    <p>Этот скрипт создает необходимые таблицы для работы модуля корзины в стоматологической клинике "Жемчуг".</p>
    
    <a href="index.php" class="button">Вернуться на главную</a>
</body>
</html> 