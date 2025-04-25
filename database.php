<?php
/**
 * Класс для работы с базой данных
 * 
 * Обеспечивает хранение данных в JSON-файлах и основные функции для работы с данными
 */
class Database {
    private $dataDir;
    private $tables = [];
    private static $instance = null;
    
    /**
     * Конструктор класса
     * Инициализирует хранилище данных
     */
    private function __construct() {
        // Директория для хранения файлов базы данных
        $this->dataDir = dirname(__FILE__) . '/data';
        
        // Создаем директорию, если она не существует
        if (!is_dir($this->dataDir)) {
            if (!mkdir($this->dataDir, 0755, true)) {
                die("Ошибка при создании директории для хранения данных");
            }
        }

        // Инициализируем базу данных при первом запуске
        $this->initDatabase();
    }
    
    /**
     * Экранирование строки для безопасного использования
     */
    public function escapeString($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
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
        // Определяем структуру таблиц в виде ассоциативных массивов
        $tables = [
            'users' => [
                'schema' => [
                    'id' => 'integer',
                    'email' => 'string',
                    'password' => 'string',
                    'first_name' => 'string',
                    'last_name' => 'string',
                    'phone' => 'string',
                    'role' => 'string',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ],
                'indices' => ['email']
            ],
            'vehicles' => [
                'schema' => [
                    'id' => 'integer',
                    'make' => 'string',
                    'model' => 'string',
                    'year' => 'integer',
                    'engine' => 'string',
                    'power' => 'integer',
                    'drive_type' => 'string',
                    'transmission' => 'string',
                    'color' => 'string',
                    'interior' => 'string',
                    'features' => 'string',
                    'image_url' => 'string',
                    'price' => 'float',
                    'monthly_payment' => 'float',
                    'status' => 'string',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ],
            'real_estate' => [
                'schema' => [
                    'id' => 'integer',
                    'title' => 'string',
                    'type' => 'string',
                    'address' => 'string',
                    'area' => 'float',
                    'rooms' => 'integer',
                    'floor' => 'integer',
                    'total_floors' => 'integer',
                    'build_year' => 'integer',
                    'description' => 'string',
                    'features' => 'string',
                    'image_url' => 'string',
                    'price' => 'float',
                    'monthly_payment' => 'float',
                    'status' => 'string',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ],
            'applications' => [
                'schema' => [
                    'id' => 'integer',
                    'user_id' => 'integer',
                    'vehicle_id' => 'integer',
                    'real_estate_id' => 'integer',
                    'manager_id' => 'integer',
                    'type' => 'string',
                    'status' => 'string',
                    'initial_payment' => 'float',
                    'term_months' => 'integer',
                    'monthly_payment' => 'float',
                    'comments' => 'string',
                    'created_at' => 'datetime',
                    'updated_at' => 'datetime'
                ]
            ]
        ];
        
        // Инициализируем таблицы
        foreach ($tables as $tableName => $tableConfig) {
            $this->createTable($tableName, $tableConfig);
        }
        
        // Добавляем тестовые данные, если база пустая
        $this->seedDatabaseIfEmpty();
    }
    
    /**
     * Создает таблицу в JSON-формате, если она не существует
     */
    private function createTable($tableName, $tableConfig) {
        $tablePath = $this->getTablePath($tableName);
        
        // Если файл таблицы не существует, создаем его
        if (!file_exists($tablePath)) {
            $tableData = [
                'schema' => $tableConfig['schema'],
                'indices' => $tableConfig['indices'] ?? [],
                'auto_increment' => 1,
                'records' => []
            ];
            
            $this->saveTableData($tableName, $tableData);
        }
        
        // Загружаем таблицу в память
        $this->tables[$tableName] = $this->loadTableData($tableName);
    }
    
    /**
     * Возвращает путь к файлу таблицы
     */
    private function getTablePath($tableName) {
        return $this->dataDir . '/' . $tableName . '.json';
    }
    
    /**
     * Загружает данные таблицы из JSON-файла
     */
    private function loadTableData($tableName) {
        $tablePath = $this->getTablePath($tableName);
        
        if (file_exists($tablePath)) {
            $jsonData = file_get_contents($tablePath);
            return json_decode($jsonData, true);
        }
        
        return null;
    }
    
    /**
     * Сохраняет данные таблицы в JSON-файл
     */
    private function saveTableData($tableName, $data) {
        $tablePath = $this->getTablePath($tableName);
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        
        return file_put_contents($tablePath, $jsonData);
    }
    
    /**
     * Заполнение базы данных тестовыми данными
     */
    private function seedDatabaseIfEmpty() {
        // Проверяем, есть ли файл с пользователями
        $usersFile = $this->dataDir . '/users.json';
        $needToSeed = false;
        
        if (file_exists($usersFile)) {
            $jsonData = file_get_contents($usersFile);
            $userData = json_decode($jsonData, true);
            if (!isset($userData['records']) || count($userData['records']) == 0) {
                $needToSeed = true;
            }
        } else {
            $needToSeed = true;
        }
        
        if ($needToSeed) {
            // Создаем структуры таблиц в JSON
            $usersData = [
                'schema' => [
                    'id' => ['type' => 'INTEGER', 'primary' => true, 'auto_increment' => true],
                    'email' => ['type' => 'TEXT', 'unique' => true],
                    'password' => ['type' => 'TEXT'],
                    'original_password' => ['type' => 'TEXT'],
                    'first_name' => ['type' => 'TEXT'],
                    'last_name' => ['type' => 'TEXT'],
                    'phone' => ['type' => 'TEXT'],
                    'role' => ['type' => 'TEXT'],
                    'status' => ['type' => 'TEXT', 'default' => 'active'],
                    'created_at' => ['type' => 'DATETIME']
                ],
                'records' => []
            ];
            
            // Добавляем тестовых пользователей
            $password = password_hash('password', PASSWORD_DEFAULT);
            $originalPassword = 'password';
            $timestamp = date('Y-m-d H:i:s');
            
            // Администратор
            $usersData['records'][] = [
                'id' => 1,
                'email' => 'admin@лизинг.орг',
                'password' => $password,
                'original_password' => $originalPassword,
                'first_name' => 'Андрей',
                'last_name' => 'Волков',
                'phone' => '+7(901)123-4567',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => $timestamp
            ];
            
            // Менеджеры
            $usersData['records'][] = [
                'id' => 2,
                'email' => 'manager1@лизинг.орг',
                'password' => $password,
                'original_password' => $originalPassword,
                'first_name' => 'Алексей',
                'last_name' => 'Смирнов',
                'phone' => '+7(902)123-4567',
                'role' => 'manager',
                'status' => 'active',
                'created_at' => $timestamp
            ];
            
            $usersData['records'][] = [
                'id' => 3,
                'email' => 'manager2@лизинг.орг',
                'password' => $password,
                'original_password' => $originalPassword,
                'first_name' => 'Елена',
                'last_name' => 'Михайлова',
                'phone' => '+7(903)123-4567',
                'role' => 'manager',
                'status' => 'active',
                'created_at' => $timestamp
            ];
            
            // Клиенты
            $usersData['records'][] = [
                'id' => 4,
                'email' => 'client@лизинг.орг',
                'password' => $password,
                'original_password' => $originalPassword,
                'first_name' => 'Иван',
                'last_name' => 'Петров',
                'phone' => '+7(904)123-4567',
                'role' => 'client',
                'status' => 'active',
                'created_at' => $timestamp
            ];
            
            $usersData['records'][] = [
                'id' => 5,
                'email' => 'maria@example.com',
                'password' => $password,
                'original_password' => $originalPassword,
                'first_name' => 'Мария',
                'last_name' => 'Иванова',
                'phone' => '+7(905)123-4567',
                'role' => 'client',
                'status' => 'active',
                'created_at' => $timestamp
            ];
            
            // Сохраняем данные пользователей в JSON
            if (!is_dir($this->dataDir)) {
                mkdir($this->dataDir, 0755, true);
            }
            file_put_contents($this->dataDir . '/users.json', json_encode($usersData, JSON_PRETTY_PRINT));

            // Создаем и заполняем данные автомобилей
            $vehiclesData = [
                'schema' => [
                    'id' => ['type' => 'INTEGER', 'primary' => true, 'auto_increment' => true],
                    'make' => ['type' => 'TEXT'],
                    'model' => ['type' => 'TEXT'],
                    'year' => ['type' => 'INTEGER'],
                    'engine' => ['type' => 'TEXT'],
                    'power' => ['type' => 'INTEGER'],
                    'drive_type' => ['type' => 'TEXT'],
                    'transmission' => ['type' => 'TEXT'],
                    'color' => ['type' => 'TEXT'],
                    'interior' => ['type' => 'TEXT'],
                    'features' => ['type' => 'TEXT'],
                    'image_url' => ['type' => 'TEXT'],
                    'price' => ['type' => 'INTEGER'],
                    'monthly_payment' => ['type' => 'INTEGER'],
                    'created_at' => ['type' => 'DATETIME']
                ],
                'records' => []
            ];
            
            // Добавляем автомобили
            $vehicles = [
                [
                    'id' => 1,
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
                    'monthly_payment' => 85000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 2,
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
                    'monthly_payment' => 65000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 3,
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
                    'monthly_payment' => 79000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 4,
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
                    'monthly_payment' => 45000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 5,
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
                    'monthly_payment' => 52000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 6,
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
                    'monthly_payment' => 38000,
                    'created_at' => $timestamp
                ]
            ];

            foreach ($vehicles as $vehicle) {
                $vehiclesData['records'][] = $vehicle;
            }
            
            // Сохраняем данные автомобилей в JSON
            file_put_contents($this->dataDir . '/vehicles.json', json_encode($vehiclesData, JSON_PRETTY_PRINT));

            // Создаем и заполняем данные объектов недвижимости
            $realEstateData = [
                'schema' => [
                    'id' => ['type' => 'INTEGER', 'primary' => true, 'auto_increment' => true],
                    'title' => ['type' => 'TEXT'],
                    'type' => ['type' => 'TEXT'],
                    'address' => ['type' => 'TEXT'],
                    'area' => ['type' => 'FLOAT'],
                    'rooms' => ['type' => 'INTEGER'],
                    'floor' => ['type' => 'INTEGER'],
                    'total_floors' => ['type' => 'INTEGER'],
                    'build_year' => ['type' => 'INTEGER'],
                    'description' => ['type' => 'TEXT'],
                    'features' => ['type' => 'TEXT'],
                    'image_url' => ['type' => 'TEXT'],
                    'price' => ['type' => 'INTEGER'],
                    'monthly_payment' => ['type' => 'INTEGER'],
                    'created_at' => ['type' => 'DATETIME']
                ],
                'records' => []
            ];
            
            // Добавляем объекты недвижимости
            $realEstateObjects = [
                [
                    'id' => 1,
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
                    'monthly_payment' => 120000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 2,
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
                    'monthly_payment' => 95000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 3,
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
                    'monthly_payment' => 180000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 4,
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
                    'monthly_payment' => 38000,
                    'created_at' => $timestamp
                ],
                [
                    'id' => 5,
                    'title' => 'Торговое помещение в ТЦ',
                    'type' => 'commercial',
                    'address' => 'Москва, Кутузовский пр-т, 57, ТЦ "Времена года"',
                    'area' => 78.3,
                    'rooms' => 1,
                    'floor' => 2,
                    'total_floors' => 4,
                    'build_year' => 2015,
                    'description' => 'Торговое помещение в престижном торговом центре. Высокая проходимость, отличное расположение, подходит для бутика или салона.',
                    'features' => 'центральное кондиционирование,круглосуточный доступ,охрана,парковка,рекламные возможности',
                    'image_url' => 'https://images.unsplash.com/photo-1581658545657-4a95507e227e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => 16750000,
                    'monthly_payment' => 88000,
                    'created_at' => $timestamp
                ]
            ];

            foreach ($realEstateObjects as $realEstate) {
                $realEstateData['records'][] = $realEstate;
            }
            
            // Сохраняем данные объектов недвижимости в JSON
            file_put_contents($this->dataDir . '/real_estate.json', json_encode($realEstateData, JSON_PRETTY_PRINT));
            
            // Создаем и заполняем данные заявок
            $applicationsData = [
                'schema' => [
                    'id' => ['type' => 'INTEGER', 'primary' => true, 'auto_increment' => true],
                    'user_id' => ['type' => 'INTEGER'],
                    'vehicle_id' => ['type' => 'INTEGER', 'nullable' => true],
                    'real_estate_id' => ['type' => 'INTEGER', 'nullable' => true],
                    'type' => ['type' => 'TEXT'],
                    'manager_id' => ['type' => 'INTEGER', 'nullable' => true],
                    'status' => ['type' => 'TEXT', 'default' => 'new'],
                    'initial_payment' => ['type' => 'INTEGER'],
                    'term_months' => ['type' => 'INTEGER'],
                    'monthly_payment' => ['type' => 'INTEGER'],
                    'comments' => ['type' => 'TEXT', 'nullable' => true],
                    'created_at' => ['type' => 'DATETIME']
                ],
                'records' => []
            ];
            
            // Добавляем заявки
            $applicationsData['records'][] = [
                'id' => 1,
                'user_id' => 4,
                'vehicle_id' => 1,
                'real_estate_id' => null,
                'type' => 'vehicle',
                'manager_id' => 2,
                'status' => 'processing',
                'initial_payment' => 1000000,
                'term_months' => 36,
                'monthly_payment' => 85000,
                'comments' => 'Интересует возможность включения КАСКО в ежемесячный платеж',
                'created_at' => $timestamp
            ];
            
            $applicationsData['records'][] = [
                'id' => 2,
                'user_id' => 5,
                'vehicle_id' => 3,
                'real_estate_id' => null,
                'type' => 'vehicle',
                'manager_id' => 3,
                'status' => 'approved',
                'initial_payment' => 1500000,
                'term_months' => 48,
                'monthly_payment' => 79000,
                'comments' => 'Необходимо оформить документы как можно скорее',
                'created_at' => $timestamp
            ];
            
            $applicationsData['records'][] = [
                'id' => 3,
                'user_id' => 5,
                'vehicle_id' => null,
                'real_estate_id' => 1,
                'type' => 'real_estate',
                'manager_id' => 2,
                'status' => 'approved',
                'initial_payment' => 5000000,
                'term_months' => 120,
                'monthly_payment' => 120000,
                'comments' => 'Требуется юридическая проверка документов объекта',
                'created_at' => $timestamp
            ];
            
            $applicationsData['records'][] = [
                'id' => 4,
                'user_id' => 5,
                'vehicle_id' => 4,
                'real_estate_id' => null,
                'type' => 'vehicle',
                'manager_id' => null,
                'status' => 'new',
                'initial_payment' => 800000,
                'term_months' => 36,
                'monthly_payment' => 45000,
                'comments' => null,
                'created_at' => $timestamp
            ];
            
            // Сохраняем данные заявок в JSON
            file_put_contents($this->dataDir . '/applications.json', json_encode($applicationsData, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Выполнить SQL-подобный запрос
     * Поддерживает базовые операции SELECT, INSERT, UPDATE, DELETE
     */
    public function query($sql, $params = []) {
        try {
            // Заменяем параметры в запросе, если они переданы
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $sql = str_replace(':' . $key, $this->escapeString($value), $sql);
                }
            }
            
            // Распознаем тип запроса
            $sql = trim($sql);
            
            if (strpos($sql, 'SELECT') === 0) {
                return $this->executeSelect($sql);
            } elseif (strpos($sql, 'INSERT') === 0) {
                return $this->executeInsert($sql);
            } elseif (strpos($sql, 'UPDATE') === 0) {
                return $this->executeUpdate($sql);
            } elseif (strpos($sql, 'DELETE') === 0) {
                return $this->executeDelete($sql);
            } elseif (strpos($sql, 'CREATE TABLE') !== false) {
                // CREATE TABLE уже обрабатывается в initDatabase
                return true;
            }
            
            throw new Exception("Неподдерживаемый тип SQL-запроса");
        } catch (Exception $e) {
            die("Ошибка запроса: " . $e->getMessage());
        }
    }
    
    /**
     * Выполнить SELECT-запрос
     */
    private function executeSelect($sql) {
        // Извлекаем имя таблицы
        preg_match('/FROM\s+([a-zA-Z0-9_]+)/i', $sql, $matches);
        $tableName = $matches[1] ?? null;
        
        if (!$tableName || !isset($this->tables[$tableName])) {
            throw new Exception("Таблица не найдена: " . $tableName);
        }
        
        $table = $this->tables[$tableName];
        $records = $table['records'];
        
        // Простой счетчик
        if (strpos($sql, 'COUNT(*)') !== false) {
            return [
                ['count' => count($records)]
            ];
        }
        
        // Обработка MIN и MAX функций
        if (preg_match('/SELECT\s+MIN\((.*?)\)\s+as\s+min,\s+MAX\((.*?)\)\s+as\s+max/i', $sql, $matches)) {
            $minField = trim($matches[1]);
            $maxField = trim($matches[2]);
            
            if (empty($records)) {
                return [
                    ['min' => null, 'max' => null]
                ];
            }
            
            $minValue = null;
            $maxValue = null;
            
            foreach ($records as $record) {
                if (isset($record[$minField])) {
                    $value = $record[$minField];
                    if ($minValue === null || $value < $minValue) {
                        $minValue = $value;
                    }
                    if ($maxValue === null || $value > $maxValue) {
                        $maxValue = $value;
                    }
                }
            }
            
            return [
                ['min' => $minValue, 'max' => $maxValue]
            ];
        }
        
        // Фильтрация по WHERE
        if (strpos($sql, 'WHERE') !== false) {
            preg_match('/WHERE\s+(.*?)(\s+ORDER BY|\s+LIMIT|$)/i', $sql, $matches);
            $whereClause = $matches[1] ?? '';
            
            if ($whereClause) {
                $filteredRecords = array_filter($records, function($record) use ($whereClause) {
                    return $this->evaluateWhereCondition($record, $whereClause);
                });
                $records = array_values($filteredRecords);
            }
        }
        
        // Сортировка по ORDER BY
        if (strpos($sql, 'ORDER BY') !== false) {
            preg_match('/ORDER BY\s+(.*?)(\s+LIMIT|$)/i', $sql, $matches);
            $orderByClause = $matches[1] ?? '';
            
            if ($orderByClause) {
                $this->sortRecords($records, $orderByClause);
            }
        }
        
        // Ограничение по LIMIT
        if (strpos($sql, 'LIMIT') !== false) {
            preg_match('/LIMIT\s+(\d+)(\s+OFFSET\s+(\d+))?/i', $sql, $matches);
            $limit = (int)($matches[1] ?? 0);
            $offset = (int)($matches[3] ?? 0);
            
            if ($limit > 0) {
                $records = array_slice($records, $offset, $limit);
            }
        }
        
        // Выбор полей (проекция)
        if (strpos($sql, 'SELECT *') === false) {
            preg_match('/SELECT\s+(.*?)\s+FROM/i', $sql, $matches);
            $fieldsStr = $matches[1] ?? '*';
            
            if ($fieldsStr !== '*') {
                $fields = array_map('trim', explode(',', $fieldsStr));
                
                $projectedRecords = [];
                foreach ($records as $record) {
                    $projectedRecord = [];
                    foreach ($fields as $field) {
                        $fieldName = trim($field);
                        if (isset($record[$fieldName])) {
                            $projectedRecord[$fieldName] = $record[$fieldName];
                        }
                    }
                    $projectedRecords[] = $projectedRecord;
                }
                
                $records = $projectedRecords;
            }
        }
        
        return $records;
    }
    
    /**
     * Выполнить INSERT-запрос
     */
    private function executeInsert($sql) {
        // Извлекаем имя таблицы
        preg_match('/INSERT INTO\s+([a-zA-Z0-9_]+)/i', $sql, $tableMatches);
        $tableName = $tableMatches[1] ?? null;
        
        if (!$tableName || !isset($this->tables[$tableName])) {
            throw new Exception("Таблица не найдена: " . $tableName);
        }
        
        // Извлекаем колонки и значения
        preg_match('/\((.*?)\)\s+VALUES\s+\((.*?)\)/is', $sql, $matches);
        $columnsStr = $matches[1] ?? '';
        $valuesStr = $matches[2] ?? '';
        
        if (empty($columnsStr) || empty($valuesStr)) {
            throw new Exception("Неверный формат INSERT запроса");
        }
        
        $columns = array_map('trim', explode(',', $columnsStr));
        
        // Разбираем значения, учитывая строки в кавычках
        $values = [];
        $valuesStr = trim($valuesStr);
        $inQuote = false;
        $currentValue = '';
        $valuesList = [];
        
        for ($i = 0; $i < strlen($valuesStr); $i++) {
            $char = $valuesStr[$i];
            
            if ($char === "'" && ($i === 0 || $valuesStr[$i-1] !== '\\')) {
                $inQuote = !$inQuote;
                $currentValue .= $char;
            } elseif ($char === ',' && !$inQuote) {
                $valuesList[] = trim($currentValue);
                $currentValue = '';
            } else {
                $currentValue .= $char;
            }
        }
        
        if (!empty($currentValue)) {
            $valuesList[] = trim($currentValue);
        }
        
        // Обрабатываем значения
        foreach ($valuesList as $index => $value) {
            if (preg_match('/^\'(.*?)\'$/s', $value, $matches)) {
                // Строковое значение
                $values[$index] = $matches[1];
            } elseif (is_numeric($value)) {
                // Числовое значение
                $values[$index] = is_int($value + 0) ? (int)$value : (float)$value;
            } elseif (strtoupper($value) === 'NULL') {
                // NULL значение
                $values[$index] = null;
            } elseif (strtoupper($value) === 'CURRENT_TIMESTAMP') {
                // Текущая дата/время
                $values[$index] = date('Y-m-d H:i:s');
            } else {
                // Другие значения
                $values[$index] = $value;
            }
        }
        
        // Создаем новую запись
        $record = [];
        foreach ($columns as $index => $column) {
            if (isset($values[$index])) {
                $record[trim($column)] = $values[$index];
            }
        }
        
        // Добавляем id, если его нет
        if (!isset($record['id'])) {
            $record['id'] = $this->tables[$tableName]['auto_increment'];
            $this->tables[$tableName]['auto_increment']++;
        }
        
        // Добавляем дату создания и обновления, если требуется
        if (!isset($record['created_at']) && isset($this->tables[$tableName]['schema']['created_at'])) {
            $record['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($record['updated_at']) && isset($this->tables[$tableName]['schema']['updated_at'])) {
            $record['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // Добавляем запись
        $this->tables[$tableName]['records'][] = $record;
        
        // Сохраняем данные таблицы
        $this->saveTableData($tableName, $this->tables[$tableName]);
        
        return true;
    }
    
    /**
     * Выполнить UPDATE-запрос
     */
    private function executeUpdate($sql) {
        // Извлекаем имя таблицы
        preg_match('/UPDATE\s+([a-zA-Z0-9_]+)/i', $sql, $tableMatches);
        $tableName = $tableMatches[1] ?? null;
        
        if (!$tableName || !isset($this->tables[$tableName])) {
            throw new Exception("Таблица не найдена: " . $tableName);
        }
        
        // Извлекаем SET и WHERE части
        preg_match('/SET\s+(.*?)(\s+WHERE\s+(.*))?$/is', $sql, $matches);
        $setClause = $matches[1] ?? '';
        $whereClause = $matches[3] ?? '';
        
        if (empty($setClause)) {
            throw new Exception("Неверный формат UPDATE запроса");
        }
        
        // Парсим SET часть
        $updates = [];
        $setParts = explode(',', $setClause);
        
        foreach ($setParts as $setPart) {
            if (preg_match('/([a-zA-Z0-9_]+)\s*=\s*(.+)/i', trim($setPart), $partMatches)) {
                $field = $partMatches[1];
                $value = trim($partMatches[2]);
                
                // Обрабатываем значение
                if (preg_match('/^\'(.*?)\'$/s', $value, $valueMatches)) {
                    $updates[$field] = $valueMatches[1];
                } elseif (is_numeric($value)) {
                    $updates[$field] = is_int($value + 0) ? (int)$value : (float)$value;
                } elseif (strtoupper($value) === 'NULL') {
                    $updates[$field] = null;
                } elseif (strtoupper($value) === 'CURRENT_TIMESTAMP') {
                    $updates[$field] = date('Y-m-d H:i:s');
                } else {
                    $updates[$field] = $value;
                }
            }
        }
        
        // Обновляем поле updated_at, если оно существует
        if (isset($this->tables[$tableName]['schema']['updated_at']) && !isset($updates['updated_at'])) {
            $updates['updated_at'] = date('Y-m-d H:i:s');
        }
        
        // Обновляем записи
        $affectedRows = 0;
        $records = &$this->tables[$tableName]['records'];
        
        foreach ($records as &$record) {
            if (empty($whereClause) || $this->evaluateWhereCondition($record, $whereClause)) {
                foreach ($updates as $field => $value) {
                    $record[$field] = $value;
                }
                $affectedRows++;
            }
        }
        
        // Сохраняем данные таблицы
        $this->saveTableData($tableName, $this->tables[$tableName]);
        
        return $affectedRows;
    }
    
    /**
     * Выполнить DELETE-запрос
     */
    private function executeDelete($sql) {
        // Извлекаем имя таблицы
        preg_match('/DELETE FROM\s+([a-zA-Z0-9_]+)/i', $sql, $tableMatches);
        $tableName = $tableMatches[1] ?? null;
        
        if (!$tableName || !isset($this->tables[$tableName])) {
            throw new Exception("Таблица не найдена: " . $tableName);
        }
        
        // Извлекаем WHERE часть
        preg_match('/WHERE\s+(.*)$/is', $sql, $matches);
        $whereClause = $matches[1] ?? '';
        
        // Удаляем записи
        $affectedRows = 0;
        $records = &$this->tables[$tableName]['records'];
        
        if (empty($whereClause)) {
            // Удаляем все записи
            $affectedRows = count($records);
            $records = [];
        } else {
            // Удаляем только записи, соответствующие условию
            $newRecords = [];
            
            foreach ($records as $record) {
                if (!$this->evaluateWhereCondition($record, $whereClause)) {
                    $newRecords[] = $record;
                } else {
                    $affectedRows++;
                }
            }
            
            $records = $newRecords;
        }
        
        // Сохраняем данные таблицы
        $this->saveTableData($tableName, $this->tables[$tableName]);
        
        return $affectedRows;
    }
    
    /**
     * Оценить условие WHERE для записи
     */
    private function evaluateWhereCondition($record, $whereClause) {
        // Простая проверка условия на равенство (id = 1)
        if (preg_match('/([a-zA-Z0-9_]+)\s*=\s*(.+)/i', $whereClause, $matches)) {
            $field = $matches[1];
            $value = trim($matches[2]);
            
            // Обрабатываем значение
            if (preg_match('/^\'(.*?)\'$/s', $value, $valueMatches)) {
                $compareValue = $valueMatches[1];
            } elseif (is_numeric($value)) {
                $compareValue = is_int($value + 0) ? (int)$value : (float)$value;
            } elseif (strtoupper($value) === 'NULL') {
                $compareValue = null;
            } else {
                $compareValue = $value;
            }
            
            return isset($record[$field]) && $record[$field] == $compareValue;
        }
        
        // По умолчанию возвращаем true, если условие не распознано
        return true;
    }
    
    /**
     * Сортировка записей по условию ORDER BY
     */
    private function sortRecords(&$records, $orderByClause) {
        $parts = explode(',', $orderByClause);
        $sortFields = [];
        
        foreach ($parts as $part) {
            $part = trim($part);
            
            if (preg_match('/([a-zA-Z0-9_]+)(?:\s+(ASC|DESC))?/i', $part, $matches)) {
                $field = $matches[1];
                $direction = strtoupper($matches[2] ?? 'ASC');
                
                $sortFields[] = [
                    'field' => $field,
                    'direction' => $direction
                ];
            }
        }
        
        if (!empty($sortFields)) {
            usort($records, function($a, $b) use ($sortFields) {
                foreach ($sortFields as $sort) {
                    $field = $sort['field'];
                    $direction = $sort['direction'];
                    
                    if (!isset($a[$field]) && !isset($b[$field])) {
                        continue;
                    }
                    
                    if (!isset($a[$field])) {
                        return $direction === 'ASC' ? -1 : 1;
                    }
                    
                    if (!isset($b[$field])) {
                        return $direction === 'ASC' ? 1 : -1;
                    }
                    
                    if ($a[$field] == $b[$field]) {
                        continue;
                    }
                    
                    $result = $a[$field] <=> $b[$field];
                    return $direction === 'ASC' ? $result : -$result;
                }
                
                return 0;
            });
        }
    }
    
    /**
     * Получить все строки из результата запроса
     */
    public function fetchAll($result) {
        // Результат уже представлен как массив записей
        return $result;
    }
    
    /**
     * Получить одну строку из результата запроса
     */
    public function fetchRow($result) {
        // Возвращаем первую запись из результата
        return isset($result[0]) ? $result[0] : null;
    }
    
    /**
     * Получить количество затронутых строк
     */
    public function affectedRows($result) {
        // Для INSERT, UPDATE, DELETE возвращается число затронутых строк
        return is_numeric($result) ? $result : 0;
    }
    
    /**
     * Получить ID последней вставленной записи
     */
    public function lastInsertId($table, $id_field = 'id') {
        // Возвращаем текущее значение auto_increment - 1
        return isset($this->tables[$table]) ? $this->tables[$table]['auto_increment'] - 1 : 0;
    }
    
    /**
     * Закрыть соединение с базой данных
     */
    public function close() {
        // Для файловой БД ничего не делаем
    }
    
    /**
     * Деструктор класса
     */
    public function __destruct() {
        $this->close();
    }
}