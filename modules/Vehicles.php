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
        $sql = "SELECT * FROM vehicles WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters)) {
            if (isset($filters['make']) && $filters['make']) {
                $make = $this->db->escapeString($filters['make']);
                $sql .= " AND make = '$make'";
            }
            
            if (isset($filters['model']) && $filters['model']) {
                $model = $this->db->escapeString($filters['model']);
                $sql .= " AND model = '$model'";
            }
            
            if (isset($filters['min_price']) && $filters['min_price']) {
                $minPrice = (float) $filters['min_price'];
                $sql .= " AND price >= $minPrice";
            }
            
            if (isset($filters['max_price']) && $filters['max_price']) {
                $maxPrice = (float) $filters['max_price'];
                $sql .= " AND price <= $maxPrice";
            }
            
            if (isset($filters['status']) && $filters['status']) {
                $status = $this->db->escapeString($filters['status']);
                $sql .= " AND status = '$status'";
            }
        }
        
        // Сортировка и пагинация
        $sql .= " ORDER BY make, model";
        
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
     * Получить данные конкретного автомобиля
     */
    public function getVehicleById($vehicleId) {
        $vehicleId = (int) $vehicleId;
        $result = $this->db->query("SELECT * FROM vehicles WHERE id = $vehicleId");
        
        if (count($result) > 0) {
            return $this->db->fetchRow($result);
        }
        
        return null;
    }
    
    /**
     * Добавить новый автомобиль
     */
    public function addVehicle($vehicleData) {
        $make = $this->db->escapeString($vehicleData['make']);
        $model = $this->db->escapeString($vehicleData['model']);
        $year = (int) $vehicleData['year'];
        $engine = $this->db->escapeString($vehicleData['engine']);
        $power = (int) $vehicleData['power'];
        $driveType = $this->db->escapeString($vehicleData['drive_type']);
        $transmission = $this->db->escapeString($vehicleData['transmission']);
        $color = $this->db->escapeString($vehicleData['color']);
        $interior = $this->db->escapeString($vehicleData['interior']);
        $features = $this->db->escapeString($vehicleData['features']);
        $imageUrl = $this->db->escapeString($vehicleData['image_url']);
        $price = (float) $vehicleData['price'];
        $monthlyPayment = (float) $vehicleData['monthly_payment'];
        $status = $this->db->escapeString($vehicleData['status'] ?? 'available');
        
        $sql = "INSERT INTO vehicles 
                (make, model, year, engine, power, drive_type, transmission, color, interior, features, 
                 image_url, price, monthly_payment, status)
                VALUES 
                ('$make', '$model', $year, '$engine', $power, '$driveType', '$transmission', 
                 '$color', '$interior', '$features', '$imageUrl', $price, $monthlyPayment, '$status')";
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'vehicle_id' => $this->db->lastInsertId('vehicles')
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при добавлении автомобиля'
        ];
    }
    
    /**
     * Обновить данные автомобиля
     */
    public function updateVehicle($vehicleId, $vehicleData) {
        $vehicleId = (int) $vehicleId;
        
        // Проверяем существование автомобиля
        $result = $this->db->query("SELECT id FROM vehicles WHERE id = $vehicleId");
        
        if (count($result) === 0) {
            return [
                'success' => false,
                'message' => 'Автомобиль не найден'
            ];
        }
        
        $updates = [];
        
        if (isset($vehicleData['make'])) {
            $make = $this->db->escapeString($vehicleData['make']);
            $updates[] = "make = '$make'";
        }
        
        if (isset($vehicleData['model'])) {
            $model = $this->db->escapeString($vehicleData['model']);
            $updates[] = "model = '$model'";
        }
        
        if (isset($vehicleData['year'])) {
            $year = (int) $vehicleData['year'];
            $updates[] = "year = $year";
        }
        
        if (isset($vehicleData['engine'])) {
            $engine = $this->db->escapeString($vehicleData['engine']);
            $updates[] = "engine = '$engine'";
        }
        
        if (isset($vehicleData['power'])) {
            $power = (int) $vehicleData['power'];
            $updates[] = "power = $power";
        }
        
        if (isset($vehicleData['drive_type'])) {
            $driveType = $this->db->escapeString($vehicleData['drive_type']);
            $updates[] = "drive_type = '$driveType'";
        }
        
        if (isset($vehicleData['transmission'])) {
            $transmission = $this->db->escapeString($vehicleData['transmission']);
            $updates[] = "transmission = '$transmission'";
        }
        
        if (isset($vehicleData['color'])) {
            $color = $this->db->escapeString($vehicleData['color']);
            $updates[] = "color = '$color'";
        }
        
        if (isset($vehicleData['interior'])) {
            $interior = $this->db->escapeString($vehicleData['interior']);
            $updates[] = "interior = '$interior'";
        }
        
        if (isset($vehicleData['features'])) {
            $features = $this->db->escapeString($vehicleData['features']);
            $updates[] = "features = '$features'";
        }
        
        if (isset($vehicleData['image_url'])) {
            $imageUrl = $this->db->escapeString($vehicleData['image_url']);
            $updates[] = "image_url = '$imageUrl'";
        }
        
        if (isset($vehicleData['price'])) {
            $price = (float) $vehicleData['price'];
            $updates[] = "price = $price";
        }
        
        if (isset($vehicleData['monthly_payment'])) {
            $monthlyPayment = (float) $vehicleData['monthly_payment'];
            $updates[] = "monthly_payment = $monthlyPayment";
        }
        
        if (isset($vehicleData['status'])) {
            $status = $this->db->escapeString($vehicleData['status']);
            $updates[] = "status = '$status'";
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE vehicles SET " . implode(', ', $updates) . " WHERE id = $vehicleId";
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'vehicle_id' => $vehicleId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных автомобиля'
        ];
    }
    
    /**
     * Удалить автомобиль
     */
    public function deleteVehicle($vehicleId) {
        $vehicleId = (int) $vehicleId;
        
        // Проверяем, есть ли заявки на этот автомобиль
        $result = $this->db->query("SELECT id FROM applications WHERE vehicle_id = $vehicleId");
        
        if (count($result) > 0) {
            return [
                'success' => false,
                'message' => 'Невозможно удалить автомобиль, так как на него есть заявки'
            ];
        }
        
        $result = $this->db->query("DELETE FROM vehicles WHERE id = $vehicleId");
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'message' => 'Автомобиль успешно удален'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при удалении автомобиля'
        ];
    }
    
    /**
     * Получить список марок автомобилей
     */
    public function getVehicleMakes() {
        $result = $this->db->query("SELECT DISTINCT make FROM vehicles ORDER BY make");
        $makes = [];
        
        foreach ($result as $row) {
            $makes[] = $row['make'];
        }
        
        return $makes;
    }
    
    /**
     * Получить список моделей для марки
     */
    public function getModelsByMake($make) {
        $make = $this->db->escapeString($make);
        $result = $this->db->query("SELECT DISTINCT model FROM vehicles WHERE make = '$make' ORDER BY model");
        $models = [];
        
        foreach ($result as $row) {
            $models[] = $row['model'];
        }
        
        return $models;
    }
    
    /**
     * Получить количество автомобилей
     */
    public function getVehiclesCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM vehicles WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters)) {
            if (isset($filters['make']) && $filters['make']) {
                $make = $this->db->escapeString($filters['make']);
                $sql .= " AND make = '$make'";
            }
            
            if (isset($filters['model']) && $filters['model']) {
                $model = $this->db->escapeString($filters['model']);
                $sql .= " AND model = '$model'";
            }
            
            if (isset($filters['min_price']) && $filters['min_price']) {
                $minPrice = (float) $filters['min_price'];
                $sql .= " AND price >= $minPrice";
            }
            
            if (isset($filters['max_price']) && $filters['max_price']) {
                $maxPrice = (float) $filters['max_price'];
                $sql .= " AND price <= $maxPrice";
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
}
?>