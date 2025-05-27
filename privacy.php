<?php
// Определяем, запросил ли пользователь скачивание файла
if (isset($_GET['download']) && $_GET['download'] == 1) {
    // Путь к файлу политики конфиденциальности
    $file = 'privacy_policy.txt';
    
    // Проверяем существование файла
    if (file_exists($file)) {
        // Устанавливаем заголовки для скачивания файла
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="Политика_конфиденциальности_Жемчуг.txt"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        
        // Выводим содержимое файла
        readfile($file);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Политика конфиденциальности - Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .policy-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        .policy-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .policy-content {
            white-space: pre-wrap;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .policy-actions {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .policy-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff8000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 0 10px;
            transition: background-color 0.3s;
        }
        .policy-btn:hover {
            background-color: #ff9933;
        }
        .policy-btn.secondary {
            background-color: #6c757d;
        }
        .policy-btn.secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
<div class="policy-container">
    <div class="policy-header">
        <h1>Политика конфиденциальности</h1>
        <h2>Стоматологическая клиника «Жемчуг»</h2>
    </div>
    
    <div class="policy-content">
        <?php
        // Путь к файлу политики конфиденциальности
        $file = 'privacy_policy.txt';
        
        // Проверяем существование файла и выводим его содержимое
        if (file_exists($file)) {
            echo nl2br(htmlspecialchars(file_get_contents($file)));
        } else {
            echo '<p>Файл политики конфиденциальности не найден.</p>';
        }
        ?>
    </div>
    
    <div class="policy-actions">
        <a href="?download=1" class="policy-btn">Скачать документ</a>
        <a href="javascript:window.close();" class="policy-btn secondary">Закрыть окно</a>
    </div>
</div>
</body>
</html> 