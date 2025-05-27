<?php
session_start();
require_once 'db_connect.php';

// Проверка авторизации (необязательно, но рекомендуется)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

// Переменные для хранения данных формы и сообщений
$title_value = '';
$content = '';
$price = '';
$duration = '';
$doctor = '';
$first_letter = '';
$success = '';
$error = '';

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Получаем данные формы
    $title_value = trim($_POST['title_value']);
    $content = trim($_POST['content']);
    $price = trim($_POST['price']);
    $duration = trim($_POST['duration']);
    $doctor = trim($_POST['doctor']);
    
    // Автоматически получаем первую букву названия услуги
    $first_letter = mb_substr($title_value, 0, 1, 'UTF-8');
    
    // Валидация данных
    if (empty($title_value) || empty($content) || empty($price) || empty($duration) || empty($doctor)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Цена должна быть положительным числом';
    } else {
        // Обработка загрузки изображения
        $target_dir = "img/";
        $image = "";
        
        // Если директория не существует, создаем её
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed = array("jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif");
            $filename = $_FILES["image"]["name"];
            $filetype = $_FILES["image"]["type"];
            $filesize = $_FILES["image"]["size"];
            
            // Проверка расширения файла
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!array_key_exists($ext, $allowed)) {
                $error = "Ошибка: Пожалуйста, выберите формат файла JPEG, PNG или GIF.";
            }
            
            // Проверка размера файла - максимум 5MB
            $maxsize = 5 * 1024 * 1024;
            if ($filesize > $maxsize) {
                $error = "Ошибка: Размер файла превышает допустимый предел (5MB).";
            }
            
            // Проверка типа MIME файла
            if (in_array($filetype, $allowed)) {
                // Генерируем уникальное имя файла
                $new_filename = "product" . time() . "." . $ext;
                $target_file = $target_dir . $new_filename;
                
                // Попытка загрузить файл
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                } else {
                    $error = "Ошибка при загрузке файла.";
                }
            } else {
                $error = "Ошибка: Проблема с типом файла. Пожалуйста, загрузите файл с правильным типом.";
            }
        } else {
            // Если файл не загружен, но включена опция создания шаблона
            if (isset($_POST['generate_placeholder']) && $_POST['generate_placeholder'] == 1) {
                $new_filename = "product_placeholder_" . time() . ".jpg";
                $target_file = $target_dir . $new_filename;
                
                // Проверяем, доступно ли расширение GD
                if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
                    // Создаем заглушку изображения с помощью GD
                    $img = imagecreatetruecolor(400, 300);
                    $bgColor = imagecolorallocate($img, 255, 165, 0); // Оранжевый фон
                    imagefill($img, 0, 0, $bgColor);
                    
                    $textColor = imagecolorallocate($img, 255, 255, 255); // Белый текст
                    $text = mb_substr($title_value, 0, 20, 'UTF-8'); // Первые 20 символов названия
                    
                    // Центрирование текста
                    $font = 5;
                    $textWidth = imagefontwidth($font) * mb_strlen($text, 'UTF-8');
                    $textHeight = imagefontheight($font);
                    $x = (400 - $textWidth) / 2;
                    $y = (300 - $textHeight) / 2;
                    
                    imagestring($img, $font, $x, $y, $text, $textColor);
                    imagejpeg($img, $target_file, 90);
                    imagedestroy($img);
                } else {
                    // Альтернативный вариант - копируем стандартное изображение-заглушку
                    // Сначала проверяем наличие файла-заглушки, если его нет - используем изображение из product1.php
                    if (file_exists('img/placeholder.jpg')) {
                        copy('img/placeholder.jpg', $target_file);
                    } elseif (file_exists('img/product1.jpg')) {
                        copy('img/product1.jpg', $target_file);
                    } else {
                        // Если ничего не найдено, создаем пустой файл
                        file_put_contents($target_file, '');
                    }
                }
                
                $image = $target_file;
            } else {
                $error = "Пожалуйста, загрузите изображение или включите опцию создания шаблона.";
            }
        }
        
        // Если нет ошибок, сохраняем данные в БД
        if (empty($error)) {
            try {
                // Получаем следующий доступный id
                $stmt = $pdo->query("SELECT MAX(id) as max_id FROM services");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $next_id = ($result && $result['max_id']) ? $result['max_id'] + 1 : 1;
                
                // Добавляем новую услугу
                $stmt = $pdo->prepare("INSERT INTO services (id, title_value, content, price, duration, doctor, image, first_letter) 
                                      VALUES (:id, :title_value, :content, :price, :duration, :doctor, :image, :first_letter)");
                
                $stmt->execute([
                    'id' => $next_id,
                    'title_value' => $title_value,
                    'content' => $content,
                    'price' => $price,
                    'duration' => $duration,
                    'doctor' => $doctor,
                    'image' => $image,
                    'first_letter' => $first_letter
                ]);
                
                // Создаем новый файл productX.php на основе шаблона
                $template_file = "product1.php";
                if (file_exists($template_file)) {
                    $template_content = file_get_contents($template_file);
                    
                    // Создаем краткое и полное описание из контента
                    $contentArray = explode("\n", $content);
                    $shortDescription = !empty($contentArray[0]) ? $contentArray[0] : $content;
                    
                    // Генерируем список характеристик из контента
                    $features = [];
                    foreach ($contentArray as $line) {
                        $line = trim($line);
                        if (!empty($line) && strlen($line) < 100 && strpos($line, '.') !== false) {
                            $features[] = $line;
                            if (count($features) >= 4) break;
                        }
                    }
                    
                    // Если не удалось найти характеристики, создаем стандартные
                    if (empty($features)) {
                        $features = [
                            'Профессиональное обслуживание',
                            'Современное оборудование',
                            'Гарантия на услуги',
                            'Опытные врачи'
                        ];
                    }
                    
                    // Подготавливаем HTML для характеристик
                    $featuresHTML = '';
                    foreach ($features as $feature) {
                        $featuresHTML .= "<li>" . htmlspecialchars($feature) . "</li>\n";
                    }
                    
                    // Подготавливаем HTML для подробного описания
                    $fullDescription = '';
                    $paragraphs = array_slice($contentArray, 1);
                    foreach ($paragraphs as $paragraph) {
                        if (!empty(trim($paragraph))) {
                            $fullDescription .= "<p>" . htmlspecialchars($paragraph) . "</p>\n";
                        }
                    }
                    
                    // Если подробное описание пустое, используем весь контент
                    if (empty(trim($fullDescription))) {
                        $fullDescription = "<p>" . htmlspecialchars($content) . "</p>";
                    }
                    
                    // Заменяем данные в шаблоне
                    $replacements = [
                        '$id = 1;' => '$id = ' . $next_id . ';', // ID
                        '<title>Лечение зубов — Стоматологическая клиника Жемчуг</title>' => '<title>' . htmlspecialchars($title_value) . ' — Стоматологическая клиника Жемчуг</title>', // Заголовок
                        '<h2 align="center">Лечение зубов</h2>' => '<h2 align="center">' . htmlspecialchars($title_value) . '</h2>', // Заголовок H2
                        'img/product1.jpg' => $image, // Изображение
                        'alt="Лечение зубов"' => 'alt="' . htmlspecialchars($title_value) . '"', // Alt текст
                        '<p class="product-short-desc">Профессиональное лечение зубов любой сложности с применением современных технологий и материалов. Безболезненно, быстро, качественно.</p>' => '<p class="product-short-desc">' . htmlspecialchars($shortDescription) . '</p>' // Краткое описание
                    ];
                    
                    // Заменить список характеристик
                    $new_content = preg_replace(
                        '/<ul class="product-features">.*?<\/ul>/s', 
                        '<ul class="product-features">' . $featuresHTML . '</ul>', 
                        $template_content
                    );
                    
                    // Заменить подробное описание
                    $new_content = preg_replace(
                        '/<div class="product-full-desc">.*?<\/div>/s', 
                        '<div class="product-full-desc">' . $fullDescription . '</div>', 
                        $new_content
                    );
                    
                    // Применить простые замены
                    foreach ($replacements as $search => $replace) {
                        $new_content = str_replace($search, $replace, $new_content);
                    }
                    
                    // Создаем новый файл с правильным номером
                    $new_file = "product" . $next_id . ".php";
                    file_put_contents($new_file, $new_content);
                    
                    $success = "Услуга успешно добавлена! Создана страница: <a href='{$new_file}'>{$new_file}</a>";
                } else {
                    $success = "Услуга успешно добавлена, но не удалось создать страницу продукта (шаблон не найден).";
                }
                
                // Сбрасываем значения полей
                $title_value = '';
                $content = '';
                $price = '';
                $duration = '';
                $doctor = '';
            } catch (PDOException $e) {
                $error = "Ошибка базы данных: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление новой услуги — Стоматологическая клиника Жемчуг</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        h1 { color: #0066cc; text-align: center; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], textarea, select { 
            width: 100%; 
            padding: 8px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea { height: 150px; }
        .btn-submit {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #ff8000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn-submit:hover { background-color: #ff9933; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 15px 0; }
        .back-link { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 8px 15px; 
            background: #0066cc; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px;
        }
        .checkbox-group {
            margin: 10px 0;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .instructions {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #0066cc;
        }
    </style>
</head>
<body>
    <h1>Добавление новой услуги</h1>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="instructions">
        <h3>Рекомендации по заполнению:</h3>
        <ul>
            <li>В поле <b>Описание услуги</b> первый абзац будет использован как краткое описание</li>
            <li>Короткие предложения с точкой в конце будут использованы как характеристики услуги</li>
            <li>Разделяйте абзацы пустой строкой для лучшей структуры текста</li>
        </ul>
    </div>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title_value">Название услуги:</label>
            <input type="text" id="title_value" name="title_value" value="<?php echo htmlspecialchars($title_value); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="content">Описание услуги:</label>
            <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
            <small>Введите полное описание услуги. Разделяйте абзацы пустыми строками.</small>
        </div>
        
        <div class="form-group">
            <label for="price">Цена (руб):</label>
            <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($price); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="duration">Продолжительность:</label>
            <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($duration); ?>" placeholder="Например: 45 минут, 1 час, от 30 до 60 минут" required>
        </div>
        
        <div class="form-group">
            <label for="doctor">Врач:</label>
            <input type="text" id="doctor" name="doctor" value="<?php echo htmlspecialchars($doctor); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="image">Изображение услуги:</label>
            <input type="file" id="image" name="image">
            <div class="checkbox-group">
                <input type="checkbox" id="generate_placeholder" name="generate_placeholder" value="1" checked>
                <label for="generate_placeholder" style="display:inline;">Создать шаблон, если изображение не загружено</label>
            </div>
        </div>
        
        <button type="submit" name="submit" class="btn-submit">Добавить услугу</button>
    </form>
    
    <a href="index.php" class="back-link">На главную</a>
</body>
</html> 