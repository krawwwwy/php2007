<?php
// Заголовок и стиль для вывода сообщений
echo '<html><head><title>Исправление CSS стилей на страницах продуктов</title>';
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
    h1 { color: #0066cc; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #0066cc; color: white; 
        text-decoration: none; border-radius: 5px; }
</style></head><body>';
echo '<h1>Исправление CSS стилей на страницах продуктов</h1>';

// Читаем файл product1.php
$source = file_get_contents('product1.php');
if (!$source) {
    die('<div class="error">Не удалось прочитать исходный файл product1.php</div>');
}

// Ищем CSS стили в исходном файле
preg_match('/<style[^>]*>(.*?)<\/style>/is', $source, $styleMatches);
if (empty($styleMatches[1])) {
    die('<div class="error">CSS стили не найдены в исходном файле</div>');
}
$sourceStyles = $styleMatches[1];

// Файлы для обновления
$files = ['product2.php', 'product3.php', 'product4.php', 'product5.php', 'product6.php'];
$successCount = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo '<div class="error">Файл ' . $file . ' не найден</div>';
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Проверяем, есть ли в файле секция стилей
    if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $content, $matches)) {
        // Заменяем содержимое стилей
        $updatedContent = str_replace($matches[0], '<style>' . $sourceStyles . '</style>', $content);
        
        // Сохраняем обновленный файл
        if (file_put_contents($file, $updatedContent)) {
            echo '<div class="success">CSS стили в файле ' . $file . ' успешно обновлены</div>';
            $successCount++;
        } else {
            echo '<div class="error">Не удалось сохранить обновленный файл ' . $file . '</div>';
        }
    } else {
        echo '<div class="error">В файле ' . $file . ' не найдена секция стилей</div>';
    }
}

if ($successCount > 0) {
    echo '<div class="success"><strong>CSS стили успешно обновлены в ' . $successCount . ' из ' . count($files) . ' файлов</strong></div>';
} else {
    echo '<div class="error"><strong>Не удалось обновить CSS стили ни в одном из файлов</strong></div>';
}

// Дополнительно проверяем и исправляем кнопку регистрации и пункты меню
echo '<h2>Исправление HTML разметки для кнопки регистрации и пунктов меню</h2>';

// Ищем HTML для кнопки регистрации и меню в исходном файле
if (preg_match('/<div[^>]*class="header__profile[^>]*>(.*?)<\/div>/is', $source, $profileMatches) && 
    preg_match('/<div[^>]*class="header__menu[^>]*>(.*?)<\/div>/is', $source, $menuMatches)) {
    
    $profileHTML = $profileMatches[0];
    $menuHTML = $menuMatches[0];
    
    $fixedCount = 0;
    
    foreach ($files as $file) {
        if (!file_exists($file)) {
            continue;
        }
        
        $content = file_get_contents($file);
        $updated = false;
        
        // Заменяем блок кнопки регистрации
        if (preg_match('/<div[^>]*class="header__profile[^>]*>(.*?)<\/div>/is', $content, $matches)) {
            $content = str_replace($matches[0], $profileHTML, $content);
            $updated = true;
        }
        
        // Заменяем блок меню
        if (preg_match('/<div[^>]*class="header__menu[^>]*>(.*?)<\/div>/is', $content, $matches)) {
            $content = str_replace($matches[0], $menuHTML, $content);
            $updated = true;
        }
        
        // Сохраняем обновленный файл
        if ($updated && file_put_contents($file, $content)) {
            echo '<div class="success">HTML разметка в файле ' . $file . ' успешно обновлена</div>';
            $fixedCount++;
        }
    }
    
    if ($fixedCount > 0) {
        echo '<div class="success"><strong>HTML разметка успешно обновлена в ' . $fixedCount . ' из ' . count($files) . ' файлов</strong></div>';
    } else {
        echo '<div class="error"><strong>Не удалось обновить HTML разметку ни в одном из файлов</strong></div>';
    }
} else {
    echo '<div class="error">Не удалось найти блоки кнопки регистрации и/или меню в исходном файле</div>';
}

echo '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
echo '</body></html>';
?> 