<?php
session_start();
require_once 'db_connect.php';

// Заголовок и стиль для вывода сообщений
$output = '<html><head><title>Восстановление оригинальной таблицы services</title>';
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
$output .= '<h1>Восстановление оригинальной таблицы services</h1>';

try {
    // Удаляем старую таблицу, если она существует
    $pdo->exec("DROP TABLE IF EXISTS services");
    
    // Создаем таблицу services с оригинальной структурой
    $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title_value VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        duration VARCHAR(50) NOT NULL,
        doctor VARCHAR(100) NOT NULL,
        image VARCHAR(255) NOT NULL,
        first_letter CHAR(1) NOT NULL,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";
    
    $pdo->exec($sql);
    $output .= '<div class="success">Таблица services создана с правильной структурой</div>';

    // Добавляем 6 оригинальных стоматологических услуг
    $originalServices = [
        [
            'title_value' => 'Лечение зубов',
            'content' => 'Профессиональное лечение зубов любой сложности с применением современных технологий и материалов. Наша клиника предлагает полный спектр услуг по лечению зубов. Мы применяем современные методики и материалы, что позволяет сделать процесс лечения комфортным и безболезненным. Наши специалисты имеют большой опыт работы и постоянно совершенствуют свои навыки. Мы используем только качественные материалы и оборудование, что гарантирует долговечность и надежность результата. Кроме того, мы предоставляем гарантию на все виды работ. Вы можете быть уверены в качестве наших услуг!',
            'price' => 3500.00,
            'duration' => '45 минут',
            'doctor' => 'Иванов Иван Иванович',
            'image' => 'img/product1.jpg',
            'first_letter' => 'Л'
        ],
        [
            'title_value' => 'Профессиональная чистка зубов',
            'content' => 'Профессиональная чистка зубов с использованием ультразвука и Air Flow. Удаление зубного камня, налета и пигментации. Профессиональная чистка зубов - это комплекс процедур, направленных на удаление мягких и твердых зубных отложений. В процессе используется современное оборудование: ультразвуковой скейлер для удаления зубного камня и система Air Flow для удаления налета и пигментации. Процедура завершается полировкой зубов и покрытием их защитным фторсодержащим лаком. Регулярная профессиональная чистка помогает предотвратить развитие кариеса и заболеваний десен.',
            'price' => 4500.00,
            'duration' => '60 минут',
            'doctor' => 'Петрова Анна Сергеевна',
            'image' => 'img/product2.jpg',
            'first_letter' => 'П'
        ],
        [
            'title_value' => 'Имплантация зубов',
            'content' => 'Имплантация зубов с использованием премиальных имплантатов. Восстановление отсутствующих зубов с гарантией до 10 лет. Имплантация зубов - современный метод восстановления утраченных зубов. В нашей клинике процедура проводится опытными хирургами-имплантологами с использованием имплантатов премиум-класса от ведущих мировых производителей. Мы предлагаем как классическую двухэтапную имплантацию, так и немедленную нагрузку (установка протеза сразу после вживления импланта). На все импланты предоставляется гарантия до 10 лет.',
            'price' => 30000.00,
            'duration' => '90 минут',
            'doctor' => 'Смирнов Алексей Владимирович',
            'image' => 'img/product3.jpg',
            'first_letter' => 'И'
        ],
        [
            'title_value' => 'Исправление прикуса',
            'content' => 'Исправление прикуса с помощью брекет-систем и элайнеров. Индивидуальный подход к каждому пациенту. Наша клиника предлагает различные методы исправления прикуса: металлические и эстетические брекет-системы, капы-элайнеры (невидимые брекеты), несъемные и съемные ортодонтические аппараты. Перед началом лечения проводится полная диагностика, включающая компьютерную томографию и 3D-моделирование результата. Врач-ортодонт составляет индивидуальный план лечения с учетом особенностей вашего прикуса и пожеланий.',
            'price' => 80000.00,
            'duration' => 'от 1 года до 2 лет',
            'doctor' => 'Козлова Екатерина Дмитриевна',
            'image' => 'img/product4.jpg',
            'first_letter' => 'И'
        ],
        [
            'title_value' => 'Протезирование зубов',
            'content' => 'Протезирование зубов с использованием современных материалов. Коронки, мосты, съемные и несъемные протезы. Протезирование зубов в нашей клинике проводится с использованием современных материалов и технологий. Мы предлагаем различные виды протезов: металлокерамические и безметалловые коронки, мостовидные протезы, съемные и условно-съемные протезы, бюгельные конструкции. Для изготовления протезов используется цифровое моделирование и высокоточное фрезерование, что обеспечивает идеальное прилегание и долговечность конструкций.',
            'price' => 15000.00,
            'duration' => '30-60 минут',
            'doctor' => 'Николаев Сергей Александрович',
            'image' => 'img/product5.jpg',
            'first_letter' => 'П'
        ],
        [
            'title_value' => 'Отбеливание зубов',
            'content' => 'Профессиональное отбеливание зубов. Безопасное и эффективное осветление эмали на несколько тонов. В нашей клинике проводится профессиональное отбеливание зубов по различным технологиям: офисное отбеливание с применением специальных гелей и активирующих ламп (Zoom, Beyond), домашнее отбеливание с использованием индивидуальных капп, комбинированное отбеливание. Перед процедурой проводится диагностика состояния зубов и профессиональная чистка. Все методики безопасны для эмали и позволяют осветлить зубы на 2-8 тонов.',
            'price' => 8000.00,
            'duration' => '90 минут',
            'doctor' => 'Соколова Ольга Игоревна',
            'image' => 'img/product6.jpg',
            'first_letter' => 'О'
        ]
    ];
    
    // Добавляем услуги в базу данных
    $stmt = $pdo->prepare("INSERT INTO services (title_value, content, price, duration, doctor, image, first_letter) 
                          VALUES (:title_value, :content, :price, :duration, :doctor, :image, :first_letter)");
    
    foreach ($originalServices as $service) {
        $stmt->execute($service);
    }
    
    $output .= '<div class="success"><strong>Услуги успешно добавлены в таблицу services!</strong></div>';
    
    // Выводим список добавленных услуг
    $output .= '<table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Продолжительность</th>
            <th>Врач</th>
            <th>Изображение</th>
        </tr>';
    
    $services = $pdo->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($services as $service) {
        $output .= "<tr>
            <td>{$service['id']}</td>
            <td>{$service['title_value']}</td>
            <td>" . number_format($service['price'], 2, ',', ' ') . " ₽</td>
            <td>{$service['duration']}</td>
            <td>{$service['doctor']}</td>
            <td>{$service['image']}</td>
        </tr>";
    }
    
    $output .= '</table>';
    
    // Проверка наличия изображений, создание заглушек если их нет
    $output .= '<h2>Проверка наличия изображений услуг</h2>';
    for ($i = 1; $i <= 6; $i++) {
        $imagePath = "img/product{$i}.jpg";
        if (!file_exists($imagePath)) {
            // Создаем директорию если нужно
            if (!file_exists('img')) {
                mkdir('img', 0777, true);
            }
            
            // Создаем заглушку изображения
            $img = imagecreatetruecolor(400, 300);
            $bgColor = imagecolorallocate($img, 255, 165, 0); // Оранжевый фон
            imagefill($img, 0, 0, $bgColor);
            
            $textColor = imagecolorallocate($img, 255, 255, 255); // Белый текст
            $text = "Стоматологическая услуга {$i}";
            
            // Центрирование текста
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            $textHeight = imagefontheight($font);
            $x = (400 - $textWidth) / 2;
            $y = (300 - $textHeight) / 2;
            
            imagestring($img, $font, $x, $y, $text, $textColor);
            imagejpeg($img, $imagePath, 90);
            imagedestroy($img);
            
            $output .= "<div class='success'>Создано изображение-заглушка: {$imagePath}</div>";
        } else {
            $output .= "<div class='success'>Изображение {$imagePath} уже существует</div>";
        }
    }
    
    $output .= '<div class="success"><strong>Теперь ваши страницы product1.php, product2.php и т.д. будут работать корректно!</strong></div>';
    
    $output .= '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
    
} catch (PDOException $e) {
    $output .= '<div class="error">Ошибка при восстановлении таблицы services: ' . $e->getMessage() . '</div>';
    $output .= '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
}

$output .= '</body></html>';
echo $output; 