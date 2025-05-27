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

// Получаем ошибки и данные формы из сессии
$errors = isset($_SESSION['reviews_errors']) ? $_SESSION['reviews_errors'] : [];
$formData = isset($_SESSION['reviews_form_data']) ? $_SESSION['reviews_form_data'] : [
    'name' => isset($_SESSION['user_id']) ? $_SESSION['name'] : '',
    'email' => $user_email,
    'service' => '',
    'rating' => '',
    'review' => ''
];
$success = isset($_SESSION['reviews_success']) ? $_SESSION['reviews_success'] : '';
$error = isset($_SESSION['reviews_error']) ? $_SESSION['reviews_error'] : '';

// Очищаем данные сессии
unset($_SESSION['reviews_errors']);
unset($_SESSION['reviews_form_data']);
unset($_SESSION['reviews_success']);
unset($_SESSION['reviews_error']);

// Загрузка отображаемых отзывов
$reviews = [];
try {
    if (isset($pdo)) {
        // Проверяем, существует ли таблица reviews
        $stmt = $pdo->query("SHOW TABLES LIKE 'reviews'");
        $tableExists = $stmt->fetch();
        
        if ($tableExists) {
            // Получаем последние 5 одобренных отзывов
            $stmt = $pdo->query("SELECT * FROM reviews WHERE approved = 1 ORDER BY date_added DESC LIMIT 5");
            $reviews = $stmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    // Если ошибка, продолжаем без загрузки отзывов
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отзывы - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        
        /* Стили для отзывов */
        .reviews-container {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .review-item {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .review-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .review-author {
            font-weight: bold;
            color: #0099cc;
        }
        
        .review-date {
            color: #777;
            font-size: 14px;
        }
        
        .review-service {
            font-style: italic;
            color: #555;
            margin-bottom: 8px;
        }
        
        .review-rating {
            color: #ff8000;
            margin-bottom: 10px;
        }
        
        .review-text {
            line-height: 1.5;
        }
        
        /* Стили для формы */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .rating-select {
            display: flex;
            margin-bottom: 10px;
        }
        
        .rating-select label {
            margin-right: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .rating-select input {
            margin-right: 5px;
        }
        
        .btn-primary {
            background-color: #0099cc;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background-color: #0077aa;
        }
        
        /* Стили для футера */
        .footer-links {
            margin-top: 10px;
        }
        .footer-links a {
            color: #555;
            margin: 0 10px;
            text-decoration: none;
            font-size: 14px;
        }
        .footer-links a:hover {
            text-decoration: underline;
            color: #ff8000;
        }
        
        .banner-img {
            border: 2px solid #fff;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        .banner-img:hover {
            transform: scale(1.05);
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
            <a href="catalog.php">Каталог</a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php">Корзина</a>
            <?php endif; ?>
            <a href="contacts.php">Контакты</a>
            <a href="reviews.php" class="active">Отзывы</a>
            <a href="about.php">О нас</a>
        </td>
        <td valign="top">
            <h2 align="center">Отзывы пациентов</h2>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <h3>Оставить отзыв</h3>
            <form class="contact-form" method="post" action="process_review.php">
                <div class="form-group">
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="service">Какую услугу вы получили:</label>
                    <select id="service" name="service" class="form-control">
                        <option value="">Выберите услугу</option>
                        <option value="Лечение зубов" <?php echo ($formData['service'] ?? '') === 'Лечение зубов' ? 'selected' : ''; ?>>Лечение зубов</option>
                        <option value="Протезирование" <?php echo ($formData['service'] ?? '') === 'Протезирование' ? 'selected' : ''; ?>>Протезирование</option>
                        <option value="Имплантация" <?php echo ($formData['service'] ?? '') === 'Имплантация' ? 'selected' : ''; ?>>Имплантация</option>
                        <option value="Отбеливание" <?php echo ($formData['service'] ?? '') === 'Отбеливание' ? 'selected' : ''; ?>>Отбеливание</option>
                        <option value="Ортодонтия" <?php echo ($formData['service'] ?? '') === 'Ортодонтия' ? 'selected' : ''; ?>>Ортодонтия</option>
                        <option value="Рентгенология" <?php echo ($formData['service'] ?? '') === 'Рентгенология' ? 'selected' : ''; ?>>Рентгенология</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Оценка:</label>
                    <div class="rating-select">
                        <label><input type="radio" name="rating" value="5" <?php echo ($formData['rating'] ?? '') === '5' ? 'checked' : ''; ?>> 5 </label>
                        <label><input type="radio" name="rating" value="4" <?php echo ($formData['rating'] ?? '') === '4' ? 'checked' : ''; ?>> 4 </label>
                        <label><input type="radio" name="rating" value="3" <?php echo ($formData['rating'] ?? '') === '3' ? 'checked' : ''; ?>> 3 </label>
                        <label><input type="radio" name="rating" value="2" <?php echo ($formData['rating'] ?? '') === '2' ? 'checked' : ''; ?>> 2 </label>
                        <label><input type="radio" name="rating" value="1" <?php echo ($formData['rating'] ?? '') === '1' ? 'checked' : ''; ?>> 1 </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="review">Ваш отзыв:</label>
                    <textarea id="review" name="review" rows="5" class="form-control"><?php echo htmlspecialchars($formData['review'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">Отправить отзыв</button>
            </form>
            
            <?php if (!empty($reviews)): ?>
            <div class="reviews-container">
                <h3>Отзывы пациентов</h3>
                
                <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="review-author"><?= htmlspecialchars($review['name']) ?></span>
                        <span class="review-date"><?= date('d.m.Y', strtotime($review['date_added'])) ?></span>
                    </div>
                    <div class="review-service">Услуга: <?= htmlspecialchars($review['service']) ?></div>
                    <div class="review-rating">
                        Оценка: <?php 
                            for ($i = 0; $i < $review['rating']; $i++) {
                                echo '★';
                            }
                            for ($i = $review['rating']; $i < 5; $i++) {
                                echo '☆';
                            }
                        ?>
                    </div>
                    <div class="review-text"><?= nl2br(htmlspecialchars($review['review'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="reviews-container">
                <p style="text-align: center;">Пока нет отзывов. Будьте первым, кто оставит отзыв о нашей клинике!</p>
            </div>
            <?php endif; ?>
            
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