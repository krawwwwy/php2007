<?php
// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'product_db';
$user = 'root';
$pass = '1234'; // Оставьте пустым, если нет пароля, иначе укажите правильный пароль

echo "<h2>Проверка подключения к базе данных</h2>";
echo "<p>Используемые параметры:</p>";
echo "<ul>";
echo "<li>Хост: $host</li>";
echo "<li>Имя базы данных: $dbname</li>";
echo "<li>Пользователь: $user</li>";
echo "<li>Пароль: " . ($pass ? "Установлен" : "Не установлен") . "</li>";
echo "</ul>";

echo "<h3>Проверка подключения к MySQL без указания базы данных (только сервер)</h3>";

// Проверяем только подключение к серверу MySQL
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div style='color: green;'><strong>Успешно подключились к серверу MySQL!</strong></div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'><strong>Ошибка подключения к серверу MySQL:</strong> " . $e->getMessage() . "</div>";
    echo "<p>Проверьте правильность имени пользователя и пароля. Также убедитесь, что сервер MySQL запущен.</p>";
    die();
}

echo "<h3>Проверка наличия и доступа к базе данных '$dbname'</h3>";

// Проверяем подключение к конкретной базе данных
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div style='color: green;'><strong>Успешно подключились к базе данных '$dbname'!</strong></div>";
} catch(PDOException $e) {
    echo "<div style='color: red;'><strong>Ошибка подключения к базе данных '$dbname':</strong> " . $e->getMessage() . "</div>";
    
    // Пытаемся определить, существует ли база данных
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        $dbExists = $stmt->fetch();
        
        if (!$dbExists) {
            echo "<p>База данных '$dbname' не существует. Возможно, вы еще не запустили скрипт создания базы данных.</p>";
            echo "<p>Перейдите по адресу: <a href='product_db_setup.php'>http://localhost/laba3/product_db_setup.php</a> для создания базы данных и таблиц.</p>";
        } else {
            echo "<p>База данных '$dbname' существует, но возникла проблема при подключении к ней. Проверьте права доступа.</p>";
        }
    } catch(PDOException $e2) {
        echo "<p>Дополнительная ошибка при проверке существования базы данных: " . $e2->getMessage() . "</p>";
    }
}

// Проверка существования необходимых таблиц
echo "<h3>Проверка существования необходимых таблиц</h3>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['product', 'product_properties', 'product_images'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $tableExists = $stmt->fetch();
        
        if (!$tableExists) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<div style='color: green;'><strong>Все необходимые таблицы существуют!</strong></div>";
    } else {
        echo "<div style='color: orange;'><strong>Отсутствуют следующие таблицы:</strong> " . implode(', ', $missing_tables) . "</div>";
        echo "<p>Перейдите по адресу: <a href='product_db_setup.php'>http://localhost/laba3/product_db_setup.php</a> для создания недостающих таблиц.</p>";
    }
} catch(PDOException $e) {
    echo "<div style='color: red;'><strong>Ошибка при проверке таблиц:</strong> " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h3>Рекомендации по устранению проблем:</h3>";
echo "<ol>";
echo "<li>Если у вас проблемы с подключением, убедитесь, что сервер MySQL запущен в XAMPP.</li>";
echo "<li>Проверьте правильность учетных данных (имя пользователя и пароль).</li>";
echo "<li>Если база данных не существует, запустите скрипт <a href='product_db_setup.php'>product_db_setup.php</a>.</li>";
echo "<li>Если вы видите ошибку доступа 'Access denied', убедитесь, что указали правильный пароль для пользователя 'root'.</li>";
echo "<li>Если вы хотите изменить параметры подключения, отредактируйте соответствующие строки в файлах:</li>";
echo "<ul>";
echo "<li>product_db_setup.php</li>";
echo "<li>products.php</li>";
echo "<li>product_properties.php</li>";
echo "<li>product_images.php</li>";
echo "<li>product_details.php</li>";
echo "</ul>";
echo "</ol>";

echo "<p>После устранения проблем с подключением, перейдите к основной странице управления товарами: <a href='products.php'>http://localhost/laba3/products.php</a></p>";
?> 