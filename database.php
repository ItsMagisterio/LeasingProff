<?php
/**
 * Класс для работы с базой данных
 * 
 * Обеспечивает соединение с PostgreSQL и основные функции для работы с данными
 */
class Database {
    private $connection;
    private static $instance = null;
    
    /**
     * Конструктор класса
     * Устанавливает соединение с базой данных
     */
    private function __construct() {
        $dbUrl = getenv('DATABASE_URL');
        $dbHost = getenv('PGHOST');
        $dbPort = getenv('PGPORT');
        $dbUser = getenv('PGUSER');
        $dbPass = getenv('PGPASSWORD');
        $dbName = getenv('PGDATABASE');

        try {
            $this->connection = pg_connect("host=$dbHost port=$dbPort dbname=$dbName user=$dbUser password=$dbPass");
            if (!$this->connection) {
                throw new Exception("Не удалось подключиться к базе данных: " . pg_last_error());
            }
        } catch (Exception $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }

        // Инициализируем базу данных при первом подключении
        $this->initDatabase();
    }
    
    /**
     * Экранирование строки для безопасного использования в SQL запросах
     */
    public function escapeString($string) {
        return pg_escape_string($this->connection, $string);
    }

    /**
     * Получить экземпляр класса (singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Создаёт необходимые таблицы если они не существуют
     */
    private function initDatabase() {
        // Создаем таблицу пользователей
        $query = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            role VARCHAR(50) NOT NULL DEFAULT 'client',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->query($query);

        // Создаем таблицу автомобилей
        $query = "CREATE TABLE IF NOT EXISTS vehicles (
            id SERIAL PRIMARY KEY,
            make VARCHAR(100) NOT NULL,
            model VARCHAR(100) NOT NULL,
            year INTEGER NOT NULL,
            engine VARCHAR(50) NOT NULL,
            power INTEGER NOT NULL,
            drive_type VARCHAR(50) NOT NULL,
            transmission VARCHAR(50) NOT NULL,
            color VARCHAR(50) NOT NULL,
            interior VARCHAR(100) NOT NULL,
            features TEXT,
            image_url TEXT,
            price NUMERIC(12, 2) NOT NULL,
            monthly_payment NUMERIC(12, 2) NOT NULL,
            status VARCHAR(20) DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->query($query);

        // Создаем таблицу недвижимости
        $query = "CREATE TABLE IF NOT EXISTS real_estate (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL, -- квартира, дом, коммерческая недвижимость и т.д.
            address TEXT NOT NULL,
            area NUMERIC(10, 2) NOT NULL, -- площадь в кв.м.
            rooms INTEGER, -- количество комнат (для жилой недвижимости)
            floor INTEGER, -- этаж (для квартир)
            total_floors INTEGER, -- всего этажей в здании
            build_year INTEGER, -- год постройки
            description TEXT,
            features TEXT, -- особенности (парковка, охрана и т.д.)
            image_url TEXT,
            price NUMERIC(12, 2) NOT NULL,
            monthly_payment NUMERIC(12, 2) NOT NULL,
            status VARCHAR(20) DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->query($query);
        
        // Создаем таблицу заявок
        $query = "CREATE TABLE IF NOT EXISTS applications (
            id SERIAL PRIMARY KEY,
            user_id INTEGER REFERENCES users(id),
            vehicle_id INTEGER REFERENCES vehicles(id) NULL,
            real_estate_id INTEGER REFERENCES real_estate(id) NULL,
            manager_id INTEGER REFERENCES users(id) NULL,
            type VARCHAR(20) NOT NULL DEFAULT 'vehicle', -- тип заявки: vehicle или real_estate
            status VARCHAR(50) DEFAULT 'new',
            initial_payment NUMERIC(12, 2) NOT NULL,
            term_months INTEGER NOT NULL,
            monthly_payment NUMERIC(12, 2) NOT NULL,
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->query($query);

        // Добавляем тестовые данные, если база пустая
        $this->seedDatabaseIfEmpty();
    }

    /**
     * Заполнение базы данных тестовыми данными
     */
    private function seedDatabaseIfEmpty() {
        // Проверяем, есть ли пользователи в базе
        $result = $this->query("SELECT COUNT(*) FROM users");
        $row = pg_fetch_row($result);
        
        if ($row[0] == 0) {
            // Добавляем тестовых пользователей
            $password = password_hash('password', PASSWORD_DEFAULT);
            
            // Администратор
            $this->query("INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES ('admin@2leasing.ru', '$password', 'Андрей', 'Волков', '+7(901)123-4567', 'admin')");
            
            // Менеджеры
            $this->query("INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES ('manager1@2leasing.ru', '$password', 'Алексей', 'Смирнов', '+7(902)123-4567', 'manager')");
            $this->query("INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES ('manager2@2leasing.ru', '$password', 'Елена', 'Михайлова', '+7(903)123-4567', 'manager')");
            
            // Клиенты
            $this->query("INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES ('client@2leasing.ru', '$password', 'Иван', 'Петров', '+7(904)123-4567', 'client')");
            $this->query("INSERT INTO users (email, password, first_name, last_name, phone, role) 
                VALUES ('maria@example.com', '$password', 'Мария', 'Иванова', '+7(905)123-4567', 'client')");

            // Добавляем автомобили
            $vehicles = [
                [
                    'make' => 'BMW', 
                    'model' => 'X5', 
                    'year' => 2024, 
                    'engine' => '3.0L', 
                    'power' => 340, 
                    'drive_type' => 'полный',
                    'transmission' => 'автомат', 
                    'color' => 'черный', 
                    'interior' => 'кожаный черный', 
                    'features' => 'панорамная крыша,адаптивный круиз-контроль,мультимедиа BMW Professional', 
                    'image_url' => 'https://images.unsplash.com/photo-1556189250-72ba954cfc2b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 7650000, 
                    'monthly_payment' => 85000
                ],
                [
                    'make' => 'Mercedes-Benz', 
                    'model' => 'E-Class', 
                    'year' => 2024, 
                    'engine' => '2.0L', 
                    'power' => 258, 
                    'drive_type' => 'задний',
                    'transmission' => 'автомат', 
                    'color' => 'серебристый', 
                    'interior' => 'кожаный коричневый', 
                    'features' => 'навигация,подогрев сидений,система MBUX', 
                    'image_url' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 5850000, 
                    'monthly_payment' => 65000
                ],
                [
                    'make' => 'Audi', 
                    'model' => 'Q7', 
                    'year' => 2023, 
                    'engine' => '3.0L', 
                    'power' => 249, 
                    'drive_type' => 'полный',
                    'transmission' => 'автомат', 
                    'color' => 'белый', 
                    'interior' => 'кожаный бежевый', 
                    'features' => '7 мест,виртуальная приборная панель,панорамная крыша', 
                    'image_url' => 'https://images.unsplash.com/photo-1608329985118-887191f6dd8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 7100000, 
                    'monthly_payment' => 79000
                ],
                [
                    'make' => 'Toyota', 
                    'model' => 'Camry', 
                    'year' => 2024, 
                    'engine' => '3.5L', 
                    'power' => 249, 
                    'drive_type' => 'передний',
                    'transmission' => 'автомат', 
                    'color' => 'красный', 
                    'interior' => 'кожаный черный', 
                    'features' => 'JBL аудиосистема,круиз-контроль,камера заднего вида', 
                    'image_url' => 'https://images.unsplash.com/photo-1621007690695-33e84c0ea918?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 4050000, 
                    'monthly_payment' => 45000
                ],
                [
                    'make' => 'Volkswagen', 
                    'model' => 'Tiguan', 
                    'year' => 2023, 
                    'engine' => '2.0L', 
                    'power' => 180, 
                    'drive_type' => 'полный',
                    'transmission' => 'робот', 
                    'color' => 'синий', 
                    'interior' => 'ткань черная', 
                    'features' => 'панорамная крыша,адаптивный круиз-контроль,система помощи при парковке', 
                    'image_url' => 'https://images.unsplash.com/photo-1606664914738-f57686f9c8b1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 4680000, 
                    'monthly_payment' => 52000
                ],
                [
                    'make' => 'KIA', 
                    'model' => 'Sportage', 
                    'year' => 2023, 
                    'engine' => '2.0L', 
                    'power' => 185, 
                    'drive_type' => 'полный',
                    'transmission' => 'автомат', 
                    'color' => 'серый', 
                    'interior' => 'кожаный черный', 
                    'features' => 'система предотвращения столкновений,панорамная крыша,камера заднего вида', 
                    'image_url' => 'https://images.unsplash.com/photo-1641844180429-baab126f41b0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80', 
                    'price' => 3420000, 
                    'monthly_payment' => 38000
                ]
            ];

            foreach ($vehicles as $vehicle) {
                $this->query("INSERT INTO vehicles (make, model, year, engine, power, drive_type, transmission, color, interior, 
                    features, image_url, price, monthly_payment) 
                VALUES (
                    '{$vehicle['make']}', 
                    '{$vehicle['model']}', 
                    {$vehicle['year']}, 
                    '{$vehicle['engine']}', 
                    {$vehicle['power']}, 
                    '{$vehicle['drive_type']}', 
                    '{$vehicle['transmission']}', 
                    '{$vehicle['color']}', 
                    '{$vehicle['interior']}', 
                    '{$vehicle['features']}', 
                    '{$vehicle['image_url']}', 
                    {$vehicle['price']}, 
                    {$vehicle['monthly_payment']}
                )");
            }

            // Добавляем объекты недвижимости
            $realEstateObjects = [
                [
                    'title' => 'Современная квартира в центре',
                    'type' => 'apartment',
                    'address' => 'Москва, ул. Тверская, 15',
                    'area' => 85.7,
                    'rooms' => 3,
                    'floor' => 7,
                    'total_floors' => 12,
                    'build_year' => 2019,
                    'description' => 'Просторная квартира с современным ремонтом в центре города. Отличный вид из окон, удобная планировка, встроенная кухня.',
                    'features' => 'подземный паркинг,охрана,видеонаблюдение,лифт,консьерж',
                    'image_url' => 'https://images.unsplash.com/photo-1622015663319-fa394f0e1b81?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 25000000,
                    'monthly_payment' => 120000
                ],
                [
                    'title' => 'Коттедж в загородном поселке',
                    'type' => 'house',
                    'address' => 'Московская область, пос. Лесной, ул. Сосновая, 8',
                    'area' => 185.0,
                    'rooms' => 5,
                    'floor' => 2,
                    'total_floors' => 2,
                    'build_year' => 2021,
                    'description' => 'Двухэтажный коттедж в закрытом коттеджном поселке. Участок 12 соток, гараж на 2 машины, баня.',
                    'features' => 'участок 12 соток,гараж,баня,камин,теплые полы,газовое отопление',
                    'image_url' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 18500000,
                    'monthly_payment' => 95000
                ],
                [
                    'title' => 'Офисное помещение в бизнес-центре',
                    'type' => 'commercial',
                    'address' => 'Москва, Пресненская наб., 12, Башня "Федерация"',
                    'area' => 120.5,
                    'rooms' => 3,
                    'floor' => 35,
                    'total_floors' => 95,
                    'build_year' => 2017,
                    'description' => 'Престижный офис в Москва-Сити с панорамными окнами. Готов к въезду, современная отделка, системы кондиционирования и вентиляции.',
                    'features' => 'отдельный вход,система контроля доступа,конференц-зал,парковка,высокоскоростной интернет',
                    'image_url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 42000000,
                    'monthly_payment' => 180000
                ],
                [
                    'title' => 'Студия в новостройке',
                    'type' => 'apartment',
                    'address' => 'Санкт-Петербург, ул. Невская, 25',
                    'area' => 32.8,
                    'rooms' => 1,
                    'floor' => 9,
                    'total_floors' => 22,
                    'build_year' => 2022,
                    'description' => 'Компактная студия с качественной отделкой в новом жилом комплексе. Отличная инфраструктура, рядом метро и парк.',
                    'features' => 'охраняемая территория,детская площадка,фитнес-центр,парковка,видеонаблюдение',
                    'image_url' => 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 6800000,
                    'monthly_payment' => 38000
                ],
                [
                    'title' => 'Торговое помещение в ТЦ',
                    'type' => 'commercial',
                    'address' => 'Москва, Кутузовский пр-т, 57, ТЦ "Времена года"',
                    'area' => 78.3,
                    'rooms' => 1,
                    'floor' => 2,
                    'total_floors' => 4,
                    'build_year' => 2015,
                    'description' => 'Торговое помещение в престижном торговом центре. Большая проходимость, витринные окна, качественная отделка.',
                    'features' => 'центральное кондиционирование,охрана,система пожаротушения,погрузочная зона,реклама на фасаде',
                    'image_url' => 'https://images.unsplash.com/photo-1604719312566-8912e9667d9f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 32000000,
                    'monthly_payment' => 150000
                ]
            ];
            
            foreach ($realEstateObjects as $realEstate) {
                $this->query("INSERT INTO real_estate (title, type, address, area, rooms, floor, total_floors, build_year, description, features, image_url, price, monthly_payment) 
                VALUES (
                    '{$this->escapeString($realEstate['title'])}', 
                    '{$this->escapeString($realEstate['type'])}', 
                    '{$this->escapeString($realEstate['address'])}', 
                    {$realEstate['area']}, 
                    {$realEstate['rooms']}, 
                    {$realEstate['floor']}, 
                    {$realEstate['total_floors']}, 
                    {$realEstate['build_year']}, 
                    '{$this->escapeString($realEstate['description'])}', 
                    '{$this->escapeString($realEstate['features'])}', 
                    '{$this->escapeString($realEstate['image_url'])}', 
                    {$realEstate['price']}, 
                    {$realEstate['monthly_payment']}
                )");
            }

            // Добавляем тестовые заявки на автомобили
            $this->query("INSERT INTO applications (user_id, vehicle_id, type, manager_id, status, initial_payment, term_months, monthly_payment, comments) 
                VALUES (4, 1, 'vehicle', 2, 'approved', 1500000, 36, 85000, 'Одобрено без замечаний')");
            
            $this->query("INSERT INTO applications (user_id, vehicle_id, type, manager_id, status, initial_payment, term_months, monthly_payment, comments) 
                VALUES (4, 2, 'vehicle', 2, 'in_progress', 1000000, 24, 70000, 'На рассмотрении')");
            
            $this->query("INSERT INTO applications (user_id, vehicle_id, type, manager_id, status, initial_payment, term_months, monthly_payment, comments) 
                VALUES (5, 3, 'vehicle', 3, 'rejected', 800000, 36, 79000, 'Недостаточный доход')");
            
            $this->query("INSERT INTO applications (user_id, vehicle_id, type, status, initial_payment, term_months, monthly_payment) 
                VALUES (5, 4, 'vehicle', 'new', 600000, 24, 45000)");
                
            // Добавляем тестовые заявки на недвижимость
            $this->query("INSERT INTO applications (user_id, real_estate_id, type, manager_id, status, initial_payment, term_months, monthly_payment, comments) 
                VALUES (4, 1, 'real_estate', 2, 'approved', 5000000, 60, 120000, 'Одобрено с комментарием: отличная кредитная история')");
                
            $this->query("INSERT INTO applications (user_id, real_estate_id, type, manager_id, status, initial_payment, term_months, monthly_payment, comments) 
                VALUES (5, 2, 'real_estate', 3, 'in_progress', 4000000, 120, 95000, 'Требуются дополнительные документы')");
                
            $this->query("INSERT INTO applications (user_id, real_estate_id, type, status, initial_payment, term_months, monthly_payment) 
                VALUES (5, 3, 'real_estate', 'new', 8000000, 84, 180000)");
        }
    }

    /**
     * Выполнение SQL запроса
     */
    public function query($sql, $params = []) {
        try {
            if (empty($params)) {
                $result = pg_query($this->connection, $sql);
            } else {
                $result = pg_query_params($this->connection, $sql, $params);
            }
            
            if (!$result) {
                throw new Exception("Ошибка выполнения запроса: " . pg_last_error($this->connection));
            }
            
            return $result;
        } catch (Exception $e) {
            die("Ошибка запроса: " . $e->getMessage());
        }
    }

    /**
     * Получить все строки из результата запроса
     */
    public function fetchAll($result) {
        return pg_fetch_all($result);
    }

    /**
     * Получить одну строку из результата запроса
     */
    public function fetchRow($result) {
        return pg_fetch_assoc($result);
    }

    /**
     * Получить количество затронутых строк
     */
    public function affectedRows($result) {
        return pg_affected_rows($result);
    }

    /**
     * Получить последний добавленный ID
     */
    public function lastInsertId($table, $id_field = 'id') {
        $result = $this->query("SELECT LASTVAL()");
        $row = $this->fetchRow($result);
        return $row['lastval'];
    }
    
    /**
     * Закрыть соединение с базой данных
     */
    public function close() {
        if ($this->connection) {
            pg_close($this->connection);
        }
    }

    /**
     * Деструктор класса
     */
    public function __destruct() {
        $this->close();
    }
}
?>