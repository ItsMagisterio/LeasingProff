<?php
/**
 * Класс для работы с заявками на лизинг
 */
class Applications {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить список всех заявок
     */
    public function getAllApplications($limit = 0, $offset = 0, $filters = []) {
        $sql = "SELECT a.*, 
                u.first_name as client_first_name, 
                u.last_name as client_last_name,
                u.email as client_email,
                m.first_name as manager_first_name,
                m.last_name as manager_last_name,
                v.make as vehicle_make,
                v.model as vehicle_model,
                v.year as vehicle_year,
                re.title as real_estate_title,
                re.area as real_estate_area,
                re.address as real_estate_address,
                re.type as real_estate_type
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN users m ON a.manager_id = m.id
                LEFT JOIN vehicles v ON a.vehicle_id = v.id
                LEFT JOIN real_estate re ON a.real_estate_id = re.id
                WHERE 1=1";
        
        // Применяем фильтры
        if (!empty($filters)) {
            if (isset($filters['user_id']) && $filters['user_id']) {
                $userId = (int) $filters['user_id'];
                $sql .= " AND a.user_id = $userId";
            }
            
            if (isset($filters['manager_id']) && $filters['manager_id']) {
                $managerId = (int) $filters['manager_id'];
                $sql .= " AND a.manager_id = $managerId";
            }
            
            if (isset($filters['status']) && $filters['status']) {
                $status = $this->db->escapeString($filters['status']);
                $sql .= " AND a.status = '$status'";
            }
            
            if (isset($filters['unassigned']) && $filters['unassigned']) {
                $sql .= " AND a.manager_id IS NULL";
            }
        }
        
        // Сортировка и пагинация
        $sql .= " ORDER BY a.created_at DESC";
        
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
     * Получить данные конкретной заявки
     */
    public function getApplicationById($applicationId) {
        $applicationId = (int) $applicationId;
        $sql = "SELECT a.*, 
                u.first_name as client_first_name, 
                u.last_name as client_last_name,
                u.email as client_email,
                u.phone as client_phone,
                m.first_name as manager_first_name,
                m.last_name as manager_last_name,
                m.email as manager_email,
                v.make as vehicle_make,
                v.model as vehicle_model,
                v.year as vehicle_year,
                v.color as vehicle_color,
                v.price as vehicle_price,
                v.image_url as vehicle_image,
                re.title as real_estate_title,
                re.type as real_estate_type,
                re.area as real_estate_area,
                re.address as real_estate_address,
                re.price as real_estate_price,
                re.image_url as real_estate_image
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN users m ON a.manager_id = m.id
                LEFT JOIN vehicles v ON a.vehicle_id = v.id
                LEFT JOIN real_estate re ON a.real_estate_id = re.id
                WHERE a.id = $applicationId";
        
        $result = $this->db->query($sql);
        
        if (pg_num_rows($result) > 0) {
            return $this->db->fetchRow($result);
        }
        
        return null;
    }
    
    /**
     * Создать новую заявку на лизинг
     */
    public function createApplication($applicationData) {
        $userId = (int) $applicationData['user_id'];
        $initialPayment = (float) $applicationData['initial_payment'];
        $termMonths = (int) $applicationData['term_months'];
        $monthlyPayment = (float) $applicationData['monthly_payment'];
        $comments = $this->db->escapeString($applicationData['comments'] ?? '');
        $type = $this->db->escapeString($applicationData['type'] ?? 'vehicle');
        
        // Формируем SQL в зависимости от типа заявки
        if ($type === 'real_estate') {
            $realEstateId = (int) $applicationData['real_estate_id'];
            $sql = "INSERT INTO applications 
                    (user_id, real_estate_id, type, status, initial_payment, term_months, monthly_payment, comments)
                    VALUES 
                    ($userId, $realEstateId, '$type', 'new', $initialPayment, $termMonths, $monthlyPayment, '$comments')";
        } else {
            $vehicleId = (int) $applicationData['vehicle_id'];
            $sql = "INSERT INTO applications 
                    (user_id, vehicle_id, type, status, initial_payment, term_months, monthly_payment, comments)
                    VALUES 
                    ($userId, $vehicleId, '$type', 'new', $initialPayment, $termMonths, $monthlyPayment, '$comments')";
        }
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'application_id' => $this->db->lastInsertId('applications')
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при создании заявки'
        ];
    }
    
    /**
     * Создать новую заявку на лизинг недвижимости
     */
    public function createRealEstateApplication($applicationData) {
        $userId = (int) $applicationData['user_id'];
        $realEstateId = (int) $applicationData['real_estate_id'];
        $initialPayment = (float) $applicationData['initial_payment'];
        $termMonths = (int) $applicationData['term_months'];
        $monthlyPayment = (float) $applicationData['monthly_payment'];
        $comments = $this->db->escapeString($applicationData['comments'] ?? '');
        $type = $this->db->escapeString($applicationData['type'] ?? 'real_estate');
        
        $sql = "INSERT INTO applications 
                (user_id, real_estate_id, type, status, initial_payment, term_months, monthly_payment, comments)
                VALUES 
                ($userId, $realEstateId, '$type', 'new', $initialPayment, $termMonths, $monthlyPayment, '$comments')";
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'application_id' => $this->db->lastInsertId('applications')
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при создании заявки на недвижимость'
        ];
    }
    
    /**
     * Обновить статус заявки
     */
    public function updateApplicationStatus($applicationId, $status, $comments = '') {
        $applicationId = (int) $applicationId;
        $status = $this->db->escapeString($status);
        $comments = $this->db->escapeString($comments);
        
        $sql = "UPDATE applications 
                SET status = '$status', comments = '$comments', updated_at = CURRENT_TIMESTAMP 
                WHERE id = $applicationId";
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'application_id' => $applicationId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении статуса заявки'
        ];
    }
    
    /**
     * Назначить менеджера на заявку
     */
    public function assignManager($applicationId, $managerId) {
        $applicationId = (int) $applicationId;
        $managerId = (int) $managerId;
        
        $sql = "UPDATE applications 
                SET manager_id = $managerId, updated_at = CURRENT_TIMESTAMP 
                WHERE id = $applicationId";
        
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'application_id' => $applicationId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при назначении менеджера'
        ];
    }
    
    /**
     * Получить количество заявок по статусам
     */
    public function getApplicationsCountByStatus($userId = null) {
        $counts = [
            'total' => 0,
            'new' => 0,
            'in_progress' => 0,
            'approved' => 0,
            'rejected' => 0,
            'signed' => 0,
            'completed' => 0
        ];
        
        $userFilter = '';
        if ($userId) {
            $userId = (int) $userId;
            $userFilter = "WHERE user_id = $userId";
        }
        
        $sql = "SELECT status, COUNT(*) as count
                FROM applications
                $userFilter
                GROUP BY status";
        
        $result = $this->db->query($sql);
        $rows = $this->db->fetchAll($result);
        
        if ($rows) {
            foreach ($rows as $row) {
                $status = $row['status'];
                $count = $row['count'];
                
                if (isset($counts[$status])) {
                    $counts[$status] = (int) $count;
                }
                
                $counts['total'] += (int) $count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Получить количество заявок по менеджерам
     */
    public function getApplicationsCountByManager() {
        $sql = "SELECT 
                m.id as manager_id,
                m.first_name,
                m.last_name,
                COUNT(a.id) as total,
                SUM(CASE WHEN a.status = 'new' THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN a.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN a.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM users m
                LEFT JOIN applications a ON m.id = a.manager_id
                WHERE m.role = 'manager'
                GROUP BY m.id, m.first_name, m.last_name
                ORDER BY total DESC";
        
        $result = $this->db->query($sql);
        return $this->db->fetchAll($result);
    }
    
    /**
     * Получить количество неназначенных заявок
     */
    public function getUnassignedApplicationsCount() {
        $sql = "SELECT COUNT(*) FROM applications WHERE manager_id IS NULL";
        $result = $this->db->query($sql);
        $row = pg_fetch_row($result);
        
        return (int) $row[0];
    }
    
    /**
     * Получить неназначенные заявки
     */
    public function getUnassignedApplications($limit = 0) {
        $sql = "SELECT a.*, 
                u.first_name as client_first_name, 
                u.last_name as client_last_name,
                v.make as vehicle_make,
                v.model as vehicle_model,
                re.title as real_estate_title,
                re.type as real_estate_type
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN vehicles v ON a.vehicle_id = v.id
                LEFT JOIN real_estate re ON a.real_estate_id = re.id
                WHERE a.manager_id IS NULL
                ORDER BY a.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $result = $this->db->query($sql);
        return $this->db->fetchAll($result);
    }
}
?>