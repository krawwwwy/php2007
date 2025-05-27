<?php
// Начинаем сессию для вывода уведомлений
session_start();

// Проверяем, что запрос пришел методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Валидация данных
    $errors = [];
    
    // Проверяем имя
    if (empty($name)) {
        $errors[] = 'Пожалуйста, укажите ваше имя';
    }
    
    // Проверяем email
    if (empty($email)) {
        $errors[] = 'Пожалуйста, укажите ваш email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Пожалуйста, укажите корректный email';
    }
    
    // Проверяем тему
    if (empty($subject)) {
        $errors[] = 'Пожалуйста, укажите тему сообщения';
    }
    
    // Проверяем сообщение
    if (empty($message)) {
        $errors[] = 'Пожалуйста, напишите ваше сообщение';
    }
    
    // Если нет ошибок, сохраняем данные в базу
    if (empty($errors)) {
        try {
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
            
            // Подключение к базе данных
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, $options);
            
            // Подготавливаем SQL запрос
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
            
            // Выполняем запрос с нашими данными
            $result = $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            
            if ($result) {
                // Устанавливаем сообщение об успешной отправке
                $_SESSION['contact_success'] = 'Спасибо! Ваше сообщение успешно отправлено. Мы свяжемся с вами в ближайшее время.';
            } else {
                // Устанавливаем сообщение об ошибке
                $_SESSION['contact_error'] = 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте еще раз.';
            }
        } catch (PDOException $e) {
            // Логируем ошибку (в реальном проекте)
            // error_log($e->getMessage());
            
            // Устанавливаем сообщение об ошибке
            $_SESSION['contact_error'] = 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте еще раз.';
        }
    } else {
        // Если есть ошибки, сохраняем их и данные формы для отображения
        $_SESSION['contact_errors'] = $errors;
        $_SESSION['contact_form_data'] = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
    }
    
    // Перенаправляем обратно на страницу контактов
    header('Location: contacts.php');
    exit;
}

// Если запрос пришел не методом POST, перенаправляем на главную
header('Location: index.php');
exit; 