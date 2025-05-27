<?php
// Скрипт для создания тестовых изображений для слайдера

// Папка для хранения изображений
$img_dir = 'img/';

// Проверяем существует ли папка и создаем ее при необходимости
if (!file_exists($img_dir)) {
    mkdir($img_dir, 0777, true);
}

// Создаем каждое из изображений для слайдера
$slider_images = [
    'slider1.jpg' => [
        'title' => 'Современное оборудование',
        'bg_color' => [41, 128, 185], // Синий
        'width' => 800,
        'height' => 400
    ],
    'slider2.jpg' => [
        'title' => 'Профессиональный персонал',
        'bg_color' => [46, 204, 113], // Зеленый
        'width' => 800,
        'height' => 400
    ],
    'slider3.jpg' => [
        'title' => 'Комфортное лечение',
        'bg_color' => [230, 126, 34], // Оранжевый
        'width' => 800,
        'height' => 400
    ]
];

// Проверяем наличие расширения GD
if (!extension_loaded('gd')) {
    echo "<p>Для создания изображений необходимо расширение GD для PHP.</p>";
    echo "<p>Пожалуйста, активируйте его в вашей конфигурации PHP или обратитесь к вашему хостинг-провайдеру.</p>";
    exit;
}

// Счетчик созданных изображений
$created = 0;
$existing = 0;

// Создаем изображения
foreach ($slider_images as $filename => $image_data) {
    $filepath = $img_dir . $filename;
    
    // Проверяем, существует ли изображение
    if (file_exists($filepath)) {
        $existing++;
        continue; // Пропускаем создание, если файл уже существует
    }
    
    // Создаем изображение
    $img = imagecreatetruecolor($image_data['width'], $image_data['height']);
    
    // Выделяем цвета
    $bg_color = imagecolorallocate($img, $image_data['bg_color'][0], $image_data['bg_color'][1], $image_data['bg_color'][2]);
    $text_color = imagecolorallocate($img, 255, 255, 255);
    $line_color = imagecolorallocate($img, 255, 255, 255);
    
    // Заливаем фон
    imagefill($img, 0, 0, $bg_color);
    
    // Добавляем текст в центр
    $font_size = 5; // Размер шрифта (1-5)
    $text = $image_data['title'] . " - Стоматология «Жемчуг»";
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($image_data['width'] - $text_width) / 2;
    $y = ($image_data['height'] - $text_height) / 2;
    
    // Добавляем несколько декоративных элементов
    // Горизонтальные линии
    imageline($img, 50, 50, $image_data['width'] - 50, 50, $line_color);
    imageline($img, 50, $image_data['height'] - 50, $image_data['width'] - 50, $image_data['height'] - 50, $line_color);
    
    // Вертикальные линии
    imageline($img, 50, 50, 50, $image_data['height'] - 50, $line_color);
    imageline($img, $image_data['width'] - 50, 50, $image_data['width'] - 50, $image_data['height'] - 50, $line_color);
    
    // Рисуем текст
    imagestring($img, $font_size, $x, $y, $text, $text_color);
    
    // Добавляем подзаголовок
    $subtitle = "Качественное обслуживание";
    $subtitle_width = imagefontwidth($font_size - 1) * strlen($subtitle);
    $x_subtitle = ($image_data['width'] - $subtitle_width) / 2;
    $y_subtitle = $y + $text_height + 20;
    imagestring($img, $font_size - 1, $x_subtitle, $y_subtitle, $subtitle, $text_color);
    
    // Сохраняем изображение
    imagejpeg($img, $filepath, 90);
    imagedestroy($img);
    
    $created++;
}

// Создаем также примеры для баннеров, если их нет
$banner_images = [
    'banner1.jpg' => [
        'title' => 'Исправление прикуса',
        'bg_color' => [155, 89, 182], // Фиолетовый
        'width' => 170,
        'height' => 80
    ],
    'banner2.jpg' => [
        'title' => 'Ортопедия',
        'bg_color' => [52, 152, 219], // Голубой
        'width' => 170,
        'height' => 80
    ],
    'banner3.jpg' => [
        'title' => 'Рентгенология',
        'bg_color' => [231, 76, 60], // Красный
        'width' => 170,
        'height' => 80
    ]
];

// Создаем баннеры
foreach ($banner_images as $filename => $image_data) {
    $filepath = $img_dir . $filename;
    
    // Проверяем, существует ли изображение
    if (file_exists($filepath)) {
        $existing++;
        continue; // Пропускаем создание, если файл уже существует
    }
    
    // Создаем изображение
    $img = imagecreatetruecolor($image_data['width'], $image_data['height']);
    
    // Выделяем цвета
    $bg_color = imagecolorallocate($img, $image_data['bg_color'][0], $image_data['bg_color'][1], $image_data['bg_color'][2]);
    $text_color = imagecolorallocate($img, 255, 255, 255);
    
    // Заливаем фон
    imagefill($img, 0, 0, $bg_color);
    
    // Добавляем текст в центр
    $font_size = 3; // Размер шрифта (1-5)
    $text = $image_data['title'];
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($image_data['width'] - $text_width) / 2;
    $y = ($image_data['height'] - $text_height) / 2;
    
    // Рисуем текст
    imagestring($img, $font_size, $x, $y, $text, $text_color);
    
    // Сохраняем изображение
    imagejpeg($img, $filepath, 90);
    imagedestroy($img);
    
    $created++;
}

// Создаем логотип, если его нет
$logo_file = $img_dir . 'logo.png';
if (!file_exists($logo_file)) {
    // Создаем изображение с прозрачным фоном
    $img = imagecreatetruecolor(120, 120);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    // Цвета
    $color1 = imagecolorallocate($img, 255, 128, 0); // Оранжевый
    $color2 = imagecolorallocate($img, 255, 255, 255); // Белый
    
    // Рисуем круг
    imagefilledellipse($img, 60, 60, 100, 100, $color1);
    
    // Рисуем внутренний круг
    imagefilledellipse($img, 60, 60, 70, 70, $color2);
    
    // Рисуем текст
    $text = "Жемчуг";
    $font_size = 3;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = (120 - $text_width) / 2;
    $y = (120 - $text_height) / 2;
    
    imagestring($img, $font_size, $x, $y, $text, $color1);
    
    // Сохраняем изображение
    imagepng($img, $logo_file);
    imagedestroy($img);
    
    $created++;
}

// Выводим статистику
echo '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Генерация изображений</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #0066cc;
            text-align: center;
        }
        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            color: #0066cc;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .images-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .image-item {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .image-item img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Генерация изображений для слайдера</h1>';

if ($created > 0) {
    echo '<div class="success">Создано ' . $created . ' изображений для вашего сайта.</div>';
}

if ($existing > 0) {
    echo '<div class="info">Пропущено ' . $existing . ' изображений, так как они уже существуют.</div>';
}

// Показываем изображения
echo '<h2>Созданные изображения для слайдера:</h2>';
echo '<div class="images-container">';

foreach ($slider_images as $filename => $image_data) {
    $filepath = $img_dir . $filename;
    if (file_exists($filepath)) {
        echo '<div class="image-item">';
        echo '<img src="' . $filepath . '" alt="' . $image_data['title'] . '">';
        echo '<p>' . $image_data['title'] . '</p>';
        echo '</div>';
    }
}

echo '</div>';

echo '<h2>Созданные баннеры:</h2>';
echo '<div class="images-container">';

foreach ($banner_images as $filename => $image_data) {
    $filepath = $img_dir . $filename;
    if (file_exists($filepath)) {
        echo '<div class="image-item">';
        echo '<img src="' . $filepath . '" alt="' . $image_data['title'] . '">';
        echo '<p>' . $image_data['title'] . '</p>';
        echo '</div>';
    }
}

echo '</div>';

// Показываем логотип
if (file_exists($logo_file)) {
    echo '<h2>Логотип:</h2>';
    echo '<div class="images-container">';
    echo '<div class="image-item">';
    echo '<img src="' . $logo_file . '" alt="Логотип">';
    echo '<p>Логотип клиники</p>';
    echo '</div>';
    echo '</div>';
}

echo '<p>Вы можете заменить эти изображения на свои собственные, просто загрузив их в папку img/ с теми же именами файлов.</p>';
echo '<a href="index.php" class="button">Вернуться на главную</a>';
echo '</body></html>';
?> 