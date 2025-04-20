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
        $sql = "SELECT * FROM real_estate WHERE 1=1";
        
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
        
        // Сортировка и пагинация
        $sql .= " ORDER BY price DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
            
            if ($offset > 0) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $result = $this->db->query($sql);
        return $this->db->fetchAll($result);
    }
    
    /**
     * Получить данные конкретного объекта недвижимости
     */
    public function getRealEstateById($realEstateId) {
        $realEstateId = (int) $realEstateId;
        $result = $this->db->query("SELECT * FROM real_estate WHERE id = $realEstateId");
        
        if (count($result) > 0) {
            return $this->db->fetchRow($result);
        }
        
        return null;
    }
    
    /**
     * Добавить новый объект недвижимости
     */
    public function addRealEstate($realEstateData) {
        $title = $this->db->escapeString($realEstateData['title']);
        $type = $this->db->escapeString($realEstateData['type']);
        $address = $this->db->escapeString($realEstateData['address']);
        $area = (float) $realEstateData['area'];
        $rooms = (int) $realEstateData['rooms'];
        $floor = (int) $realEstateData['floor'];
        $totalFloors = (int) $realEstateData['total_floors'];
        $buildYear = (int) $realEstateData['build_year'];
        $description = $this->db->escapeString($realEstateData['description']);
        $features = $this->db->escapeString($realEstateData['features']);
        $imageUrl = $this->db->escapeString($realEstateData['image_url']);
        $price = (float) $realEstateData['price'];
        $monthlyPayment = (float) $realEstateData['monthly_payment'];
        $status = $this->db->escapeString($realEstateData['status'] ?? 'available');
        
        $sql = "INSERT INTO real_estate 
                (title, type, address, area, rooms, floor, total_floors, build_year, description, features, image_url, price, monthly_payment, status)
                VALUES 
                ('$title', '$type', '$address', $area, $rooms, $floor, $totalFloors, $buildYear, '$description', '$features', '$imageUrl', $price, $monthlyPayment, '$status')";
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'real_estate_id' => $this->db->lastInsertId('real_estate')
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
        
        // Проверяем существование объекта
        $result = $this->db->query("SELECT id FROM real_estate WHERE id = $realEstateId");
        
        if (count($result) === 0) {
            return [
                'success' => false,
                'message' => 'Объект недвижимости не найден'
            ];
        }
        
        $updates = [];
        
        if (isset($realEstateData['title'])) {
            $title = $this->db->escapeString($realEstateData['title']);
            $updates[] = "title = '$title'";
        }
        
        if (isset($realEstateData['type'])) {
            $type = $this->db->escapeString($realEstateData['type']);
            $updates[] = "type = '$type'";
        }
        
        if (isset($realEstateData['address'])) {
            $address = $this->db->escapeString($realEstateData['address']);
            $updates[] = "address = '$address'";
        }
        
        if (isset($realEstateData['area'])) {
            $area = (float) $realEstateData['area'];
            $updates[] = "area = $area";
        }
        
        if (isset($realEstateData['rooms'])) {
            $rooms = (int) $realEstateData['rooms'];
            $updates[] = "rooms = $rooms";
        }
        
        if (isset($realEstateData['floor'])) {
            $floor = (int) $realEstateData['floor'];
            $updates[] = "floor = $floor";
        }
        
        if (isset($realEstateData['total_floors'])) {
            $totalFloors = (int) $realEstateData['total_floors'];
            $updates[] = "total_floors = $totalFloors";
        }
        
        if (isset($realEstateData['build_year'])) {
            $buildYear = (int) $realEstateData['build_year'];
            $updates[] = "build_year = $buildYear";
        }
        
        if (isset($realEstateData['description'])) {
            $description = $this->db->escapeString($realEstateData['description']);
            $updates[] = "description = '$description'";
        }
        
        if (isset($realEstateData['features'])) {
            $features = $this->db->escapeString($realEstateData['features']);
            $updates[] = "features = '$features'";
        }
        
        if (isset($realEstateData['image_url'])) {
            $imageUrl = $this->db->escapeString($realEstateData['image_url']);
            $updates[] = "image_url = '$imageUrl'";
        }
        
        if (isset($realEstateData['price'])) {
            $price = (float) $realEstateData['price'];
            $updates[] = "price = $price";
        }
        
        if (isset($realEstateData['monthly_payment'])) {
            $monthlyPayment = (float) $realEstateData['monthly_payment'];
            $updates[] = "monthly_payment = $monthlyPayment";
        }
        
        if (isset($realEstateData['status'])) {
            $status = $this->db->escapeString($realEstateData['status']);
            $updates[] = "status = '$status'";
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE real_estate SET " . implode(', ', $updates) . " WHERE id = $realEstateId";
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
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
        $result = $this->db->query("SELECT id FROM applications WHERE real_estate_id = $realEstateId");
        
        if (count($result) > 0) {
            return [
                'success' => false,
                'message' => 'Невозможно удалить объект недвижимости, так как на него есть заявки'
            ];
        }
        
        $result = $this->db->query("DELETE FROM real_estate WHERE id = $realEstateId");
        
        if ($this->db->affectedRows($result) > 0) {
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
        $result = $this->db->query("SELECT DISTINCT type FROM real_estate ORDER BY type");
        $types = [];
        
        foreach ($result as $row) {
            $types[] = $row['type'];
        }
        
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
        $result = $this->db->query("SELECT MIN(price) as min, MAX(price) as max FROM real_estate");
        
        if (pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            return [
                'min' => (int) $row['min'],
                'max' => (int) $row['max']
            ];
        }
        
        return [
            'min' => 0,
            'max' => 10000000
        ];
    }
    
    /**
     * Получить диапазон площадей недвижимости
     */
    public function getRealEstateAreaRange() {
        $result = $this->db->query("SELECT MIN(area) as min, MAX(area) as max FROM real_estate");
        
        if (pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            return [
                'min' => (float) $row['min'],
                'max' => (float) $row['max']
            ];
        }
        
        return [
            'min' => 0,
            'max' => 500
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
        $row = pg_fetch_row($result);
        
        return (int) $row[0];
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
        
        while ($row = pg_fetch_assoc($result)) {
            $values[] = $row[$field];
        }
        
        return $values;
    }
}
?>