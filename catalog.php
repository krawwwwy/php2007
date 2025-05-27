<?php
session_start();

// Получаем email пользователя, если он вошел в систему
$user_email = '';
if (isset($_SESSION['user_id'])) {
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
        
        // Получение email пользователя
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $user_email = $user['email'];
        }
    } catch (PDOException $e) {
        // В случае ошибки оставляем email пустым
    }
}

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

// Массив для резервного хранения сервисов (на случай недоступности БД)
$services_backup = [
    [
        'id' => 1,
        'title_value' => 'Лечение зубов',
        'content' => 'Лечение кариеса, пульпита, периодонтита и других заболеваний зубов с использованием современных материалов и оборудования.',
        'price' => 'от 1000 руб.',
        'duration' => '30-90 мин.',
        'doctor' => 'стоматолог-терапевт',
        'image' => 'img/product1.jpg',
        'first_letter' => 'Л',
        'category_id' => 1
    ],
    [
        'id' => 2,
        'title_value' => 'Протезирование',
        'content' => 'Восстановление утраченных зубов с помощью современных протезов и коронок из различных материалов.',
        'price' => 'от 2000 руб.',
        'duration' => '60-120 мин.',
        'doctor' => 'стоматолог-ортопед',
        'image' => 'img/product2.jpg',
        'first_letter' => 'П',
        'category_id' => 2
    ],
    [
        'id' => 3,
        'title_value' => 'Имплантация',
        'content' => 'Установка дентальных имплантов для замещения утраченных зубов с последующим протезированием.',
        'price' => 'от 5000 руб.',
        'duration' => '60-180 мин.',
        'doctor' => 'стоматолог-хирург',
        'image' => 'img/product3.jpg',
        'first_letter' => 'И',
        'category_id' => 2
    ],
    [
        'id' => 4,
        'title_value' => 'Отбеливание',
        'content' => 'Профессиональное отбеливание зубов по современным технологиям для достижения белоснежной улыбки.',
        'price' => 'от 3000 руб.',
        'duration' => '60-90 мин.',
        'doctor' => 'стоматолог-терапевт',
        'image' => 'img/tooth-whitening.jpg',
        'first_letter' => 'О',
        'category_id' => 1
    ],
    [
        'id' => 5,
        'title_value' => 'Ортодонтия',
        'content' => 'Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров.',
        'price' => 'от 4000 руб.',
        'duration' => '30-60 мин.',
        'doctor' => 'ортодонт',
        'image' => 'img/banner1.jpg',
        'first_letter' => 'О',
        'category_id' => 3
    ],
    [
        'id' => 6,
        'title_value' => 'Рентгенология',
        'content' => 'Диагностика состояния зубов и челюстей с помощью рентгенологического оборудования.',
        'price' => 'от 500 руб.',
        'duration' => '10-20 мин.',
        'doctor' => 'рентгенолог',
        'image' => 'img/banner3.jpg',
        'first_letter' => 'Р',
        'category_id' => 3
    ]
];

// Резервные категории на случай недоступности БД
$categories_backup = [
    ['id' => 1, 'name' => 'Терапевтические услуги', 'description' => 'Лечебные процедуры для оздоровления зубов и десен'],
    ['id' => 2, 'name' => 'Хирургические услуги', 'description' => 'Процедуры по хирургическому вмешательству в полости рта'],
    ['id' => 3, 'name' => 'Диагностические услуги', 'description' => 'Диагностика состояния полости рта и зубов']
];

// Инициализация переменных
$db_connection = false;
$services = [];
$categories = [];
$error_message = '';
$creation_message = '';
$search_query = '';
$current_category = 0; // 0 означает "все категории"

// Получаем категорию из GET-параметра
if (isset($_GET['category'])) {
    $current_category = (int)$_GET['category'];
}

// Функции для работы с сервисами
function searchServicesByName($pdo, $searchQuery, $categoryId = 0) {
    try {
        $searchQuery = "%" . $searchQuery . "%";
        
        if ($categoryId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM services WHERE title_value LIKE :query AND category_id = :category_id");
            $stmt->execute(['query' => $searchQuery, 'category_id' => $categoryId]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM services WHERE title_value LIKE :query");
            $stmt->execute(['query' => $searchQuery]);
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function searchServicesByNameArray($services, $searchQuery, $categoryId = 0) {
    $results = [];
    $searchQuery = mb_strtolower($searchQuery, 'UTF-8');
    
    foreach ($services as $service) {
        $title = mb_strtolower($service['title_value'], 'UTF-8');
        
        if ($categoryId > 0 && $service['category_id'] != $categoryId) {
            continue; // Пропускаем услуги не из указанной категории
        }
        
        if (mb_strpos($title, $searchQuery, 0, 'UTF-8') !== false) {
            $results[] = $service;
        }
    }
    
    return $results;
}

function getServicesByCategory($pdo, $categoryId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function filterServicesByCategory($services, $categoryId) {
    $results = [];
    
    foreach ($services as $service) {
        if ($service['category_id'] == $categoryId) {
            $results[] = $service;
        }
    }
    
    return $results;
}

// Попытка подключения к базе данных и получения данных
try {
    // Сначала подключаемся без указания базы данных
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
    
    // Проверяем существование базы данных
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetch();
    
    if (!$dbExists) {
        // Создаем базу данных
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $creation_message = "База данных '$dbname' успешно создана!";
    }
    
    // Подключаемся к созданной/существующей базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    $db_connection = true;
    
    // Проверяем существование таблицы categories
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    $categoriesTableExists = $stmt->fetch();
    
    // Если таблицы категорий нет - создаем её
    if (!$categoriesTableExists) {
        // Создаем таблицу категорий
        $pdo->exec("CREATE TABLE `categories` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Вставляем категории
        $stmt = $pdo->prepare("INSERT INTO `categories` (name, description) VALUES (:name, :description)");
        foreach ($categories_backup as $category) {
            $stmt->execute([
                'name' => $category['name'],
                'description' => $category['description']
            ]);
        }
    }
    
    // Получаем список всех категорий
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
    
    // Проверяем существование таблицы services
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        // Создаем таблицу services
        $pdo->exec("CREATE TABLE `services` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title_value` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `price` VARCHAR(100) NOT NULL,
            `duration` VARCHAR(100) NOT NULL,
            `doctor` VARCHAR(255) NOT NULL,
            `image` VARCHAR(255) NOT NULL,
            `first_letter` CHAR(1) NOT NULL,
            `category_id` INT(11) UNSIGNED NOT NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Вставляем данные услуг
        $services = [
            [
                'title_value' => 'Лечение зубов',
                'content' => 'Лечение кариеса, пульпита, периодонтита и других заболеваний зубов с использованием современных материалов и оборудования.',
                'price' => 'от 1000 руб.',
                'duration' => '30-90 мин.',
                'doctor' => 'стоматолог-терапевт',
                'image' => 'img/product1.jpg',
                'first_letter' => 'Л',
                'category_id' => 1
            ],
            [
                'title_value' => 'Протезирование',
                'content' => 'Восстановление утраченных зубов с помощью современных протезов и коронок из различных материалов.',
                'price' => 'от 2000 руб.',
                'duration' => '60-120 мин.',
                'doctor' => 'стоматолог-ортопед',
                'image' => 'img/product2.jpg',
                'first_letter' => 'П',
                'category_id' => 2
            ],
            [
                'title_value' => 'Имплантация',
                'content' => 'Установка дентальных имплантов для замещения утраченных зубов с последующим протезированием.',
                'price' => 'от 5000 руб.',
                'duration' => '60-180 мин.',
                'doctor' => 'стоматолог-хирург',
                'image' => 'img/product3.jpg',
                'first_letter' => 'И',
                'category_id' => 2
            ],
            [
                'title_value' => 'Отбеливание',
                'content' => 'Профессиональное отбеливание зубов по современным технологиям для достижения белоснежной улыбки.',
                'price' => 'от 3000 руб.',
                'duration' => '60-90 мин.',
                'doctor' => 'стоматолог-терапевт',
                'image' => 'img/tooth-whitening.jpg',
                'first_letter' => 'О',
                'category_id' => 1
            ],
            [
                'title_value' => 'Ортодонтия',
                'content' => 'Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров.',
                'price' => 'от 4000 руб.',
                'duration' => '30-60 мин.',
                'doctor' => 'ортодонт',
                'image' => 'img/banner1.jpg',
                'first_letter' => 'О',
                'category_id' => 3
            ],
            [
                'title_value' => 'Рентгенология',
                'content' => 'Диагностика состояния зубов и челюстей с помощью рентгенологического оборудования.',
                'price' => 'от 500 руб.',
                'duration' => '10-20 мин.',
                'doctor' => 'рентгенолог',
                'image' => 'img/banner3.jpg',
                'first_letter' => 'Р',
                'category_id' => 3
            ]
        
        ];
        
        // Подготавливаем запрос на вставку
        $stmt = $pdo->prepare("INSERT INTO `services` 
            (title_value, content, price, duration, doctor, image, first_letter, category_id) 
            VALUES (:title_value, :content, :price, :duration, :doctor, :image, :first_letter, :category_id)");
        
        // Вставляем данные
        foreach ($services as $service) {
            $stmt->execute([
                'title_value' => $service['title_value'],
                'content' => $service['content'],
                'price' => $service['price'],
                'duration' => $service['duration'],
                'doctor' => $service['doctor'],
                'image' => $service['image'],
                'first_letter' => $service['first_letter'],
                'category_id' => $service['category_id']
            ]);
        }
        
        $creation_message = "База данных и таблица с услугами успешно созданы!";
    }
    
    // Получаем все услуги из БД, применяя фильтрацию по категории, если выбрана
    if ($current_category > 0) {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $current_category]);
        $services = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM services");
        $services = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    // Если ошибка подключения к БД, используем резервные данные
    $error_message = $e->getMessage();
    $services = $services_backup; // Используем резервные данные для услуг
    $categories = $categories_backup; // Используем резервные данные для категорий
    
    // Применяем фильтрацию по категории к резервным данным
    if ($current_category > 0) {
        $services = filterServicesByCategory($services_backup, $current_category);
    }
}

// Проверяем параметры запроса для поиска
if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
    $search_query = trim(strip_tags($_POST['search_query']));
    
    if ($db_connection) {
        $services = searchServicesByName($pdo, $search_query, $current_category);
    } else {
        $services = searchServicesByNameArray($services_backup, $search_query, $current_category);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог услуг - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Стили для формы поиска */
        .search-container {
            margin: 20px 0;
            padding: 20px;
            background: #f5faff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,153,204,0.06);
            border: 1px solid #cce6f6;
        }
        .search-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .search-input {
            width: 100%;
            max-width: 400px;
            padding: 10px 15px;
            border: 2px solid #0099cc;
            border-radius: 25px;
            font-size: 16px;
            background: #fff;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .search-input:focus {
            outline: none;
            border-color: #0077a3;
            box-shadow: 0 0 8px rgba(0,153,204,0.3);
        }
        .search-button {
            background: #0099cc;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-button:hover {
            background: #0077a3;
        }
        .search-hint {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        
        /* Стили для результатов поиска */
        .search-results {
            padding: 20px 0;
        }
        .search-title {
            color: #0099cc;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .search-item {
            margin-bottom: 25px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .search-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .search-item h3 {
            color: #0099cc;
            margin-top: 0;
        }
        .search-item .price {
            color: #0099cc;
            font-weight: bold;
            margin: 10px 0;
        }
        .search-item img {
            float: left;
            max-width: 120px;
            margin-right: 20px;
            border: 2px solid #0099cc;
            border-radius: 5px;
        }
        .search-item-details {
            overflow: hidden;
        }
        .no-results {
            padding: 30px;
            text-align: center;
            background: #f9f9f9;
            border-radius: 8px;
            color: #666;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success-message {
            color: green;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
        }
        .error-message {
            color: red;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
        }
        .search-char {
            background: #0099cc;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        /* Сохраняем блок со стилями для информации пользователя */
        .user-info {
            text-align: center;
            padding: 5px;
        }
        .user-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }
        .user-info .name {
            font-weight: bold;
            font-size: 16px;
            color: #222;
        }
        .user-info .email {
            font-style: italic;
            color: #444;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 15px;
            background: #fff;
            text-decoration: none;
            border-radius: 3px;
            color: #ff8000;
            font-weight: bold;
            border: 1px solid #ff8000;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: #ff9933;
            color: white;
        }
    </style>
<script src="fix_styles.js"></script>
</head>
<body>
<table border="0" width="900" cellpadding="0" cellspacing="0" align="center" bgcolor="#ff8000">
    <tr>
        <td width="150" align="center"><img src="img/logo.png" alt="Логотип" class="logo-img"></td>
        <td align="center"><h1 style="margin:0;">Стоматологическая клиника «Жемчуг»</h1></td>
        <td width="200">
            <div style="background: #ffc040; border-radius: 2px; padding: 18px 18px 16px 18px;">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <p class="name"><?= htmlspecialchars($_SESSION['name']) ?></p>
                        <p class="email"><?= htmlspecialchars($user_email) ?></p>
                        <a href="logout.php" class="logout-btn">Выйти</a>
                    </div>
                <?php else: ?>
                    <form class="auth-form" method="post" action="login.php" style="margin:0;padding:0;box-shadow:none;background:none;border:none;">
                        <table style="background:none;border:none;">
                            <tr>
                                <td align="right" style="font-weight:bold;color:#222;">логин:</td>
                                <td><input type="text" name="username" class="auth-input" style="width:140px;"></td>
                            </tr>
                            <tr>
                                <td align="right" style="font-weight:bold;color:#222;">пароль:</td>
                                <td><input type="password" name="password" class="auth-input" style="width:140px;"></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="display:flex;gap:8px;">
                                    <button type="submit" name="login" value="1" class="auth-btn" style="padding:2px 12px; background:#fff8e1; border:2px solid #ffa500; cursor:pointer; font-size:14px;">Войти</button>
                                    <a href="register.php" class="auth-btn" style="padding:2px 12px; background:#fff8e1; border:2px solid #ffa500; cursor:pointer; display:inline-block; text-decoration:none; color:#000; text-align:center; font-size:14px;">Регистрация</a>
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>
<table border="0" width="900" cellpadding="5" cellspacing="0" align="center">
    <tr bgcolor="#fff">
        <td colspan="4" align="center">
            <ul class="main-menu">
                <li><a href="https://gemchug93.ru/about/stomatologiya-na-meshcheryakova/" target="_blank">Новое отделение</a></li>
                <li><a href="https://gemchug93.ru/services/" target="_blank">Услуги</a></li>
                <li><a href="https://gemchug93.ru/spec/" target="_blank">Акции</a></li>
                <li><a href="https://gemchug93.ru/services/lechenie-pod-narkozom/" target="_blank">Лечение зубов под наркозом</a></li>
            </ul>
        </td>
    </tr>
</table>
<hr style="width:900px; margin:auto;">
<table border="0" width="900" cellpadding="5" cellspacing="0" align="center">
    <tr>
        <td width="150" valign="top" align="center" bgcolor="#ff8000" class="side-menu">
            <a href="index.php">Главная</a>
            <a href="catalog.php" class="active">Каталог</a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php">Корзина</a>
            <?php endif; ?>
            <a href="contacts.php">Контакты</a>
            <a href="about.php">О нас</a>

        </td>
        <td valign="top">
            <h2 align="center">Каталог услуг</h2>
            
            <?php if(isset($_SESSION['success_message'])): ?>
            <div class="message success-message">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error_message'])): ?>
            <div class="message error-message">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
            
            <?php if(isset($creation_message)): ?>
            <div class="message success-message">
                <?= $creation_message ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error_message) && !empty($error_message)): ?>
            <div class="message error-message">
                <p><strong>Произошла ошибка при подключении к базе данных:</strong> <?= htmlspecialchars($error_message) ?></p>
                <p>Используется локальная версия каталога.</p>
            </div>
            <?php endif; ?>
            
            <!-- Форма поиска -->
            <div class="search-container">
                <form method="post" action="catalog.php" class="search-form">
                    <input type="text" name="search_query" class="search-input" placeholder="Введите название услуги или часть названия" 
                           value="<?= htmlspecialchars($search_query) ?>" required>
                    <button type="submit" class="search-button">Поиск</button>
                    <div class="search-hint">
                        <p>Введите название услуги или часть названия, например: "Лечение" или "Протезирование"</p>
                    </div>
                </form>
            </div>
            
            <div class="search-results">
            <?php
            // Показываем заголовок с информацией о поиске
            if (!empty($search_query)) {
                echo "<h2 class='search-title'>Поиск услуг: <span class='search-char'>" . htmlspecialchars($search_query) . "</span></h2>";
            } else {
                echo "<h2 class='search-title'>Все стоматологические услуги</h2>";
            }
            
            // Отображаем результаты поиска или все услуги
            if (!empty($services)) {
                foreach ($services as $service) {
                    echo "<div class='search-item'>";
                    // Add link to product page based on service ID
                    $productLink = 'product' . $service['id'] . '.php';
                    // All services now have PHP pages, so we link all of them
                    echo "<a href='{$productLink}'><img src='{$service['image']}' alt='{$service['title_value']}'></a>";
                    echo "<div class='search-item-details'>";
                    echo "<h3><a href='{$productLink}' style='text-decoration:none;color:#0099cc;'>{$service['title_value']}</a></h3>";
                    echo "<p class='price'>{$service['price']}</p>";
                    echo "<p>{$service['content']}</p>";
                    echo "<p><strong>Продолжительность:</strong> {$service['duration']}</p>";
                    echo "<p><strong>Врач:</strong> {$service['doctor']}</p>";
                    echo "<a href='{$productLink}' style='display:inline-block;margin-top:10px;padding:5px 15px;background:#0099cc;color:#fff;text-decoration:none;border-radius:3px;'>Подробнее</a>";
                    
                    // Добавляем кнопку "Добавить в корзину" только для авторизованных пользователей
                    if(isset($_SESSION['user_id'])) {
                        echo "<form method='post' action='add_to_cart.php' style='display:inline-block;margin-left:10px;'>";
                        echo "<input type='hidden' name='service_id' value='{$service['id']}'>";
                        echo "<input type='hidden' name='redirect' value='catalog.php'>";
                        echo "<button type='submit' style='padding:5px 15px;background:#4CAF50;color:#fff;border:none;border-radius:3px;cursor:pointer;'>Добавить в корзину</button>";
                        echo "</form>";
                    }
                    
                    echo "</div>";
                    echo "<div style='clear:both;'></div>";
                    echo "</div>";
                }
            } else {
                if (!empty($search_query)) {
                    // Если был запрос, но нет результатов
                    echo "<div class='no-results'>";
                    echo "<p>К сожалению, услуги, содержащие в названии <span class='search-char'>" . 
                          htmlspecialchars($search_query) . "</span>, не найдены.</p>";
                    echo "<p>Попробуйте изменить поисковый запрос или просмотрите все услуги.</p>";
                    echo "</div>";
                } else {
                    // Если нет запросов и нет услуг (что странно)
                    echo "<div class='no-results'>";
                    echo "<p>Услуги не найдены. Пожалуйста, проверьте подключение к базе данных или попробуйте позже.</p>";
                    echo "</div>";
                }
            }
            ?>
            </div>
        </td>
        <td width="190" valign="top" align="center" bgcolor="#ff8000">
            <a href="https://gemchug93.ru/services/ortodontiya/" style="text-decoration:none;color:#000;">
                <img src="img/banner1.jpg" alt="Исправление прикуса" class="banner-img" style="width:170px;height:80px;"><br>
                <span style="display:block;font-size:1.1em;font-weight:bold;margin-bottom:18px;">Исправление прикуса</span>
            </a>
            <a href="https://gemchug93.ru/services/ortopediya/" style="text-decoration:none;color:#000;">
                <img src="img/banner2.jpg" alt="Ортопедия" class="banner-img" style="width:170px;height:80px;"><br>
                <span style="display:block;font-size:1.1em;font-weight:bold;margin-bottom:18px;">Ортопедия</span>
            </a>
            <a href="https://gemchug93.ru/services/rentgenkabinet/" style="text-decoration:none;color:#000;">
                <img src="img/banner3.jpg" alt="Рентгенология" class="banner-img" style="width:170px;height:80px;"><br>
                <span style="display:block;font-size:1.1em;font-weight:bold;margin-bottom:18px;">Рентгенология</span>
            </a>
        </td>
    </tr>
</table>
<table border="0" width="900" cellpadding="5" cellspacing="0" align="center" style="margin-top:0;" class="site-footer-fixed">
    <tr>
        <td align="center" style="border-top:1px solid #ccc;padding-top:10px;">
            <hr style="width:900px; margin:auto;">
            &copy; 2025 Стоматологическая клиника «Жемчуг». Все права защищены.
            <div class="footer-links">
                <a href="javascript:void(0);" onclick="window.open('privacy.php', 'Политика конфиденциальности', 'width=800,height=600,scrollbars=yes');">Политика конфиденциальности</a>
            </div>
        </td>
    </tr>
</table>
</body>
</html> 