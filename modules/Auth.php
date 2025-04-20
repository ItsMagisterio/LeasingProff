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
        $email = $this->db->escapeString($email);
        $result = $this->db->query("SELECT * FROM users WHERE email = '$email'");
        
        if (count($result) > 0) {
            $user = $this->db->fetchRow($result);
            
            if (password_verify($password, $user['password'])) {
                // Сохраняем пользователя в сессии
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function register($userData) {
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
        $role = 'client'; // По умолчанию все новые пользователи - клиенты
        
        // Добавляем пользователя
        $result = $this->db->query("
            INSERT INTO users (email, password, first_name, last_name, phone, role) 
            VALUES ('$email', '$password', '$firstName', '$lastName', '$phone', '$role')
        ");
        
        if ($this->db->affectedRows($result) > 0) {
            // Получаем ID нового пользователя
            $userId = $this->db->lastInsertId('users');
            
            // Авторизуем пользователя
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            
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
        
        $userId = $_SESSION['user_id'];
        $result = $this->db->query("SELECT * FROM users WHERE id = $userId");
        
        if (count($result) > 0) {
            return $this->db->fetchRow($result);
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
        
        return $_SESSION['user_role'] === $role;
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