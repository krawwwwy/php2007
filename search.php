<?php
session_start();

// Получаем email пользователя, если он вошел в систему
$user_email = '';
if (isset($_SESSION['user_id'])) {
    // Параметры подключения к базе данных
    $host = 'localhost';
    $dbname = 'dental_clinic';
    $user = 'root';
    $pass = '';
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

// Проверка существования базы данных и/или её создание
try {
    // Сначала подключимся без указания базы данных
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
            `first_letter` CHAR(1) NOT NULL
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
                'first_letter' => 'Л'
            ],
            [
                'title_value' => 'Протезирование',
                'content' => 'Восстановление утраченных зубов с помощью современных протезов и коронок из различных материалов.',
                'price' => 'от 2000 руб.',
                'duration' => '60-120 мин.',
                'doctor' => 'стоматолог-ортопед',
                'image' => 'img/product2.jpg',
                'first_letter' => 'П'
            ],
            [
                'title_value' => 'Имплантация',
                'content' => 'Установка дентальных имплантов для замещения утраченных зубов с последующим протезированием.',
                'price' => 'от 5000 руб.',
                'duration' => '60-180 мин.',
                'doctor' => 'стоматолог-хирург',
                'image' => 'img/product3.jpg',
                'first_letter' => 'И'
            ],
            [
                'title_value' => 'Отбеливание',
                'content' => 'Профессиональное отбеливание зубов по современным технологиям для достижения белоснежной улыбки.',
                'price' => 'от 3000 руб.',
                'duration' => '60-90 мин.',
                'doctor' => 'стоматолог-терапевт',
                'image' => 'img/tooth-whitening.jpg',
                'first_letter' => 'О'
            ],
            [
                'title_value' => 'Ортодонтия',
                'content' => 'Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров.',
                'price' => 'от 4000 руб.',
                'duration' => '30-60 мин.',
                'doctor' => 'ортодонт',
                'image' => 'img/banner1.jpg',
                'first_letter' => 'О'
            ],
            [
                'title_value' => 'Рентгенология',
                'content' => 'Диагностика состояния зубов и челюстей с помощью рентгенологического оборудования.',
                'price' => 'от 500 руб.',
                'duration' => '10-20 мин.',
                'doctor' => 'рентгенолог',
                'image' => 'img/banner3.jpg',
                'first_letter' => 'Р'
            ]
           
        ];
        
        // Подготавливаем запрос на вставку
        $stmt = $pdo->prepare("INSERT INTO `services` 
            (title_value, content, price, duration, doctor, image, first_letter) 
            VALUES (:title_value, :content, :price, :duration, :doctor, :image, :first_letter)");
        
        // Вставляем данные
        foreach ($services as $service) {
            $stmt->execute([
                'title_value' => $service['title_value'],
                'content' => $service['content'],
                'price' => $service['price'],
                'duration' => $service['duration'],
                'doctor' => $service['doctor'],
                'image' => $service['image'],
                'first_letter' => $service['first_letter']
            ]);
        }
        
        $creation_message = "База и данные успешно созданы!";
    }

} catch (PDOException $e) {
    // Если возникла ошибка подключения к БД, используем резервный массив данных
    $error_message = $e->getMessage();
    
    // Создаем массив с данными о стоматологических услугах для локального использования
    $services = [
        [
            'id' => 1,
            'title_value' => 'Лечение зубов',
            'content' => 'Лечение кариеса, пульпита, периодонтита и других заболеваний зубов с использованием современных материалов и оборудования.',
            'price' => 'от 1000 руб.',
            'duration' => '30-90 мин.',
            'doctor' => 'стоматолог-терапевт',
            'image' => 'img/product1.jpg',
            'first_letter' => 'Л'
        ],
        [
            'id' => 2,
            'title_value' => 'Протезирование',
            'content' => 'Восстановление утраченных зубов с помощью современных протезов и коронок из различных материалов.',
            'price' => 'от 2000 руб.',
            'duration' => '60-120 мин.',
            'doctor' => 'стоматолог-ортопед',
            'image' => 'img/product2.jpg',
            'first_letter' => 'П'
        ],
        [
            'id' => 3,
            'title_value' => 'Имплантация',
            'content' => 'Установка дентальных имплантов для замещения утраченных зубов с последующим протезированием.',
            'price' => 'от 5000 руб.',
            'duration' => '60-180 мин.',
            'doctor' => 'стоматолог-хирург',
            'image' => 'img/product3.jpg',
            'first_letter' => 'И'
        ],
        [
            'id' => 4,
            'title_value' => 'Отбеливание',
            'content' => 'Профессиональное отбеливание зубов по современным технологиям для достижения белоснежной улыбки.',
            'price' => 'от 3000 руб.',
            'duration' => '60-90 мин.',
            'doctor' => 'стоматолог-терапевт',
            'image' => 'img/tooth-whitening.jpg',
            'first_letter' => 'О'
        ],
        [
            'id' => 5,
            'title_value' => 'Ортодонтия',
            'content' => 'Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров.',
            'price' => 'от 4000 руб.',
            'duration' => '30-60 мин.',
            'doctor' => 'ортодонт',
            'image' => 'img/banner1.jpg',
            'first_letter' => 'О'
        ],
        [
            'id' => 6,
            'title_value' => 'Рентгенология',
            'content' => 'Диагностика состояния зубов и челюстей с помощью рентгенологического оборудования.',
            'price' => 'от 500 руб.',
            'duration' => '10-20 мин.',
            'doctor' => 'рентгенолог',
            'image' => 'img/banner3.jpg',
            'first_letter' => 'Р'
        ],
        [
            'id' => 7,
            'title_value' => 'Реставрация зубов',
            'content' => 'Восстановление формы и функции повреждённых зубов с помощью современных композитных материалов.',
            'price' => 'от 2500 руб.',
            'duration' => '60-120 мин.',
            'doctor' => 'стоматолог-терапевт',
            'image' => 'img/restoration.jpg',
            'first_letter' => 'Р'
        ],
        [
            'id' => 8,
            'title_value' => 'Удаление зубов',
            'content' => 'Безболезненное удаление зубов любой сложности с помощью современных методик.',
            'price' => 'от 1500 руб.',
            'duration' => '30-60 мин.',
            'doctor' => 'стоматолог-хирург',
            'image' => 'img/tooth-removal.jpg',
            'first_letter' => 'У'
        ],
        [
            'id' => 9,
            'title_value' => 'Установка виниров',
            'content' => 'Установка тонких керамических или композитных накладок на переднюю поверхность зубов для улучшения их внешнего вида.',
            'price' => 'от 6000 руб.',
            'duration' => '60-120 мин.',
            'doctor' => 'стоматолог-ортопед',
            'image' => 'img/veneers.jpg',
            'first_letter' => 'У'
        ],
        [
            'id' => 10,
            'title_value' => 'Детская стоматология',
            'content' => 'Профилактика и лечение зубов у детей с учетом возрастных особенностей в комфортной обстановке.',
            'price' => 'от 800 руб.',
            'duration' => '30-60 мин.',
            'doctor' => 'детский стоматолог',
            'image' => 'img/child-dentistry.jpg',
            'first_letter' => 'Д'
        ],
        [
            'id' => 11,
            'title_value' => 'Консультация врача',
            'content' => 'Осмотр полости рта, диагностика заболеваний, составление плана лечения.',
            'price' => 'от 500 руб.',
            'duration' => '20-30 мин.',
            'doctor' => 'стоматолог',
            'image' => 'img/consultation.jpg',
            'first_letter' => 'К'
        ],
        [
            'id' => 12,
            'title_value' => 'Профессиональная чистка',
            'content' => 'Снятие зубного камня и налета, полировка зубов с использованием профессиональных средств.',
            'price' => 'от 2000 руб.',
            'duration' => '40-60 мин.',
            'doctor' => 'стоматолог-гигиенист',
            'image' => 'img/cleaning.jpg',
            'first_letter' => 'П'
        ]
    ];
}

// Функция поиска услуг по названию или части названия - поиск через БД
function searchServicesByName($pdo, $searchQuery) {
    try {
        $searchQuery = "%" . $searchQuery . "%";
        $stmt = $pdo->prepare("SELECT * FROM services WHERE title_value LIKE :query");
        $stmt->execute(['query' => $searchQuery]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // В случае ошибки БД вернем пустой массив
        return [];
    }
}

// Функция поиска услуг по названию - поиск через массив (резервный вариант)
function searchServicesByNameArray($services, $searchQuery) {
    $results = [];
    $searchQuery = mb_strtolower($searchQuery, 'UTF-8');
    
    foreach ($services as $service) {
        // Преобразуем название услуги в нижний регистр для регистронезависимого поиска
        $title = mb_strtolower($service['title_value'], 'UTF-8');
        
        // Проверяем, содержит ли название запрошенную строку
        if (mb_strpos($title, $searchQuery, 0, 'UTF-8') !== false) {
            $results[] = $service;
        }
    }
    
    return $results;
}

// Используем поиск по первой букве для категоризации
function getServicesByFirstLetter($pdo, $letter) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM services WHERE first_letter = :letter");
        $stmt->execute(['letter' => $letter]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // В случае ошибки возвращаем пустой массив
        return [];
    }
}

// Переменная для хранения результатов поиска
$search_results = [];

// Проверяем, был ли отправлен поисковый запрос
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    
    // Подготовка запроса к базе данных
    $stmt = $pdo->prepare("SELECT * FROM services WHERE 
                          title_value LIKE :query OR 
                          content LIKE :query OR 
                          doctor LIKE :query");
    
    // Выполнение запроса
    $stmt->execute(['query' => '%' . $search_query . '%']);
    
    // Получение результатов
    $search_results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск - Стоматологическая клиника Жемчуг</title>
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
            background: #fff;как
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
                                    <button type="submit" name="login" value="1" class="auth-btn">Войти</button>
                                    <a href="register.php" class="auth-btn">Регистрация</a>
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
    <tr>
        <td width="150" valign="top" align="center" bgcolor="#ff8000" class="side-menu">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="contacts.php">Контакты</a>
            <a href="about.php">О нас</a>
        </td>
        <td valign="top" bgcolor="#ffffcc" style="padding: 20px;">
            <div class="search-container">
                <form class="search-form" action="search.php" method="GET">
                    <input type="text" name="query" class="search-input" placeholder="Введите поисковый запрос" value="<?= isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '' ?>">
                    <button type="submit" class="search-button">Найти</button>
                    <div class="search-hint">Введите название услуги, описание или имя врача</div>
                </form>
            </div>

            <div class="search-results">
                <?php if (isset($_GET['query']) && !empty($_GET['query'])): ?>
                    <h2 class="search-title">Результаты поиска для "<?= htmlspecialchars($_GET['query']) ?>"</h2>
                    
                    <?php if (!empty($search_results)): ?>
                        <?php foreach ($search_results as $service): ?>
                            <div class="search-item">
                                <?php if (!empty($service['image'])): ?>
                                    <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title_value']) ?>">
                                <?php endif; ?>
                                <div class="search-item-details">
                                    <h3><?= htmlspecialchars($service['title_value']) ?></h3>
                                    <p><?= nl2br(htmlspecialchars($service['content'])) ?></p>
                                    <p class="price">Стоимость: <?= htmlspecialchars($service['price']) ?> руб.</p>
                                    <p>Длительность: <?= htmlspecialchars($service['duration']) ?></p>
                                    <?php if (!empty($service['doctor'])): ?>
                                        <p>Врач: <?= htmlspecialchars($service['doctor']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <p>По вашему запросу ничего не найдено. Попробуйте другой запрос.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h2 class="search-title">Поиск по услугам клиники</h2>
                    <p>Введите в поле поиска интересующую вас услугу, симптом или имя врача.</p>
                <?php endif; ?>
            </div>
        </td>
    </tr>
</table>

<table border="0" width="900" cellpadding="5" cellspacing="0" align="center" bgcolor="#ff8000">
    <tr>
        <td align="center">
            <p style="margin: 5px 0; color: #fff;">© 2023 Стоматологическая клиника «Жемчуг». Все права защищены.</p>
        </td>
    </tr>
</table>
</body>
</html> 