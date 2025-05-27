<?php
// Список всех необходимых изображений
$imageFiles = [
    'img/products/tv_samsung.jpg',
    'img/products/iphone_13.jpg',
    'img/products/asus_vivobook.jpg',
    'img/products/bosch_fridge.jpg',
    'img/products/lg_washer.jpg',
    'img/products/delonghi_coffee.jpg',
    'img/products/ikea_sofa.jpg',
    'img/products/leroy_kitchen.jpg',
    
    // Дополнительные изображения
    'img/products/tv_samsung_1.jpg',
    'img/products/tv_samsung_2.jpg',
    'img/products/iphone_13_1.jpg',
    'img/products/iphone_13_2.jpg',
    'img/products/asus_vivobook_1.jpg',
    'img/products/asus_vivobook_2.jpg',
    'img/products/bosch_fridge_1.jpg',
    'img/products/lg_washer_1.jpg',
    'img/products/delonghi_coffee_1.jpg',
    'img/products/ikea_sofa_1.jpg',
    'img/products/leroy_kitchen_1.jpg',
];

// Создаем директорию, если она не существует
if (!file_exists('img/products')) {
    mkdir('img/products', 0777, true);
}

// Создаем случайные цвета для заглушек
$colors = [
    [255, 99, 71],  // Томатный
    [65, 105, 225], // Королевский синий
    [50, 205, 50],  // Лаймовый
    [255, 165, 0],  // Оранжевый
    [186, 85, 211], // Фиолетовый
    [255, 215, 0],  // Золотой
    [95, 158, 160], // Зеленовато-голубой
    [250, 128, 114] // Лососевый
];

// Функция для создания заглушки изображения
function createDummyImage($path, $width = 400, $height = 300) {
    global $colors;
    
    // Выбираем случайный цвет
    $colorIndex = rand(0, count($colors) - 1);
    $bgColor = $colors[$colorIndex];
    
    // Создаем изображение
    $img = imagecreatetruecolor($width, $height);
    
    // Заливаем фон
    $background = imagecolorallocate($img, $bgColor[0], $bgColor[1], $bgColor[2]);
    imagefill($img, 0, 0, $background);
    
    // Добавляем текст с названием файла
    $filename = basename($path);
    $textColor = imagecolorallocate($img, 255, 255, 255);
    $font = 5; // Встроенный шрифт
    
    // Центрируем текст
    $textWidth = imagefontwidth($font) * strlen($filename);
    $textHeight = imagefontheight($font);
    $centerX = ($width - $textWidth) / 2;
    $centerY = ($height - $textHeight) / 2;
    
    imagestring($img, $font, $centerX, $centerY, $filename, $textColor);
    
    // Сохраняем изображение
    imagejpeg($img, $path, 90);
    imagedestroy($img);
}

echo "<h2>Создание заглушек изображений</h2>";

// Создаем заглушки для всех изображений
foreach ($imageFiles as $image) {
    createDummyImage($image);
    echo "Создано изображение: " . $image . "<br>";
}

echo "<p>Все заглушки изображений успешно созданы!</p>";
echo "<p><a href='setup_original_products.php'>Теперь вы можете настроить оригинальные товары</a></p>";
?> 