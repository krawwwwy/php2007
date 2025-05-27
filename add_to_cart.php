<?php
session_start();

// Проверяем авторизацию пользователя
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Получаем ID пользователя
$user_id = $_SESSION['user_id'];

// Проверяем, был ли передан ID услуги
if (!isset($_POST['service_id']) || empty($_POST['service_id'])) {
    $_SESSION['error_message'] = 'Не указана услуга для добавления в корзину';
    header('Location: catalog.php');
    exit;
}

$service_id = (int)$_POST['service_id'];
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Устанавливаем минимальное количество = 1
if ($quantity < 1) {
    $quantity = 1;
}

// Подключаемся к базе данных
require_once 'db_connect.php';

try {
    // Проверяем, существует ли уже эта услуга в корзине пользователя
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND service_id = :service_id");
    $stmt->execute([
        'user_id' => $user_id,
        'service_id' => $service_id
    ]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Если услуга уже в корзине, увеличиваем количество
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id");
        $stmt->execute([
            'quantity' => $new_quantity,
            'id' => $existing_item['id']
        ]);
        $_SESSION['success_message'] = 'Количество услуги в корзине увеличено';
    } else {
        // Если услуги нет в корзине, добавляем новую запись
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, service_id, quantity) VALUES (:user_id, :service_id, :quantity)");
        $stmt->execute([
            'user_id' => $user_id,
            'service_id' => $service_id,
            'quantity' => $quantity
        ]);
        $_SESSION['success_message'] = 'Услуга успешно добавлена в корзину';
    }
    
    // Если был передан параметр redirect, перенаправляем на указанную страницу
    if (isset($_POST['redirect'])) {
        header('Location: ' . $_POST['redirect']);
        exit;
    }
    
    // По умолчанию перенаправляем на страницу услуги
    header('Location: service_view.php?id=' . $service_id);
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Произошла ошибка при добавлении в корзину: ' . $e->getMessage();
    header('Location: catalog.php');
    exit;
}
?> 