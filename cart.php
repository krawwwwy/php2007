<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    // Если не авторизован, перенаправляем на страницу входа
    header("Location: login.php");
    exit;
}

// Получаем email пользователя, если он вошел в систему
$user_email = '';
if (isset($_SESSION['user_id'])) {
    // Параметры подключения к базе данных
    require_once 'db_connect.php';
    
    try {
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

// Удаление товара из корзины
if (isset($_POST['remove_item']) && isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = :cart_id AND user_id = :user_id");
        $stmt->execute([
            'cart_id' => $cart_id,
            'user_id' => $_SESSION['user_id']
        ]);
        $success_message = "Услуга успешно удалена из корзины";
    } catch (PDOException $e) {
        $error_message = "Ошибка при удалении услуги из корзины";
    }
}

// Получение содержимого корзины пользователя
$cart_items = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantity, s.id AS service_id, s.title_value, s.price, s.image 
        FROM cart c 
        JOIN services s ON c.service_id = s.id 
        WHERE c.user_id = :user_id
        ORDER BY c.date_added DESC
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Ошибка при загрузке корзины: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .cart-item-image {
            width: 100px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-title {
            font-size: 18px;
            margin-bottom: 5px;
            color: #0066cc;
        }
        
        .cart-item-price {
            font-weight: bold;
            color: #333;
        }
        
        .cart-item-actions {
            display: flex;
            align-items: center;
        }
        
        .cart-item-quantity {
            margin-right: 15px;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .remove-btn {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .remove-btn:hover {
            background-color: #cc0000;
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .cart-total {
            margin-top: 20px;
            text-align: right;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .continue-shopping {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #0099cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .continue-shopping:hover {
            background-color: #0077aa;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
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
        
        .banner-img {
            border: 2px solid #fff;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        .banner-img:hover {
            transform: scale(1.05);
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
            <a href="cart.php" class="active">Корзина</a>
            <a href="contacts.php">Контакты</a>
            <a href="reviews.php">Отзывы</a>
            <a href="about.php">О нас</a>
        </td>
        <td valign="top">
            <div class="cart-container">
                <h2>Корзина</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <p>Ваша корзина пуста</p>
                        <a href="catalog.php" class="continue-shopping">Перейти в каталог услуг</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title_value']) ?>" class="cart-item-image">
                            <div class="cart-item-details">
                                <h3 class="cart-item-title"><?= htmlspecialchars($item['title_value']) ?></h3>
                                <p class="cart-item-price"><?= htmlspecialchars($item['price']) ?></p>
                            </div>
                            <div class="cart-item-actions">
                                <div class="cart-item-quantity">
                                    Количество: <?= $item['quantity'] ?>
                                </div>
                                <form method="post" action="cart.php">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="remove_item" class="remove-btn">Удалить</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-total">
                        <p><strong>Итого: <?= count($cart_items) ?> услуг</strong></p>
                        <a href="catalog.php" class="continue-shopping">Продолжить покупки</a>
                    </div>
                <?php endif; ?>
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