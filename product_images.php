<?php
session_start();

// Параметры подключения к базе данных
$host = 'localhost';
$dbname = 'product_db';
$user = 'root';
$pass = '1234'; // Оставим пустым или укажите свой пароль, если он есть
$charset = 'cp1251';

// Настройки для PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Инициализация переменных
$images = [];
$products = [];
$message = '';
$error = '';
$product_id_filter = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Попытка подключения к базе данных
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Получаем список всех товаров для выпадающего списка
    $stmt = $pdo->query("SELECT id, name FROM product ORDER BY name");
    $products = $stmt->fetchAll();
    
    // Обработка удаления изображения
    if (isset($_GET['delete']) && $_GET['delete'] > 0) {
        $id = (int)$_GET['delete'];
        
        // Получаем данные удаляемого изображения для удаления файла
        $stmt = $pdo->prepare("SELECT image FROM product_images WHERE id = ?");
        $stmt->execute([$id]);
        $image_data = $stmt->fetch();
        
        // Удаляем изображение из БД
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$id]);
        
        // Если изображение существует на сервере, удаляем его
        if ($image_data && !empty($image_data['image']) && file_exists($image_data['image'])) {
            unlink($image_data['image']);
        }
        
        $message = "Изображение успешно удалено";
    }
    
    // Обработка формы добавления изображения
    if (isset($_POST['submit'])) {
        $product_id = (int)$_POST['product_id'];
        $title = $_POST['title'];
        
        // Обработка загрузки изображения
        if (isset($_FILES['image']) && $_FILES['image']['name']) {
            $image_name = time() . '_' . $_FILES['image']['name'];
            $upload_dir = 'uploads/';
            
            // Создаем директорию, если не существует
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // Изображение успешно загружено, добавляем в БД
                $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image, title) VALUES (?, ?, ?)");
                $stmt->execute([$product_id, $image_path, $title]);
                $message = "Изображение успешно добавлено";
                
                // Обновляем фильтр по товару
                $product_id_filter = $product_id;
            } else {
                $error = "Ошибка при загрузке изображения";
            }
        } else {
            $error = "Выберите файл изображения";
        }
    }
    
    // Определяем условие фильтрации
    $where_clause = $product_id_filter > 0 ? "WHERE product_id = $product_id_filter" : "";
    
    // Получаем список изображений товаров
    $stmt = $pdo->query("SELECT pi.*, p.name as product_name 
                        FROM product_images pi
                        LEFT JOIN product p ON pi.product_id = p.id
                        $where_clause
                        ORDER BY pi.product_id, pi.id");
    $images = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление изображениями товаров</title>
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
        .message {
            padding: 10px;
            margin-bottom: 20px;
            background: #e8f5e9;
            border-left: 5px solid #4caf50;
            color: #2e7d32;
        }
        .error {
            padding: 10px;
            margin-bottom: 20px;
            background: #ffebee;
            border-left: 5px solid #f44336;
            color: #c62828;
        }
        form {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"],
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover,
        button:hover {
            background: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .thumbnail {
            max-width: 100px;
            max-height: 100px;
            cursor: pointer;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 5px 10px;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
        }
        .btn-delete {
            background: #F44336;
        }
        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .filter-form .form-group {
            margin-bottom: 0;
            flex-grow: 1;
        }
        .filter-form button {
            height: 38px;
        }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            grid-gap: 15px;
            margin-top: 20px;
        }
        .image-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            position: relative;
        }
        .image-item img {
            max-width: 100%;
            height: auto;
            cursor: pointer;
        }
        .image-title {
            margin: 8px 0;
            font-weight: bold;
        }
        .image-product {
            font-size: 12px;
            color: #666;
        }
        .delete-button {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(244, 67, 54, 0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 14px;
            line-height: 24px;
            padding: 0;
            cursor: pointer;
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
        <h1>Управление изображениями товаров</h1>
        
        <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Форма фильтрации по товару -->
        <form class="filter-form" method="get">
            <div class="form-group">
                <label for="product_id">Фильтр по товару</label>
                <select name="product_id" id="product_id">
                    <option value="0">Все товары</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= $product_id_filter == $product['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Применить фильтр</button>
        </form>
        
        <h2>Добавление нового изображения</h2>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image_product_id">Товар</label>
                <select name="product_id" id="image_product_id" required>
                    <option value="">Выберите товар</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= $product_id_filter == $product['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="title">Название изображения</label>
                <input type="text" name="title" id="title" required>
            </div>
            
            <div class="form-group">
                <label for="image">Изображение</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            
            <div class="form-group">
                <input type="submit" name="submit" value="Добавить изображение">
            </div>
        </form>
        
        <h2>Галерея изображений</h2>
        
        <?php if (empty($images)): ?>
            <p>Нет доступных изображений</p>
        <?php else: ?>
            <div class="image-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="image-item">
                        <img src="<?= htmlspecialchars($image['image']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" onclick="openImage('<?= htmlspecialchars($image['image']) ?>')">
                        <p class="image-title"><?= htmlspecialchars($image['title']) ?></p>
                        <p class="image-product">Товар: <?= htmlspecialchars($image['product_name']) ?></p>
                        <a href="?delete=<?= $image['id'] ?><?= $product_id_filter > 0 ? '&product_id=' . $product_id_filter : '' ?>" 
                           onclick="return confirm('Вы уверены, что хотите удалить это изображение?')" 
                           class="btn btn-delete delete-button" 
                           title="Удалить">×</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="products.php" style="text-decoration: none; color: #2196F3;">Управление товарами</a> |
            <a href="product_properties.php" style="text-decoration: none; color: #2196F3;">Управление свойствами</a>
        </div>
    </div>
</body>
</html> 