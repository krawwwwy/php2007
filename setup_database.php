<?php
session_start();
require_once 'db_connect.php';

// Заголовок и стиль для вывода сообщений
$output = '<html><head><title>Настройка базы данных</title>';
$output .= '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
    h1 { color: #0066cc; }
    .success { color: green; background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .error { color: red; background: #ffebee; padding: 10px; border-radius: 5px; margin: 5px 0; }
    .back-link { display: inline-block; margin-top: 20px; padding: 10px 15px; background: #0066cc; color: white; 
        text-decoration: none; border-radius: 5px; }
</style></head><body>';
$output .= '<h1>Настройка базы данных стоматологической клиники</h1>';

try {
    // SQL запросы для создания таблиц
    $sqlQueries = [
        // Таблица стоматологических услуг
        "CREATE TABLE IF NOT EXISTS services (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1",

        // Таблица свойств услуг
        "CREATE TABLE IF NOT EXISTS service_properties (
            id int(11) NOT NULL AUTO_INCREMENT,
            service_id int(11) NOT NULL,
            property_name varchar(255) NOT NULL,
            property_value varchar(255) NOT NULL,
            property_price decimal(20,2) NOT NULL DEFAULT '0.00',
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1",

        // Таблица изображений услуг
        "CREATE TABLE IF NOT EXISTS service_images (
            id int(11) NOT NULL AUTO_INCREMENT,
            service_id int(11) NOT NULL,
            image varchar(255) NOT NULL,
            title varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            UNIQUE KEY id (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1"
    ];

    // Выполнение запросов
    foreach ($sqlQueries as $query) {
        $pdo->exec($query);
        $output .= '<div class="success">Успешно выполнен запрос: ' . htmlspecialchars(substr($query, 0, 50) . '...') . '</div>';
    }

    // Добавление демо-данных
    // Удаляем существующие данные для чистой установки
    $pdo->exec("DELETE FROM service_images");
    $pdo->exec("DELETE FROM service_properties");
    $pdo->exec("DELETE FROM services");
    
    // Добавляем тестовые услуги
    $services = [
        [
            'category_id' => 1, // Терапия
            'name' => 'Лечение кариеса',
            'alias' => 'caries-treatment',
            'short_description' => 'Лечение кариеса любой сложности с использованием современных материалов.',
            'description' => 'Наши специалисты проведут тщательную диагностику и подберут оптимальный вариант лечения кариеса. Мы используем только современные материалы и оборудование, что позволяет проводить лечение безболезненно и с долговременным результатом.',
            'price' => 3500.00,
            'image' => 'img/services/caries.jpg',
            'available' => 1,
            'meta_keywords' => 'лечение кариеса, стоматология, пломбы, кариес',
            'meta_description' => 'Лечение кариеса любой сложности в стоматологической клинике Жемчуг',
            'meta_title' => 'Лечение кариеса - Стоматология Жемчуг'
        ],
        [
            'category_id' => 2, // Хирургия
            'name' => 'Удаление зуба',
            'alias' => 'tooth-extraction',
            'short_description' => 'Безболезненное удаление зубов с применением современного анестезиологического оборудования.',
            'description' => 'Процедура удаления зуба проводится нашими опытными хирургами-стоматологами с использованием эффективных анестетиков. В клинике применяются все необходимые меры для снижения рисков осложнений и обеспечения комфортного послеоперационного периода.',
            'price' => 2000.00,
            'image' => 'img/services/extraction.jpg',
            'available' => 1,
            'meta_keywords' => 'удаление зуба, хирургическая стоматология, экстракция зуба',
            'meta_description' => 'Безболезненное удаление зубов в стоматологической клинике Жемчуг',
            'meta_title' => 'Удаление зубов - Стоматология Жемчуг'
        ],
        [
            'category_id' => 3, // Ортопедия
            'name' => 'Установка металлокерамической коронки',
            'alias' => 'metal-ceramic-crown',
            'short_description' => 'Изготовление и установка качественных металлокерамических коронок.',
            'description' => 'Металлокерамические коронки – это надежный и эстетичный способ восстановления зуба. Коронка имеет металлический каркас, покрытый керамикой. Изделие точно повторяет анатомическую форму зуба и его оттенок. Срок службы такой коронки составляет от 7 до 15 лет.',
            'price' => 15000.00,
            'image' => 'img/services/crown.jpg',
            'available' => 1,
            'meta_keywords' => 'металлокерамическая коронка, ортопедическая стоматология, протезирование',
            'meta_description' => 'Установка металлокерамических коронок в стоматологической клинике Жемчуг',
            'meta_title' => 'Металлокерамические коронки - Стоматология Жемчуг'
        ],
        [
            'category_id' => 4, // Ортодонтия
            'name' => 'Установка брекет-системы',
            'alias' => 'braces-installation',
            'short_description' => 'Исправление прикуса с помощью современных брекет-систем.',
            'description' => 'Мы предлагаем различные виды брекет-систем: металлические, керамические, сапфировые и лингвальные. Наши ортодонты подберут оптимальный вариант, учитывая ваши пожелания и особенности клинического случая. Средний срок лечения составляет 1,5-2 года.',
            'price' => 45000.00,
            'image' => 'img/services/braces.jpg',
            'available' => 1,
            'meta_keywords' => 'брекеты, ортодонтия, исправление прикуса',
            'meta_description' => 'Установка брекет-систем в стоматологической клинике Жемчуг',
            'meta_title' => 'Брекет-системы - Стоматология Жемчуг'
        ],
        [
            'category_id' => 5, // Имплантология
            'name' => 'Установка импланта',
            'alias' => 'dental-implant',
            'short_description' => 'Имплантация зубов с использованием имплантатов премиум-класса.',
            'description' => 'Дентальная имплантация – современный метод восстановления утраченных зубов. Имплант представляет собой титановый штифт, который вживляется в костную ткань и заменяет корень зуба. На имплант устанавливается коронка, полностью восстанавливающая функциональность и эстетику зуба.',
            'price' => 30000.00,
            'image' => 'img/services/implant.jpg',
            'available' => 1,
            'meta_keywords' => 'имплантация зубов, дентальные импланты, имплантология',
            'meta_description' => 'Установка зубных имплантов в стоматологической клинике Жемчуг',
            'meta_title' => 'Имплантация зубов - Стоматология Жемчуг'
        ]
    ];
    
    // Добавляем услуги
    $serviceStmt = $pdo->prepare("INSERT INTO services 
        (category_id, name, alias, short_description, description, price, image, available, meta_keywords, meta_description, meta_title) VALUES 
        (:category_id, :name, :alias, :short_description, :description, :price, :image, :available, :meta_keywords, :meta_description, :meta_title)");
    
    $servicePropStmt = $pdo->prepare("INSERT INTO service_properties
        (service_id, property_name, property_value, property_price) VALUES
        (:service_id, :property_name, :property_value, :property_price)");
        
    $serviceImgStmt = $pdo->prepare("INSERT INTO service_images
        (service_id, image, title) VALUES
        (:service_id, :image, :title)");
    
    foreach ($services as $service) {
        $serviceStmt->execute($service);
        $serviceId = $pdo->lastInsertId();
        
        // Добавляем свойства в зависимости от категории услуги
        switch ($service['category_id']) {
            case 1: // Терапия (кариес)
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Сложность',
                    'property_value' => 'Средняя',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Длительность',
                    'property_value' => '45 минут',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Материал пломбы',
                    'property_value' => 'Светоотверждаемый композит',
                    'property_price' => 500
                ]);
                break;
                
            case 2: // Хирургия (удаление)
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Сложность',
                    'property_value' => 'Простое',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Длительность',
                    'property_value' => '30 минут',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Анестезия',
                    'property_value' => 'Местная',
                    'property_price' => 300
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Сложное удаление',
                    'property_value' => 'С рассечением',
                    'property_price' => 1500
                ]);
                break;
                
            case 3: // Ортопедия (коронка)
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Материал',
                    'property_value' => 'Металлокерамика',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Срок изготовления',
                    'property_value' => '7-10 дней',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Премиум материал',
                    'property_value' => 'E-max',
                    'property_price' => 5000
                ]);
                break;
                
            case 4: // Ортодонтия (брекеты)
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Тип брекетов',
                    'property_value' => 'Металлические',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Срок лечения',
                    'property_value' => '1.5-2 года',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Керамические брекеты',
                    'property_value' => 'Эстетичные',
                    'property_price' => 15000
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Сапфировые брекеты',
                    'property_value' => 'Премиум',
                    'property_price' => 25000
                ]);
                break;
                
            case 5: // Имплантология
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Производитель',
                    'property_value' => 'Straumann (Швейцария)',
                    'property_price' => 10000
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Срок приживления',
                    'property_value' => '3-6 месяцев',
                    'property_price' => 0
                ]);
                $servicePropStmt->execute([
                    'service_id' => $serviceId,
                    'property_name' => 'Синус-лифтинг',
                    'property_value' => 'При необходимости',
                    'property_price' => 15000
                ]);
                break;
        }
        
        // Добавляем дополнительные изображения
        $imageCount = rand(1, 3); // Случайное число изображений
        for ($i = 1; $i <= $imageCount; $i++) {
            $imgName = pathinfo($service['image'], PATHINFO_FILENAME);
            $imgExt = pathinfo($service['image'], PATHINFO_EXTENSION) ?: 'jpg';
            $imgPath = 'img/services/' . $imgName . '_add' . $i . '.' . $imgExt;
            
            $serviceImgStmt->execute([
                'service_id' => $serviceId, 
                'image' => $imgPath,
                'title' => 'Дополнительное изображение ' . $i . ' для ' . $service['name']
            ]);
        }
    }
    
    $output .= '<div class="success"><strong>Демонстрационные данные успешно добавлены!</strong></div>';
    $output .= '<div class="success">Добавлено 5 услуг с характеристиками и изображениями</div>';

    $output .= '<div class="success"><strong>Все таблицы успешно созданы!</strong></div>';
    $output .= '<a href="services.php" class="back-link">Перейти к управлению услугами</a>';
    
} catch (PDOException $e) {
    $output .= '<div class="error">Ошибка при настройке базы данных: ' . $e->getMessage() . '</div>';
    $output .= '<a href="index.php" class="back-link">На главную</a>';
}

$output .= '</body></html>';
echo $output; 