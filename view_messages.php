<?php
session_start();

// Проверка авторизации (в реальном проекте можно добавить проверку на права администратора)
if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: login.php');
    exit;
}

// Получаем ID сообщения для изменения статуса, если оно было передано
$messageId = isset($_GET['mark_read']) ? (int)$_GET['mark_read'] : 0;
$markReplied = isset($_GET['mark_replied']) ? (int)$_GET['mark_replied'] : 0;

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
    
    // Если есть ID для изменения статуса на "прочитано"
    if ($messageId > 0) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
        $stmt->execute(['id' => $messageId]);
        
        // Перенаправляем, чтобы избежать повторного обновления при обновлении страницы
        header('Location: view_messages.php');
        exit;
    }
    
    // Если есть ID для изменения статуса на "отвечено"
    if ($markReplied > 0) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = :id");
        $stmt->execute(['id' => $markReplied]);
        
        // Перенаправляем, чтобы избежать повторного обновления при обновлении страницы
        header('Location: view_messages.php');
        exit;
    }
    
    // Получаем все сообщения, отсортированные по дате (новые в начале)
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Ошибка базы данных: ' . $e->getMessage();
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр сообщений - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <!-- Добавляем уникальный идентификатор для стилей сообщений чтобы избежать конфликтов -->
    <style>
        /* Контейнер сообщений */
        #messages-container .message-item {
            margin-bottom: 20px !important;
            padding: 15px !important;
            border-radius: 5px !important;
            background: #f9f9f9 !important;
            border: 1px solid #e0e0e0 !important;
            display: block !important;
            width: auto !important;
            box-sizing: border-box !important;
            position: relative !important;
            overflow: visible !important;
        }
        
        #messages-container .message-item.new {
            border-left: 5px solid #ff8000 !important;
            background: #fff8e1 !important;
        }
        
        #messages-container .message-item.read {
            border-left: 5px solid #0099cc !important;
        }
        
        #messages-container .message-item.replied {
            border-left: 5px solid #4CAF50 !important;
        }
        
        #messages-container .message-header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 10px !important;
            padding-bottom: 10px !important;
            border-bottom: 1px solid #eee !important;
        }
        
        #messages-container .message-subject {
            font-weight: bold !important;
            font-size: 18px !important;
            color: #333 !important;
            margin: 0 !important;
            padding: 0 !important;
            text-align: left !important;
        }
        
        #messages-container .message-meta {
            font-size: 14px !important;
            color: #666 !important;
            margin: 10px 0 !important;
            padding: 0 !important;
            text-align: left !important;
            line-height: 1.5 !important;
        }
        
        #messages-container .message-actions {
            margin-top: 10px !important;
            text-align: right !important;
            display: block !important;
            clear: both !important;
        }
        
        #messages-container .message-actions a {
            display: inline-block !important;
            margin-left: 10px !important;
            padding: 5px 10px !important;
            background: #f0f0f0 !important;
            border-radius: 3px !important;
            color: #333 !important;
            text-decoration: none !important;
            font-size: 14px !important;
            border: 1px solid #ddd !important;
            cursor: pointer !important;
        }
        
        #messages-container .message-actions a:hover {
            background: #e0e0e0 !important;
            color: #000 !important;
        }
        
        #messages-container .status-badge {
            display: inline-block !important;
            padding: 3px 8px !important;
            border-radius: 3px !important;
            font-size: 12px !important;
            font-weight: bold !important;
            color: white !important;
            margin-left: 10px !important;
            text-align: center !important;
        }
        
        #messages-container .status-new {
            background: #ff8000 !important;
        }
        
        #messages-container .status-read {
            background: #0099cc !important;
        }
        
        #messages-container .status-replied {
            background: #4CAF50 !important;
        }
        
        #messages-container .empty-state {
            text-align: center !important;
            padding: 40px 20px !important;
            color: #666 !important;
            font-size: 16px !important;
            margin: 20px auto !important;
            background: #f9f9f9 !important;
            border-radius: 5px !important;
            border: 1px dashed #ccc !important;
        }
        
        #messages-container .back-link {
            display: block !important;
            margin: 20px 0 !important;
            text-align: center !important;
            padding: 10px !important;
            color: #0099cc !important;
            text-decoration: underline !important;
            font-weight: bold !important;
        }
        
        #messages-container .message-content {
            margin: 15px 0 !important; 
            white-space: pre-line !important;
            background-color: #fff !important;
            padding: 10px !important;
            border: 1px solid #eee !important;
            border-radius: 4px !important;
            color: #333 !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
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
                        <a href="logout.php" class="logout-btn">Выйти</a>
                    </div>
                <?php endif; ?>
            </div>
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
            <a href="view_messages.php" class="active">Сообщения</a>
        </td>
        <td valign="top">
            <h2 align="center">Сообщения от пользователей</h2>
            
            <!-- Обертка с уникальным ID для стилизации -->
            <div id="messages-container">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <p>Пока нет сообщений от пользователей.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                    <div class="message-item <?php echo $message['status']; ?>">
                        <div class="message-header">
                            <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                            <span class="status-badge status-<?php echo $message['status']; ?>">
                                <?php 
                                switch($message['status']) {
                                    case 'new':
                                        echo 'Новое';
                                        break;
                                    case 'read':
                                        echo 'Прочитано';
                                        break;
                                    case 'replied':
                                        echo 'Отвечено';
                                        break;
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="message-meta">
                            <strong>От:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)<br>
                            <strong>Дата:</strong> <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                        </div>
                        
                        <div class="message-content">
                            <?php echo htmlspecialchars($message['message']); ?>
                        </div>
                        
                        <div class="message-actions">
                            <?php if ($message['status'] === 'new'): ?>
                            <a href="?mark_read=<?php echo $message['id']; ?>">Отметить как прочитанное</a>
                            <?php endif; ?>
                            
                            <?php if ($message['status'] !== 'replied'): ?>
                            <a href="?mark_replied=<?php echo $message['id']; ?>">Отметить как отвеченное</a>
                            <?php endif; ?>
                            
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>">Ответить по email</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <a href="index.php" class="back-link">Вернуться на главную</a>
            </div>
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