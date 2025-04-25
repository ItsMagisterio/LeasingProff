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
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        $usersFile = __DIR__ . '/../data/users.json';
        $vehiclesFile = __DIR__ . '/../data/vehicles.json';
        $realEstateFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            return [];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        $applications = $appData['records'];
        
        // Загружаем данные пользователей, транспорта и недвижимости
        $users = [];
        if (file_exists($usersFile)) {
            $userData = json_decode(file_get_contents($usersFile), true);
            if (isset($userData['records']) && is_array($userData['records'])) {
                foreach ($userData['records'] as $user) {
                    if (isset($user['id'])) {
                        $users[$user['id']] = $user;
                    }
                }
            }
        }
        
        $vehicles = [];
        if (file_exists($vehiclesFile)) {
            $vehicleData = json_decode(file_get_contents($vehiclesFile), true);
            if (isset($vehicleData['records']) && is_array($vehicleData['records'])) {
                foreach ($vehicleData['records'] as $vehicle) {
                    if (isset($vehicle['id'])) {
                        $vehicles[$vehicle['id']] = $vehicle;
                    }
                }
            }
        }
        
        $realEstates = [];
        if (file_exists($realEstateFile)) {
            $reData = json_decode(file_get_contents($realEstateFile), true);
            if (isset($reData['records']) && is_array($reData['records'])) {
                foreach ($reData['records'] as $re) {
                    if (isset($re['id'])) {
                        $realEstates[$re['id']] = $re;
                    }
                }
            }
        }
        
        // Применяем фильтры
        if (!empty($filters)) {
            $applications = array_filter($applications, function($app) use ($filters) {
                if (isset($filters['user_id']) && $filters['user_id'] && $app['user_id'] != $filters['user_id']) {
                    return false;
                }
                
                if (isset($filters['manager_id']) && $filters['manager_id'] && $app['manager_id'] != $filters['manager_id']) {
                    return false;
                }
                
                if (isset($filters['status']) && $filters['status'] && $app['status'] != $filters['status']) {
                    return false;
                }
                
                if (isset($filters['unassigned']) && $filters['unassigned'] && !empty($app['manager_id'])) {
                    return false;
                }
                
                return true;
            });
        }
        
        // Сортировка по дате создания (новые в начале)
        usort($applications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Добавляем дополнительные поля из связанных таблиц
        foreach ($applications as &$app) {
            // Добавляем данные о пользователе
            if (isset($app['user_id']) && isset($users[$app['user_id']])) {
                $user = $users[$app['user_id']];
                $app['client_first_name'] = $user['first_name'] ?? '';
                $app['client_last_name'] = $user['last_name'] ?? '';
                $app['client_email'] = $user['email'] ?? '';
            }
            
            // Добавляем данные о менеджере
            if (isset($app['manager_id']) && isset($users[$app['manager_id']])) {
                $manager = $users[$app['manager_id']];
                $app['manager_first_name'] = $manager['first_name'] ?? '';
                $app['manager_last_name'] = $manager['last_name'] ?? '';
            }
            
            // Добавляем данные о транспорте
            if (isset($app['vehicle_id']) && isset($vehicles[$app['vehicle_id']])) {
                $vehicle = $vehicles[$app['vehicle_id']];
                $app['vehicle_make'] = $vehicle['make'] ?? '';
                $app['vehicle_model'] = $vehicle['model'] ?? '';
                $app['vehicle_year'] = $vehicle['year'] ?? '';
            }
            
            // Добавляем данные о недвижимости
            if (isset($app['real_estate_id']) && isset($realEstates[$app['real_estate_id']])) {
                $re = $realEstates[$app['real_estate_id']];
                $app['real_estate_title'] = $re['title'] ?? '';
                $app['real_estate_area'] = $re['area'] ?? '';
                $app['real_estate_address'] = $re['address'] ?? '';
                $app['real_estate_type'] = $re['type'] ?? '';
            }
        }
        
        // Пагинация
        if ($limit > 0) {
            $applications = array_slice($applications, $offset, $limit);
        }
        
        return array_values($applications);
    }
    
    /**
     * Получить данные конкретной заявки
     */
    public function getApplicationById($applicationId) {
        $applicationId = (int) $applicationId;
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        $usersFile = __DIR__ . '/../data/users.json';
        $vehiclesFile = __DIR__ . '/../data/vehicles.json';
        $realEstateFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            return null;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return null;
        }
        
        // Ищем нужную заявку
        $application = null;
        foreach ($appData['records'] as $app) {
            if (isset($app['id']) && (int)$app['id'] === $applicationId) {
                $application = $app;
                break;
            }
        }
        
        if (!$application) {
            return null;
        }
        
        // Загружаем данные о пользователе
        if (isset($application['user_id']) && file_exists($usersFile)) {
            $userData = json_decode(file_get_contents($usersFile), true);
            if (isset($userData['records']) && is_array($userData['records'])) {
                foreach ($userData['records'] as $user) {
                    if (isset($user['id']) && (int)$user['id'] === (int)$application['user_id']) {
                        $application['client_first_name'] = $user['first_name'] ?? '';
                        $application['client_last_name'] = $user['last_name'] ?? '';
                        $application['client_email'] = $user['email'] ?? '';
                        $application['client_phone'] = $user['phone'] ?? '';
                        break;
                    }
                }
            }
        }
        
        // Загружаем данные о менеджере
        if (isset($application['manager_id']) && file_exists($usersFile)) {
            $userData = json_decode(file_get_contents($usersFile), true);
            if (isset($userData['records']) && is_array($userData['records'])) {
                foreach ($userData['records'] as $user) {
                    if (isset($user['id']) && (int)$user['id'] === (int)$application['manager_id']) {
                        $application['manager_first_name'] = $user['first_name'] ?? '';
                        $application['manager_last_name'] = $user['last_name'] ?? '';
                        $application['manager_email'] = $user['email'] ?? '';
                        break;
                    }
                }
            }
        }
        
        // Загружаем данные о транспорте
        if (isset($application['vehicle_id']) && file_exists($vehiclesFile)) {
            $vehicleData = json_decode(file_get_contents($vehiclesFile), true);
            if (isset($vehicleData['records']) && is_array($vehicleData['records'])) {
                foreach ($vehicleData['records'] as $vehicle) {
                    if (isset($vehicle['id']) && (int)$vehicle['id'] === (int)$application['vehicle_id']) {
                        $application['vehicle_make'] = $vehicle['make'] ?? '';
                        $application['vehicle_model'] = $vehicle['model'] ?? '';
                        $application['vehicle_year'] = $vehicle['year'] ?? '';
                        $application['vehicle_color'] = $vehicle['color'] ?? '';
                        $application['vehicle_price'] = $vehicle['price'] ?? '';
                        $application['vehicle_image'] = $vehicle['image_url'] ?? '';
                        break;
                    }
                }
            }
        }
        
        // Загружаем данные о недвижимости
        if (isset($application['real_estate_id']) && file_exists($realEstateFile)) {
            $reData = json_decode(file_get_contents($realEstateFile), true);
            if (isset($reData['records']) && is_array($reData['records'])) {
                foreach ($reData['records'] as $re) {
                    if (isset($re['id']) && (int)$re['id'] === (int)$application['real_estate_id']) {
                        $application['real_estate_title'] = $re['title'] ?? '';
                        $application['real_estate_type'] = $re['type'] ?? '';
                        $application['real_estate_area'] = $re['area'] ?? '';
                        $application['real_estate_address'] = $re['address'] ?? '';
                        $application['real_estate_price'] = $re['price'] ?? '';
                        $application['real_estate_image'] = $re['image_url'] ?? '';
                        break;
                    }
                }
            }
        }
        
        return $application;
    }
    
    /**
     * Создать новую заявку на лизинг
     */
    public function createApplication($applicationData) {
        $userId = (int) $applicationData['user_id'];
        $initialPayment = (float) $applicationData['initial_payment'];
        $termMonths = (int) $applicationData['term_months'];
        $monthlyPayment = (float) $applicationData['monthly_payment'];
        $comments = $applicationData['comments'] ?? '';
        $type = $applicationData['type'] ?? 'vehicle';
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        
        // Проверка существования файла и создание пустой структуры, если файла нет
        if (!file_exists($jsonFile)) {
            $appData = [
                'next_id' => 1,
                'records' => []
            ];
        } else {
            $jsonData = file_get_contents($jsonFile);
            $appData = json_decode($jsonData, true);
            
            if (!isset($appData['next_id'])) {
                $appData['next_id'] = 1;
            }
            
            if (!isset($appData['records'])) {
                $appData['records'] = [];
            }
        }
        
        // Определяем ID для новой заявки
        if (isset($appData['auto_increment'])) {
            $newId = $appData['auto_increment'];
            $appData['auto_increment']++;
        } else {
            $newId = $appData['next_id'];
            $appData['next_id']++;
        }
        
        // Создаем новую заявку
        $newApplication = [
            'id' => $newId,
            'user_id' => $userId,
            'status' => 'new',
            'type' => $type,
            'initial_payment' => $initialPayment,
            'term_months' => $termMonths,
            'monthly_payment' => $monthlyPayment,
            'comments' => $comments,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Добавляем специфические поля в зависимости от типа
        if ($type === 'real_estate') {
            $newApplication['real_estate_id'] = (int) $applicationData['real_estate_id'];
        } else {
            $newApplication['vehicle_id'] = (int) $applicationData['vehicle_id'];
        }
        
        // Добавляем заявку в массив
        $appData['records'][] = $newApplication;
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($appData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            return [
                'success' => true,
                'application_id' => $newId
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
        // Добавляем тип 'real_estate' и вызываем основной метод createApplication
        $applicationData['type'] = 'real_estate';
        return $this->createApplication($applicationData);
    }
    
    /**
     * Обновить статус заявки
     */
    public function updateApplicationStatus($applicationId, $status, $comments = '') {
        $applicationId = (int) $applicationId;
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        
        if (!file_exists($jsonFile)) {
            return [
                'success' => false,
                'message' => 'Файл с заявками не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных заявок'
            ];
        }
        
        $applicationUpdated = false;
        
        // Обновляем статус и комментарии заявки
        foreach ($appData['records'] as &$app) {
            if (isset($app['id']) && (int)$app['id'] === $applicationId) {
                $app['status'] = $status;
                $app['comments'] = $comments;
                $app['updated_at'] = date('Y-m-d H:i:s');
                $applicationUpdated = true;
                break;
            }
        }
        
        if (!$applicationUpdated) {
            return [
                'success' => false,
                'message' => 'Заявка не найдена'
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($appData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
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
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        
        if (!file_exists($jsonFile)) {
            return [
                'success' => false,
                'message' => 'Файл с заявками не найден'
            ];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return [
                'success' => false,
                'message' => 'Ошибка структуры данных заявок'
            ];
        }
        
        $applicationUpdated = false;
        
        // Обновляем статус и комментарии заявки
        foreach ($appData['records'] as &$app) {
            if (isset($app['id']) && (int)$app['id'] === $applicationId) {
                $app['manager_id'] = $managerId;
                $app['updated_at'] = date('Y-m-d H:i:s');
                $applicationUpdated = true;
                break;
            }
        }
        
        if (!$applicationUpdated) {
            return [
                'success' => false,
                'message' => 'Заявка не найдена'
            ];
        }
        
        // Сохраняем обновленные данные в файл
        $result = file_put_contents($jsonFile, json_encode($appData, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
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
        
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        
        if (!file_exists($jsonFile)) {
            return $counts;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return $counts;
        }
        
        $applications = $appData['records'];
        
        // Фильтруем заявки по пользователю, если указан
        if ($userId) {
            $userId = (int) $userId;
            $applications = array_filter($applications, function($app) use ($userId) {
                return isset($app['user_id']) && (int)$app['user_id'] === $userId;
            });
        }
        
        // Подсчитываем количество заявок по статусам
        foreach ($applications as $app) {
            if (isset($app['status'])) {
                $status = $app['status'];
                
                if (isset($counts[$status])) {
                    $counts[$status]++;
                }
                
                $counts['total']++;
            }
        }
        
        return $counts;
    }
    
    /**
     * Получить количество заявок по менеджерам
     */
    public function getApplicationsCountByManager() {
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        $usersFile = __DIR__ . '/../data/users.json';
        
        $managerStats = [];
        
        if (!file_exists($jsonFile) || !file_exists($usersFile)) {
            return [];
        }
        
        // Загружаем данные о пользователях
        $userData = json_decode(file_get_contents($usersFile), true);
        if (!isset($userData['records']) || !is_array($userData['records'])) {
            return [];
        }
        
        // Находим всех менеджеров
        $managers = [];
        foreach ($userData['records'] as $user) {
            if (isset($user['role']) && $user['role'] === 'manager') {
                $managers[$user['id']] = [
                    'manager_id' => $user['id'],
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'total' => 0,
                    'new' => 0,
                    'in_progress' => 0, 
                    'approved' => 0,
                    'rejected' => 0
                ];
            }
        }
        
        // Если нет менеджеров, возвращаем пустой массив
        if (empty($managers)) {
            return [];
        }
        
        // Загружаем данные о заявках
        $appData = json_decode(file_get_contents($jsonFile), true);
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return array_values($managers);
        }
        
        // Обрабатываем заявки
        foreach ($appData['records'] as $app) {
            if (isset($app['manager_id']) && isset($managers[$app['manager_id']])) {
                $managers[$app['manager_id']]['total']++;
                
                if (isset($app['status'])) {
                    switch ($app['status']) {
                        case 'new':
                            $managers[$app['manager_id']]['new']++;
                            break;
                        case 'in_progress':
                            $managers[$app['manager_id']]['in_progress']++;
                            break;
                        case 'approved':
                            $managers[$app['manager_id']]['approved']++;
                            break;
                        case 'rejected':
                            $managers[$app['manager_id']]['rejected']++;
                            break;
                    }
                }
            }
        }
        
        // Сортируем менеджеров по общему количеству заявок
        usort($managers, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        
        return array_values($managers);
    }
    
    /**
     * Получить количество неназначенных заявок
     */
    public function getUnassignedApplicationsCount() {
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        
        if (!file_exists($jsonFile)) {
            return 0;
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return 0;
        }
        
        // Фильтруем заявки без менеджера
        $unassignedApplications = array_filter($appData['records'], function($app) {
            return empty($app['manager_id']) || $app['manager_id'] === null;
        });
        
        return count($unassignedApplications);
    }
    

    
    /**
     * Получить неназначенные заявки
     */
    public function getUnassignedApplications($limit = 0) {
        // Чтение из JSON-файла
        $jsonFile = __DIR__ . '/../data/applications.json';
        $usersFile = __DIR__ . '/../data/users.json';
        $vehiclesFile = __DIR__ . '/../data/vehicles.json';
        $realEstateFile = __DIR__ . '/../data/real_estate.json';
        
        if (!file_exists($jsonFile)) {
            return [];
        }
        
        $jsonData = file_get_contents($jsonFile);
        $appData = json_decode($jsonData, true);
        
        if (!isset($appData['records']) || !is_array($appData['records'])) {
            return [];
        }
        
        // Фильтруем заявки без менеджера
        $unassignedApplications = array_filter($appData['records'], function($app) {
            return empty($app['manager_id']) || $app['manager_id'] === null;
        });
        
        // Сортировка по дате создания (новые в начале)
        usort($unassignedApplications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Загружаем данные пользователей, транспорта и недвижимости
        $users = [];
        if (file_exists($usersFile)) {
            $userData = json_decode(file_get_contents($usersFile), true);
            if (isset($userData['records']) && is_array($userData['records'])) {
                foreach ($userData['records'] as $user) {
                    if (isset($user['id'])) {
                        $users[$user['id']] = $user;
                    }
                }
            }
        }
        
        $vehicles = [];
        if (file_exists($vehiclesFile)) {
            $vehicleData = json_decode(file_get_contents($vehiclesFile), true);
            if (isset($vehicleData['records']) && is_array($vehicleData['records'])) {
                foreach ($vehicleData['records'] as $vehicle) {
                    if (isset($vehicle['id'])) {
                        $vehicles[$vehicle['id']] = $vehicle;
                    }
                }
            }
        }
        
        $realEstates = [];
        if (file_exists($realEstateFile)) {
            $reData = json_decode(file_get_contents($realEstateFile), true);
            if (isset($reData['records']) && is_array($reData['records'])) {
                foreach ($reData['records'] as $re) {
                    if (isset($re['id'])) {
                        $realEstates[$re['id']] = $re;
                    }
                }
            }
        }
        
        // Добавляем дополнительные поля из связанных таблиц
        foreach ($unassignedApplications as &$app) {
            // Добавляем данные о пользователе
            if (isset($app['user_id']) && isset($users[$app['user_id']])) {
                $user = $users[$app['user_id']];
                $app['client_first_name'] = $user['first_name'] ?? '';
                $app['client_last_name'] = $user['last_name'] ?? '';
            }
            
            // Добавляем данные о транспорте
            if (isset($app['vehicle_id']) && isset($vehicles[$app['vehicle_id']])) {
                $vehicle = $vehicles[$app['vehicle_id']];
                $app['vehicle_make'] = $vehicle['make'] ?? '';
                $app['vehicle_model'] = $vehicle['model'] ?? '';
            }
            
            // Добавляем данные о недвижимости
            if (isset($app['real_estate_id']) && isset($realEstates[$app['real_estate_id']])) {
                $re = $realEstates[$app['real_estate_id']];
                $app['real_estate_title'] = $re['title'] ?? '';
                $app['real_estate_type'] = $re['type'] ?? '';
            }
        }
        
        // Пагинация
        if ($limit > 0) {
            $unassignedApplications = array_slice($unassignedApplications, 0, $limit);
        }
        
        return array_values($unassignedApplications);
    }
}
?>