<?php
session_start();

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'product_db';
$user = 'root';
$pass = '1234'; 
$charset = 'cp1251';

// Настройки для PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Инициализация переменных
$product = null;
$properties = [];
$images = [];
$error = '';

// Проверка наличия ID товара в параметрах запроса
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error = 'Не указан идентификатор товара';
} else {
    $product_id = (int)$_GET['id'];
    
    // Попытка подключения к базе данных
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
        
        // Получаем данные о товаре
        $stmt = $pdo->prepare("SELECT * FROM product WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Товар не найден';
        } else {
            // Получаем свойства товара
            $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE product_id = ? ORDER BY property_name");
            $stmt->execute([$product_id]);
            $properties = $stmt->fetchAll();
            
            // Получаем изображения товара
            $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $images = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "Ошибка базы данных: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $product ? htmlspecialchars($product['name']) : 'Детали товара' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        h1, h2 {
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .error {
            padding: 10px;
            margin-bottom: 20px;
            background: #ffebee;
            border-left: 5px solid #f44336;
            color: #c62828;
        }
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product-main-image {
            flex: 0 0 300px;
            margin-bottom: 20px;
        }
        .product-main-image img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .product-info {
            flex: 1 1 600px;
        }
        .product-price {
            font-size: 24px;
            color: #e53935;
            margin-bottom: 15px;
        }
        .product-availability {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .available {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .unavailable {
            background: #ffebee;
            color: #c62828;
        }
        .product-description {
            margin-bottom: 20px;
        }
        .product-properties {
            margin-bottom: 20px;
        }
        .property-item {
            display: flex;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .property-name {
            flex: 0 0 150px;
            font-weight: bold;
        }
        .property-value {
            flex: 1;
        }
        .property-price {
            flex: 0 0 120px;
            text-align: right;
            color: #e53935;
        }
        .product-gallery {
            margin-top: 30px;
        }
        .gallery-title {
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery-item {
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
            cursor: pointer;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            color: white;
            background: #2196F3;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #1976D2;
        }
    </style>
    <script>
        function openImage(url) {
            window.open(url, '_blank');
        }
    </script>
<script src="fix_styles.js"></script>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
            <a href="products.php" class="btn">Вернуться к списку товаров</a>
        <?php elseif ($product): ?>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-container">
                <div class="product-main-image">
                    <?php if ($product['image']): ?>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" onclick="openImage('<?= htmlspecialchars($product['image']) ?>')">
                    <?php else: ?>
                        <div style="width:300px;height:300px;background:#eee;display:flex;align-items:center;justify-content:center;color:#999;">
                            Нет изображения
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div class="product-price">
                        <?= number_format($product['price'], 2, ',', ' ') ?> руб.
                    </div>
                    
                    <div class="product-availability <?= $product['available'] ? 'available' : 'unavailable' ?>">
                        <?= $product['available'] ? 'Есть в наличии' : 'Нет в наличии' ?>
                    </div>
                    
                    <div class="product-description">
                        <h2>Короткое описание</h2>
                        <p><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                        
                        <h2>Полное описание</h2>
                        <div><?= nl2br(htmlspecialchars($product['description'])) ?></div>
                    </div>
                    
                    <?php if (!empty($properties)): ?>
                        <div class="product-properties">
                            <h2>Свойства товара</h2>
                            <?php foreach ($properties as $property): ?>
                                <div class="property-item">
                                    <div class="property-name"><?= htmlspecialchars($property['property_name']) ?>:</div>
                                    <div class="property-value"><?= htmlspecialchars($property['property_value']) ?></div>
                                    <?php if ($property['property_price'] > 0): ?>
                                        <div class="property-price">+<?= number_format($property['property_price'], 2, ',', ' ') ?> руб.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($images)): ?>
                <div class="product-gallery">
                    <h2 class="gallery-title">Галерея изображений</h2>
                    <div class="gallery-container">
                        <?php foreach ($images as $image): ?>
                            <div class="gallery-item" onclick="openImage('<?= htmlspecialchars($image['image']) ?>')" title="<?= htmlspecialchars($image['title']) ?>">
                                <img src="<?= htmlspecialchars($image['image']) ?>" alt="<?= htmlspecialchars($image['title']) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
                <a href="products.php" class="btn">Вернуться к списку товаров</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 