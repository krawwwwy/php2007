<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получение ID услуги
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['error'] = 'Услуга не найдена';
    header('Location: services.php');
    exit;
}

try {
    // Получаем данные об услуге
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        $_SESSION['error'] = 'Услуга не найдена';
        header('Location: services.php');
        exit;
    }
    
    // Получаем свойства услуги
    $stmt = $pdo->prepare("SELECT * FROM service_properties WHERE service_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $properties = $stmt->fetchAll();
    
    // Получаем изображения услуги
    $stmt = $pdo->prepare("SELECT * FROM service_images WHERE service_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Ошибка загрузки данных: ' . $e->getMessage();
    header('Location: services.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($service['name']) ?> - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .service-container {
            padding: 20px;
        }
        
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        
        .service-name {
            font-size: 24px;
            margin: 0;
            color: #0099cc;
        }
        
        .service-price {
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }
        
        .service-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-unavailable {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .service-actions {
            margin-top: 10px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #2196F3;
            color: white;
        }
        
        .btn-danger {
            background-color: #f44336;
            color: white;
        }
        
        .service-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        
        .gallery-image {
            max-width: 150px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 3px;
            cursor: pointer;
        }
        
        .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 18px;
            color: #0099cc;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .property-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .property-table th, .property-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .property-table th {
            background-color: #f2f2f2;
        }
        
        .property-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .property-table .price-column {
            text-align: right;
        }
        
        .description {
            line-height: 1.6;
            color: #444;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin: 20px 0;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            background-color: #f8f8f8;
            margin-right: 5px;
        }
        
        .tab.active {
            background-color: white;
            border-color: #ddd;
            border-bottom-color: white;
        }
        
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        
        .tab-content.active {
            display: block;
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
            <a href="view_messages.php">Сообщения</a>
            <a href="services.php" class="active">Услуги</a>
        </td>
        <td valign="top">
            <div class="service-container">
                <div class="service-header">
                    <div>
                        <h1 class="service-name"><?= htmlspecialchars($service['name']) ?></h1>
                        <div class="service-category">Категория: <?= $service['category_id'] ?></div>
                    </div>
                    <div>
                        <div class="service-price"><?= number_format($service['price'], 2, ',', ' ') ?> ₽</div>
                        <span class="service-status <?= $service['available'] ? 'status-available' : 'status-unavailable' ?>">
                            <?= $service['available'] ? 'Доступна для записи' : 'Недоступна' ?>
                        </span>
                    </div>
                </div>
                
                <div class="service-actions">
                    <a href="service_edit.php?id=<?= $service['id'] ?>" class="btn btn-secondary">Редактировать</a>
                    <a href="services.php" class="btn btn-primary">Назад к списку</a>
                    <a href="javascript:void(0)" onclick="confirmDelete(<?= $service['id'] ?>)" class="btn btn-danger">Удалить</a>
                </div>
                
                <div class="tabs">
                    <div class="tab active" data-tab="details">Детали услуги</div>
                    <div class="tab" data-tab="properties">Свойства</div>
                    <div class="tab" data-tab="images">Изображения</div>
                    <div class="tab" data-tab="seo">SEO информация</div>
                </div>
                
                <!-- Вкладка с деталями -->
                <div class="tab-content active" id="details-tab">
                    <?php if (!empty($service['image'])): ?>
                        <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-image">
                    <?php endif; ?>
                    
                    <h3 class="section-title">Краткое описание</h3>
                    <div class="description">
                        <?= !empty($service['short_description']) ? nl2br(htmlspecialchars($service['short_description'])) : '<em>Нет краткого описания</em>' ?>
                    </div>
                    
                    <h3 class="section-title">Полное описание</h3>
                    <div class="description">
                        <?= !empty($service['description']) ? nl2br(htmlspecialchars($service['description'])) : '<em>Нет подробного описания</em>' ?>
                    </div>
                </div>
                
                <!-- Вкладка со свойствами -->
                <div class="tab-content" id="properties-tab">
                    <?php if (!empty($properties)): ?>
                        <table class="property-table">
                            <thead>
                                <tr>
                                    <th>Свойство</th>
                                    <th>Значение</th>
                                    <th class="price-column">Дополнительная стоимость</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($property['property_name']) ?></td>
                                        <td><?= htmlspecialchars($property['property_value']) ?></td>
                                        <td class="price-column">
                                            <?php if ($property['property_price'] > 0): ?>
                                                + <?= number_format($property['property_price'], 2, ',', ' ') ?> ₽
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>У этой услуги нет дополнительных свойств.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Вкладка с изображениями -->
                <div class="tab-content" id="images-tab">
                    <?php if (!empty($images)): ?>
                        <div class="image-gallery">
                            <?php foreach ($images as $image): ?>
                                <div class="gallery-item">
                                    <img src="<?= htmlspecialchars($image['image']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" class="gallery-image">
                                    <?php if (!empty($image['title'])): ?>
                                        <p><?= htmlspecialchars($image['title']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>У этой услуги нет дополнительных изображений.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Вкладка с SEO информацией -->
                <div class="tab-content" id="seo-tab">
                    <h3 class="section-title">Meta Title</h3>
                    <div class="description">
                        <?= !empty($service['meta_title']) ? htmlspecialchars($service['meta_title']) : '<em>Не указан</em>' ?>
                    </div>
                    
                    <h3 class="section-title">Meta Description</h3>
                    <div class="description">
                        <?= !empty($service['meta_description']) ? htmlspecialchars($service['meta_description']) : '<em>Не указан</em>' ?>
                    </div>
                    
                    <h3 class="section-title">Meta Keywords</h3>
                    <div class="description">
                        <?= !empty($service['meta_keywords']) ? htmlspecialchars($service['meta_keywords']) : '<em>Не указаны</em>' ?>
                    </div>
                    
                    <h3 class="section-title">URL алиас</h3>
                    <div class="description">
                        <?= !empty($service['alias']) ? htmlspecialchars($service['alias']) : '<em>Не указан</em>' ?>
                    </div>
                </div>
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

<script>
// Функционал вкладок
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Делаем все вкладки неактивными
            tabs.forEach(t => t.classList.remove('active'));
            
            // Скрываем все содержимое вкладок
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Активируем выбранную вкладку
            this.classList.add('active');
            
            // Показываем содержимое выбранной вкладки
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
});

// Функция подтверждения удаления
function confirmDelete(id) {
    if (confirm('Вы уверены, что хотите удалить эту услугу? Это действие нельзя отменить.')) {
        window.location.href = 'services.php?delete=' + id;
    }
}
</script>
</body>
</html> 