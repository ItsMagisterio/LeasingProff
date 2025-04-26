<?php
/**
 * Класс для работы с аутентификацией пользователей
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Авторизация пользователя
     */
    public function login($email, $password) {
        // Логирование попытки входа
        error_log("Login attempt for email: " . $email);
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        error_log("Reading users from: " . $jsonFile);
        
        if (!file_exists($jsonFile)) {
            return false;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $userData = json_decode($jsonData, true);
        
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            error_log("Invalid users data structure");
            return false;
        }
        error_log("Found " . count($userData['records']) . " user records");
        
        // Ищем пользователя с указанным email
        $user = null;
        foreach ($userData['records'] as $record) {
            if (isset($record['email']) && $record['email'] === $email) {
                $user = $record;
                break;
            }
        }
        
        // Проверяем найденного пользователя
        if ($user && password_verify($password, $user['password'])) {
            // Сохраняем пользователя в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'client'; // Проверяем наличие роли
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function register($userData) {
        $email = $userData['email'];
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        // Проверка существования файла и создание пустой структуры, если файла нет
        if (!file_exists($jsonFile)) {
            $usersData = [
                'next_id' => 1,
                'records' => []
            ];
        } else {
            $jsonData = file_get_contents($jsonFile);
            $usersData = json_decode($jsonData, true);
            
            if (!isset($usersData['next_id'])) {
                $usersData['next_id'] = 1;
            }
            
            if (!isset($usersData['records'])) {
                $usersData['records'] = [];
            }
        }
        
        // Проверяем, не занят ли email
        foreach ($usersData['records'] as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                return [
                    'success' => false,
                    'message' => 'Email уже зарегистрирован в системе'
                ];
            }
        }
        
        // Хэшируем пароль
        $password = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Определяем ID для нового пользователя
        $userId = $usersData['next_id'];
        $usersData['next_id']++;
        
        // Подготавливаем данные для нового пользователя
        $newUser = [
            'id' => $userId,
            'email' => $email,
            'password' => $password,
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'phone' => $userData['phone'],
            'role' => 'client', // По умолчанию все новые пользователи - клиенты
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Добавляем пользователя в массив
        $usersData['records'][] = $newUser;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($usersData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            // Авторизуем пользователя
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'client';
            $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
            
            return [
                'success' => true,
                'message' => 'Регистрация успешно завершена',
                'user_id' => $userId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при регистрации пользователя'
        ];
    }
    
    /**
     * Выход пользователя
     */
    public function logout() {
        // Удаляем данные сессии
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_role']);
        unset($_SESSION['user_name']);
        
        // Уничтожаем сессию
        session_destroy();
        
        return true;
    }
    
    /**
     * Проверка авторизации пользователя
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Получение информации о текущем пользователе
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $userId = (int) $_SESSION['user_id'];
        
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
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Проверка роли пользователя
     */
    public function checkRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Проверяем базовое сравнение ролей
        if ($_SESSION['user_role'] === $role) {
            return true;
        }
        
        // Дополнительная проверка: админ может делать всё, что может менеджер
        if ($_SESSION['user_role'] === 'admin' && $role === 'manager') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверка доступа к административным функциям
     */
    public function isAdmin() {
        return $this->checkRole('admin');
    }
    
    /**
     * Проверка доступа для менеджеров
     */
    public function isManager() {
        return $this->checkRole('manager') || $this->checkRole('admin');
    }
    
    /**
     * Проверка доступа для клиентов
     */
    public function isClient() {
        return $this->checkRole('client');
    }
    
    /**
     * Получить имя пользователя
     */
    public function getUserName() {
        return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Гость';
    }
    
    /**
     * Получить email пользователя
     */
    public function getUserEmail() {
        return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
    }
    
    /**
     * Получить ID пользователя
     */
    public function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    }
}
?>