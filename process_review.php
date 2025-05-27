<?php
session_start();

// Если форма не была отправлена, перенаправляем на страницу отзывов
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reviews.php");
    exit;
}

// Очистка и валидация данных формы
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
$service = isset($_POST['service']) ? trim(strip_tags($_POST['service'])) : '';
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$review = isset($_POST['review']) ? trim(strip_tags($_POST['review'])) : '';

// Сохраняем форму для возможного повторного заполнения
$_SESSION['reviews_form_data'] = [
    'name' => $name,
    'email' => $email,
    'service' => $service,
    'rating' => $rating > 0 ? (string) $rating : '',
    'review' => $review
];

// Валидация данных
$errors = [];

if (empty($name)) {
    $errors[] = 'Пожалуйста, введите ваше имя';
}

if (empty($email)) {
    $errors[] = 'Пожалуйста, введите вашу электронную почту';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Пожалуйста, введите корректный адрес электронной почты';
}

if (empty($service)) {
    $errors[] = 'Пожалуйста, выберите услугу';
}

if ($rating <= 0 || $rating > 5) {
    $errors[] = 'Пожалуйста, выберите оценку от 1 до 5';
}

if (empty($review)) {
    $errors[] = 'Пожалуйста, напишите текст отзыва';
} elseif (strlen($review) < 10) {
    $errors[] = 'Текст отзыва должен содержать не менее 10 символов';
}

// Если есть ошибки, возвращаемся на страницу формы с ошибками
if (!empty($errors)) {
    $_SESSION['reviews_errors'] = $errors;
    header("Location: reviews.php");
    exit;
}

// Путь к файлу с отзывами
$reviews_file = 'reviews_data.json';

// Получаем текущие отзывы из файла
$reviews = [];
if (file_exists($reviews_file)) {
    $file_content = file_get_contents($reviews_file);
    if (!empty($file_content)) {
        $reviews = json_decode($file_content, true);
        // Если файл поврежден, создаем новый массив
        if ($reviews === null) {
            $reviews = [];
        }
    }
}

// Добавляем новый отзыв
$new_review = [
    'id' => time(), // Используем текущее время как ID
    'name' => $name,
    'email' => $email,
    'service' => $service,
    'rating' => $rating,
    'review' => $review,
    'approved' => 0, // По умолчанию отзыв не одобрен
    'date_added' => date('Y-m-d H:i:s')
];

$reviews[] = $new_review;

// Сохраняем отзывы обратно в файл
try {
    if (file_put_contents($reviews_file, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Успешное сохранение отзыва
        $_SESSION['reviews_success'] = 'Спасибо за ваш отзыв! Он будет опубликован после проверки администратором.';
        
        // Очищаем данные формы
        unset($_SESSION['reviews_form_data']);
    } else {
        throw new Exception('Не удалось записать в файл');
    }
} catch (Exception $e) {
    // Ошибка при сохранении отзыва
    $_SESSION['reviews_error'] = 'Произошла ошибка при сохранении отзыва. Пожалуйста, попробуйте позже.';
}

// Перенаправляем обратно на страницу отзывов
header("Location: reviews.php");
exit;
?> 