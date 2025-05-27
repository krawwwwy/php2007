<?php
// Шаблон product1.php
$template = file_get_contents('product1.php');

// Массив нумерации для замены
$pages = [2, 3, 4, 5, 6];

echo "<h2>Генерация страниц productX.php</h2>";

foreach ($pages as $pageNum) {
    // Заменяем все упоминания '1' на текущий номер страницы
    $content = str_replace('$id = 1;', '$id = ' . $pageNum . ';', $template);
    
    // Сохраняем в новый файл
    $filename = "product{$pageNum}.php";
    file_put_contents($filename, $content);
    
    echo "Создан файл: <b>{$filename}</b> с ID={$pageNum}<br>";
}

echo "<p>Все файлы успешно созданы!</p>";
echo "<p>1. Запустите <a href='restore_original_dental_services.php'><b>restore_original_dental_services.php</b></a> чтобы добавить стоматологические услуги в таблицу</p>";
echo "<p>2. Затем вы можете открыть любую из страниц:</p>";
echo "<ul>";
echo "<li><a href='product1.php'>product1.php - Лечение зубов</a></li>";
echo "<li><a href='product2.php'>product2.php - Профессиональная чистка зубов</a></li>";
echo "<li><a href='product3.php'>product3.php - Имплантация зубов</a></li>";
echo "<li><a href='product4.php'>product4.php - Исправление прикуса</a></li>";
echo "<li><a href='product5.php'>product5.php - Протезирование зубов</a></li>";
echo "<li><a href='product6.php'>product6.php - Отбеливание зубов</a></li>";
echo "</ul>";
?> 