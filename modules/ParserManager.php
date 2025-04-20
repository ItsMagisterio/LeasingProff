<?php
/**
 * Класс для управления парсерами данных с сайтов конкурентов
 * 
 * Обеспечивает функционал для парсинга автомобилей с 15 сайтов конкурентов
 */
class ParserManager {
    private $connection;
    private $parsers = [];
    private $logFile = 'parser_log.txt';
    
    /**
     * Конструктор класса
     */
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Регистрируем все доступные парсеры
        $this->registerParsers();
    }
    
    /**
     * Регистрирует все доступные парсеры в системе
     */
    private function registerParsers() {
        // Список сайтов конкурентов для парсинга
        $this->parsers = [
            'autolizing' => [
                'name' => 'АвтоЛизинг',
                'url' => 'https://autolizing.ru', 
                'enabled' => true
            ],
            'europlan' => [
                'name' => 'Европлан',
                'url' => 'https://europlan.ru', 
                'enabled' => true
            ],
            'alfaleasing' => [
                'name' => 'Альфа-Лизинг',
                'url' => 'https://alfaleasing.ru', 
                'enabled' => true
            ],
            'carcade' => [
                'name' => 'Каркаде',
                'url' => 'https://carcade.com', 
                'enabled' => true
            ],
            'vtb-leasing' => [
                'name' => 'ВТБ Лизинг',
                'url' => 'https://vtb-leasing.ru', 
                'enabled' => true
            ],
            'sberleasing' => [
                'name' => 'Сбербанк Лизинг',
                'url' => 'https://www.sberleasing.ru', 
                'enabled' => true
            ],
            'gazpromleasing' => [
                'name' => 'Газпромбанк Лизинг',
                'url' => 'https://www.gazpromleasing.ru', 
                'enabled' => true
            ],
            'ctrl-leasing' => [
                'name' => 'Контрол Лизинг',
                'url' => 'https://ctrl-leasing.ru', 
                'enabled' => true
            ],
            'baltlease' => [
                'name' => 'Балтийский Лизинг',
                'url' => 'https://baltlease.ru', 
                'enabled' => true
            ],
            'raiffeisen-leasing' => [
                'name' => 'Райффайзен Лизинг',
                'url' => 'https://www.raiffeisen-leasing.ru', 
                'enabled' => true
            ],
            'element-leasing' => [
                'name' => 'Элемент Лизинг',
                'url' => 'https://elementleasing.ru', 
                'enabled' => true
            ],
            'reso-leasing' => [
                'name' => 'РЕСО Лизинг',
                'url' => 'https://www.resoleasing.com', 
                'enabled' => true
            ],
            'soglasie' => [
                'name' => 'Согласие',
                'url' => 'https://www.soglasie.ru', 
                'enabled' => true
            ],
            'majleasing' => [
                'name' => 'МАЙ Лизинг',
                'url' => 'https://www.majleasing.ru', 
                'enabled' => true
            ],
            'uralsib' => [
                'name' => 'УРАЛСИБ',
                'url' => 'https://www.leasing.uralsib.ru', 
                'enabled' => true
            ]
        ];
    }
    
    /**
     * Запускает парсинг всех включенных сайтов
     */
    public function parseAll() {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'sites' => []
        ];
        
        foreach ($this->parsers as $key => $parser) {
            if ($parser['enabled']) {
                $result = $this->parseSite($key);
                $results['sites'][$key] = $result;
                $results['total']++;
                
                if ($result['success']) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            }
        }
        
        $this->logParsingResults($results);
        return $results;
    }
    
    /**
     * Парсит отдельный сайт и сохраняет данные в БД
     * 
     * @param string $siteKey Ключ сайта из списка парсеров
     * @return array Результаты парсинга
     */
    public function parseSite($siteKey) {
        if (!isset($this->parsers[$siteKey])) {
            return [
                'success' => false,
                'message' => 'Парсер для указанного сайта не найден'
            ];
        }
        
        $parser = $this->parsers[$siteKey];
        $this->log("Начинаем парсинг сайта: {$parser['name']} ({$parser['url']})");
        
        try {
            // Имитация парсинга для демонстрации (в реальном проекте здесь будет код парсера)
            // В реальном проекте здесь нужно использовать, например, библиотеку Goutte или Simple HTML DOM Parser
            
            // Генерируем тестовые автомобили для каждого парсера
            $vehiclesData = $this->generateTestVehicles($parser);
            
            // Сохраняем данные в БД
            $savedCount = $this->saveVehiclesToDB($vehiclesData, $parser['name']);
            
            $this->log("Парсинг сайта {$parser['name']} завершен: добавлено {$savedCount} автомобилей");
            
            return [
                'success' => true,
                'message' => "Успешно добавлено {$savedCount} автомобилей",
                'count' => $savedCount
            ];
        } catch (Exception $e) {
            $errorMsg = "Ошибка при парсинге сайта {$parser['name']}: " . $e->getMessage();
            $this->log($errorMsg);
            
            return [
                'success' => false,
                'message' => $errorMsg
            ];
        }
    }
    
    /**
     * Генерирует тестовые данные для демонстрации парсинга
     * В реальном проекте этот метод будет заменен на реальный парсинг
     */
    private function generateTestVehicles($parser) {
        $makes = ['Toyota', 'BMW', 'Mercedes-Benz', 'Audi', 'Volkswagen', 'KIA', 'Hyundai', 'Skoda', 'Renault', 'Nissan'];
        $models = [
            'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Land Cruiser', 'Hilux'],
            'BMW' => ['3 Series', '5 Series', 'X5', 'X3', '7 Series'],
            'Mercedes-Benz' => ['E-Class', 'C-Class', 'S-Class', 'GLE', 'GLC'],
            'Audi' => ['A4', 'A6', 'Q5', 'Q7', 'A8'],
            'Volkswagen' => ['Tiguan', 'Polo', 'Passat', 'Touareg', 'Golf'],
            'KIA' => ['Rio', 'Sportage', 'Ceed', 'Sorento', 'K5'],
            'Hyundai' => ['Solaris', 'Tucson', 'Santa Fe', 'Creta', 'Sonata'],
            'Skoda' => ['Octavia', 'Kodiaq', 'Karoq', 'Superb', 'Rapid'],
            'Renault' => ['Duster', 'Kaptur', 'Logan', 'Arkana', 'Sandero'],
            'Nissan' => ['X-Trail', 'Qashqai', 'Murano', 'Terrano', 'Patrol']
        ];
        
        $engines = ['1.6L', '2.0L', '2.5L', '3.0L', '3.5L', '1.8TSI', '2.0TDI', '2.0T'];
        $transmissions = ['автомат', 'механика', 'робот', 'вариатор'];
        $driveTypes = ['передний', 'задний', 'полный'];
        $colors = ['белый', 'черный', 'серебристый', 'серый', 'синий', 'красный', 'коричневый'];
        $interiors = ['ткань черная', 'ткань бежевая', 'кожа черная', 'кожа бежевая', 'кожа коричневая', 'эко-кожа черная'];
        $features = [
            'кондиционер', 'климат-контроль', 'подогрев сидений', 'система помощи при парковке', 
            'камера заднего вида', 'датчик дождя', 'круиз-контроль', 'мультимедийная система', 
            'навигация', 'панорамная крыша', 'адаптивный круиз-контроль', 'система бесключевого доступа'
        ];
        
        $vehicles = [];
        
        // Генерируем от 5 до 15 случайных автомобилей для каждого парсера
        $count = rand(5, 15);
        
        for ($i = 0; $i < $count; $i++) {
            $make = $makes[array_rand($makes)];
            $modelArray = $models[$make];
            $model = $modelArray[array_rand($modelArray)];
            
            // Случайный набор особенностей автомобиля
            $featureList = [];
            $featureCount = rand(2, 6);
            $featureIndexes = array_rand($features, $featureCount);
            if (!is_array($featureIndexes)) {
                $featureIndexes = [$featureIndexes];
            }
            
            foreach ($featureIndexes as $index) {
                $featureList[] = $features[$index];
            }
            
            // Генерируем цену и ежемесячный платеж
            $price = rand(1500000, 8000000);
            $monthlyPayment = floor($price / 100) * (rand(100, 120) / 100);
            
            // Добавляем автомобиль в список
            $vehicles[] = [
                'make' => $make,
                'model' => $model,
                'year' => rand(2020, 2024),
                'engine' => $engines[array_rand($engines)],
                'power' => rand(110, 400),
                'drive_type' => $driveTypes[array_rand($driveTypes)],
                'transmission' => $transmissions[array_rand($transmissions)],
                'color' => $colors[array_rand($colors)],
                'interior' => $interiors[array_rand($interiors)],
                'features' => implode(',', $featureList),
                'image_url' => $this->getRandomCarImageUrl(),
                'price' => $price,
                'monthly_payment' => $monthlyPayment,
                'source' => $parser['name'],
                'source_url' => $parser['url'] . '/vehicle/' . rand(1000, 9999),
                'status' => 'available'
            ];
        }
        
        return $vehicles;
    }
    
    /**
     * Возвращает случайное изображение автомобиля
     */
    private function getRandomCarImageUrl() {
        $images = [
            'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1583121274602-3e2820c69888?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1546614042-7df3c24c9e5d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1542362567-b07e54358753?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1553440569-bcc63803a83d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1550355291-bbee04a92027?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1556800572-1b8aeef2c54f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
            'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'
        ];
        
        return $images[array_rand($images)];
    }
    
    /**
     * Сохраняет данные автомобилей в базу данных
     * 
     * @param array $vehiclesData Массив с данными автомобилей
     * @param string $source Источник данных (название сайта)
     * @return int Количество сохраненных записей
     */
    private function saveVehiclesToDB($vehiclesData, $source) {
        $savedCount = 0;
        
        foreach ($vehiclesData as $vehicle) {
            // Проверяем, есть ли уже такой автомобиль в базе
            $query = "SELECT COUNT(*) FROM vehicles WHERE 
                      make = '{$this->escapeString($vehicle['make'])}' AND 
                      model = '{$this->escapeString($vehicle['model'])}' AND 
                      year = {$vehicle['year']} AND 
                      engine = '{$this->escapeString($vehicle['engine'])}' AND 
                      color = '{$this->escapeString($vehicle['color'])}' AND 
                      source = '{$this->escapeString($source)}'";
                      
            $result = $this->db->query($query);
            
            // Если такого автомобиля нет, добавляем его
            if (isset($result[0]) && $result[0]['COUNT(*)'] == 0) {
                $query = "INSERT INTO vehicles (
                    make, model, year, engine, power, drive_type, transmission, 
                    color, interior, features, image_url, price, monthly_payment, 
                    source, source_url, status
                ) VALUES (
                    '{$this->escapeString($vehicle['make'])}',
                    '{$this->escapeString($vehicle['model'])}',
                    {$vehicle['year']},
                    '{$this->escapeString($vehicle['engine'])}',
                    {$vehicle['power']},
                    '{$this->escapeString($vehicle['drive_type'])}',
                    '{$this->escapeString($vehicle['transmission'])}',
                    '{$this->escapeString($vehicle['color'])}',
                    '{$this->escapeString($vehicle['interior'])}',
                    '{$this->escapeString($vehicle['features'])}',
                    '{$this->escapeString($vehicle['image_url'])}',
                    {$vehicle['price']},
                    {$vehicle['monthly_payment']},
                    '{$this->escapeString($source)}',
                    '{$this->escapeString($vehicle['source_url'])}',
                    'available'
                )";
                
                $result = $this->db->query($query);
                if ($result !== false && $this->db->affectedRows($result) > 0) {
                    $savedCount++;
                } else {
                    $this->log("Ошибка при сохранении автомобиля {$vehicle['make']} {$vehicle['model']}: " . $this->db->lastError());
                }
            }
        }
        
        return $savedCount;
    }
    
    /**
     * Экранирует строку для безопасного использования в SQL запросах
     */
    private function escapeString($string) {
        return $this->db->escapeString($string);
    }
    
    /**
     * Добавляет запись в лог
     */
    private function log($message) {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[{$date}] {$message}\n";
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Записывает результаты парсинга в лог
     */
    private function logParsingResults($results) {
        $this->log("Парсинг завершен. Всего сайтов: {$results['total']}, успешно: {$results['success']}, не удалось: {$results['failed']}");
        
        foreach ($results['sites'] as $site => $result) {
            $status = $result['success'] ? 'успешно' : 'ошибка';
            $this->log("- {$site}: {$status}, {$result['message']}");
        }
    }
    
    /**
     * Возвращает список всех доступных парсеров
     */
    public function getParsers() {
        return $this->parsers;
    }
    
    /**
     * Включает или отключает парсер для конкретного сайта
     */
    public function setParserStatus($siteKey, $enabled) {
        if (isset($this->parsers[$siteKey])) {
            $this->parsers[$siteKey]['enabled'] = (bool)$enabled;
            return true;
        }
        
        return false;
    }
}