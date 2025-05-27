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
$products = [];
$message = '';
$error = '';
$edit_id = null;
$edit_product = null;

// Попытка подключения к базе данных
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
    
    // Обработка удаления товара
    if (isset($_GET['delete']) && $_GET['delete'] > 0) {
        $id = (int)$_GET['delete'];
        
        // Удаляем связанные свойства
        $stmt = $pdo->prepare("DELETE FROM product_properties WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // Удаляем связанные изображения
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        
        // Удаляем сам товар
        $stmt = $pdo->prepare("DELETE FROM product WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = "Товар успешно удален";
    }

    // Обработка редактирования товара
    if (isset($_GET['edit']) && $_GET['edit'] > 0) {
        $edit_id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM product WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_product = $stmt->fetch();
    }
    
    // Обработка формы добавления/редактирования товара
    if (isset($_POST['submit'])) {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $manufacturer_id = (int)$_POST['manufacturer_id'];
        $name = $_POST['name'];
        $alias = $_POST['alias'] ?: transliterate($name);
        $short_description = $_POST['short_description'];
        $description = $_POST['description'];
        $price = (float)$_POST['price'];
        $available = isset($_POST['available']) ? 1 : 0;
        $meta_keywords = $_POST['meta_keywords'];
        $meta_description = $_POST['meta_description'];
        $meta_title = $_POST['meta_title'];
        
        // Обработка загрузки изображения
        $image = '';
        if ($_FILES['image']['name']) {
            $image_name = time() . '_' . $_FILES['image']['name'];
            $upload_dir = 'uploads/';
            
            // Создаем директорию, если не существует
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
                // Изображение успешно загружено
            } else {
                $error = "Ошибка при загрузке изображения";
                $image = '';
            }
        } elseif (isset($_POST['old_image']) && $_POST['old_image']) {
            $image = $_POST['old_image'];
        }
        
        if ($id > 0) {
            // Обновляем существующий товар
            $sql = "UPDATE product SET 
                    manufacturer_id = :manufacturer_id,
                    name = :name,
                    alias = :alias,
                    short_description = :short_description,
                    description = :description,
                    price = :price,
                    available = :available,
                    meta_keywords = :meta_keywords,
                    meta_description = :meta_description,
                    meta_title = :meta_title";
            
            $params = [
                ':manufacturer_id' => $manufacturer_id,
                ':name' => $name,
                ':alias' => $alias,
                ':short_description' => $short_description,
                ':description' => $description,
                ':price' => $price,
                ':available' => $available,
                ':meta_keywords' => $meta_keywords,
                ':meta_description' => $meta_description,
                ':meta_title' => $meta_title,
            ];
            
            if ($image) {
                $sql .= ", image = :image";
                $params[':image'] = $image;
            }
            
            $sql .= " WHERE id = :id";
            $params[':id'] = $id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "Товар успешно обновлен";
        } else {
            // Добавляем новый товар
            $sql = "INSERT INTO product (
                    manufacturer_id, name, alias, short_description, 
                    description, price, image, available, 
                    meta_keywords, meta_description, meta_title
                ) VALUES (
                    :manufacturer_id, :name, :alias, :short_description,
                    :description, :price, :image, :available,
                    :meta_keywords, :meta_description, :meta_title
                )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':manufacturer_id' => $manufacturer_id,
                ':name' => $name,
                ':alias' => $alias,
                ':short_description' => $short_description,
                ':description' => $description,
                ':price' => $price,
                ':image' => $image,
                ':available' => $available,
                ':meta_keywords' => $meta_keywords,
                ':meta_description' => $meta_description,
                ':meta_title' => $meta_title
            ]);
            
            $message = "Товар успешно добавлен";
        }
        
        // Сбрасываем редактирование
        $edit_id = null;
        $edit_product = null;
    }
    
    // Определяем порядок сортировки
    $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'id';
    $order_dir = isset($_GET['order_dir']) && $_GET['order_dir'] == 'desc' ? 'DESC' : 'ASC';
    
    // Проверяем корректность поля сортировки
    $allowed_fields = ['id', 'name', 'price', 'available'];
    if (!in_array($order_by, $allowed_fields)) {
        $order_by = 'id';
    }
    
    // Получаем список всех товаров с сортировкой
    $stmt = $pdo->prepare("SELECT * FROM product ORDER BY $order_by $order_dir");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

// Функция для транслитерации русского текста в латиницу
function transliterate($string) {
    $converter = [
        'а' => 'a',   'б' => 'b',   'в' => 'v',   'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',   'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',   'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',   'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch', 'ь' => '',    'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',  ' ' => '-',   '.' => '-',   ',' => '-'
    ];
    
    $result = mb_strtolower($string, 'UTF-8');
    $result = strtr($result, $converter);
    $result = preg_replace('/[^-a-z0-9]/', '', $result);
    $result = preg_replace('/-+/', '-', $result);
    $result = trim($result, '-');
    
    return $result;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление товарами</title>
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
        th a {
            color: #333;
            text-decoration: none;
        }
        th a:hover {
            text-decoration: underline;
        }
        .thumbnail {
            max-width: 100px;
            max-height: 100px;
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
            width: 80px;
            display: inline-block;
            text-align: center;
            margin: 2px;
            box-sizing: border-box;
        }
        .btn-edit {
            background: #2196F3;
        }
        .btn-delete {
            background: #F44336;
        }
        .btn-details {
            background: #9C27B0;
        }
    </style>
<script src="fix_styles.js"></script>
</head>
<body>
    <div class="container">
        <h1>Управление товарами</h1>
        
        <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <h2><?= $edit_product ? 'Редактирование товара' : 'Добавление нового товара' ?></h2>
        
        <form method="post" enctype="multipart/form-data">
            <?php if ($edit_product): ?>
                <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                <input type="hidden" name="old_image" value="<?= $edit_product['image'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="manufacturer_id">ID производителя</label>
                <input type="number" name="manufacturer_id" id="manufacturer_id" value="<?= $edit_product ? $edit_product['manufacturer_id'] : '1' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="name">Название товара</label>
                <input type="text" name="name" id="name" value="<?= $edit_product ? $edit_product['name'] : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="alias">Алиас (оставьте пустым для автогенерации)</label>
                <input type="text" name="alias" id="alias" value="<?= $edit_product ? $edit_product['alias'] : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="short_description">Короткое описание</label>
                <textarea name="short_description" id="short_description" rows="3" required><?= $edit_product ? $edit_product['short_description'] : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="description">Полное описание</label>
                <textarea name="description" id="description" rows="5" required><?= $edit_product ? $edit_product['description'] : '' ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена</label>
                <input type="number" name="price" id="price" step="0.01" value="<?= $edit_product ? $edit_product['price'] : '0.00' ?>" required>
            </div>
            
            <div class="form-group">
                <label for="image">Изображение</label>
                <?php if ($edit_product && $edit_product['image']): ?>
                    <div>
                        <img src="<?= htmlspecialchars($edit_product['image']) ?>" alt="Текущее изображение" class="thumbnail"><br>
                        <small>Текущее изображение</small>
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="image" accept="image/*" <?= $edit_product ? '' : 'required' ?>>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="available" <?= $edit_product && $edit_product['available'] ? 'checked' : '' ?>>
                    Доступен на складе
                </label>
            </div>
            
            <div class="form-group">
                <label for="meta_keywords">Meta Keywords</label>
                <input type="text" name="meta_keywords" id="meta_keywords" value="<?= $edit_product ? $edit_product['meta_keywords'] : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="meta_description">Meta Description</label>
                <input type="text" name="meta_description" id="meta_description" value="<?= $edit_product ? $edit_product['meta_description'] : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="meta_title">Meta Title</label>
                <input type="text" name="meta_title" id="meta_title" value="<?= $edit_product ? $edit_product['meta_title'] : '' ?>">
            </div>
            
            <div class="form-group">
                <input type="submit" name="submit" value="<?= $edit_product ? 'Обновить товар' : 'Добавить товар' ?>">
                <?php if ($edit_product): ?>
                    <a href="products.php" style="margin-left: 10px; text-decoration: none;">Отмена</a>
                <?php endif; ?>
            </div>
        </form>
        
        <h2>Список товаров</h2>
        
        <table>
            <thead>
                <tr>
                    <th><a href="?order_by=id&order_dir=<?= $order_by == 'id' && $order_dir == 'ASC' ? 'desc' : 'asc' ?>">ID</a></th>
                    <th><a href="?order_by=name&order_dir=<?= $order_by == 'name' && $order_dir == 'ASC' ? 'desc' : 'asc' ?>">Название</a></th>
                    <th>Изображение</th>
                    <th><a href="?order_by=price&order_dir=<?= $order_by == 'price' && $order_dir == 'ASC' ? 'desc' : 'asc' ?>">Цена</a></th>
                    <th><a href="?order_by=available&order_dir=<?= $order_by == 'available' && $order_dir == 'ASC' ? 'desc' : 'asc' ?>">Доступность</a></th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" align="center">Нет товаров</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td>
                                <?php if ($product['image']): ?>
                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="thumbnail">
                                <?php else: ?>
                                    Нет изображения
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($product['price'], 2, '.', ' ') ?> руб.</td>
                            <td><?= $product['available'] ? 'Да' : 'Нет' ?></td>
                            <td class="actions">
                                <a href="?edit=<?= $product['id'] ?>" class="btn btn-edit">Редактировать</a>
                                <a href="?delete=<?= $product['id'] ?>" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</a>
                                <a href="product_details.php?id=<?= $product['id'] ?>" class="btn btn-details">Подробнее</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="product_db_setup.php" style="text-decoration: none; color: #2196F3;">Настройка базы данных</a> |
            <a href="product_properties.php" style="text-decoration: none; color: #2196F3;">Управление свойствами</a> |
            <a href="product_images.php" style="text-decoration: none; color: #2196F3;">Управление изображениями</a>
        </div>
    </div>
</body>
</html> 