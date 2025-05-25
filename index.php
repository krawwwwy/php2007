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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .banner-img {
            border: 2px solid #fff;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        .banner-img:hover {
            transform: scale(1.05);
        }
        .promo {
            margin-bottom: 30px;
        }
        .promo img {
            width: 100%;
            max-width: 500px;
            display: block;
            margin: 0 auto;
            border: 2px solid #0099cc;
            border-radius: 8px;
        }
        .testimonials {
            background: #f5faff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .testimonial {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .testimonial:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .testimonial p {
            font-style: italic;
            color: #555;
        }
        .testimonial-author {
            font-weight: bold;
            color: #0099cc;
            text-align: right;
        }
        .contact-info {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        .contact-info p {
            margin: 5px 0;
        }
        .contact-info strong {
            color: #0099cc;
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
            <a href="index.php" class="active">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="contacts.php">Контакты</a>
            <a href="about.php">О нас</a>

        </td>
        <td valign="top">
            <h2>Добро пожаловать в стоматологическую клинику «Жемчуг»!</h2>
            <div class="promo">
                <img src="img/clinic.jpg" alt="Стоматологическая клиника Жемчуг">
            </div>
            <p>Наша клиника предлагает полный спектр стоматологических услуг для всей семьи. Мы используем современное оборудование и материалы, что позволяет нам гарантировать высокое качество лечения.</p>
            <p>У нас работают опытные врачи, которые постоянно повышают свою квалификацию и следят за последними тенденциями в стоматологии.</p>
            
            <div class="testimonials">
                <h3>Отзывы наших пациентов</h3>
                <div class="testimonial">
                    <p>"Прекрасная клиника с внимательным персоналом. Лечила зубы у доктора Иванова, очень довольна результатом!"</p>
                    <div class="testimonial-author">Анна С.</div>
                </div>
                <div class="testimonial">
                    <p>"Удалял зуб мудрости, всё прошло быстро и безболезненно. Спасибо за профессионализм!"</p>
                    <div class="testimonial-author">Дмитрий К.</div>
                </div>
            </div>
            
            <div class="contact-info">
                <h3>Наши контакты</h3>
                <p><strong>Адрес:</strong> г. Москва, ул. Пушкина, д. 10</p>
                <p><strong>Телефон:</strong> +7 (495) 123-45-67</p>
                <p><strong>Email:</strong> info@zhemchug.ru</p>
                <p><strong>Часы работы:</strong> Пн-Пт: 9:00-20:00, Сб: 10:00-18:00, Вс: выходной</p>
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
        </td>
    </tr>
</table>
</body>
</html> 