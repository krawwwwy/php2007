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
$properties = [];
$products = [];
$message = '';
$error = '';
$edit_id = null;
$edit_property = null;
$product_id_filter = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Попытка подключения к базе данных
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Получаем список всех товаров для выпадающего списка
    $stmt = $pdo->query("SELECT id, name FROM product ORDER BY name");
    $products = $stmt->fetchAll();
    
    // Обработка удаления свойства
    if (isset($_GET['delete']) && $_GET['delete'] > 0) {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM product_properties WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Свойство успешно удалено";
    }

    // Обработка редактирования свойства
    if (isset($_GET['edit']) && $_GET['edit'] > 0) {
        $edit_id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM product_properties WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_property = $stmt->fetch();
    }
    
    // Обработка формы добавления/редактирования свойства
    if (isset($_POST['submit'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $product_id = (int)$_POST['product_id'];
        $property_name = $_POST['property_name'];
        $property_value = $_POST['property_value'];
        $property_price = (float)$_POST['property_price'];
        
        if ($id > 0) {
            // Обновляем существующее свойство
            $stmt = $pdo->prepare("UPDATE product_properties SET 
                product_id = ?, 
                property_name = ?, 
                property_value = ?, 
                property_price = ? 
                WHERE id = ?");
            $stmt->execute([$product_id, $property_name, $property_value, $property_price, $id]);
            $message = "Свойство успешно обновлено";
        } else {
            // Добавляем новое свойство
            $stmt = $pdo->prepare("INSERT INTO product_properties 
                (product_id, property_name, property_value, property_price) 
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $property_name, $property_value, $property_price]);
            $message = "Свойство успешно добавлено";
        }
        
        // Сбрасываем редактирование и обновляем фильтр по товару
        $edit_id = null;
        $edit_property = null;
        $product_id_filter = $product_id;
    }
    
    // Определяем условие фильтрации
    $where_clause = $product_id_filter > 0 ? "WHERE product_id = $product_id_filter" : "";
    
    // Получаем список свойств товаров с сортировкой
    $stmt = $pdo->query("SELECT pp.*, p.name as product_name 
                        FROM product_properties pp
                        LEFT JOIN product p ON pp.product_id = p.id
                        $where_clause
                        ORDER BY pp.product_id, pp.property_name");
    $properties = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление свойствами товаров</title>
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
        input[type="number"],
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
        .btn-edit {
            background: #2196F3;
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
    </style>
<script src="fix_styles.js"></script>
</head>
<body>
    <div class="container">
        <h1>Управление свойствами товаров</h1>
        
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
        
        <h2><?= $edit_property ? 'Редактирование свойства' : 'Добавление нового свойства' ?></h2>
        
        <form method="post">
            <?php if ($edit_property): ?>
                <input type="hidden" name="id" value="<?= $edit_property['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="property_product_id">Товар</label>
                <select name="product_id" id="property_product_id" required>
                    <option value="">Выберите товар</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= $product['id'] ?>" <?= $edit_property && $edit_property['product_id'] == $product['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($product['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="property_name">Название свойства</label>
                <input type="text" name="property_name" id="property_name" value="<?= $edit_property ? htmlspecialchars($edit_property['property_name']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="property_value">Значение свойства</label>
                <input type="text" name="property_value" id="property_value" value="<?= $edit_property ? htmlspecialchars($edit_property['property_value']) : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="property_price">Цена свойства</label>
                <input type="number" name="property_price" id="property_price" step="0.01" value="<?= $edit_property ? $edit_property['property_price'] : '0.00' ?>" required>
            </div>
            
            <div class="form-group">
                <input type="submit" name="submit" value="<?= $edit_property ? 'Обновить свойство' : 'Добавить свойство' ?>">
                <?php if ($edit_property): ?>
                    <a href="product_properties.php<?= $product_id_filter > 0 ? '?product_id=' . $product_id_filter : '' ?>" style="margin-left: 10px; text-decoration: none;">Отмена</a>
                <?php endif; ?>
            </div>
        </form>
        
        <h2>Список свойств</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Товар</th>
                    <th>Название свойства</th>
                    <th>Значение свойства</th>
                    <th>Цена</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr>
                        <td colspan="6" align="center">Нет свойств</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($properties as $property): ?>
                        <tr>
                            <td><?= $property['id'] ?></td>
                            <td><?= htmlspecialchars($property['product_name']) ?></td>
                            <td><?= htmlspecialchars($property['property_name']) ?></td>
                            <td><?= htmlspecialchars($property['property_value']) ?></td>
                            <td><?= number_format($property['property_price'], 2, '.', ' ') ?> руб.</td>
                            <td class="actions">
                                <a href="?edit=<?= $property['id'] ?><?= $product_id_filter > 0 ? '&product_id=' . $product_id_filter : '' ?>" class="btn btn-edit">Редактировать</a>
                                <a href="?delete=<?= $property['id'] ?><?= $product_id_filter > 0 ? '&product_id=' . $product_id_filter : '' ?>" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить это свойство?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="products.php" style="text-decoration: none; color: #2196F3;">Управление товарами</a> |
            <a href="product_images.php" style="text-decoration: none; color: #2196F3;">Управление изображениями</a>
        </div>
    </div>
</body>
</html> 