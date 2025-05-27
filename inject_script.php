<?php
// Список всех файлов HTML/PHP в директории
$files = glob("*.php");

// Исключаем текущий файл из списка
$files = array_diff($files, ["inject_script.php", "fix_styles.js"]);

// Количество успешно обработанных файлов
$success_count = 0;

echo "<h1>Внедрение скрипта для исправления стилей</h1>";

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Проверяем, что это HTML-файл с тегом </head>
    if (strpos($content, "</head>") !== false) {
        // Проверяем, что скрипт еще не внедрен
        if (strpos($content, "fix_styles.js") === false) {
            // Подготавливаем тег скрипта для внедрения
            $script_tag = '<script src="fix_styles.js"></script>' . PHP_EOL . '</head>';
            
            // Заменяем закрывающий тег head на скрипт + закрывающий тег
            $content = str_replace("</head>", $script_tag, $content);
            
            // Сохраняем изменения
            file_put_contents($file, $content);
            
            echo "<p style='color: green;'>✓ Файл <strong>{$file}</strong> успешно обновлен</p>";
            $success_count++;
        } else {
            echo "<p style='color: blue;'>ℹ Файл <strong>{$file}</strong> уже содержит скрипт</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Файл <strong>{$file}</strong> не является HTML-файлом или не содержит тег </head></p>";
    }
}

echo "<hr>";
echo "<h3>Итоги:</h3>";
echo "<p>Всего обработано файлов: " . count($files) . "</p>";
echo "<p>Успешно обновлено: {$success_count}</p>";

if ($success_count > 0) {
    echo "<p><strong>Скрипт успешно внедрен! Обновите страницы в браузере, чтобы увидеть изменения.</strong></p>";
}
?> 