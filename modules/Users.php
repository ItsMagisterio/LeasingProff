<?php
/**
 * Класс для работы с пользователями
 */
class Users {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Получить список всех пользователей
     */
    public function getAllUsers($role = null, $limit = 0, $offset = 0) {
        $sql = "SELECT id, email, first_name, last_name, phone, role, created_at FROM users";
        
        if ($role) {
            $role = $this->db->escapeString($role);
            $sql .= " WHERE role = '$role'";
        }
        
        $sql .= " ORDER BY last_name, first_name";
        
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
     * Получить данные конкретного пользователя
     */
    public function getUserById($userId) {
        $userId = (int) $userId;
        $sql = "SELECT id, email, first_name, last_name, phone, role, created_at FROM users WHERE id = $userId";
        
        $result = $this->db->query($sql);
        
        if (count($result) > 0) {
            return $this->db->fetchRow($result);
        }
        
        return null;
    }
    
    /**
     * Получить данные пользователя по email
     */
    public function getUserByEmail($email) {
        $email = $this->db->escapeString($email);
        $sql = "SELECT id, email, first_name, last_name, phone, role, created_at FROM users WHERE email = '$email'";
        
        $result = $this->db->query($sql);
        
        if (count($result) > 0) {
            return $this->db->fetchRow($result);
        }
        
        return null;
    }
    
    /**
     * Обновить данные пользователя
     */
    public function updateUser($userId, $userData) {
        $userId = (int) $userId;
        
        // Проверяем существование пользователя
        $result = $this->db->query("SELECT id FROM users WHERE id = $userId");
        
        if (count($result) === 0) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }
        
        $updates = [];
        
        if (isset($userData['first_name'])) {
            $firstName = $this->db->escapeString($userData['first_name']);
            $updates[] = "first_name = '$firstName'";
        }
        
        if (isset($userData['last_name'])) {
            $lastName = $this->db->escapeString($userData['last_name']);
            $updates[] = "last_name = '$lastName'";
        }
        
        if (isset($userData['phone'])) {
            $phone = $this->db->escapeString($userData['phone']);
            $updates[] = "phone = '$phone'";
        }
        
        if (isset($userData['role'])) {
            $role = $this->db->escapeString($userData['role']);
            $updates[] = "role = '$role'";
        }
        
        if (isset($userData['password']) && $userData['password']) {
            $password = password_hash($userData['password'], PASSWORD_DEFAULT);
            $updates[] = "password = '$password'";
        }
        
        $updates[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $userId";
        $result = $this->db->query($sql);
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'user_id' => $userId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных пользователя'
        ];
    }
    
    /**
     * Удалить пользователя
     */
    public function deleteUser($userId) {
        $userId = (int) $userId;
        
        // Проверяем, есть ли заявки у этого пользователя
        $result = $this->db->query("SELECT id FROM applications WHERE user_id = $userId");
        
        if (pg_num_rows($result) > 0) {
            return [
                'success' => false,
                'message' => 'Невозможно удалить пользователя, так как у него есть заявки'
            ];
        }
        
        $result = $this->db->query("DELETE FROM users WHERE id = $userId");
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'message' => 'Пользователь успешно удален'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при удалении пользователя'
        ];
    }
    
    /**
     * Получить количество пользователей по ролям
     */
    public function getUsersCountByRole() {
        $counts = [
            'total' => 0,
            'client' => 0,
            'manager' => 0,
            'admin' => 0
        ];
        
        $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $result = $this->db->query($sql);
        $rows = $this->db->fetchAll($result);
        
        if ($rows) {
            foreach ($rows as $row) {
                $role = $row['role'];
                $count = $row['count'];
                
                if (isset($counts[$role])) {
                    $counts[$role] = (int) $count;
                }
                
                $counts['total'] += (int) $count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Получить список менеджеров
     */
    public function getManagers() {
        $sql = "SELECT id, first_name, last_name, email FROM users WHERE role = 'manager' ORDER BY last_name, first_name";
        $result = $this->db->query($sql);
        return $this->db->fetchAll($result);
    }
    
    /**
     * Добавить нового менеджера
     */
    public function createManager($userData) {
        // Проверяем, не занят ли email
        $email = pg_escape_string($userData['email']);
        $result = $this->db->query("SELECT id FROM users WHERE email = '$email'");
        
        if (pg_num_rows($result) > 0) {
            return [
                'success' => false,
                'message' => 'Email уже зарегистрирован в системе'
            ];
        }
        
        // Хэшируем пароль
        $password = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Подготавливаем данные
        $firstName = pg_escape_string($userData['first_name']);
        $lastName = pg_escape_string($userData['last_name']);
        $phone = pg_escape_string($userData['phone']);
        $role = 'manager';
        
        // Добавляем пользователя
        $result = $this->db->query("
            INSERT INTO users (email, password, first_name, last_name, phone, role) 
            VALUES ('$email', '$password', '$firstName', '$lastName', '$phone', '$role')
        ");
        
        if ($this->db->affectedRows($result) > 0) {
            return [
                'success' => true,
                'message' => 'Менеджер успешно создан',
                'user_id' => $this->db->lastInsertId('users')
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при создании менеджера'
        ];
    }
}
?>