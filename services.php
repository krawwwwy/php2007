<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации (аналогично view_messages.php)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Удаление услуги
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Сначала удаляем связанные записи (т.к. у нас нет внешних ключей)
        $pdo->exec("DELETE FROM service_properties WHERE service_id = $id");
        $pdo->exec("DELETE FROM service_images WHERE service_id = $id");
        
        // Затем удаляем саму услугу
        $pdo->exec("DELETE FROM services WHERE id = $id");
        
        $_SESSION['success'] = 'Услуга успешно удалена';
        header('Location: services.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Ошибка удаления услуги: ' . $e->getMessage();
    }
}

// Получение списка услуг с сортировкой
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Проверка и безопасное экранирование параметров сортировки
$allowedSorts = ['id', 'name', 'price', 'available', 'category_id'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($sort, $allowedSorts)) {
    $sort = 'id';
}

if (!in_array($order, $allowedOrders)) {
    $order = 'ASC';
}

// Фильтрация по категории (если указана)
$categoryFilter = '';
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = (int)$_GET['category'];
    $categoryFilter = "WHERE category_id = $category";
}

// Фильтрация по доступности
$availableFilter = '';
if (isset($_GET['available'])) {
    $available = (int)$_GET['available'];
    $availableFilter = $categoryFilter ? " AND available = $available" : "WHERE available = $available";
}

// Поиск по названию
$nameFilter = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $nameFilter = $categoryFilter || $availableFilter ? 
        " AND name LIKE '%$search%'" : 
        "WHERE name LIKE '%$search%'";
}

// Составляем SQL запрос с учетом параметров сортировки и фильтров
$sql = "SELECT * FROM services $categoryFilter $availableFilter $nameFilter ORDER BY $sort $order";
$services = $pdo->query($sql)->fetchAll();

// Проверяем существование таблицы services перед запросом категорий
try {
    // Проверяем существует ли таблица services
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() > 0) {
        // Получаем список категорий для фильтрации
        $categories = $pdo->query("SELECT DISTINCT category_id FROM services")->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $categories = [];
    }
} catch (PDOException $e) {
    $categories = [];
    $_SESSION['error'] = 'Таблица услуг не найдена. Пожалуйста, запустите <a href="setup_database.php">настройку базы данных</a>.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление услугами - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #services-container {
            padding: 20px;
        }
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .services-table th, .services-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        
        .services-table th {
            background-color: #f2f2f2;
            position: relative;
            cursor: pointer;
        }
        
        .services-table th:hover {
            background-color: #e5e5e5;
        }
        
        .services-table th a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 5px;
        }
        
        .services-table th a:after {
            content: "▼";
            font-size: 10px;
            margin-left: 5px;
            opacity: 0.5;
        }
        
        .services-table th a.asc:after {
            content: "▼";
            opacity: 1;
        }
        
        .services-table th a.desc:after {
            content: "▲";
            opacity: 1;
        }
        
        .services-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .services-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .controls {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .add-btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        
        .action-btn {
            margin-right: 5px;
            padding: 4px 8px;
            text-decoration: none;
            border-radius: 3px;
            color: white;
        }
        
        .edit-btn {
            background-color: #2196F3;
        }
        
        .delete-btn {
            background-color: #f44336;
        }
        
        .service-image {
            max-width: 80px;
            max-height: 80px;
        }
        
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 2px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
        }
        
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
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
            <div id="services-container">
                <h2 align="center">Управление услугами клиники</h2>
                
                <?php if (isset($_SESSION['error']) && strpos($_SESSION['error'], 'настройку базы данных') !== false): ?>
                <div class="alert alert-warning" style="background-color: #fff3cd; color: #856404; padding: 20px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ffeeba; font-size: 16px; text-align: center;">
                    <h3 style="color: #856404;">Необходима настройка базы данных</h3>
                    <p><?php echo $_SESSION['error']; ?></p>
                    <p><a href="setup_database.php" style="display: inline-block; padding: 10px 15px; background: #ffc107; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;">Запустить настройку базы данных</a></p>
                    <?php unset($_SESSION['error']); ?>
                </div>
                <?php elseif (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb;">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
                <?php elseif (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #f5c6cb;">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <div class="controls">
                    <div class="filters">
                        <form action="" method="get">
                            <select name="category">
                                <option value="">Все категории</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= isset($_GET['category']) && $_GET['category'] == $cat ? 'selected' : '' ?>>
                                    Категория <?= $cat ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="available">
                                <option value="">Все услуги</option>
                                <option value="1" <?= isset($_GET['available']) && $_GET['available'] == 1 ? 'selected' : '' ?>>Доступные</option>
                                <option value="0" <?= isset($_GET['available']) && $_GET['available'] == 0 ? 'selected' : '' ?>>Недоступные</option>
                            </select>
                            
                            <input type="text" name="search" placeholder="Поиск по названию" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            
                            <button type="submit">Применить</button>
                            <a href="services.php">Сбросить</a>
                        </form>
                    </div>
                    
                    <a href="service_edit.php" class="add-btn">Добавить услугу</a>
                </div>
                
                <table class="services-table">
                    <thead>
                        <tr>
                            <th><a href="?sort=id&order=<?= $sort == 'id' && $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="<?= $sort == 'id' ? strtolower($order) : '' ?>">ID</a></th>
                            <th><a href="?sort=name&order=<?= $sort == 'name' && $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="<?= $sort == 'name' ? strtolower($order) : '' ?>">Название</a></th>
                            <th>Изображение</th>
                            <th><a href="?sort=price&order=<?= $sort == 'price' && $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="<?= $sort == 'price' ? strtolower($order) : '' ?>">Цена</a></th>
                            <th><a href="?sort=category_id&order=<?= $sort == 'category_id' && $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="<?= $sort == 'category_id' ? strtolower($order) : '' ?>">Категория</a></th>
                            <th><a href="?sort=available&order=<?= $sort == 'available' && $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="<?= $sort == 'available' ? strtolower($order) : '' ?>">Доступность</a></th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Нет доступных услуг</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?= $service['id'] ?></td>
                                    <td><?= htmlspecialchars($service['name']) ?></td>
                                    <td>
                                        <?php if (!empty($service['image'])): ?>
                                            <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="service-image">
                                        <?php else: ?>
                                            Нет изображения
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($service['price'], 2, ',', ' ') ?> ₽</td>
                                    <td>Категория <?= $service['category_id'] ?></td>
                                    <td><?= $service['available'] ? 'Да' : 'Нет' ?></td>
                                    <td>
                                        <a href="service_view.php?id=<?= $service['id'] ?>" class="action-btn" style="background-color: #9C27B0;">Просмотр</a>
                                        <a href="service_edit.php?id=<?= $service['id'] ?>" class="action-btn edit-btn">Изменить</a>
                                        <a href="javascript:void(0)" onclick="confirmDelete(<?= $service['id'] ?>)" class="action-btn delete-btn">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <a href="#">1</a>
                    <a href="#" class="active">2</a>
                    <a href="#">3</a>
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
function confirmDelete(id) {
    if (confirm('Вы уверены, что хотите удалить эту услугу? Это действие нельзя отменить.')) {
        window.location.href = 'services.php?delete=' + id;
    }
}
</script>
</body>
</html> 