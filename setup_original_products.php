<?php
session_start();
require_once 'db_connect.php';

// Заголовок и стиль для вывода сообщений
$output = '<html><head><title>Восстановление оригинальных товаров</title>';
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
$output .= '<h1>Восстановление оригинальных товаров</h1>';

try {
    // Проверяем существование таблиц
    $tablesExist = true;
    $tables = ['services', 'service_properties', 'service_images'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            $tablesExist = false;
            break;
        }
    }
    
    // Если таблиц нет, создаем их
    if (!$tablesExist) {
        $sqlQueries = [
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

            "CREATE TABLE IF NOT EXISTS service_properties (
                id int(11) NOT NULL AUTO_INCREMENT,
                service_id int(11) NOT NULL,
                property_name varchar(255) NOT NULL,
                property_value varchar(255) NOT NULL,
                property_price decimal(20,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (id),
                UNIQUE KEY id (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1",

            "CREATE TABLE IF NOT EXISTS service_images (
                id int(11) NOT NULL AUTO_INCREMENT,
                service_id int(11) NOT NULL,
                image varchar(255) NOT NULL,
                title varchar(255) NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                UNIQUE KEY id (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1"
        ];

        foreach ($sqlQueries as $query) {
            $pdo->exec($query);
            $output .= '<div class="success">Создана таблица: ' . htmlspecialchars(substr($query, 0, 50) . '...') . '</div>';
        }
    }
    
    // Удаляем существующие данные
    $pdo->exec("DELETE FROM service_images");
    $pdo->exec("DELETE FROM service_properties");
    $pdo->exec("DELETE FROM services");
    
    // Сбрасываем AUTO_INCREMENT
    $pdo->exec("ALTER TABLE services AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE service_properties AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE service_images AUTO_INCREMENT = 1");
    
    // Оригинальные товары (совместимые с product1.php, product2.php и т.д.)
    $products = [
        [
            'id' => 1,
            'name' => 'Телевизор Samsung UE50AU7100U',
            'alias' => 'samsung-ue50au7100u',
            'category_id' => 1, // Электроника
            'short_description' => '50" (127 см) LED-телевизор Samsung UE50AU7100U черный',
            'description' => 'Телевизор Samsung UE50AU7100U оснащен 50-дюймовым экраном с разрешением 4K UHD (3840x2160) и частотой обновления 60 Гц. Модель поддерживает технологии HDR, имеет мощность звука 20 Вт, интерфейсы HDMI x3 и USB x1.',
            'price' => 43999.00,
            'image' => 'img/products/tv_samsung.jpg',
            'available' => 1,
            'meta_keywords' => 'телевизор, Samsung, 4K, UHD, Smart TV',
            'meta_description' => '50" (127 см) LED-телевизор Samsung UE50AU7100U с разрешением 4K UHD и Smart TV',
            'meta_title' => 'Телевизор Samsung UE50AU7100U - купить по выгодной цене'
        ],
        [
            'id' => 2,
            'name' => 'Смартфон Apple iPhone 13',
            'alias' => 'apple-iphone-13',
            'category_id' => 1, // Электроника
            'short_description' => 'Смартфон Apple iPhone 13 128GB синий',
            'description' => 'Apple iPhone 13 оснащен дисплеем Super Retina XDR 6,1", процессором A15 Bionic, двойной камерой 12 Мп + 12 Мп, поддержкой Face ID и быстрой зарядкой. Память устройства составляет 128 ГБ.',
            'price' => 79990.00,
            'image' => 'img/products/iphone_13.jpg',
            'available' => 1,
            'meta_keywords' => 'смартфон, Apple, iPhone, iPhone 13',
            'meta_description' => 'Смартфон Apple iPhone 13 128GB с мощным процессором A15 Bionic',
            'meta_title' => 'Купить Apple iPhone 13 128GB по выгодной цене'
        ],
        [
            'id' => 3,
            'name' => 'Ноутбук ASUS VivoBook 15',
            'alias' => 'asus-vivobook-15',
            'category_id' => 1, // Электроника
            'short_description' => 'Ноутбук ASUS VivoBook 15 X515EA-BQ1189 серебристый',
            'description' => 'ASUS VivoBook 15 X515EA оснащен 15,6" Full HD-экраном, процессором Intel Core i5-1135G7, 8 ГБ оперативной памяти, SSD-накопителем 512 ГБ и видеокартой Intel Iris Xe Graphics.',
            'price' => 54999.00,
            'image' => 'img/products/asus_vivobook.jpg',
            'available' => 1,
            'meta_keywords' => 'ноутбук, ASUS, VivoBook, Intel Core i5',
            'meta_description' => '15.6" Ноутбук ASUS VivoBook 15 с процессором Intel Core i5',
            'meta_title' => 'Ноутбук ASUS VivoBook 15 X515EA - мощный и доступный'
        ],
        [
            'id' => 4,
            'name' => 'Холодильник Bosch KGN39VL21R',
            'alias' => 'bosch-kgn39vl21r',
            'category_id' => 2, // Бытовая техника
            'short_description' => 'Холодильник Bosch KGN39VL21R с системой No Frost',
            'description' => 'Bosch KGN39VL21R - двухкамерный холодильник с системой No Frost, объемом 388 л, классом энергопотребления A+, цифровым управлением, шириной 60 см и уровнем шума 43 дБ.',
            'price' => 47990.00,
            'image' => 'img/products/bosch_fridge.jpg',
            'available' => 1,
            'meta_keywords' => 'холодильник, Bosch, NoFrost, двухкамерный',
            'meta_description' => 'Холодильник Bosch KGN39VL21R с системой No Frost и объемом 388 литров',
            'meta_title' => 'Купить холодильник Bosch KGN39VL21R по выгодной цене'
        ],
        [
            'id' => 5,
            'name' => 'Стиральная машина LG F2V3GS6W',
            'alias' => 'lg-f2v3gs6w',
            'category_id' => 2, // Бытовая техника
            'short_description' => 'Стиральная машина LG F2V3GS6W с инверторным мотором',
            'description' => 'Стиральная машина LG F2V3GS6W имеет загрузку 7 кг, скорость отжима 1200 об/мин, класс энергопотребления А+++ и множество режимов стирки. Оснащена инверторным двигателем с прямым приводом и технологией AI DD.',
            'price' => 36990.00,
            'image' => 'img/products/lg_washer.jpg',
            'available' => 1,
            'meta_keywords' => 'стиральная машина, LG, инверторный мотор',
            'meta_description' => 'Стиральная машина LG F2V3GS6W с инверторным мотором и загрузкой 7 кг',
            'meta_title' => 'Стиральная машина LG F2V3GS6W - надежное качество'
        ],
        [
            'id' => 6,
            'name' => 'Кофемашина DeLonghi ECAM 370.95.T',
            'alias' => 'delonghi-ecam-370-95-t',
            'category_id' => 2, // Бытовая техника
            'short_description' => 'Автоматическая кофемашина DeLonghi ECAM 370.95.T',
            'description' => 'Автоматическая кофемашина DeLonghi ECAM 370.95.T с сенсорным управлением, мощностью 1450 Вт и давлением 19 бар. Оснащена встроенной кофемолкой, капучинатором и системой автоочистки.',
            'price' => 89999.00,
            'image' => 'img/products/delonghi_coffee.jpg',
            'available' => 1,
            'meta_keywords' => 'кофемашина, DeLonghi, автоматическая, капучинатор',
            'meta_description' => 'Автоматическая кофемашина DeLonghi ECAM 370.95.T с капучинатором',
            'meta_title' => 'Кофемашина DeLonghi ECAM 370.95.T - идеальный кофе дома'
        ],
        [
            'id' => 7,
            'name' => 'Диван-кровать IKEA БЕДИНГЕ',
            'alias' => 'ikea-bedinge',
            'category_id' => 3, // Мебель
            'short_description' => 'Трехместный диван-кровать IKEA БЕДИНГЕ с чехлом',
            'description' => 'Диван-кровать IKEA БЕДИНГЕ легко трансформируется в полноценную кровать. Имеет надежный металлический каркас, съемный чехол, который можно стирать, и вместительный ящик для хранения.',
            'price' => 24990.00,
            'image' => 'img/products/ikea_sofa.jpg',
            'available' => 1,
            'meta_keywords' => 'диван, кровать, ИКЕА, БЕДИНГЕ, мебель',
            'meta_description' => 'Трехместный диван-кровать IKEA БЕДИНГЕ со съемным чехлом',
            'meta_title' => 'Диван-кровать IKEA БЕДИНГЕ - функциональность и комфорт'
        ],
        [
            'id' => 8,
            'name' => 'Кухонный гарнитур LEROY MERLIN Delinia',
            'alias' => 'leroy-merlin-delinia',
            'category_id' => 3, // Мебель
            'short_description' => 'Модульный кухонный гарнитур LEROY MERLIN Delinia',
            'description' => 'Модульный кухонный гарнитур LEROY MERLIN Delinia состоит из напольных и настенных шкафов, столешницы и пенала. Фасады выполнены из МДФ с глянцевым покрытием, фурнитура с доводчиками.',
            'price' => 79990.00,
            'image' => 'img/products/leroy_kitchen.jpg',
            'available' => 1,
            'meta_keywords' => 'кухня, гарнитур, LEROY MERLIN, Delinia',
            'meta_description' => 'Модульный кухонный гарнитур LEROY MERLIN Delinia с глянцевыми фасадами',
            'meta_title' => 'Кухонный гарнитур LEROY MERLIN Delinia - современный дизайн для вашей кухни'
        ]
    ];
    
    // Добавляем товары с фиксированными ID
    foreach ($products as $product) {
        $id = $product['id'];
        unset($product['id']); // Удаляем ID из массива атрибутов
        
        $stmt = $pdo->prepare("INSERT INTO services (id, category_id, name, alias, short_description, description, price, image, available, meta_keywords, meta_description, meta_title) VALUES 
        (:id, :category_id, :name, :alias, :short_description, :description, :price, :image, :available, :meta_keywords, :meta_description, :meta_title)");
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $product['category_id'], PDO::PARAM_INT);
        $stmt->bindParam(':name', $product['name'], PDO::PARAM_STR);
        $stmt->bindParam(':alias', $product['alias'], PDO::PARAM_STR);
        $stmt->bindParam(':short_description', $product['short_description'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $product['description'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $product['price'], PDO::PARAM_STR);
        $stmt->bindParam(':image', $product['image'], PDO::PARAM_STR);
        $stmt->bindParam(':available', $product['available'], PDO::PARAM_INT);
        $stmt->bindParam(':meta_keywords', $product['meta_keywords'], PDO::PARAM_STR);
        $stmt->bindParam(':meta_description', $product['meta_description'], PDO::PARAM_STR);
        $stmt->bindParam(':meta_title', $product['meta_title'], PDO::PARAM_STR);
        
        $stmt->execute();
    }
    
    // Добавляем свойства для товаров
    $properties = [
        // Телевизор Samsung
        ['service_id' => 1, 'property_name' => 'Диагональ', 'property_value' => '50" (127 см)', 'property_price' => 0],
        ['service_id' => 1, 'property_name' => 'Разрешение', 'property_value' => '3840x2160 (4K UHD)', 'property_price' => 0],
        ['service_id' => 1, 'property_name' => 'Smart TV', 'property_value' => 'Есть, Tizen', 'property_price' => 0],
        
        // iPhone 13
        ['service_id' => 2, 'property_name' => 'Память', 'property_value' => '128 ГБ', 'property_price' => 0],
        ['service_id' => 2, 'property_name' => 'Процессор', 'property_value' => 'A15 Bionic', 'property_price' => 0],
        ['service_id' => 2, 'property_name' => 'Камера', 'property_value' => '12 Мп + 12 Мп', 'property_price' => 0],
        
        // Ноутбук ASUS
        ['service_id' => 3, 'property_name' => 'Процессор', 'property_value' => 'Intel Core i5-1135G7', 'property_price' => 0],
        ['service_id' => 3, 'property_name' => 'Оперативная память', 'property_value' => '8 ГБ', 'property_price' => 0],
        ['service_id' => 3, 'property_name' => 'Накопитель', 'property_value' => 'SSD 512 ГБ', 'property_price' => 0],
        
        // Холодильник Bosch
        ['service_id' => 4, 'property_name' => 'Объем', 'property_value' => '388 л', 'property_price' => 0],
        ['service_id' => 4, 'property_name' => 'Система размораживания', 'property_value' => 'No Frost', 'property_price' => 0],
        ['service_id' => 4, 'property_name' => 'Класс энергопотребления', 'property_value' => 'A+', 'property_price' => 0],
        
        // Стиральная машина LG
        ['service_id' => 5, 'property_name' => 'Загрузка', 'property_value' => '7 кг', 'property_price' => 0],
        ['service_id' => 5, 'property_name' => 'Скорость отжима', 'property_value' => '1200 об/мин', 'property_price' => 0],
        ['service_id' => 5, 'property_name' => 'Тип двигателя', 'property_value' => 'Инверторный', 'property_price' => 0],
        
        // Кофемашина DeLonghi
        ['service_id' => 6, 'property_name' => 'Мощность', 'property_value' => '1450 Вт', 'property_price' => 0],
        ['service_id' => 6, 'property_name' => 'Давление', 'property_value' => '19 бар', 'property_price' => 0],
        ['service_id' => 6, 'property_name' => 'Управление', 'property_value' => 'Сенсорное', 'property_price' => 0],
        
        // Диван IKEA
        ['service_id' => 7, 'property_name' => 'Материал обивки', 'property_value' => 'Текстиль', 'property_price' => 0],
        ['service_id' => 7, 'property_name' => 'Размер спального места', 'property_value' => '140x200 см', 'property_price' => 0],
        ['service_id' => 7, 'property_name' => 'Механизм трансформации', 'property_value' => 'Книжка', 'property_price' => 0],
        
        // Кухонный гарнитур
        ['service_id' => 8, 'property_name' => 'Материал фасадов', 'property_value' => 'МДФ глянец', 'property_price' => 0],
        ['service_id' => 8, 'property_name' => 'Количество модулей', 'property_value' => '7 шт', 'property_price' => 0],
        ['service_id' => 8, 'property_name' => 'Длина гарнитура', 'property_value' => '2.4 м', 'property_price' => 0]
    ];
    
    $propStmt = $pdo->prepare("INSERT INTO service_properties (service_id, property_name, property_value, property_price) VALUES (:service_id, :property_name, :property_value, :property_price)");
    
    foreach ($properties as $property) {
        $propStmt->execute($property);
    }
    
    // Добавляем дополнительные изображения для товаров
    $images = [
        ['service_id' => 1, 'image' => 'img/products/tv_samsung_1.jpg', 'title' => 'Вид спереди'],
        ['service_id' => 1, 'image' => 'img/products/tv_samsung_2.jpg', 'title' => 'Вид сбоку'],
        
        ['service_id' => 2, 'image' => 'img/products/iphone_13_1.jpg', 'title' => 'Вид спереди'],
        ['service_id' => 2, 'image' => 'img/products/iphone_13_2.jpg', 'title' => 'Вид сзади'],
        
        ['service_id' => 3, 'image' => 'img/products/asus_vivobook_1.jpg', 'title' => 'Вид сбоку'],
        ['service_id' => 3, 'image' => 'img/products/asus_vivobook_2.jpg', 'title' => 'Клавиатура'],
        
        ['service_id' => 4, 'image' => 'img/products/bosch_fridge_1.jpg', 'title' => 'Внутреннее пространство'],
        
        ['service_id' => 5, 'image' => 'img/products/lg_washer_1.jpg', 'title' => 'Панель управления'],
        
        ['service_id' => 6, 'image' => 'img/products/delonghi_coffee_1.jpg', 'title' => 'Приготовление кофе'],
        
        ['service_id' => 7, 'image' => 'img/products/ikea_sofa_1.jpg', 'title' => 'В разложенном виде'],
        
        ['service_id' => 8, 'image' => 'img/products/leroy_kitchen_1.jpg', 'title' => 'Другой ракурс']
    ];
    
    $imgStmt = $pdo->prepare("INSERT INTO service_images (service_id, image, title) VALUES (:service_id, :image, :title)");
    
    foreach ($images as $image) {
        $imgStmt->execute($image);
    }
    
    // Выводим список добавленных товаров
    $output .= '<div class="success"><strong>Восстановлены оригинальные товары!</strong></div>';
    
    $output .= '<table>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Цена</th>
        </tr>';
    
    foreach ($products as $product) {
        $id = $product['id'];
        $output .= "<tr>
            <td>{$id}</td>
            <td>{$product['name']}</td>
            <td>Категория {$product['category_id']}</td>
            <td>" . number_format($product['price'], 2, ',', ' ') . " ₽</td>
        </tr>";
    }
    
    $output .= '</table>';
    
    // Создание директории для изображений
    if (!file_exists('img/products')) {
        mkdir('img/products', 0777, true);
    }
    
    $output .= '<div class="success">Создана директория для изображений товаров: img/products</div>';
    $output .= '<div class="success"><strong>Теперь ваши страницы product1.php, product2.php и т.д. будут работать корректно!</strong></div>';
    $output .= '<a href="services.php" class="back-link">Перейти к управлению товарами</a>';
    
} catch (PDOException $e) {
    $output .= '<div class="error">Ошибка при восстановлении оригинальных товаров: ' . $e->getMessage() . '</div>';
    $output .= '<a href="index.php" class="back-link">На главную</a>';
}

$output .= '</body></html>';
echo $output; 