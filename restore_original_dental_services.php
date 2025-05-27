<?php
session_start();
require_once 'db_connect.php';

// Заголовок и стиль для вывода сообщений
$output = '<html><head><title>Восстановление оригинальных стоматологических услуг</title>';
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
$output .= '<h1>Восстановление оригинальных стоматологических услуг</h1>';

try {
    // Проверяем существование таблицы services
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() == 0) {
        // Создаем таблицу services если она не существует
        $sql = "CREATE TABLE IF NOT EXISTS services (
            id int(11) NOT NULL AUTO_INCREMENT,
            category_id smallint(6) NOT NULL,
            name varchar(255) NOT NULL,
            alias varchar(255) NOT NULL,
            short_description text NOT NULL,
            description text NOT NULL,
            price decimal(20,2) NOT NULL,
            image varchar(255) NOT NULL DEFAULT '',
            available smallint(1) NOT NULL DEFAULT '1',
            meta_keywords varchar(255) NOT NULL DEFAULT '',
            meta_description varchar(255) NOT NULL DEFAULT '',
            meta_title varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1";
        
        $pdo->exec($sql);
        $output .= '<div class="success">Таблица services создана</div>';
    }

    // Очищаем таблицу services, сбрасываем AUTO_INCREMENT
    $pdo->exec("DELETE FROM services");
    $pdo->exec("ALTER TABLE services AUTO_INCREMENT = 1");
    
    // Добавляем 6 оригинальных стоматологических услуг
    $originalServices = [
        [
            'id' => 1,
            'name' => 'Лечение зубов',
            'alias' => 'lechenie-zubov',
            'category_id' => 1, // Терапия
            'short_description' => 'Профессиональное лечение зубов любой сложности с применением современных технологий и материалов. Безболезненно, быстро, качественно.',
            'description' => 'Наша клиника предлагает полный спектр услуг по лечению зубов. Мы применяем современные методики и материалы, что позволяет сделать процесс лечения комфортным и безболезненным. Наши специалисты имеют большой опыт работы и постоянно совершенствуют свои навыки. Мы используем только качественные материалы и оборудование, что гарантирует долговечность и надежность результата. Кроме того, мы предоставляем гарантию на все виды работ. Вы можете быть уверены в качестве наших услуг!',
            'price' => 3500.00,
            'image' => 'img/product1.jpg',
            'available' => 1,
            'meta_keywords' => 'лечение зубов, стоматология, кариес, пульпит',
            'meta_description' => 'Профессиональное лечение зубов в стоматологической клинике Жемчуг',
            'meta_title' => 'Лечение зубов - Стоматология Жемчуг'
        ],
        [
            'id' => 2,
            'name' => 'Профессиональная чистка зубов',
            'alias' => 'prof-chistka',
            'category_id' => 1, // Терапия
            'short_description' => 'Профессиональная чистка зубов с использованием ультразвука и Air Flow. Удаление зубного камня, налета и пигментации.',
            'description' => 'Профессиональная чистка зубов - это комплекс процедур, направленных на удаление мягких и твердых зубных отложений. В процессе используется современное оборудование: ультразвуковой скейлер для удаления зубного камня и система Air Flow для удаления налета и пигментации. Процедура завершается полировкой зубов и покрытием их защитным фторсодержащим лаком. Регулярная профессиональная чистка помогает предотвратить развитие кариеса и заболеваний десен.',
            'price' => 4500.00,
            'image' => 'img/product2.jpg',
            'available' => 1,
            'meta_keywords' => 'чистка зубов, профессиональная гигиена, Air Flow, ультразвук',
            'meta_description' => 'Профессиональная чистка зубов в стоматологии Жемчуг',
            'meta_title' => 'Профессиональная чистка зубов - Стоматология Жемчуг'
        ],
        [
            'id' => 3,
            'name' => 'Имплантация зубов',
            'alias' => 'implantaciya',
            'category_id' => 2, // Хирургия
            'short_description' => 'Имплантация зубов с использованием премиальных имплантатов. Восстановление отсутствующих зубов с гарантией до 10 лет.',
            'description' => 'Имплантация зубов - современный метод восстановления утраченных зубов. В нашей клинике процедура проводится опытными хирургами-имплантологами с использованием имплантатов премиум-класса от ведущих мировых производителей. Мы предлагаем как классическую двухэтапную имплантацию, так и немедленную нагрузку (установка протеза сразу после вживления импланта). На все импланты предоставляется гарантия до 10 лет.',
            'price' => 30000.00,
            'image' => 'img/product3.jpg',
            'available' => 1,
            'meta_keywords' => 'имплантация зубов, импланты, протезирование зубов',
            'meta_description' => 'Имплантация зубов в стоматологической клинике Жемчуг',
            'meta_title' => 'Имплантация зубов - Стоматология Жемчуг'
        ],
        [
            'id' => 4,
            'name' => 'Исправление прикуса',
            'alias' => 'ortodontiya',
            'category_id' => 3, // Ортодонтия
            'short_description' => 'Исправление прикуса с помощью брекет-систем и элайнеров. Индивидуальный подход к каждому пациенту.',
            'description' => 'Наша клиника предлагает различные методы исправления прикуса: металлические и эстетические брекет-системы, капы-элайнеры (невидимые брекеты), несъемные и съемные ортодонтические аппараты. Перед началом лечения проводится полная диагностика, включающая компьютерную томографию и 3D-моделирование результата. Врач-ортодонт составляет индивидуальный план лечения с учетом особенностей вашего прикуса и пожеланий.',
            'price' => 80000.00,
            'image' => 'img/product4.jpg',
            'available' => 1,
            'meta_keywords' => 'исправление прикуса, брекеты, элайнеры, ортодонтия',
            'meta_description' => 'Исправление прикуса в стоматологической клинике Жемчуг',
            'meta_title' => 'Исправление прикуса - Стоматология Жемчуг'
        ],
        [
            'id' => 5,
            'name' => 'Протезирование зубов',
            'alias' => 'protezirovanie',
            'category_id' => 4, // Ортопедия
            'short_description' => 'Протезирование зубов с использованием современных материалов. Коронки, мосты, съемные и несъемные протезы.',
            'description' => 'Протезирование зубов в нашей клинике проводится с использованием современных материалов и технологий. Мы предлагаем различные виды протезов: металлокерамические и безметалловые коронки, мостовидные протезы, съемные и условно-съемные протезы, бюгельные конструкции. Для изготовления протезов используется цифровое моделирование и высокоточное фрезерование, что обеспечивает идеальное прилегание и долговечность конструкций.',
            'price' => 15000.00,
            'image' => 'img/product5.jpg',
            'available' => 1,
            'meta_keywords' => 'протезирование зубов, коронки, протезы, ортопедия',
            'meta_description' => 'Протезирование зубов в стоматологической клинике Жемчуг',
            'meta_title' => 'Протезирование зубов - Стоматология Жемчуг'
        ],
        [
            'id' => 6,
            'name' => 'Отбеливание зубов',
            'alias' => 'otbelivanie',
            'category_id' => 5, // Эстетическая стоматология
            'short_description' => 'Профессиональное отбеливание зубов. Безопасное и эффективное осветление эмали на несколько тонов.',
            'description' => 'В нашей клинике проводится профессиональное отбеливание зубов по различным технологиям: офисное отбеливание с применением специальных гелей и активирующих ламп (Zoom, Beyond), домашнее отбеливание с использованием индивидуальных капп, комбинированное отбеливание. Перед процедурой проводится диагностика состояния зубов и профессиональная чистка. Все методики безопасны для эмали и позволяют осветлить зубы на 2-8 тонов.',
            'price' => 8000.00,
            'image' => 'img/product6.jpg',
            'available' => 1,
            'meta_keywords' => 'отбеливание зубов, zoom, beyond, эстетическая стоматология',
            'meta_description' => 'Профессиональное отбеливание зубов в стоматологической клинике Жемчуг',
            'meta_title' => 'Отбеливание зубов - Стоматология Жемчуг'
        ]
    ];
    
    // Добавляем услуги в базу данных
    foreach ($originalServices as $service) {
        $id = $service['id'];
        unset($service['id']);
        
        $columns = implode(', ', array_keys($service));
        $placeholders = ':' . implode(', :', array_keys($service));
        
        $sql = "INSERT INTO services (id, $columns) VALUES ($id, $placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($service);
    }
    
    $output .= '<div class="success"><strong>Оригинальные стоматологические услуги успешно восстановлены!</strong></div>';
    
    // Выводим список добавленных услуг
    $output .= '<table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
        </tr>';
    
    foreach ($originalServices as $service) {
        $output .= "<tr>
            <td>{$service['id']}</td>
            <td>{$service['name']}</td>
            <td>Категория {$service['category_id']}</td>
            <td>" . number_format($service['price'], 2, ',', ' ') . " ₽</td>
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
    
    $output .= '<div class="back-link"><a href="services.php" style="color: white; text-decoration: none;">Перейти к управлению услугами</a></div>';
    
} catch (PDOException $e) {
    $output .= '<div class="error">Ошибка при восстановлении стоматологических услуг: ' . $e->getMessage() . '</div>';
    $output .= '<div class="back-link"><a href="index.php" style="color: white; text-decoration: none;">На главную</a></div>';
}

$output .= '</body></html>';
echo $output; 