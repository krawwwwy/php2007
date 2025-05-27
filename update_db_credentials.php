<?php
// Укажите здесь правильные учетные данные для подключения к MySQL
$new_host = 'localhost';
$new_dbname = 'product_db';
$new_user = 'root';
$new_pass = '1234'; // Укажите здесь пароль для пользователя root
$new_charset = 'cp1251';

// Список файлов, в которых нужно обновить параметры подключения
$files = [
    'product_db_setup.php',
    'products.php',
    'product_properties.php',
    'product_images.php',
    'product_details.php',
    'databaseconnect.php'
];

echo "<h2>Обновление параметров подключения к базе данных</h2>";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Заменяем параметры подключения
        $pattern = '/\$host\s*=\s*\'.*?\';/';
        $content = preg_replace($pattern, "\$host = '$new_host';", $content);
        
        $pattern = '/\$dbname\s*=\s*\'.*?\';/';
        $content = preg_replace($pattern, "\$dbname = '$new_dbname';", $content);
        
        $pattern = '/\$user\s*=\s*\'.*?\';/';
        $content = preg_replace($pattern, "\$user = '$new_user';", $content);
        
        $pattern = '/\$pass\s*=\s*\'.*?\';.*?\/\//';
        $content = preg_replace($pattern, "\$pass = '$new_pass'; //", $content);
        
        $pattern = '/\$charset\s*=\s*\'.*?\';/';
        $content = preg_replace($pattern, "\$charset = '$new_charset';", $content);
        
        // Сохраняем изменения
        file_put_contents($file, $content);
        
        echo "<div style='color: green;'>✓ Файл '$file' успешно обновлен</div>";
    } else {
        echo "<div style='color: red;'>✗ Файл '$file' не найден</div>";
    }
}

echo "<hr>";
echo "<h3>Новые параметры подключения:</h3>";
echo "<ul>";
echo "<li>Хост: $new_host</li>";
echo "<li>Имя базы данных: $new_dbname</li>";
echo "<li>Пользователь: $new_user</li>";
echo "<li>Пароль: " . ($new_pass ? "Установлен" : "Не установлен") . "</li>";
echo "<li>Кодировка: $new_charset</li>";
echo "</ul>";

echo "<p>Теперь проверьте подключение с новыми параметрами: <a href='databaseconnect.php'>http://localhost/laba3/databaseconnect.php</a></p>";
?> 