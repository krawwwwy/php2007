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

// Если пользователь уже вошел, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
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

$error = '';
$success = '';

// Если форма отправлена и нажата кнопка входа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    
    // Очистка и валидация введенных данных
    $username = trim(strip_tags($_POST['username']));
    $password = $_POST['password'];
    
    if (empty($username)) {
        $error = 'Пожалуйста, введите логин';
    } elseif (empty($password)) {
        $error = 'Пожалуйста, введите пароль';
    } else {
        try {
            // Подключение к базе данных
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
            
            // Подготовка и выполнение запроса
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $userRecord = $stmt->fetch();
            
            if ($userRecord && password_verify($password, $userRecord['password'])) {
                // Успешный вход
                $_SESSION['user_id'] = $userRecord['id'];
                $_SESSION['username'] = $userRecord['username'];
                $_SESSION['name'] = $userRecord['name'];
                
                // Перенаправление на страницу каталога после успешного входа
                header("Location: catalog.php");
                exit;
            } else {
                $error = 'Неверный логин или пароль';
            }
        } catch (PDOException $e) {
            $error = 'Произошла ошибка при входе. Пожалуйста, попробуйте позже.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 30px auto;
            padding: 20px;
            background: #f5faff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-submit {
            background-color: #0099cc;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-submit:hover {
            background-color: #0077aa;
        }
        .error-message {
            color: #ff0000;
            margin-bottom: 15px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
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
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог</a>
            <a href="contacts.php">Контакты</a>
            <a href="about.php">О нас</a>
        <td valign="top">
            <div class="login-container">
                <h2 align="center">Вход в личный кабинет</h2>
                
                <?php if(!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">Логин:</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    
                    <div align="center">
                        <button type="submit" class="btn-primary" name="login">Войти</button>
                    </div>
                    
                    <div class="register-link">
                        <p>Ещё не зарегистрированы? <a href="register.php">Зарегистрироваться</a></p>
                    </div>
                </form>
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