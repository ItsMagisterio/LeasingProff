<?php
/**
 * Класс для работы с автомобилями
 */
class Vehicles {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить список всех автомобилей
     */
    public function getAllVehicles($limit = 0, $offset = 0, $filters = []) {
        // Логирование для отладки
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на getAllVehicles с фильтрами: " . print_r($filters, true) . "\n", FILE_APPEND);
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        
        // Проверяем существование файла
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл vehicles.json не найден\n", FILE_APPEND);
            return [];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        // Проверяем корректность данных
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в vehicles.json\n", FILE_APPEND);
            return [];
        }
        
        $vehicles = $data['records'];
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Найдено записей: " . count($vehicles) . "\n", FILE_APPEND);
        
        // Применяем фильтры
        if (!empty($filters)) {
            $vehicles = array_filter($vehicles, function($vehicle) use ($filters) {
                // Логируем свойство для отладки (только первое)
                static $logged = false;
                if (!$logged) {
                    file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Проверяем автомобиль: " . print_r($vehicle, true) . "\n", FILE_APPEND);
                    $logged = true;
                }
                
                if (isset($filters['make']) && $filters['make'] && (!isset($vehicle['make']) || $vehicle['make'] !== $filters['make'])) {
                    return false;
                }
                
                if (isset($filters['model']) && $filters['model'] && (!isset($vehicle['model']) || $vehicle['model'] !== $filters['model'])) {
                    return false;
                }
                
                if (isset($filters['min_price']) && $filters['min_price'] && (!isset($vehicle['price']) || $vehicle['price'] < $filters['min_price'])) {
                    return false;
                }
                
                if (isset($filters['max_price']) && $filters['max_price'] && (!isset($vehicle['price']) || $vehicle['price'] > $filters['max_price'])) {
                    return false;
                }
                
                if (isset($filters['status']) && $filters['status']) {
                    // Если статус не указан, считаем его доступным по умолчанию
                    if (!isset($vehicle['status'])) {
                        return $filters['status'] === 'available';
                    } else if ($vehicle['status'] !== $filters['status'] && $vehicle['status'] !== 'deleted') {
                        return false;
                    }
                } else {
                    // Если фильтр по статусу не задан, исключаем удаленные автомобили
                    if (isset($vehicle['status']) && $vehicle['status'] === 'deleted') {
                        return false;
                    }
                }
                
                return true;
            });
        }
        
        // Сортировка (по убыванию цены)
        usort($vehicles, function($a, $b) {
            $makeA = $a['make'] ?? '';
            $makeB = $b['make'] ?? '';
            
            if ($makeA === $makeB) {
                $modelA = $a['model'] ?? '';
                $modelB = $b['model'] ?? '';
                return strcmp($modelA, $modelB);
            }
            
            return strcmp($makeA, $makeB);
        });
        
        // Применяем пагинацию
        if ($limit > 0) {
            $vehicles = array_slice($vehicles, $offset, $limit);
        }
        
        return array_values($vehicles);
    }
    
    /**
     * Получить данные конкретного автомобиля
     */
    public function getVehicleById($vehicleId) {
        $vehicleId = (int) $vehicleId;
        
        // Прямое чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
            
            if (isset($data['records']) && is_array($data['records'])) {
                foreach ($data['records'] as $vehicle) {
                    if (isset($vehicle['id']) && (int)$vehicle['id'] === $vehicleId) {
                        return $vehicle;
                    }
                }
            }
        }
        
        // Запасной вариант с использованием SQL-запроса
        $result = $this->db->query("SELECT * FROM vehicles WHERE id = $vehicleId");
        
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Добавить новый автомобиль
     */
    public function addVehicle($vehicleData) {
        // Логирование входных данных
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Данные для добавления автомобиля: " . print_r($vehicleData, true) . "\n", FILE_APPEND);
        
        try {
            // Создаем директорию data, если она не существует
            $dataDir = __DIR__ . '/../data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            // Чтение JSON-файла
            $jsonFile = __DIR__ . '/../data/vehicles.json';
            
            if (!file_exists($jsonFile)) {
                // Создаем новую структуру, если файла нет
                $data = [
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
                    ],
                    'indices' => [],
                    'auto_increment' => 1,
                    'records' => [],
                    'next_id' => 1
                ];
            } else {
                // Читаем существующие данные
                $jsonData = file_get_contents($jsonFile);
                $data = json_decode($jsonData, true);
                
                // Инициализируем структуру, если она отсутствует или повреждена
                if (!is_array($data)) {
                    $data = [
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
                        ],
                        'indices' => [],
                        'auto_increment' => 1,
                        'records' => [],
                        'next_id' => 1
                    ];
                } else {
                    if (!isset($data['next_id'])) {
                        $data['next_id'] = 1;
                        
                        // Найдем максимальный ID
                        if (isset($data['records']) && is_array($data['records'])) {
                            foreach ($data['records'] as $vehicle) {
                                if (isset($vehicle['id']) && (int)$vehicle['id'] >= $data['next_id']) {
                                    $data['next_id'] = (int)$vehicle['id'] + 1;
                                }
                            }
                        }
                    }
                    
                    if (!isset($data['records']) || !is_array($data['records'])) {
                        $data['records'] = [];
                    }
                }
            }
            
            // Определяем ID для нового автомобиля
            $vehicleId = $data['next_id'];
            $data['next_id']++;
            
            // Создаем новый автомобиль с обработкой отсутствующих полей
            $newVehicle = [
                'id' => $vehicleId,
                'make' => $vehicleData['make'] ?? '',
                'model' => $vehicleData['model'] ?? '',
                'year' => (int) ($vehicleData['year'] ?? 0),
                'engine' => $vehicleData['engine'] ?? '',
                'power' => (int) ($vehicleData['power'] ?? 0),
                'drive_type' => $vehicleData['drive_type'] ?? '',
                'transmission' => $vehicleData['transmission'] ?? '',
                'color' => $vehicleData['color'] ?? '',
                'interior' => $vehicleData['interior'] ?? '',
                'features' => $vehicleData['features'] ?? '',
                'image_url' => $vehicleData['image_url'] ?? '',
                'price' => (float) ($vehicleData['price'] ?? 0),
                'monthly_payment' => (float) ($vehicleData['monthly_payment'] ?? 0),
                'status' => $vehicleData['status'] ?? 'available',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Добавляем автомобиль в массив
            $data['records'][] = $newVehicle;
            
            // Сохраняем обновленные данные в файл
            $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
            
            if ($result !== false) {
                file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Автомобиль успешно добавлен, ID: " . $vehicleId . "\n", FILE_APPEND);
                
                return [
                    'success' => true,
                    'vehicle_id' => $vehicleId,
                    'message' => 'Автомобиль успешно добавлен'
                ];
            }
            
            // Если здесь, значит не удалось записать в файл
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Ошибка: не удалось записать данные в файл\n", FILE_APPEND);
            
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении автомобиля: не удалось записать данные в файл'
            ];
            
        } catch (Exception $e) {
            // Логирование исключения
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Исключение: " . $e->getMessage() . "\n", FILE_APPEND);
            
            return [
                'success' => false,
                'message' => 'Ошибка при добавлении автомобиля: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Обновить данные автомобиля
     */
    public function updateVehicle($vehicleId, $vehicleData) {
        $vehicleId = (int) $vehicleId;
        
        // Логирование
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на обновление автомобиля ID: " . $vehicleId . "\n", FILE_APPEND);
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл vehicles.json не найден\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Файл с автомобилями не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в vehicles.json\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных автомобилей'
            ];
        }
        
        $vehicleFound = false;
        $vehicleUpdated = false;
        
        // Обновляем данные автомобиля
        foreach ($data['records'] as &$vehicle) {
            if (isset($vehicle['id']) && (int)$vehicle['id'] === $vehicleId) {
                $vehicleFound = true;
                
                // Обновляем поля автомобиля
                if (isset($vehicleData['make'])) {
                    $vehicle['make'] = $vehicleData['make'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['model'])) {
                    $vehicle['model'] = $vehicleData['model'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['year'])) {
                    $vehicle['year'] = (int) $vehicleData['year'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['engine'])) {
                    $vehicle['engine'] = $vehicleData['engine'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['power'])) {
                    $vehicle['power'] = (int) $vehicleData['power'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['drive_type'])) {
                    $vehicle['drive_type'] = $vehicleData['drive_type'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['transmission'])) {
                    $vehicle['transmission'] = $vehicleData['transmission'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['color'])) {
                    $vehicle['color'] = $vehicleData['color'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['interior'])) {
                    $vehicle['interior'] = $vehicleData['interior'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['features'])) {
                    $vehicle['features'] = $vehicleData['features'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['image_url'])) {
                    $vehicle['image_url'] = $vehicleData['image_url'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['price'])) {
                    $vehicle['price'] = (float) $vehicleData['price'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['monthly_payment'])) {
                    $vehicle['monthly_payment'] = (float) $vehicleData['monthly_payment'];
                    $vehicleUpdated = true;
                }
                
                if (isset($vehicleData['status'])) {
                    $vehicle['status'] = $vehicleData['status'];
                    $vehicleUpdated = true;
                }
                
                if ($vehicleUpdated) {
                    $vehicle['updated_at'] = date('Y-m-d H:i:s');
                }
                
                break;
            }
        }
        
        if (!$vehicleFound) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Автомобиль с ID " . $vehicleId . " не найден\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Автомобиль не найден'
            ];
        }
        
        if (!$vehicleUpdated) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Нет данных для обновления автомобиля\n", FILE_APPEND);
            return [
                'success' => true,
                'message' => 'Нет данных для обновления'
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Автомобиль успешно обновлен\n", FILE_APPEND);
            return [
                'success' => true,
                'vehicle_id' => $vehicleId,
                'message' => 'Данные автомобиля успешно обновлены'
            ];
        }
        
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Ошибка при записи обновленных данных\n", FILE_APPEND);
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных автомобиля'
        ];
    }
    
    /**
     * Удалить автомобиль (мягкое или полное удаление)
     */
    public function deleteVehicle($vehicleId) {
        $vehicleId = (int) $vehicleId;
        
        // Логирование
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на удаление автомобиля ID: " . $vehicleId . "\n", FILE_APPEND);
        
        // Проверяем, есть ли заявки на этот автомобиль
        $applicationsFile = __DIR__ . '/../data/applications.json';
        $hasApplications = false;
        
        if (file_exists($applicationsFile)) {
            $applicationsData = file_get_contents($applicationsFile);
            $applications = json_decode($applicationsData, true);
            
            if (isset($applications['records']) && is_array($applications['records'])) {
                foreach ($applications['records'] as $application) {
                    if (isset($application['vehicle_id']) && (int)$application['vehicle_id'] === $vehicleId) {
                        $hasApplications = true;
                        break;
                    }
                }
            }
        }
        
        // Чтение JSON-файла автомобилей
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл vehicles.json не найден\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Файл с автомобилями не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в vehicles.json\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных автомобилей'
            ];
        }
        
        $vehicleFound = false;
        
        if ($hasApplications) {
            // Мягкое удаление - устанавливаем статус deleted
            foreach ($data['records'] as &$vehicle) {
                if (isset($vehicle['id']) && (int)$vehicle['id'] === $vehicleId) {
                    $vehicleFound = true;
                    $vehicle['status'] = 'deleted';
                    $vehicle['updated_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
        } else {
            // Полное удаление - удаляем запись из массива
            $newRecords = [];
            foreach ($data['records'] as $vehicle) {
                if (isset($vehicle['id']) && (int)$vehicle['id'] === $vehicleId) {
                    $vehicleFound = true;
                } else {
                    $newRecords[] = $vehicle;
                }
            }
            
            if ($vehicleFound) {
                $data['records'] = $newRecords;
            }
        }
        
        if (!$vehicleFound) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Автомобиль с ID " . $vehicleId . " не найден\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Автомобиль не найден'
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Автомобиль успешно удален (метод: " . ($hasApplications ? 'мягкое удаление' : 'полное удаление') . ")\n", FILE_APPEND);
            return [
                'success' => true,
                'message' => 'Автомобиль успешно удален'
            ];
        }
        
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Ошибка при удалении автомобиля\n", FILE_APPEND);
        return [
            'success' => false,
            'message' => 'Ошибка при удалении автомобиля'
        ];
    }
    
    /**
     * Получить список марок автомобилей
     */
    public function getVehicleMakes() {
        // Логирование для отладки
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на получение списка марок автомобилей\n", FILE_APPEND);
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        $makes = [];
        
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл vehicles.json не найден\n", FILE_APPEND);
            return $makes;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в vehicles.json\n", FILE_APPEND);
            return $makes;
        }
        
        // Извлекаем все уникальные марки
        foreach ($data['records'] as $vehicle) {
            if (isset($vehicle['make']) && !in_array($vehicle['make'], $makes) && (!isset($vehicle['status']) || $vehicle['status'] !== 'deleted')) {
                $makes[] = $vehicle['make'];
            }
        }
        
        // Сортировка марок по алфавиту
        sort($makes);
        
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Найдено марок: " . count($makes) . "\n", FILE_APPEND);
        
        return $makes;
    }
    
    /**
     * Получить список моделей для марки
     */
    public function getModelsByMake($make) {
        // Логирование для отладки
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на получение моделей для марки: " . $make . "\n", FILE_APPEND);
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/vehicles.json';
        $models = [];
        
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл vehicles.json не найден\n", FILE_APPEND);
            return $models;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в vehicles.json\n", FILE_APPEND);
            return $models;
        }
        
        // Извлекаем все уникальные модели для данной марки
        foreach ($data['records'] as $vehicle) {
            if (isset($vehicle['make']) && $vehicle['make'] === $make && 
                isset($vehicle['model']) && !in_array($vehicle['model'], $models) && 
                (!isset($vehicle['status']) || $vehicle['status'] !== 'deleted')) {
                $models[] = $vehicle['model'];
            }
        }
        
        // Сортировка моделей по алфавиту
        sort($models);
        
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Найдено моделей: " . count($models) . "\n", FILE_APPEND);
        
        return $models;
    }
    
    /**
     * Получить количество автомобилей
     */
    public function getVehiclesCount($filters = []) {
        // Логирование для отладки
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на getVehiclesCount с фильтрами: " . print_r($filters, true) . "\n", FILE_APPEND);
        
        // Используем тот же метод для получения автомобилей с фильтрами, но без пагинации
        $vehicles = $this->getAllVehicles(0, 0, $filters);
        
        // Возвращаем количество полученных автомобилей
        $count = count($vehicles);
        
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Найдено автомобилей: " . $count . "\n", FILE_APPEND);
        
        return $count;
    }
}
?>