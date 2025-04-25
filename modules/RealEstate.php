<?php
/**
 * Класс для работы с объектами недвижимости
 */
class RealEstate {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Получить список всех объектов недвижимости
     */
    public function getAllRealEstate($limit = 0, $offset = 0, $filters = []) {
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        // Логирование для отладки
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Запрос на getAllRealEstate с фильтрами: " . print_r($filters, true) . "\n", FILE_APPEND);
        
        // Проверяем существование файла
        if (!file_exists($jsonFile)) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Файл real_estate.json не найден\n", FILE_APPEND);
            return [];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        // Проверяем корректность данных
        if (!isset($data['records']) || !is_array($data['records'])) {
            file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Некорректная структура данных в real_estate.json\n", FILE_APPEND);
            return [];
        }
        
        $properties = $data['records'];
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Найдено записей: " . count($properties) . "\n", FILE_APPEND);
        
        // Apply filters
        if (!empty($filters)) {
            $properties = array_filter($properties, function($property) use ($filters) {
                // Логируем свойство для отладки (только первое)
                static $logged = false;
                if (!$logged) {
                    file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Проверяем свойство: " . print_r($property, true) . "\n", FILE_APPEND);
                    $logged = true;
                }
                
                if (isset($filters['type']) && $filters['type'] && (!isset($property['type']) || $property['type'] !== $filters['type'])) {
                    return false;
                }
                if (isset($filters['min_price']) && $filters['min_price'] && (!isset($property['price']) || $property['price'] < $filters['min_price'])) {
                    return false;
                }
                if (isset($filters['max_price']) && $filters['max_price'] && (!isset($property['price']) || $property['price'] > $filters['max_price'])) {
                    return false;
                }
                if (isset($filters['min_area']) && $filters['min_area'] && (!isset($property['area']) || $property['area'] < $filters['min_area'])) {
                    return false;
                }
                if (isset($filters['max_area']) && $filters['max_area'] && (!isset($property['area']) || $property['area'] > $filters['max_area'])) {
                    return false;
                }
                if (isset($filters['rooms']) && $filters['rooms'] && (!isset($property['rooms']) || $property['rooms'] != $filters['rooms'])) {
                    return false;
                }
                if (isset($filters['status']) && $filters['status']) {
                    // Если статус не указан в объекте, считаем его доступным по умолчанию
                    if (!isset($property['status'])) {
                        return $filters['status'] === 'available';
                    } else if ($property['status'] !== $filters['status']) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Sort by price descending
        usort($properties, function($a, $b) {
            return $b['price'] - $a['price'];
        });

        // Apply pagination
        if ($limit > 0) {
            $properties = array_slice($properties, $offset, $limit);
        }

        return array_values($properties);
    }

    /**
     * Получить данные конкретного объекта недвижимости
     */
    public function getRealEstateById($realEstateId) {
        $realEstateId = (int) $realEstateId;
        
        // Прямое чтение из JSON-файла (совместимо с getAllRealEstate)
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        if (file_exists($jsonFile)) {
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
            
            if (isset($data['records']) && is_array($data['records'])) {
                foreach ($data['records'] as $property) {
                    if (isset($property['id']) && (int)$property['id'] === $realEstateId) {
                        return $property;
                    }
                }
            }
        }
        
        // Запасной вариант с использованием SQL-запроса
        $result = $this->db->query("SELECT * FROM real_estate WHERE id = $realEstateId");
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    /**
     * Добавить новый объект недвижимости
     */
    public function addRealEstate($realEstateData) {
        // Логирование входных данных
        file_put_contents(__DIR__ . '/../debug.log', date('Y-m-d H:i:s') . " - Данные для добавления недвижимости: " . print_r($realEstateData, true) . "\n", FILE_APPEND);

        // Создаем директорию data, если она не существует
        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            // Создаем новую структуру, если файла нет
            $data = [
                'next_id' => 1,
                'records' => []
            ];
        } else {
            // Читаем существующие данные
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);
            
            // Инициализируем структуру, если она отсутствует или повреждена
            if (!is_array($data)) {
                $data = ['next_id' => 1, 'records' => []];
            } else {
                if (!isset($data['next_id'])) {
                    $data['next_id'] = 1;
                }
                
                if (!isset($data['records']) || !is_array($data['records'])) {
                    $data['records'] = [];
                }
            }
        }
        
        // Определяем ID для нового объекта недвижимости
        $realEstateId = $data['next_id'];
        $data['next_id']++;
        
        // Создаем новый объект недвижимости с обработкой отсутствующих полей
        $newRealEstate = [
            'id' => $realEstateId,
            'title' => $realEstateData['title'] ?? '',
            'type' => $realEstateData['type'] ?? '',
            'status' => $realEstateData['status'] ?? 'available',
            'address' => $realEstateData['address'] ?? '',
            'area' => (float) ($realEstateData['area'] ?? 0),
            'rooms' => (int) ($realEstateData['rooms'] ?? 0),
            'floor' => (int) ($realEstateData['floor'] ?? 0),
            'description' => $realEstateData['description'] ?? '',
            'features' => $realEstateData['features'] ?? '',
            'image_url' => $realEstateData['image_url'] ?? '',
            'price' => (float) ($realEstateData['price'] ?? 0),
            'monthly_payment' => (float) ($realEstateData['monthly_payment'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Добавляем дополнительные поля, если они есть
        if (isset($realEstateData['total_floors'])) {
            $newRealEstate['total_floors'] = (int) $realEstateData['total_floors'];
        }
        
        if (isset($realEstateData['build_year'])) {
            $newRealEstate['build_year'] = (int) $realEstateData['build_year'];
        }
        
        // Добавляем объект в массив
        $data['records'][] = $newRealEstate;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            return [
                'success' => true,
                'real_estate_id' => $realEstateId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при добавлении объекта недвижимости'
        ];
    }

    /**
     * Обновить данные объекта недвижимости
     */
    public function updateRealEstate($realEstateId, $realEstateData) {
        $realEstateId = (int) $realEstateId;
        
        // Чтение JSON-файла
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            return [
                'success' => false,
                'message' => 'Файл с объектами недвижимости не найден'
            ];
        }
        
        // Читаем существующие данные
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных объектов недвижимости'
            ];
        }
        
        $propertyFound = false;
        $propertyUpdated = false;
        
        // Обновляем данные объекта недвижимости
        foreach ($data['records'] as &$property) {
            if (isset($property['id']) && (int)$property['id'] === $realEstateId) {
                $propertyFound = true;
                
                // Обновляем поля объекта
                if (isset($realEstateData['title'])) {
                    $property['title'] = $realEstateData['title'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['type'])) {
                    $property['type'] = $realEstateData['type'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['address'])) {
                    $property['address'] = $realEstateData['address'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['area'])) {
                    $property['area'] = (float) $realEstateData['area'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['rooms'])) {
                    $property['rooms'] = (int) $realEstateData['rooms'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['floor'])) {
                    $property['floor'] = (int) $realEstateData['floor'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['total_floors'])) {
                    $property['total_floors'] = (int) $realEstateData['total_floors'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['build_year'])) {
                    $property['build_year'] = (int) $realEstateData['build_year'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['description'])) {
                    $property['description'] = $realEstateData['description'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['features'])) {
                    $property['features'] = $realEstateData['features'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['image_url'])) {
                    $property['image_url'] = $realEstateData['image_url'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['price'])) {
                    $property['price'] = (float) $realEstateData['price'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['monthly_payment'])) {
                    $property['monthly_payment'] = (float) $realEstateData['monthly_payment'];
                    $propertyUpdated = true;
                }
                
                if (isset($realEstateData['status'])) {
                    $property['status'] = $realEstateData['status'];
                    $propertyUpdated = true;
                }
                
                $property['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        if (!$propertyFound) {
            return [
                'success' => false,
                'message' => 'Объект недвижимости не найден'
            ];
        }
        
        if (!$propertyUpdated) {
            return [
                'success' => true,
                'message' => 'Нет изменений в данных объекта недвижимости',
                'real_estate_id' => $realEstateId
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            return [
                'success' => true,
                'real_estate_id' => $realEstateId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных объекта недвижимости'
        ];
    }

    /**
     * Удалить объект недвижимости
     */
    public function deleteRealEstate($realEstateId) {
        $realEstateId = (int) $realEstateId;
        
        // Проверяем, есть ли заявки на этот объект
        $applicationsFile = __DIR__ . '/../data/applications.json';
        if (file_exists($applicationsFile)) {
            $applicationsData = file_get_contents($applicationsFile);
            $applications = json_decode($applicationsData, true);
            
            if (isset($applications['records']) && is_array($applications['records'])) {
                foreach ($applications['records'] as $application) {
                    if (isset($application['real_estate_id']) && (int)$application['real_estate_id'] === $realEstateId) {
                        return [
                            'success' => false,
                            'message' => 'Невозможно удалить объект недвижимости, так как на него есть заявки'
                        ];
                    }
                }
            }
        }
        
        // Удаляем объект недвижимости
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            return [
                'success' => false,
                'message' => 'Файл с объектами недвижимости не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных объектов недвижимости'
            ];
        }
        
        $propertyFound = false;
        $newRecords = [];
        
        // Фильтруем объекты, исключая тот, который нужно удалить
        foreach ($data['records'] as $property) {
            if (isset($property['id']) && (int)$property['id'] === $realEstateId) {
                $propertyFound = true;
            } else {
                $newRecords[] = $property;
            }
        }
        
        if (!$propertyFound) {
            return [
                'success' => false,
                'message' => 'Объект недвижимости не найден'
            ];
        }
        
        // Обновляем массив объектов недвижимости
        $data['records'] = $newRecords;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => 'Объект недвижимости успешно удален'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при удалении объекта недвижимости'
        ];
    }

    /**
     * Получить список типов недвижимости
     */
    public function getRealEstateTypes() {
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        $types = [];
        
        if (!file_exists($jsonFile)) {
            return $types;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records'])) {
            return $types;
        }
        
        // Извлекаем все типы недвижимости
        foreach ($data['records'] as $property) {
            if (isset($property['type']) && !in_array($property['type'], $types)) {
                $types[] = $property['type'];
            }
        }
        
        // Сортировка типов
        sort($types);
        
        return $types;
    }

    /**
     * Получить список уникальных типов недвижимости
     */
    public function getUniqueRealEstateTypes() {
        return $this->getRealEstateTypes();
    }

    /**
     * Получить диапазон цен недвижимости
     */
    public function getRealEstatePriceRange() {
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        // Значения по умолчанию
        $min = 0;
        $max = 10000000;
        
        if (!file_exists($jsonFile)) {
            return [
                'min' => $min,
                'max' => $max
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records']) || empty($data['records'])) {
            return [
                'min' => $min,
                'max' => $max
            ];
        }
        
        // Находим минимальную и максимальную цену
        $prices = [];
        foreach ($data['records'] as $property) {
            if (isset($property['price'])) {
                $prices[] = (float) $property['price'];
            }
        }
        
        if (!empty($prices)) {
            $min = min($prices);
            $max = max($prices);
        }
        
        return [
            'min' => (int) $min,
            'max' => (int) $max
        ];
    }

    /**
     * Получить диапазон площадей недвижимости
     */
    public function getRealEstateAreaRange() {
        $jsonFile = __DIR__ . '/../data/real_estate.json';
        
        // Значения по умолчанию
        $min = 0;
        $max = 500;
        
        if (!file_exists($jsonFile)) {
            return [
                'min' => $min,
                'max' => $max
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
        
        if (!isset($data['records']) || !is_array($data['records']) || empty($data['records'])) {
            return [
                'min' => $min,
                'max' => $max
            ];
        }
        
        // Находим минимальную и максимальную площадь
        $areas = [];
        foreach ($data['records'] as $property) {
            if (isset($property['area'])) {
                $areas[] = (float) $property['area'];
            }
        }
        
        if (!empty($areas)) {
            $min = min($areas);
            $max = max($areas);
        }
        
        return [
            'min' => (float) $min,
            'max' => (float) $max
        ];
    }

    /**
     * Получить количество объектов недвижимости
     */
    public function getRealEstateCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM real_estate WHERE 1=1";

        // Применяем фильтры
        if (!empty($filters)) {
            if (isset($filters['type']) && $filters['type']) {
                $type = $this->db->escapeString($filters['type']);
                $sql .= " AND type = '$type'";
            }

            if (isset($filters['min_price']) && $filters['min_price']) {
                $minPrice = (float) $filters['min_price'];
                $sql .= " AND price >= $minPrice";
            }

            if (isset($filters['max_price']) && $filters['max_price']) {
                $maxPrice = (float) $filters['max_price'];
                $sql .= " AND price <= $maxPrice";
            }

            if (isset($filters['min_area']) && $filters['min_area']) {
                $minArea = (float) $filters['min_area'];
                $sql .= " AND area >= $minArea";
            }

            if (isset($filters['max_area']) && $filters['max_area']) {
                $maxArea = (float) $filters['max_area'];
                $sql .= " AND area <= $maxArea";
            }

            if (isset($filters['rooms']) && $filters['rooms']) {
                $rooms = (int) $filters['rooms'];
                $sql .= " AND rooms = $rooms";
            }

            if (isset($filters['status']) && $filters['status']) {
                $status = $this->db->escapeString($filters['status']);
                $sql .= " AND status = '$status'";
            }
        }

        $result = $this->db->query($sql);

        if (is_array($result) && isset($result[0]) && isset($result[0]['COUNT(*)'])) {
            return (int) $result[0]['COUNT(*)'];
        }

        return 0;
    }

    /**
     * Получить список уникальных значений по полю
     */
    public function getDistinctValues($field) {
        $allowedFields = ['type', 'rooms', 'build_year'];

        if (!in_array($field, $allowedFields)) {
            return [];
        }

        $field = $this->db->escapeString($field);
        $result = $this->db->query("SELECT DISTINCT $field FROM real_estate ORDER BY $field");
        $values = [];

        if (!empty($result)) {
            foreach ($result as $row) {
                if (isset($row[$field])) {
                    $values[] = $row[$field];
                }
            }
        }

        return $values;
    }
}
?>