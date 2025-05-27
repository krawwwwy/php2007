<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Инициализация переменных
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$title = $isEdit ? 'Редактирование услуги' : 'Добавление новой услуги';
$service = [
    'id' => '',
    'category_id' => '',
    'name' => '',
    'alias' => '',
    'short_description' => '',
    'description' => '',
    'price' => '',
    'image' => '',
    'available' => 1,
    'meta_keywords' => '',
    'meta_description' => '',
    'meta_title' => ''
];
$properties = [];
$images = [];

// Если это редактирование - загружаем данные услуги
if ($isEdit) {
    try {
        // Получаем данные об услуге
        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $serviceData = $stmt->fetch();
        
        if (!$serviceData) {
            $_SESSION['error'] = 'Услуга не найдена';
            header('Location: services.php');
            exit;
        }
        
        $service = $serviceData;
        
        // Получаем свойства услуги
        $stmt = $pdo->prepare("SELECT * FROM service_properties WHERE service_id = ?");
        $stmt->execute([$id]);
        $properties = $stmt->fetchAll();
        
        // Получаем изображения услуги
        $stmt = $pdo->prepare("SELECT * FROM service_images WHERE service_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка загрузки данных: ' . $e->getMessage();
        header('Location: services.php');
        exit;
    }
}

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $service = [
        'id' => $id,
        'category_id' => $_POST['category_id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'alias' => $_POST['alias'] ?? '',
        'short_description' => $_POST['short_description'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? '',
        'image' => $_POST['image'] ?? '',
        'available' => isset($_POST['available']) ? 1 : 0,
        'meta_keywords' => $_POST['meta_keywords'] ?? '',
        'meta_description' => $_POST['meta_description'] ?? '',
        'meta_title' => $_POST['meta_title'] ?? ''
    ];
    
    // Валидация
    $errors = [];
    
    if (empty($service['name'])) {
        $errors[] = 'Название услуги обязательно для заполнения';
    }
    
    if (empty($service['category_id']) || !is_numeric($service['category_id'])) {
        $errors[] = 'Выберите категорию услуги';
    }
    
    if (empty($service['price']) || !is_numeric(str_replace(',', '.', $service['price']))) {
        $errors[] = 'Укажите корректную цену услуги';
    }
    
    // Если нет ошибок - сохраняем данные
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Преобразуем цену из запятой в точку для сохранения в БД
            $service['price'] = str_replace(',', '.', $service['price']);
            
            if ($isEdit) {
                // Обновление существующей услуги
                $sql = "UPDATE services SET 
                    category_id = :category_id,
                    name = :name,
                    alias = :alias,
                    short_description = :short_description,
                    description = :description,
                    price = :price,
                    image = :image,
                    available = :available,
                    meta_keywords = :meta_keywords,
                    meta_description = :meta_description,
                    meta_title = :meta_title
                WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($service);
                
                $message = 'Услуга успешно обновлена';
            } else {
                // Добавление новой услуги
                $sql = "INSERT INTO services (
                    category_id, name, alias, short_description, description, 
                    price, image, available, meta_keywords, meta_description, meta_title
                ) VALUES (
                    :category_id, :name, :alias, :short_description, :description, 
                    :price, :image, :available, :meta_keywords, :meta_description, :meta_title
                )";
                
                $stmt = $pdo->prepare($sql);
                unset($service['id']); // ID генерируется автоматически
                $stmt->execute($service);
                
                $id = $pdo->lastInsertId();
                $message = 'Новая услуга успешно добавлена';
            }
            
            // Обработка свойств услуги
            if (isset($_POST['properties']) && is_array($_POST['properties'])) {
                // Удаляем старые свойства, если это редактирование
                if ($isEdit) {
                    $pdo->exec("DELETE FROM service_properties WHERE service_id = $id");
                }
                
                // Добавляем новые свойства
                $propertyStmt = $pdo->prepare("
                    INSERT INTO service_properties (service_id, property_name, property_value, property_price) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($_POST['properties'] as $property) {
                    if (!empty($property['name']) && isset($property['value'])) {
                        $propertyPrice = !empty($property['price']) ? str_replace(',', '.', $property['price']) : 0;
                        $propertyStmt->execute([
                            $id,
                            $property['name'],
                            $property['value'],
                            $propertyPrice
                        ]);
                    }
                }
            }
            
            // Обработка загруженных изображений
            // В реальном проекте здесь бы был код загрузки файлов на сервер
            // Но для упрощения мы просто сохраняем путь к изображению из формы
            if (isset($_POST['images']) && is_array($_POST['images'])) {
                // Удаляем старые изображения, если это редактирование
                if ($isEdit) {
                    $pdo->exec("DELETE FROM service_images WHERE service_id = $id");
                }
                
                // Добавляем новые изображения
                $imageStmt = $pdo->prepare("
                    INSERT INTO service_images (service_id, image, title) 
                    VALUES (?, ?, ?)
                ");
                
                foreach ($_POST['images'] as $image) {
                    if (!empty($image['path'])) {
                        $imageStmt->execute([
                            $id,
                            $image['path'],
                            $image['title'] ?? ''
                        ]);
                    }
                }
            }
            
            $pdo->commit();
            
            $_SESSION['success'] = $message;
            header('Location: services.php');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Ошибка сохранения данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?> - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check input {
            margin-right: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-secondary {
            background-color: #f44336;
            color: white;
            margin-left: 10px;
        }
        
        .property-row, .image-row {
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
        }
        
        .btn-add-row {
            background-color: #2196F3;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-remove-row {
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
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
            <div class="form-container">
                <h2><?= htmlspecialchars($title) ?></h2>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <!-- Вкладки для разделения данных формы -->
                    <div class="tabs">
                        <div class="tab active" data-tab="general">Основная информация</div>
                        <div class="tab" data-tab="properties">Свойства услуги</div>
                        <div class="tab" data-tab="images">Изображения</div>
                        <div class="tab" data-tab="seo">SEO информация</div>
                    </div>
                    
                    <!-- Вкладка с основной информацией -->
                    <div class="tab-content active" id="general-tab">
                        <div class="form-group">
                            <label for="name">Название услуги*:</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($service['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Категория*:</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">-- Выберите категорию --</option>
                                <option value="1" <?= $service['category_id'] == 1 ? 'selected' : '' ?>>Терапия</option>
                                <option value="2" <?= $service['category_id'] == 2 ? 'selected' : '' ?>>Хирургия</option>
                                <option value="3" <?= $service['category_id'] == 3 ? 'selected' : '' ?>>Ортопедия</option>
                                <option value="4" <?= $service['category_id'] == 4 ? 'selected' : '' ?>>Ортодонтия</option>
                                <option value="5" <?= $service['category_id'] == 5 ? 'selected' : '' ?>>Имплантология</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="alias">Алиас (URL):</label>
                            <input type="text" id="alias" name="alias" class="form-control" value="<?= htmlspecialchars($service['alias']) ?>">
                            <small>Используется для формирования ЧПУ-ссылок. Если оставить пустым, будет сгенерирован автоматически.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Цена услуги*:</label>
                            <input type="text" id="price" name="price" class="form-control" value="<?= htmlspecialchars($service['price']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Краткое описание:</label>
                            <textarea id="short_description" name="short_description" class="form-control"><?= htmlspecialchars($service['short_description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Полное описание:</label>
                            <textarea id="description" name="description" class="form-control" rows="6"><?= htmlspecialchars($service['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Основное изображение (URL):</label>
                            <input type="text" id="image" name="image" class="form-control" value="<?= htmlspecialchars($service['image']) ?>">
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="available" name="available" <?= $service['available'] ? 'checked' : '' ?>>
                            <label for="available">Доступна для записи</label>
                        </div>
                    </div>
                    
                    <!-- Вкладка со свойствами услуги -->
                    <div class="tab-content" id="properties-tab">
                        <h3>Свойства услуги</h3>
                        <p>Добавьте свойства услуги, такие как продолжительность, материалы и т.д.</p>
                        
                        <div id="properties-container">
                            <?php if (empty($properties)): ?>
                                <div class="property-row">
                                    <input type="text" name="properties[0][name]" placeholder="Название свойства" class="form-control" style="flex: 1;">
                                    <input type="text" name="properties[0][value]" placeholder="Значение" class="form-control" style="flex: 1;">
                                    <input type="text" name="properties[0][price]" placeholder="Цена" class="form-control" style="width: 100px;">
                                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($properties as $index => $property): ?>
                                <div class="property-row">
                                    <input type="text" name="properties[<?= $index ?>][name]" placeholder="Название свойства" class="form-control" style="flex: 1;" value="<?= htmlspecialchars($property['property_name']) ?>">
                                    <input type="text" name="properties[<?= $index ?>][value]" placeholder="Значение" class="form-control" style="flex: 1;" value="<?= htmlspecialchars($property['property_value']) ?>">
                                    <input type="text" name="properties[<?= $index ?>][price]" placeholder="Цена" class="form-control" style="width: 100px;" value="<?= htmlspecialchars($property['property_price']) ?>">
                                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn-add-row" onclick="addPropertyRow()">Добавить свойство</button>
                    </div>
                    
                    <!-- Вкладка с изображениями -->
                    <div class="tab-content" id="images-tab">
                        <h3>Дополнительные изображения</h3>
                        <p>Добавьте дополнительные изображения услуги</p>
                        
                        <div id="images-container">
                            <?php if (empty($images)): ?>
                                <div class="image-row">
                                    <input type="text" name="images[0][path]" placeholder="URL изображения" class="form-control" style="flex: 2;">
                                    <input type="text" name="images[0][title]" placeholder="Название изображения" class="form-control" style="flex: 1;">
                                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($images as $index => $image): ?>
                                <div class="image-row">
                                    <input type="text" name="images[<?= $index ?>][path]" placeholder="URL изображения" class="form-control" style="flex: 2;" value="<?= htmlspecialchars($image['image']) ?>">
                                    <input type="text" name="images[<?= $index ?>][title]" placeholder="Название изображения" class="form-control" style="flex: 1;" value="<?= htmlspecialchars($image['title']) ?>">
                                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn-add-row" onclick="addImageRow()">Добавить изображение</button>
                    </div>
                    
                    <!-- Вкладка с SEO информацией -->
                    <div class="tab-content" id="seo-tab">
                        <h3>SEO информация</h3>
                        <p>Заполните мета-данные для поисковой оптимизации</p>
                        
                        <div class="form-group">
                            <label for="meta_title">Meta Title:</label>
                            <input type="text" id="meta_title" name="meta_title" class="form-control" value="<?= htmlspecialchars($service['meta_title']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_description">Meta Description:</label>
                            <textarea id="meta_description" name="meta_description" class="form-control"><?= htmlspecialchars($service['meta_description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords:</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($service['meta_keywords']) ?>">
                        </div>
                    </div>
                    
                    <!-- Кнопки формы -->
                    <div style="margin-top: 20px; text-align: center;">
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Сохранить изменения' : 'Добавить услугу' ?></button>
                        <a href="services.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
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

// Функционал динамического добавления свойств
let propertyCounter = <?= max(1, count($properties)) ?>;

function addPropertyRow() {
    const container = document.getElementById('properties-container');
    const row = document.createElement('div');
    row.className = 'property-row';
    row.innerHTML = `
        <input type="text" name="properties[${propertyCounter}][name]" placeholder="Название свойства" class="form-control" style="flex: 1;">
        <input type="text" name="properties[${propertyCounter}][value]" placeholder="Значение" class="form-control" style="flex: 1;">
        <input type="text" name="properties[${propertyCounter}][price]" placeholder="Цена" class="form-control" style="width: 100px;">
        <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
    `;
    container.appendChild(row);
    propertyCounter++;
}

// Функционал динамического добавления изображений
let imageCounter = <?= max(1, count($images)) ?>;

function addImageRow() {
    const container = document.getElementById('images-container');
    const row = document.createElement('div');
    row.className = 'image-row';
    row.innerHTML = `
        <input type="text" name="images[${imageCounter}][path]" placeholder="URL изображения" class="form-control" style="flex: 2;">
        <input type="text" name="images[${imageCounter}][title]" placeholder="Название изображения" class="form-control" style="flex: 1;">
        <button type="button" class="btn-remove-row" onclick="removeRow(this)">Удалить</button>
    `;
    container.appendChild(row);
    imageCounter++;
}

// Функция удаления строки
function removeRow(button) {
    const row = button.parentElement;
    row.remove();
}
</script>
</body>
</html> 