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
        $applicationsFile = __DIR__ . '/../data/applications.json';
        if (file_exists($applicationsFile)) {
            $applicationsData = file_get_contents($applicationsFile);
            $applications = json_decode($applicationsData, true);
            
            if (isset($applications['records']) && is_array($applications['records'])) {
                foreach ($applications['records'] as $application) {
                    if (isset($application['user_id']) && (int)$application['user_id'] === $userId) {
                        return [
                            'success' => false,
                            'message' => 'Невозможно удалить пользователя, так как у него есть заявки'
                        ];
                    }
                }
            }
        }
        
        // Удаляем пользователя
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
        $newRecords = [];
        
        // Фильтруем пользователей, исключая того, которого нужно удалить
        foreach ($usersData['records'] as $user) {
            if (isset($user['id']) && (int)$user['id'] === $userId) {
                $userFound = true;
            } else {
                $newRecords[] = $user;
            }
        }
        
        if (!$userFound) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }
        
        // Обновляем массив пользователей
        $usersData['records'] = $newRecords;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($usersData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
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
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/users.json';
        
        if (!file_exists($jsonFile)) {
            return $counts;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $userData = json_decode($jsonData, true);
        
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            return $counts;
        }
        
        // Подсчитываем количество пользователей по ролям
        foreach ($userData['records'] as $user) {
            if (isset($user['role'])) {
                $role = $user['role'];
                
                if (isset($counts[$role])) {
                    $counts[$role]++;
                }
                
                $counts['total']++;
            } else {
                // Если роль не указана, считаем как клиента (по умолчанию)
                $counts['client']++;
                $counts['total']++;
            }
        }
        
        return $counts;
    }
    
    /**
     * Получить список менеджеров
     */
    public function getManagers() {
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
        
        $managers = [];
        
        // Фильтруем пользователей с ролью "manager"
        foreach ($userData['records'] as $user) {
            if (isset($user['role']) && $user['role'] === 'manager') {
                // Добавляем только нужные поля
                $manager = [
                    'id' => $user['id'],
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'email' => $user['email'] ?? ''
                ];
                
                $managers[] = $manager;
            }
        }
        
        // Сортировка по фамилии и имени
        usort($managers, function($a, $b) {
            $lastNameA = $a['last_name'] ?? '';
            $lastNameB = $b['last_name'] ?? '';
            
            $firstNameA = $a['first_name'] ?? '';
            $firstNameB = $b['first_name'] ?? '';
            
            if ($lastNameA === $lastNameB) {
                return $firstNameA <=> $firstNameB;
            }
            
            return $lastNameA <=> $lastNameB;
        });
        
        return $managers;
    }
    
    /**
     * Добавить нового менеджера
     */
    public function createManager($userData) {
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
        
        // Определяем ID для нового менеджера
        $userId = $usersData['next_id'];
        $usersData['next_id']++;
        
        // Подготавливаем данные для нового менеджера
        $newUser = [
            'id' => $userId,
            'email' => $email,
            'password' => $password,
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'phone' => $userData['phone'],
            'role' => 'manager', // Новая запись - менеджер
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Добавляем пользователя в массив
        $usersData['records'][] = $newUser;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($usersData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => 'Менеджер успешно создан',
                'user_id' => $userId
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Ошибка при создании менеджера'
        ];
    }
    
    /**
     * Изменить роль пользователя
     * @param int $userId ID пользователя
     * @param string $newRole Новая роль ('client', 'manager', 'admin')
     * @return array Результат операции
     */
    public function updateUserRole($userId, $newRole) {
        if (!in_array($newRole, ['client', 'manager', 'admin'])) {
            return [
                'success' => false,
                'message' => 'Некорректная роль пользователя'
            ];
        }
        
        // Обновляем поле role в данных пользователя
        return $this->updateUser($userId, ['role' => $newRole]);
    }
    
    /**
     * Получить список всех пользователей с определенной ролью
     * @param string $role Роль пользователей ('client', 'manager', 'admin')
     * @return array Массив пользователей с указанной ролью
     */
    public function getUsersByRole($role) {
        // Проверка валидности роли
        if (!in_array($role, ['client', 'manager', 'admin'])) {
            return [];
        }
        
        // Используем существующий метод getAllUsers с фильтрацией по роли
        return $this->getAllUsers($role);
    }
    
    /**
     * Обновить статус пользователя (активный/неактивный)
     * @param int $userId ID пользователя
     * @param int $status Новый статус (1 - активен, 0 - заблокирован)
     * @return array Результат операции
     */
    public function updateUserStatus($userId, $status) {
        $userId = (int) $userId;
        $status = (int) $status;
        
        // Проверка валидности статуса
        if ($status !== 0 && $status !== 1) {
            return [
                'success' => false,
                'message' => 'Некорректный статус пользователя'
            ];
        }
        
        // Обновляем поле is_active в данных пользователя
        return $this->updateUser($userId, ['is_active' => $status]);
    }
}
?>