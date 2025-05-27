<?php
session_start();

// Проверяем, авторизован ли пользователь как администратор
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'dental_clinic';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

// Проверяем, если отзыв нужно одобрить или отклонить
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int) $_GET['id'];
    
    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE reviews SET approved = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['admin_message'] = 'Отзыв успешно одобрен';
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $_SESSION['admin_message'] = 'Отзыв успешно удален';
        }
        
        header("Location: admin_reviews.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['admin_error'] = 'Произошла ошибка: ' . $e->getMessage();
        header("Location: admin_reviews.php");
        exit;
    }
}

// Получаем список отзывов
$reviews = [];
$message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : '';
$error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';

// Очищаем сообщения в сессии
unset($_SESSION['admin_message']);
unset($_SESSION['admin_error']);

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Получаем список всех отзывов
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY date_added DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Произошла ошибка при подключении к базе данных: ' . $e->getMessage();
    $reviews = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Администрирование отзывов - Стоматологическая клиника "Жемчуг"</title>
    <meta charset="utf-8">
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 900px;
            margin: 0 auto;
            background: white;
        }
        .header {
            background-color: #ff8000;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        .side-menu {
            padding: 10px 0;
        }
        .side-menu a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 0;
            margin: 5px 0;
            font-weight: bold;
        }
        .side-menu a:hover, .side-menu a.active {
            background-color: white;
            color: #ff8000;
        }
        .content {
            padding: 20px;
        }
        .error-message {
            background-color: #ffeded;
            color: #d8000c;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success-message {
            background-color: #dff2bf;
            color: #4f8a10;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .review-item {
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .review-name {
            font-weight: bold;
        }
        .review-date {
            color: #777;
            font-size: 0.9em;
        }
        .review-service {
            color: #555;
            margin-bottom: 10px;
            font-style: italic;
        }
        .review-rating {
            margin-bottom: 10px;
            color: #ff8000;
        }
        .review-text {
            line-height: 1.5;
        }
        .review-status {
            margin-top: 10px;
            padding: 5px 10px;
            display: inline-block;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-approved {
            background-color: #dff2bf;
            color: #4f8a10;
        }
        .status-pending {
            background-color: #feefb3;
            color: #9f6000;
        }
        .action-buttons {
            margin-top: 10px;
        }
        .btn {
            padding: 5px 10px;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-approve {
            background-color: #4CAF50;
        }
        .btn-reject {
            background-color: #f44336;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .logout-btn {
            padding: 5px 10px;
            background-color: #ff8000;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #ff9933;
            color: white;
        }
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <table width="900" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td colspan="3">
                    <table width="900" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="150" align="center">
                                <img src="logo.png" width="150" height="150" alt="Логотип">
                            </td>
                            <td align="center">
                                <h1>Стоматологическая клиника "Жемчуг"</h1>
                                <h2>Панель администратора</h2>
                                <div style="position: absolute; top: 10px; right: 10px;">
                                    <a href="logout.php" class="logout-btn">Выйти</a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="150" valign="top" align="center" bgcolor="#ff8000" class="side-menu">
                    <a href="index.php">На главную</a>
                    <a href="admin.php">Панель админа</a>
                    <a href="admin_reviews.php" class="active">Модерация отзывов</a>
                </td>
                <td width="750" valign="top" align="left" class="content" colspan="2">
                    <h2>Управление отзывами</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($message)): ?>
                        <div class="success-message">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($reviews)): ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Услуга</th>
                                <th>Оценка</th>
                                <th>Текст</th>
                                <th>Дата</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            <?php foreach($reviews as $review): ?>
                                <tr>
                                    <td><?php echo $review['id']; ?></td>
                                    <td><?php echo htmlspecialchars($review['name']); ?></td>
                                    <td><?php echo htmlspecialchars($review['email']); ?></td>
                                    <td><?php echo htmlspecialchars($review['service']); ?></td>
                                    <td>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $review['rating']): ?>★<?php else: ?>☆<?php endif; ?>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($review['review'])); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($review['date_added'])); ?></td>
                                    <td>
                                        <?php if($review['approved']): ?>
                                            <span class="review-status status-approved">Одобрен</span>
                                        <?php else: ?>
                                            <span class="review-status status-pending">Ожидает</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!$review['approved']): ?>
                                            <a href="admin_reviews.php?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-approve">Одобрить</a>
                                        <?php endif; ?>
                                        <a href="admin_reviews.php?action=reject&id=<?php echo $review['id']; ?>" class="btn btn-reject" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?');">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <p>Отзывов пока нет.</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <hr width="900" align="center">
        <div align="center">
            &copy; 2025 Стоматологическая клиника "Жемчуг". Все права защищены.
        </div>
        <div class="footer-links" align="center">
            <a href="javascript:void(0)" onclick="window.open('privacy.php', 'privacy', 'width=800,height=600,scrollbars=yes')">Политика конфиденциальности</a>
        </div>
    </div>
</body>
</html> 