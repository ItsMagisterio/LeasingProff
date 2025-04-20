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
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        if (!file_exists($jsonFile)) {
            return [];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $userData = json_decode($jsonData, true);
        
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            return [];
        }
        
        $users = $userData['records'];
        
        // Фильтрация по роли, если указана
        if ($role) {
            $users = array_filter($users, function($user) use ($role) {
                return isset($user['role']) && $user['role'] === $role;
            });
        }
        
        // Сортировка по фамилии и имени
        usort($users, function($a, $b) {
            $lastNameA = $a['last_name'] ?? '';
            $lastNameB = $b['last_name'] ?? '';
            
            $firstNameA = $a['first_name'] ?? '';
            $firstNameB = $b['first_name'] ?? '';
            
            if ($lastNameA === $lastNameB) {
                return $firstNameA <=> $firstNameB;
            }
            
            return $lastNameA <=> $lastNameB;
        });
        
        // Применение пагинации
        if ($limit > 0) {
            $users = array_slice($users, $offset, $limit);
        }
        
        // Удаляем пароли из результата
        foreach ($users as &$user) {
            if (isset($user['password'])) {
                unset($user['password']);
            }
        }
        
        return $users;
    }
    
    /**
     * Получить данные конкретного пользователя
     */
    public function getUserById($userId) {
        $userId = (int) $userId;
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        if (!file_exists($jsonFile)) {
            return null;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $userData = json_decode($jsonData, true);
        
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            return null;
        }
        
        // Ищем пользователя с указанным ID
        foreach ($userData['records'] as $user) {
            if (isset($user['id']) && (int)$user['id'] === $userId) {
                // Удаляем пароль из результата
                if (isset($user['password'])) {
                    unset($user['password']);
                }
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Получить данные пользователя по email
     */
    public function getUserByEmail($email) {
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        if (!file_exists($jsonFile)) {
            return null;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $userData = json_decode($jsonData, true);
        
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            return null;
        }
        
        // Ищем пользователя с указанным email
        foreach ($userData['records'] as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                // Удаляем пароль из результата
                if (isset($user['password'])) {
                    unset($user['password']);
                }
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Обновить данные пользователя
     */
    public function updateUser($userId, $userData) {
        $userId = (int) $userId;
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        if (!file_exists($jsonFile)) {
            return [
                'success' => false,
                'message' => 'Файл с пользователями не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $usersData = json_decode($jsonData, true);
        
        if (!isset($usersData['records']) || !is_array($usersData['records'])) {
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных пользователей'
            ];
        }
        
        $userFound = false;
        $userUpdated = false;
        
        // Обновляем данные пользователя
        foreach ($usersData['records'] as &$user) {
            if (isset($user['id']) && (int)$user['id'] === $userId) {
                $userFound = true;
                
                // Обновляем поля пользователя
                if (isset($userData['first_name'])) {
                    $user['first_name'] = $userData['first_name'];
                    $userUpdated = true;
                }
                
                if (isset($userData['last_name'])) {
                    $user['last_name'] = $userData['last_name'];
                    $userUpdated = true;
                }
                
                if (isset($userData['phone'])) {
                    $user['phone'] = $userData['phone'];
                    $userUpdated = true;
                }
                
                if (isset($userData['role'])) {
                    $user['role'] = $userData['role'];
                    $userUpdated = true;
                }
                
                if (isset($userData['password']) && $userData['password']) {
                    $user['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
                    $userUpdated = true;
                }
                
                $user['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        if (!$userFound) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }
        
        if (!$userUpdated) {
            return [
                'success' => true,
                'message' => 'Нет изменений в данных пользователя',
                'user_id' => $userId
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($usersData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
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
        
        if (count($result) > 0) {
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
        $email = $this->db->escapeString($userData['email']);
        $result = $this->db->query("SELECT id FROM users WHERE email = '$email'");
        
        if (count($result) > 0) {
            return [
                'success' => false,
                'message' => 'Email уже зарегистрирован в системе'
            ];
        }
        
        // Хэшируем пароль
        $password = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Подготавливаем данные
        $firstName = $this->db->escapeString($userData['first_name']);
        $lastName = $this->db->escapeString($userData['last_name']);
        $phone = $this->db->escapeString($userData['phone']);
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