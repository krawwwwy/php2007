<?php
// Заголовок и стиль для вывода сообщений
echo '<html><head><title>Добавление скрипта fix_styles.js на страницы продуктов</title>';
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
    h1 { color: #0066cc; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #0066cc; color: white; 
        text-decoration: none; border-radius: 5px; }
</style></head><body>';
echo '<h1>Добавление скрипта fix_styles.js на страницы продуктов</h1>';

// Проверим, существует ли файл fix_styles.js
if (!file_exists('fix_styles.js')) {
    echo '<div class="error">Файл fix_styles.js не найден. Создаем его...</div>';
    
    // Создаем файл fix_styles.js со стилями для меню и кнопок регистрации
    $jsContent = '// Скрипт для исправления стилей
document.addEventListener("DOMContentLoaded", function() {
    // Исправление стилей пунктов основного меню
    var menuLinks = document.querySelectorAll("ul.main-menu li a");
    menuLinks.forEach(function(link) {
        link.style.display = "block";
        link.style.padding = "8px 15px";
        link.style.color = "#000";
        link.style.textDecoration = "none";
        link.style.fontWeight = "bold";
        link.style.fontSize = "16px";
        
        // Добавляем эффект при наведении
        link.addEventListener("mouseover", function() {
            this.style.backgroundColor = "#ffd280";
        });
        
        link.addEventListener("mouseout", function() {
            this.style.backgroundColor = "";
        });
    });
    
    // Исправление стилей кнопок в форме авторизации
    var authButtons = document.querySelectorAll(".auth-btn");
    authButtons.forEach(function(button) {
        button.style.padding = "2px 12px";
        button.style.background = "#fff8e1";
        button.style.border = "2px solid #ffa500";
        button.style.cursor = "pointer";
        button.style.fontSize = "14px";
        
        if (button.tagName === "A") {
            button.style.display = "inline-block";
            button.style.textDecoration = "none";
            button.style.color = "#000";
            button.style.textAlign = "center";
        }
        
        // Добавляем эффект при наведении
        button.addEventListener("mouseover", function() {
            this.style.backgroundColor = "#ffc040";
        });
        
        button.addEventListener("mouseout", function() {
            this.style.backgroundColor = "#fff8e1";
        });
    });
});';
    
    file_put_contents('fix_styles.js', $jsContent);
    echo '<div class="success">Файл fix_styles.js успешно создан</div>';
}

// Файлы для обновления
$files = ['product2.php', 'product3.php', 'product4.php', 'product5.php', 'product6.php'];
$successCount = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo '<div class="error">Файл ' . $file . ' не найден</div>';
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Проверяем, есть ли уже подключение скрипта fix_styles.js
    if (strpos($content, 'fix_styles.js') === false) {
        // Добавляем подключение скрипта перед закрывающим тегом </head>
        $content = str_replace('</head>', '<script src="fix_styles.js"></script>' . PHP_EOL . '</head>', $content);
        
        // Сохраняем обновленный файл
        if (file_put_contents($file, $content)) {
            echo '<div class="success">Скрипт fix_styles.js добавлен в файл ' . $file . '</div>';
            $successCount++;
        } else {
            echo '<div class="error">Не удалось сохранить обновленный файл ' . $file . '</div>';
        }
    } else {
        echo '<div class="success">Файл ' . $file . ' уже содержит скрипт fix_styles.js</div>';
    }
}

if ($successCount > 0) {
    echo '<div class="success"><strong>Скрипт fix_styles.js успешно добавлен в ' . $successCount . ' файлов</strong></div>';
} else {
    echo '<div class="error"><strong>Не удалось добавить скрипт fix_styles.js ни в один из файлов</strong></div>';
}

echo '<div class="success"><strong>Теперь страницы продуктов должны выглядеть одинаково</strong></div>';
echo '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
echo '</body></html>';
?> 