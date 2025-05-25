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
    <title>Ортодонтия — Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <script>
    function openImage(url) {
        window.open(url, '_blank');
    }
    </script>
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
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="contacts.php">Контакты</a>
            <a href="about.php">О нас</a>

        </td>
        <td valign="top">
            <h2 align="center">Ортодонтия</h2>
            
            <h3 class="product-heading">Краткое описание товара</h3>
            <img src="img/banner1.jpg" alt="Ортодонтия" width="220" class="product-image" onclick="openImage(this.src)">
            <p class="product-short-desc">Исправление прикуса и выравнивание зубного ряда с помощью брекет-систем и элайнеров. Современные методы, удобство и эстетика, гарантированный результат.</p>
            
            <h3 class="product-heading">Характеристики товара</h3>
            <ul class="product-features">
                <li>Разные типы брекет-систем (металлические, керамические, сапфировые)</li>
                <li>Невидимые капы-элайнеры</li>
                <li>Индивидуальный план лечения</li>
                <li>Цифровое моделирование результата</li>
                <li>Контроль на всех этапах лечения</li>
            </ul>
            
            <h3 class="product-heading">Подробное описание товара</h3>
            <div class="product-full-desc">
                <p>Ортодонтическое лечение позволяет исправить прикус, выровнять зубы и создать красивую, здоровую улыбку. Наша клиника предлагает различные варианты ортодонтического лечения: классические брекет-системы разных типов, а также съемные прозрачные капы (элайнеры).</p>
                <p>Процесс лечения начинается с консультации ортодонта, где врач проводит осмотр, делает необходимые снимки и предлагает варианты лечения. С помощью цифрового моделирования вы сможете увидеть предполагаемый результат еще до начала лечения.</p>
                <p>Продолжительность лечения зависит от сложности случая и выбранной методики. В среднем, оно занимает от 6 месяцев до 2 лет. На протяжении всего периода лечения врач проводит регулярный контроль и коррекцию, обеспечивая достижение наилучшего результата.</p>
                <p>После завершения активного периода лечения пациент получает ретейнеры для закрепления результата и рекомендации по уходу.</p>
            </div>
            <hr>
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