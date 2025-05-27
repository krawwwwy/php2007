<?php
session_start();
require_once 'db_connect.php';

// Заголовок и стиль для вывода сообщений
$output = '<html><head><title>Изменение порядка услуг</title>';
$output .= '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
    h1 { color: #0066cc; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #0066cc; color: white; 
        text-decoration: none; border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f2f2f2; }
</style></head><body>';
$output .= '<h1>Изменение порядка услуг в таблице services</h1>';

try {
    // Сначала проверяем существование таблицы
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() == 0) {
        $output .= '<div class="error">Таблица services не существует. Пожалуйста, сначала создайте её с помощью update_services_table.php</div>';
        $output .= '<div class="back-link"><a href="update_services_table.php" style="color: white; text-decoration: none;">Создать таблицу services</a></div>';
    } else {
        // Создаем временную таблицу для хранения данных
        $pdo->exec("CREATE TEMPORARY TABLE temp_services LIKE services");
        $pdo->exec("INSERT INTO temp_services SELECT * FROM services");
        
        // Очищаем основную таблицу
        $pdo->exec("TRUNCATE TABLE services");
        $pdo->exec("ALTER TABLE services AUTO_INCREMENT = 1");
        
        // Определяем новый порядок услуг по их названиям
        $newOrderMap = [
            1 => 'Лечение зубов',
            2 => 'Протезирование зубов',
            3 => 'Имплантация зубов',
            4 => 'Отбеливание зубов',
            5 => 'Исправление прикуса',
            6 => 'Профессиональная чистка зубов'
        ];
        
        $output .= '<div class="success">Устанавливаем новый порядок услуг:</div>';
        $output .= '<ol>';
        foreach ($newOrderMap as $id => $title) {
            $output .= '<li>' . htmlspecialchars($title) . '</li>';
        }
        $output .= '</ol>';
        
        // Проверяем наличие всех услуг
        $existingServices = [];
        foreach ($newOrderMap as $title) {
            $stmt = $pdo->prepare("SELECT * FROM temp_services WHERE title_value LIKE :title LIMIT 1");
            $stmt->execute(['title' => "%$title%"]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($service) {
                $existingServices[] = $service;
            } else {
                $output .= '<div class="error">Услуга "' . htmlspecialchars($title) . '" не найдена в базе данных</div>';
            }
        }
        
        // Если все услуги найдены, переставляем их
        if (count($existingServices) == count($newOrderMap)) {
            // Вставляем услуги в новом порядке
            foreach ($newOrderMap as $id => $title) {
                foreach ($existingServices as $service) {
                    if (strpos($service['title_value'], $title) !== false) {
                        $stmt = $pdo->prepare("INSERT INTO services 
                            (id, title_value, content, price, duration, doctor, image, first_letter) 
                            VALUES (:id, :title_value, :content, :price, :duration, :doctor, :image, :first_letter)");
                        
                        $params = [
                            'id' => $id,
                            'title_value' => $service['title_value'],
                            'content' => $service['content'],
                            'price' => $service['price'],
                            'duration' => $service['duration'],
                            'doctor' => $service['doctor'],
                            'image' => $service['image'],
                            'first_letter' => $service['first_letter']
                        ];
                        
                        $stmt->execute($params);
                        break;
                    }
                }
            }
            
            $output .= '<div class="success"><strong>Порядок услуг успешно изменен!</strong></div>';
            
            // Выводим список услуг в новом порядке
            $output .= '<h2>Новый порядок услуг в таблице</h2>';
            $output .= '<table>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Продолжительность</th>
                    <th>Врач</th>
                </tr>';
            
            $services = $pdo->query("SELECT * FROM services ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($services as $service) {
                $output .= "<tr>
                    <td>{$service['id']}</td>
                    <td>{$service['title_value']}</td>
                    <td>" . number_format($service['price'], 2, ',', ' ') . " ₽</td>
                    <td>{$service['duration']}</td>
                    <td>{$service['doctor']}</td>
                </tr>";
            }
            
            $output .= '</table>';
            
            // Проверяем, нужно ли обновить файлы product*.php для соответствия новым ID
            $output .= '<h2>Обновление файлов product*.php</h2>';
            
            $productFiles = [];
            for ($i = 1; $i <= 6; $i++) {
                $filename = "product{$i}.php";
                if (file_exists($filename)) {
                    $productFiles[$i] = $filename;
                }
            }
            
            if (!empty($productFiles)) {
                // Используем старый порядок для сопоставления
                $oldOrderMap = [
                    'Лечение зубов' => 1,
                    'Профессиональная чистка зубов' => 2,
                    'Имплантация зубов' => 3,
                    'Исправление прикуса' => 4,
                    'Протезирование зубов' => 5,
                    'Отбеливание зубов' => 6
                ];
                
                // Получаем новый порядок в обратном порядке (название => id)
                $newOrderByTitle = array_flip($newOrderMap);
                
                $filesUpdated = 0;
                
                // Для каждого файла product*.php
                foreach ($productFiles as $productId => $filename) {
                    $content = file_get_contents($filename);
                    
                    if (preg_match('/<h2\s+align="center">(.*?)<\/h2>/is', $content, $matches)) {
                        $serviceTitle = trim($matches[1]);
                        
                        // Ищем соответствующую услугу
                        foreach ($newOrderMap as $newId => $title) {
                            if (strpos($serviceTitle, $title) !== false || strpos($title, $serviceTitle) !== false) {
                                // Нужно изменить $id в файле
                                if ($newId != $productId) {
                                    // Заменяем строку "$id = X;" на "$id = Y;"
                                    $oldIdLine = '$id = ' . $productId . ';';
                                    $newIdLine = '$id = ' . $newId . ';';
                                    $updatedContent = str_replace($oldIdLine, $newIdLine, $content);
                                    
                                    if ($content !== $updatedContent) {
                                        file_put_contents($filename, $updatedContent);
                                        $output .= "<div class='success'>Файл {$filename} обновлен: ID изменен с {$productId} на {$newId}</div>";
                                        $filesUpdated++;
                                    } else {
                                        $output .= "<div class='error'>Не удалось обновить файл {$filename}: строка {$oldIdLine} не найдена</div>";
                                    }
                                } else {
                                    $output .= "<div class='success'>Файл {$filename} не требует обновления (ID уже правильный)</div>";
                                }
                                break;
                            }
                        }
                    }
                }
                
                if ($filesUpdated > 0) {
                    $output .= "<div class='success'><strong>Файлы продуктов успешно обновлены: {$filesUpdated}</strong></div>";
                } else {
                    $output .= "<div class='error'>Не удалось обновить файлы продуктов</div>";
                }
            } else {
                $output .= "<div class='error'>Файлы product*.php не найдены</div>";
            }
        }
        
        // Удаляем временную таблицу
        $pdo->exec("DROP TEMPORARY TABLE IF EXISTS temp_services");
    }
    
    $output .= '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
    
} catch (PDOException $e) {
    $output .= '<div class="error">Ошибка при изменении порядка услуг: ' . $e->getMessage() . '</div>';
    $output .= '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
}

$output .= '</body></html>';
echo $output; 
?> 