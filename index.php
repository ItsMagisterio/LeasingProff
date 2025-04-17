<?php
// Подключаем конфигурацию
require_once 'config.php';

// Инициализируем классы для работы с данными
$auth = new Auth();
$vehicles = new Vehicles();
$applications = new Applications();
$users = new Users();
$realEstate = new RealEstate();

// Определяем текущую страницу из GET-параметра
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Обработка форм и AJAX-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Операции с пользователями
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Авторизация
        if ($action === 'login') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($email, $password)) {
                // Определяем редирект в зависимости от роли
                if ($auth->isAdmin()) {
                    header('Location: index.php?page=dashboard-admin');
                } elseif ($auth->isManager()) {
                    header('Location: index.php?page=dashboard-manager');
                } else {
                    header('Location: index.php?page=dashboard-client');
                }
                exit;
            } else {
                $error = 'Неверный email или пароль';
                $page = 'login';
            }
        }
        
        // Регистрация
        elseif ($action === 'register') {
            $userData = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'phone' => $_POST['phone'] ?? ''
            ];
            
            // Проверка подтверждения пароля
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $error = 'Пароли не совпадают';
                $page = 'register';
            } 
            // Проверка согласия с условиями
            elseif (!isset($_POST['terms'])) {
                $error = 'Необходимо согласиться с условиями использования';
                $page = 'register';
            }
            else {
                $result = $auth->register($userData);
                
                if ($result['success']) {
                    header('Location: index.php?page=dashboard-client');
                    exit;
                } else {
                    $error = $result['message'];
                    $page = 'register';
                }
            }
        }
        
        // Выход из системы
        elseif ($action === 'logout') {
            $auth->logout();
            header('Location: index.php');
            exit;
        }
        
        // Подача заявки на лизинг автомобиля
        elseif ($action === 'submit_application') {
            // Проверяем авторизацию
            if (!$auth->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            $applicationData = [
                'user_id' => $auth->getUserId(),
                'vehicle_id' => (int) $_POST['vehicle_id'],
                'initial_payment' => (float) $_POST['initial_payment'],
                'term_months' => (int) $_POST['term_months'],
                'monthly_payment' => (float) $_POST['monthly_payment'],
                'comments' => $_POST['comments'] ?? '',
                'type' => 'vehicle'
            ];
            
            $result = $applications->createApplication($applicationData);
            
            if ($result['success']) {
                $success = 'Заявка успешно отправлена';
                $page = 'dashboard-client';
            } else {
                $error = $result['message'];
                $page = 'vehicle';
            }
        }
        
        // Подача заявки на лизинг недвижимости
        elseif ($action === 'submit_real_estate_application') {
            // Проверяем авторизацию
            if (!$auth->isLoggedIn()) {
                header('Location: index.php?page=login');
                exit;
            }
            
            $applicationData = [
                'user_id' => $auth->getUserId(),
                'real_estate_id' => (int) $_POST['real_estate_id'],
                'initial_payment' => (float) $_POST['initial_payment'],
                'term_months' => (int) $_POST['term_months'],
                'monthly_payment' => (float) $_POST['monthly_payment'],
                'comments' => $_POST['comments'] ?? '',
                'type' => 'real_estate'
            ];
            
            $result = $applications->createApplication($applicationData);
            
            if ($result['success']) {
                $success = 'Заявка на недвижимость успешно отправлена';
                $page = 'dashboard-client';
            } else {
                $error = $result['message'];
                $page = 'real-estate-item';
            }
        }
        
        // Назначение менеджера на заявку (для админа)
        elseif ($action === 'assign_manager' && $auth->isAdmin()) {
            $applicationId = (int) $_POST['application_id'];
            $managerId = (int) $_POST['manager_id'];
            
            $result = $applications->assignManager($applicationId, $managerId);
            
            if ($result['success']) {
                $success = 'Менеджер успешно назначен';
            } else {
                $error = $result['message'];
            }
            
            $page = 'dashboard-admin';
        }
        
        // Обновление статуса заявки (для менеджера)
        elseif ($action === 'update_application_status' && $auth->isManager()) {
            $applicationId = (int) $_POST['application_id'];
            $status = $_POST['status'];
            $comments = $_POST['comments'] ?? '';
            
            $result = $applications->updateApplicationStatus($applicationId, $status, $comments);
            
            if ($result['success']) {
                $success = 'Статус заявки успешно обновлен';
            } else {
                $error = $result['message'];
            }
            
            $page = 'dashboard-manager';
        }
        
        // Добавление нового автомобиля (для менеджера и админа)
        elseif ($action === 'add_vehicle' && $auth->isManager()) {
            $vehicleData = [
                'make' => $_POST['make'],
                'model' => $_POST['model'],
                'year' => (int) $_POST['year'],
                'engine' => $_POST['engine'],
                'power' => (int) $_POST['power'],
                'drive_type' => $_POST['drive_type'],
                'transmission' => $_POST['transmission'],
                'color' => $_POST['color'],
                'interior' => $_POST['interior'],
                'features' => $_POST['features'],
                'image_url' => $_POST['image_url'],
                'price' => (float) $_POST['price'],
                'monthly_payment' => (float) $_POST['monthly_payment'],
                'status' => $_POST['status'] ?? 'available'
            ];
            
            $result = $vehicles->addVehicle($vehicleData);
            
            if ($result['success']) {
                $success = 'Автомобиль успешно добавлен';
                $page = 'vehicles-admin';
            } else {
                $error = $result['message'];
                $page = 'add-vehicle';
            }
        }
        
        // Добавление нового менеджера (для админа)
        elseif ($action === 'add_manager' && $auth->isAdmin()) {
            $userData = [
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'phone' => $_POST['phone']
            ];
            
            $result = $users->createManager($userData);
            
            if ($result['success']) {
                $success = 'Менеджер успешно добавлен';
                $page = 'managers';
            } else {
                $error = $result['message'];
                $page = 'add-manager';
            }
        }
    }
}

// HTML заголовок
function outputHeader($title = 'Лизинг недвижимости и транспорта') {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . ' | 2Leasing</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="css/custom.css">
        <!-- JavaScript -->
        <script src="js/leasing-calculator.js" defer></script>
        <style>
            /* Базовые стили прямо в HTML для быстрой загрузки */
            :root {
                --primary-color: #0d6efd;
                --secondary-color: #0056b3;
                --accent-color: #fd7e14;
                --light-color: #f8f9fa;
                --dark-color: #343a40;
            }
            
            body {
                font-family: "Roboto", sans-serif;
                color: #333;
                overflow-x: hidden;
            }
            
            .navbar {
                background-color: white;
                padding: 1rem 0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            
            .navbar-brand {
                font-weight: 700;
                font-size: 1.5rem;
                color: var(--primary-color);
            }
            
            .hero-section {
                background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("https://images.unsplash.com/photo-1604054094957-c5369c2e5f44?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80");
                background-size: cover;
                background-position: center;
                color: white;
                padding: 5rem 0;
                text-align: center;
            }
            
            .hero-section h1 {
                font-size: 3.5rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
            }
            
            .features-section {
                padding: 5rem 0;
                background-color: var(--light-color);
            }
            
            .feature-card {
                text-align: center;
                padding: 2rem;
                transition: transform 0.3s;
                height: 100%;
            }
            
            .feature-card:hover {
                transform: translateY(-10px);
            }
            
            .feature-icon {
                font-size: 3rem;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
            }
            
            .vehicles-section, .realestate-section {
                padding: 5rem 0;
            }
            
            .vehicle-card, .realestate-card {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                overflow: hidden;
                transition: box-shadow 0.3s;
                height: 100%;
            }
            
            .vehicle-card:hover, .realestate-card:hover {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .vehicle-img, .realestate-img {
                height: 200px;
                object-fit: cover;
                width: 100%;
            }
            
            .vehicle-price, .realestate-price {
                color: var(--accent-color);
                font-weight: 700;
                font-size: 1.2rem;
            }
            
            .vehicle-features, .realestate-features {
                list-style: none;
                padding-left: 0;
            }
            
            .vehicle-features li, .realestate-features li {
                margin-bottom: 0.5rem;
            }
            
            .call-to-action {
                background-color: var(--primary-color);
                color: white;
                padding: 5rem 0;
                text-align: center;
            }
            
            .footer {
                background-color: var(--dark-color);
                color: white;
                padding: 3rem 0;
            }
            
            .user-dashboard {
                padding: 3rem 0;
            }
            
            .dashboard-card {
                border-radius: 0.5rem;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                padding: 1.5rem;
                margin-bottom: 2rem;
                background-color: white;
            }
            
            .dashboard-nav {
                background-color: var(--secondary-color);
                color: white;
                padding: 1rem;
                border-radius: 0.5rem;
                margin-bottom: 2rem;
            }
            
            .dashboard-nav a {
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                text-decoration: none;
            }
            
            .dashboard-nav a:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }
            
            .dashboard-nav a.active {
                background-color: rgba(255, 255, 255, 0.2);
                font-weight: 600;
            }
            
            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
            }
            
            .btn-primary:hover {
                background-color: var(--secondary-color);
                border-color: var(--secondary-color);
            }
            
            .btn-accent {
                background-color: var(--accent-color);
                border-color: var(--accent-color);
                color: white;
            }
            
            .btn-accent:hover {
                background-color: #e68a00;
                border-color: #e68a00;
                color: white;
            }
            
            /* Адаптивность */
            @media (max-width: 768px) {
                .hero-section h1 {
                    font-size: 2.5rem;
                }
            }
        </style>
    </head>
    <body>';
}

// Навигационная панель
function outputNavigation() {
    global $auth, $page;
    
    // Для определения активного пункта меню
    $is_home = $page === 'home' || empty($page);
    $is_marketplace = $page === 'marketplace' || $page === 'vehicle';
    $is_real_estate = $page === 'real-estate' || $page === 'real-estate-item';
    
    echo '<nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <!-- Логотип - переход на главную страницу -->
            <a class="navbar-brand" href="index.php" title="Вернуться на главную страницу">
                <span class="text-primary">2</span>Leasing
            </a>
            <!-- Кнопка мобильного меню -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Пункт Главная -->
                    <li class="nav-item">
                        <a class="nav-link' . ($is_home ? ' active' : '') . '" href="index.php" title="Главная страница">Главная</a>
                    </li>
                    <!-- Выпадающее меню Транспорт -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle' . ($is_marketplace ? ' active' : '') . '" href="#" id="transportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Просмотр автомобилей">
                            Транспорт
                        </a>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="transportDropdown">
                            <li><a class="dropdown-item" href="index.php?page=marketplace" title="Просмотр всех автомобилей">Все автомобили</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=marketplace&type=sedan" title="Просмотр седанов">Седаны</a></li>
                            <li><a class="dropdown-item" href="index.php?page=marketplace&type=suv" title="Просмотр внедорожников">Внедорожники</a></li>
                            <li><a class="dropdown-item" href="index.php?page=marketplace&type=business" title="Просмотр автомобилей бизнес-класса">Бизнес-класс</a></li>
                            <li><a class="dropdown-item" href="index.php?page=marketplace&type=commercial" title="Просмотр коммерческого транспорта">Коммерческий транспорт</a></li>
                        </ul>
                    </li>
                    <!-- Выпадающее меню Недвижимость -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle' . ($is_real_estate ? ' active' : '') . '" href="#" id="realEstateDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Просмотр недвижимости">
                            Недвижимость
                        </a>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="realEstateDropdown">
                            <li><a class="dropdown-item" href="index.php?page=real-estate" title="Просмотр всех объектов недвижимости">Все объекты</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?page=real-estate&type=apartment" title="Просмотр квартир">Квартиры</a></li>
                            <li><a class="dropdown-item" href="index.php?page=real-estate&type=house" title="Просмотр домов">Дома</a></li>
                            <li><a class="dropdown-item" href="index.php?page=real-estate&type=commercial" title="Просмотр коммерческой недвижимости">Коммерческая недвижимость</a></li>
                            <li><a class="dropdown-item" href="index.php?page=real-estate&type=land" title="Просмотр земельных участков">Земельные участки</a></li>
                        </ul>
                    </li>
                    <!-- Ссылка на калькулятор лизинга -->
                    <li class="nav-item">
                        <a class="nav-link" href="' . ($is_home ? '#calculator' : 'index.php#calculator') . '" title="Рассчитать стоимость лизинга">Калькулятор</a>
                    </li>
                    <!-- Ссылка на раздел О нас -->
                    <li class="nav-item">
                        <a class="nav-link" href="' . ($is_home ? '#about' : 'index.php#about') . '" title="Информация о компании">О нас</a>
                    </li>
                    <!-- Ссылка на раздел Контакты -->
                    <li class="nav-item">
                        <a class="nav-link" href="' . ($is_home ? '#contact' : 'index.php#contact') . '" title="Связаться с нами">Контакты</a>
                    </li>';
                    
    if ($auth->isLoggedIn()) {
        // Дополнительные пункты меню для авторизованных пользователей
        if ($auth->isAdmin()) {
            echo '<li class="nav-item">
                <a class="nav-link" href="index.php?page=dashboard-admin">Админ-панель</a>
            </li>';
        } elseif ($auth->isManager()) {
            echo '<li class="nav-item">
                <a class="nav-link" href="index.php?page=dashboard-manager">Панель менеджера</a>
            </li>';
        } else {
            echo '<li class="nav-item">
                <a class="nav-link" href="index.php?page=dashboard-client">Личный кабинет</a>
            </li>';
        }
    }
                    
    echo '</ul>
                <div class="d-flex align-items-center">';
    
    if ($auth->isLoggedIn()) {
        // Выпадающее меню для авторизованного пользователя
        echo '<div class="dropdown">
                <a class="btn btn-outline-primary rounded-pill dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Профиль пользователя">
                    <i class="fas fa-user-circle me-1"></i> ' . htmlspecialchars($auth->getUserName()) . '
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                    <!-- Профиль пользователя -->
                    <li><a class="dropdown-item" href="index.php?page=profile" title="Просмотр и редактирование личного профиля"><i class="fas fa-user me-2"></i>Профиль</a></li>
                    <!-- Список заявок пользователя -->
                    <li><a class="dropdown-item" href="index.php?page=applications" title="Просмотр ваших заявок на лизинг"><i class="fas fa-clipboard-list me-2"></i>Мои заявки</a></li>';
                    
        // Дополнительные пункты меню в зависимости от роли пользователя
        if ($auth->isAdmin()) {
            echo '<li><a class="dropdown-item" href="index.php?page=dashboard-admin" title="Переход в панель администратора"><i class="fas fa-tools me-2"></i>Админ-панель</a></li>';
        } elseif ($auth->isManager()) {
            echo '<li><a class="dropdown-item" href="index.php?page=dashboard-manager" title="Переход в панель менеджера"><i class="fas fa-tasks me-2"></i>Панель менеджера</a></li>';
        } else {
            echo '<li><a class="dropdown-item" href="index.php?page=dashboard-client" title="Переход в личный кабинет клиента"><i class="fas fa-home me-2"></i>Личный кабинет</a></li>';
        }
                    
        echo '<li><hr class="dropdown-divider"></li>
                    <!-- Кнопка выхода из системы -->
                    <li>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="dropdown-item text-danger" title="Выйти из системы"><i class="fas fa-sign-out-alt me-2"></i>Выход</button>
                        </form>
                    </li>
                </ul>
              </div>';
              
        // Убрали кнопку "Новая заявка" по запросу пользователя
    } else {
        // Кнопки для неавторизованных пользователей
        echo '<a href="index.php?page=login" class="btn btn-outline-primary rounded-pill me-2" title="Войти в систему"><i class="fas fa-sign-in-alt me-1"></i> Вход</a>
              <a href="index.php?page=register" class="btn btn-primary rounded-pill" title="Зарегистрироваться в системе"><i class="fas fa-user-plus me-1"></i> Регистрация</a>';
    }
    
    echo '</div>
            </div>
        </div>
    </nav>';
}

// Подвал
function outputFooter() {
    echo '<footer class="footer mt-auto py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="mb-4">
                        <a href="index.php" class="text-decoration-none">
                            <h4 class="text-white"><span class="text-primary">2</span>Leasing</h4>
                        </a>
                    </div>
                    <p class="text-white-50 mb-4">Комплексные решения для лизинга недвижимости и транспорта на выгодных условиях для физических и юридических лиц по всей России.</p>
                    <div class="d-flex gap-3 mb-3">
                        <a href="https://facebook.com" target="_blank" class="social-icon" title="Наша страница в Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" class="social-icon" title="Наш Instagram профиль">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://vk.com" target="_blank" class="social-icon" title="Наша группа ВКонтакте">
                            <i class="fab fa-vk"></i>
                        </a>
                        <a href="https://t.me/2leasing" target="_blank" class="social-icon" title="Наш канал в Telegram">
                            <i class="fab fa-telegram"></i>
                        </a>
                        <a href="https://youtube.com" target="_blank" class="social-icon" title="Наш YouTube канал">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 col-6">
                    <h5 class="text-white mb-4">Компания</h5>
                    <ul class="footer-links">
                        <li><a href="' . ($is_home ? '#about' : 'index.php#about') . '" title="Информация о компании">О нас</a></li>
                        <li><a href="' . ($is_home ? '#contact' : 'index.php#contact') . '" title="Контактная информация">Контакты</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <h5 class="text-white mb-4">Услуги</h5>
                    <ul class="footer-links">
                        <li><a href="index.php?page=real-estate" title="Лизинг квартир, домов и коммерческой недвижимости">Лизинг недвижимости</a></li>
                        <li><a href="index.php?page=marketplace" title="Лизинг автомобилей и спецтехники">Лизинг транспорта</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Контакты</h5>
                    <ul class="footer-links">
                        <li>
                            <a href="https://maps.google.com/?q=Москва,+Тверская+улица,+10" target="_blank" class="d-flex text-decoration-none" title="Посмотреть на карте">
                                <i class="fas fa-map-marker-alt me-3 mt-1 text-primary"></i>
                                <span>г. Москва, ул. Тверская, д. 10, офис 305</span>
                            </a>
                        </li>
                        <li>
                            <a href="tel:+74951234567" class="d-flex text-decoration-none" title="Позвонить нам">
                                <i class="fas fa-phone-alt me-3 mt-1 text-primary"></i>
                                <span>+7 (495) 123-45-67</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@2leasing.ru" class="d-flex text-decoration-none" title="Написать нам на почту">
                                <i class="fas fa-envelope me-3 mt-1 text-primary"></i>
                                <span>info@2leasing.ru</span>
                            </a>
                        </li>
                        <li>
                            <div class="d-flex">
                                <i class="fas fa-clock me-3 mt-1 text-primary"></i>
                                <span>Пн-Пт: 9:00 - 20:00<br>Сб: 10:00 - 18:00</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4 border-light">
            
            <div class="row align-items-center">
                <div class="col-md-5 mb-3 mb-md-0">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="index.php?page=privacy" class="text-white-50 text-decoration-none small" title="Наша политика конфиденциальности">Политика конфиденциальности</a></li>
                        <li class="list-inline-item ms-3"><a href="index.php?page=terms" class="text-white-50 text-decoration-none small" title="Условия использования сервиса">Условия использования</a></li>
                    </ul>
                </div>
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <a href="#" class="text-white-50 text-decoration-none small" onclick="window.scrollTo({top: 0, behavior: \'smooth\'}); return false;" title="Прокрутить наверх">
                        <i class="fas fa-chevron-up me-1"></i> Наверх
                    </a>
                </div>
                <div class="col-md-5 text-md-end">
                    <p class="text-white-50 mb-0 small">&copy; ' . date('Y') . ' 2Leasing. Все права защищены.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Скрипты -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Плавная прокрутка к якорям
        document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
            anchor.addEventListener(\'click\', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute(\'href\'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: \'smooth\'
                    });
                }
            });
        });
        
        // Включаем все всплывающие подсказки
        var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Добавление классов к карточкам для анимации
        document.addEventListener(\'DOMContentLoaded\', function() {
            // Добавляем overlay к карточкам
            const vehicleCards = document.querySelectorAll(\'.vehicle-card\');
            vehicleCards.forEach(card => {
                // Получаем ссылку на детальную страницу
                const cardLink = card.querySelector(\'a\') ? card.querySelector(\'a\').getAttribute(\'href\') : \'\';
                
                // Добавляем оверлей к картинке
                const imgContainer = card.querySelector(\'.card-img-top\') ? card.querySelector(\'.card-img-top\').parentNode : null;
                
                if (imgContainer && cardLink) {
                    const overlay = document.createElement(\'div\');
                    overlay.className = \'custom-card-overlay\';
                    overlay.innerHTML = `<a href="${cardLink}" class="btn btn-primary rounded-pill">Подробнее</a>`;
                    imgContainer.style.position = \'relative\';
                    imgContainer.appendChild(overlay);
                }
            });
            
            const realestateCards = document.querySelectorAll(\'.realestate-card\');
            realestateCards.forEach(card => {
                // Получаем ссылку на детальную страницу
                const cardLink = card.querySelector(\'a\') ? card.querySelector(\'a\').getAttribute(\'href\') : \'\';
                
                // Добавляем оверлей к картинке
                const imgContainer = card.querySelector(\'.card-img-top\') ? card.querySelector(\'.card-img-top\').parentNode : null;
                
                if (imgContainer && cardLink) {
                    const overlay = document.createElement(\'div\');
                    overlay.className = \'custom-card-overlay\';
                    overlay.innerHTML = `<a href="${cardLink}" class="btn btn-accent rounded-pill">Подробнее</a>`;
                    imgContainer.style.position = \'relative\';
                    imgContainer.appendChild(overlay);
                }
            });
            
            // Анимация появления при скролле
            const elements = document.querySelectorAll(\'.vehicle-card, .realestate-card, .feature-box, .calculator-card\');
            elements.forEach(el => {
                el.classList.add(\'fade-in-element\');
            });
            
            // Добавляем плавную анимацию к кнопкам калькулятора
            const calcButtons = document.querySelectorAll(\'.calculator-result .btn-primary\');
            calcButtons.forEach(btn => {
                btn.classList.add(\'pulse\');
            });
        });
        
        // Добавляем класс для навигационной панели при прокрутке
        window.addEventListener(\'scroll\', function() {
            const navbar = document.querySelector(\'.navbar\');
            if (window.scrollY > 50) {
                navbar.classList.add(\'scrolled\');
            } else {
                navbar.classList.remove(\'scrolled\');
            }
        });
        
        // Анимация появления элементов при прокрутке
        document.addEventListener(\'DOMContentLoaded\', function() {
            // Добавляем класс fade-in-element к основным секциям
            const sections = document.querySelectorAll(\'.vehicle-card, .realestate-card, .feature-card, .calculator-card\');
            sections.forEach(section => {
                section.classList.add(\'fade-in-element\');
            });
            
            const fadeElements = document.querySelectorAll(\'.fade-in-element\');
            
            function checkFade() {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add(\'fade-in\');
                    }
                });
            }
            
            // Запускаем проверку при загрузке страницы
            checkFade();
            // Запускаем проверку при прокрутке
            window.addEventListener(\'scroll\', checkFade);
            
            // Установка активного класса для текущей страницы в навигации
            const currentPage = window.location.href;
            const navLinks = document.querySelectorAll(\'.nav-link\');
            
            navLinks.forEach(link => {
                if (link.href === currentPage) {
                    link.classList.add(\'active\');
                }
            });
        });
    </script>
    </body>
    </html>';
}

// Профиль пользователя
function includeProfilePage() {
    global $auth;
    
    // Перенаправление на страницу входа, если пользователь не авторизован
    if (!$auth->isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <div>
                                    <h2 class="mb-0">Профиль пользователя</h2>
                                    <p class="text-muted mb-0">Управление личными данными</p>
                                </div>
                            </div>
                            
                            <form method="post" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">Имя</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="' . htmlspecialchars($auth->getUserName()) . '" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Фамилия</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="Пользователь" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="user@example.com" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Телефон</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="+7 (900) 123-45-67">
                                    </div>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Адрес</label>
                                        <input type="text" class="form-control" id="address" name="address" value="г. Москва, ул. Примерная, д. 10, кв. 123">
                                    </div>
                                    
                                    <div class="col-12 mt-4 border-top pt-4">
                                        <h5>Изменение пароля</h5>
                                        <p class="text-muted small">Оставьте поля пустыми, если не хотите менять пароль</p>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="currentPassword" class="form-label">Текущий пароль</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="newPassword" class="form-label">Новый пароль</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirmPassword" class="form-label">Подтверждение пароля</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="fas fa-save me-2"></i> Сохранить изменения
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница заявок пользователя
function includeApplicationsPage() {
    global $auth;
    
    // Перенаправление на страницу входа, если пользователь не авторизован
    if (!$auth->isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
    
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                        <i class="fas fa-clipboard-list fa-2x"></i>
                                    </div>
                                    <div>
                                        <h2 class="mb-0">Мои заявки</h2>
                                        <p class="text-muted mb-0">История заявок на лизинг</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Дата</th>
                                            <th scope="col">Тип</th>
                                            <th scope="col">Объект</th>
                                            <th scope="col">Стоимость</th>
                                            <th scope="col">Статус</th>
                                            <th scope="col">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>15.04.2025</td>
                                            <td>Транспорт</td>
                                            <td>BMW X5 2023</td>
                                            <td>5 800 000 ₽</td>
                                            <td><span class="badge bg-success">Одобрена</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Просмотреть"><i class="fas fa-eye"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Скачать документы"><i class="fas fa-download"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>10.04.2025</td>
                                            <td>Недвижимость</td>
                                            <td>Квартира, 65 м²</td>
                                            <td>12 500 000 ₽</td>
                                            <td><span class="badge bg-warning text-dark">В обработке</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Просмотреть"><i class="fas fa-eye"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Отменить"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>05.03.2025</td>
                                            <td>Транспорт</td>
                                            <td>Toyota Camry 2022</td>
                                            <td>3 200 000 ₽</td>
                                            <td><span class="badge bg-danger">Отклонена</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Просмотреть"><i class="fas fa-eye"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Повторить заявку"><i class="fas fa-redo"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-center mt-4">
                                <p class="text-muted mb-0">Показаны все заявки</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}



// Страница вакансий
function includeCareersPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Вакансии</h2>
                <p class="lead text-muted">Присоединяйтесь к нашей команде профессионалов</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4>Менеджер по лизингу</h4>
                            <p class="text-primary mb-3">Москва, полный рабочий день</p>
                            <p class="text-muted">Мы ищем опытного менеджера по лизингу для работы с клиентами и партнерами. Вы будете отвечать за консультирование клиентов, подготовку предложений и сопровождение сделок.</p>
                            <h5>Требования:</h5>
                            <ul class="text-muted mb-3">
                                <li>Опыт работы в сфере лизинга от 2 лет</li>
                                <li>Высшее экономическое образование</li>
                                <li>Знание законодательства в области лизинга</li>
                                <li>Навыки ведения переговоров и продаж</li>
                            </ul>
                            <button type="button" class="btn btn-primary rounded-pill px-4">Откликнуться</button>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4>Финансовый аналитик</h4>
                            <p class="text-primary mb-3">Москва, полный рабочий день</p>
                            <p class="text-muted">Мы ищем финансового аналитика для оценки рисков и анализа лизинговых сделок. Вы будете отвечать за финансовый анализ клиентов и подготовку отчетности.</p>
                            <h5>Требования:</h5>
                            <ul class="text-muted mb-3">
                                <li>Опыт работы в финансовом анализе от 3 лет</li>
                                <li>Высшее экономическое образование</li>
                                <li>Знание бухгалтерского учета и финансового анализа</li>
                                <li>Опыт работы с базами данных и финансовой отчетностью</li>
                            </ul>
                            <button type="button" class="btn btn-primary rounded-pill px-4">Откликнуться</button>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4>Специалист по маркетингу</h4>
                            <p class="text-primary mb-3">Москва, удаленная работа</p>
                            <p class="text-muted">Мы ищем специалиста по маркетингу для продвижения наших услуг. Вы будете отвечать за разработку и реализацию маркетинговой стратегии, ведение социальных сетей и создание контента.</p>
                            <h5>Требования:</h5>
                            <ul class="text-muted mb-3">
                                <li>Опыт работы в маркетинге от 2 лет</li>
                                <li>Опыт ведения социальных сетей и создания контента</li>
                                <li>Знание основ SEO и контекстной рекламы</li>
                                <li>Навыки аналитики и работы с метриками</li>
                            </ul>
                            <button type="button" class="btn btn-primary rounded-pill px-4">Откликнуться</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница блога
function includeBlogPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Блог о лизинге</h2>
                <p class="lead text-muted">Полезные статьи, новости и советы по лизингу</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary me-2">Новости</span>
                                <small class="text-muted">15 апреля 2025</small>
                            </div>
                            <h3 class="mb-3">Изменения в законодательстве о лизинге в 2025 году</h3>
                            <p class="text-muted">В 2025 году вступили в силу новые изменения в законодательстве о лизинге, которые значительно упрощают процедуру оформления сделок и снижают налоговую нагрузку на лизингополучателей...</p>
                            <a href="#" class="btn btn-outline-primary rounded-pill">Читать далее</a>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-success me-2">Советы</span>
                                <small class="text-muted">10 апреля 2025</small>
                            </div>
                            <h3 class="mb-3">Как выбрать оптимальный срок лизинга для автомобиля</h3>
                            <p class="text-muted">Срок лизинга является одним из ключевых параметров, влияющих на стоимость сделки. В этой статье мы рассмотрим, как определить оптимальный срок лизинга для автомобиля в зависимости от его класса и ваших потребностей...</p>
                            <a href="#" class="btn btn-outline-primary rounded-pill">Читать далее</a>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info text-dark me-2">Аналитика</span>
                                <small class="text-muted">5 апреля 2025</small>
                            </div>
                            <h3 class="mb-3">Тренды рынка лизинга недвижимости в 2025 году</h3>
                            <p class="text-muted">Рынок лизинга недвижимости стремительно развивается. В 2025 году мы наблюдаем ряд интересных тенденций, которые могут повлиять на решения инвесторов и предпринимателей...</p>
                            <a href="#" class="btn btn-outline-primary rounded-pill">Читать далее</a>
                        </div>
                    </div>
                    
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Категории</h4>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Новости
                                    <span class="badge bg-primary rounded-pill">12</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Советы
                                    <span class="badge bg-primary rounded-pill">8</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Аналитика
                                    <span class="badge bg-primary rounded-pill">5</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    Интервью
                                    <span class="badge bg-primary rounded-pill">3</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Подписка на новости</h4>
                            <p class="text-muted mb-3">Получайте новые статьи на вашу электронную почту</p>
                            <form>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Ваш email" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">Подписаться</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница лизинга для физических лиц
function includePersonalLeasingPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Лизинг для физических лиц</h2>
                <p class="lead text-muted">Доступные программы лизинга для частных клиентов</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-car fa-2x text-white"></i>
                                </div>
                                <h3 class="mb-0">Автолизинг для физических лиц</h3>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Срок лизинга от 12 до 60 месяцев</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Первоначальный взнос от 10%</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Возможность включения страховки в платежи</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Минимальный пакет документов</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Возможность выкупа автомобиля по окончании срока</span>
                                </li>
                            </ul>
                            <a href="#calculator" class="btn btn-primary rounded-pill">Рассчитать лизинг</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-home fa-2x text-white"></i>
                                </div>
                                <h3 class="mb-0">Лизинг недвижимости для физических лиц</h3>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Срок лизинга от 12 до 120 месяцев</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Первоначальный взнос от 20%</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Лизинг квартир, домов и коммерческой недвижимости</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Возможность досрочного выкупа</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Индивидуальный график платежей</span>
                                </li>
                            </ul>
                            <a href="#calculator" class="btn btn-primary rounded-pill">Рассчитать лизинг</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-lg-10 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="mb-4 text-center">Как оформить лизинг</h3>
                            <div class="row g-4">
                                <div class="col-md-3 text-center">
                                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-calculator fa-2x text-primary"></i>
                                    </div>
                                    <h5>Расчет</h5>
                                    <p class="text-muted small">Рассчитайте лизинг на калькуляторе</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                                    </div>
                                    <h5>Заявка</h5>
                                    <p class="text-muted small">Подайте заявку онлайн</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-check-circle fa-2x text-primary"></i>
                                    </div>
                                    <h5>Одобрение</h5>
                                    <p class="text-muted small">Получите одобрение</p>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="rounded-circle bg-light mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        <i class="fas fa-handshake fa-2x text-primary"></i>
                                    </div>
                                    <h5>Оформление</h5>
                                    <p class="text-muted small">Подпишите договор и получите объект</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница лизинга для юридических лиц
function includeBusinessLeasingPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Лизинг для юридических лиц</h2>
                <p class="lead text-muted">Эффективные решения для вашего бизнеса</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-truck fa-2x text-white"></i>
                                </div>
                                <h3 class="mb-0">Лизинг транспорта для бизнеса</h3>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Легковой и грузовой транспорт, спецтехника</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Срок лизинга от 12 до 60 месяцев</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Первоначальный взнос от 10%</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Ускоренная амортизация и налоговые преимущества</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Комплексное страхование</span>
                                </li>
                            </ul>
                            <a href="#calculator" class="btn btn-primary rounded-pill">Рассчитать лизинг</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                    <i class="fas fa-building fa-2x text-white"></i>
                                </div>
                                <h3 class="mb-0">Лизинг недвижимости для бизнеса</h3>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Офисные, торговые, складские помещения</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Срок лизинга от 24 до 120 месяцев</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Первоначальный взнос от 20%</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Гибкий график платежей с учетом сезонности</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Возможность выкупа или продления лизинга</span>
                                </li>
                            </ul>
                            <a href="#calculator" class="btn btn-primary rounded-pill">Рассчитать лизинг</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-lg-10 mx-auto">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="mb-4 text-center">Преимущества лизинга для бизнеса</h3>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-percentage fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Налоговые льготы</h5>
                                            <p class="text-muted small">Снижение налогооблагаемой базы за счет отнесения лизинговых платежей на расходы</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-bolt fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Ускоренная амортизация</h5>
                                            <p class="text-muted small">Применение повышающего коэффициента при расчете амортизации</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-coins fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Сохранение оборотных средств</h5>
                                            <p class="text-muted small">Нет необходимости выводить значительные средства из оборота</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница автопарка под ключ
function includeFleetLeasingPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Автопарк под ключ</h2>
                <p class="lead text-muted">Комплексные решения для формирования и обслуживания автопарка вашей компании</p>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-6">
                    <h3 class="mb-4">Что включает в себя услуга</h3>
                    <p class="text-muted mb-4">Мы предлагаем полный комплекс услуг по формированию, обновлению и обслуживанию автопарка вашей компании. Вы получаете готовое решение, которое позволит вам сосредоточиться на основном бизнесе, не отвлекаясь на вопросы управления транспортом.</p>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-search text-white"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5>Подбор и приобретение автомобилей</h5>
                            <p class="text-muted small">Поможем выбрать оптимальные модели под ваши задачи и бюджет</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-file-contract text-white"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5>Лизинговое финансирование</h5>
                            <p class="text-muted small">Оформление всех автомобилей в лизинг на выгодных условиях</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-shield-alt text-white"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5>Комплексное страхование</h5>
                            <p class="text-muted small">ОСАГО, КАСКО и другие виды страхования для всего автопарка</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-tools text-white"></i>
                            </div>
                        </div>
                        <div class="ms-3">
                            <h5>Сервисное обслуживание</h5>
                            <p class="text-muted small">Регулярное техобслуживание, ремонт и сезонная замена шин</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Преимущества для вашего бизнеса</h4>
                            
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Экономия времени и ресурсов</h5>
                                            <p class="text-muted small">Все заботы по управлению автопарком мы берем на себя</p>
                                        </div>
                                    </div>
                                </li>
                                
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Оптимизация расходов</h5>
                                            <p class="text-muted small">Прозрачный бюджет и экономия на масштабах при обслуживании</p>
                                        </div>
                                    </div>
                                </li>
                                
                                <li class="mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Снижение налоговой нагрузки</h5>
                                            <p class="text-muted small">Лизинговые платежи относятся на расходы и снижают налогооблагаемую базу</p>
                                        </div>
                                    </div>
                                </li>
                                
                                <li>
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h5>Регулярное обновление автопарка</h5>
                                            <p class="text-muted small">Возможность планового обновления техники без крупных единовременных вложений</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            
                            <div class="mt-4">
                                <a href="index.php?page=contact" class="btn btn-primary rounded-pill px-4">Оставить заявку</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">Наши клиенты</h3>
                            <div class="row row-cols-2 row-cols-md-4 g-4 text-center">
                                <div class="col">
                                    <div class="p-3">
                                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center" style="height: 100px;">
                                            <h5 class="mb-0 text-muted">Компания А</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center" style="height: 100px;">
                                            <h5 class="mb-0 text-muted">Компания Б</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center" style="height: 100px;">
                                            <h5 class="mb-0 text-muted">Компания В</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-3">
                                        <div class="bg-light rounded p-3 d-flex align-items-center justify-content-center" style="height: 100px;">
                                            <h5 class="mb-0 text-muted">Компания Г</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница страхования
function includeInsurancePage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Страхование</h2>
                <p class="lead text-muted">Комплексные решения для страхования лизингового имущества</p>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="rounded-circle bg-primary mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-car fa-2x text-white"></i>
                            </div>
                            <h4>ОСАГО и КАСКО</h4>
                            <p class="text-muted mb-4">Обязательное и добровольное страхование транспортных средств. Защита от ущерба, угона и других рисков.</p>
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Быстрое оформление полиса</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Выгодные тарифы для клиентов лизинга</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Помощь при урегулировании страховых случаев</span>
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Включение страховки в лизинговые платежи</span>
                                </li>
                            </ul>
                            <a href="#insurance-form" class="btn btn-primary rounded-pill">Оформить страховку</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="rounded-circle bg-primary mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-building fa-2x text-white"></i>
                            </div>
                            <h4>Страхование недвижимости</h4>
                            <p class="text-muted mb-4">Комплексная защита объектов недвижимости от пожара, затопления, повреждений и других рисков.</p>
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Индивидуальный расчет стоимости полиса</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Включение в лизинговый договор</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Страхование конструктивных элементов и отделки</span>
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Страхование гражданской ответственности</span>
                                </li>
                            </ul>
                            <a href="#insurance-form" class="btn btn-primary rounded-pill">Оформить страховку</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="rounded-circle bg-primary mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-industry fa-2x text-white"></i>
                            </div>
                            <h4>Страхование оборудования</h4>
                            <p class="text-muted mb-4">Защита промышленного, торгового и другого оборудования, приобретаемого в лизинг.</p>
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Страхование от поломок и выхода из строя</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Защита от краж и умышленного повреждения</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Страхование на период транспортировки</span>
                                </li>
                                <li>
                                    <i class="fas fa-check text-primary me-2"></i>
                                    <span>Специальные условия для крупных лизинговых сделок</span>
                                </li>
                            </ul>
                            <a href="#insurance-form" class="btn btn-primary rounded-pill">Оформить страховку</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm" id="insurance-form">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">Заявка на страхование</h3>
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="insuranceName" class="form-label">Имя</label>
                                        <input type="text" class="form-control" id="insuranceName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="insurancePhone" class="form-label">Телефон</label>
                                        <input type="tel" class="form-control" id="insurancePhone" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="insuranceEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="insuranceEmail" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="insuranceType" class="form-label">Тип страхования</label>
                                        <select class="form-select" id="insuranceType" required>
                                            <option value="" selected disabled>Выберите тип страхования</option>
                                            <option value="auto">ОСАГО и КАСКО</option>
                                            <option value="property">Страхование недвижимости</option>
                                            <option value="equipment">Страхование оборудования</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="insuranceMessage" class="form-label">Комментарий</label>
                                        <textarea class="form-control" id="insuranceMessage" rows="3"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="insuranceAgree" required>
                                            <label class="form-check-label small text-muted" for="insuranceAgree">
                                                Я согласен с <a href="index.php?page=privacy">политикой конфиденциальности</a> и даю согласие на обработку персональных данных
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mt-4">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5">Отправить заявку</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница политики конфиденциальности
function includePrivacyPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="mb-4">Политика конфиденциальности</h2>
                            
                            <p class="text-muted">Последнее обновление: 10 апреля 2025 г.</p>
                            
                            <p>Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных, предоставляемых пользователями при использовании сайта и услуг компании "2Leasing".</p>
                            
                            <h4 class="mt-4">1. Общие положения</h4>
                            <p>1.1. Настоящая Политика конфиденциальности является официальным документом и определяет порядок обработки и защиты информации о физических и юридических лицах, использующих сайт и услуги "2Leasing".</p>
                            <p>1.2. Целью настоящей Политики является обеспечение надлежащей защиты информации о пользователях, в том числе их персональных данных, от несанкционированного доступа и разглашения.</p>
                            <p>1.3. Отношения, связанные со сбором, хранением, распространением и защитой информации о пользователях, регулируются настоящей Политикой и действующим законодательством Российской Федерации.</p>
                            <p>1.4. Используя сайт и услуги "2Leasing", пользователь выражает свое согласие с условиями настоящей Политики конфиденциальности.</p>
                            
                            <h4 class="mt-4">2. Сбор и использование персональных данных</h4>
                            <p>2.1. Компания "2Leasing" собирает и хранит только ту персональную информацию, которая необходима для предоставления услуг лизинга и связанных с ними сервисов.</p>
                            <p>2.2. Персональная информация пользователя, которую обрабатывает "2Leasing", включает в себя:</p>
                            <ul>
                                <li>Фамилию, имя, отчество</li>
                                <li>Контактную информацию (телефон, email, адрес)</li>
                                <li>Паспортные данные (для оформления договоров)</li>
                                <li>Финансовую информацию (для оценки платежеспособности)</li>
                                <li>Информацию о предпочтениях и интересах пользователя</li>
                            </ul>
                            
                            <h4 class="mt-4">3. Цели обработки персональных данных</h4>
                            <p>3.1. "2Leasing" обрабатывает персональные данные пользователей в следующих целях:</p>
                            <ul>
                                <li>Идентификация пользователя в рамках предоставления услуг</li>
                                <li>Заключение и исполнение договоров лизинга</li>
                                <li>Обработка заявок и запросов пользователей</li>
                                <li>Предоставление пользователям персонализированных предложений</li>
                                <li>Улучшение качества услуг и удобства использования сайта</li>
                                <li>Проведение статистических и маркетинговых исследований</li>
                            </ul>
                            
                            <h4 class="mt-4">4. Защита персональных данных</h4>
                            <p>4.1. "2Leasing" принимает необходимые организационные и технические меры для защиты персональной информации пользователя от неправомерного или случайного доступа, уничтожения, изменения, блокирования, копирования, распространения, а также от иных неправомерных действий третьих лиц.</p>
                            
                            <h4 class="mt-4">5. Заключительные положения</h4>
                            <p>5.1. "2Leasing" имеет право вносить изменения в настоящую Политику конфиденциальности. При внесении изменений в актуальной редакции указывается дата последнего обновления.</p>
                            <p>5.2. По всем вопросам, связанным с настоящей Политикой, пользователь может обратиться в компанию по контактным данным, указанным на сайте.</p>
                            
                            <div class="mt-5">
                                <a href="index.php" class="btn btn-outline-primary rounded-pill">
                                    <i class="fas fa-arrow-left me-2"></i> Вернуться на главную
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница условий использования
function includeTermsPage() {
    echo '<section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-lg-5">
                            <h2 class="mb-4">Условия использования</h2>
                            
                            <p class="text-muted">Последнее обновление: 10 апреля 2025 г.</p>
                            
                            <p>Настоящий документ определяет условия использования сайта и услуг компании "2Leasing". Используя наш сайт и услуги, вы соглашаетесь с настоящими условиями.</p>
                            
                            <h4 class="mt-4">1. Общие положения</h4>
                            <p>1.1. Сайт и услуги "2Leasing" предназначены для предоставления информации о лизинговых продуктах и услугах, а также для оформления заявок на лизинг.</p>
                            <p>1.2. Компания "2Leasing" оставляет за собой право в любое время изменять, добавлять или удалять пункты настоящих Условий без уведомления пользователя.</p>
                            <p>1.3. Продолжение использования сайта и услуг "2Leasing" после внесения изменений в Условия означает принятие и согласие пользователя с такими изменениями.</p>
                            
                            <h4 class="mt-4">2. Права и обязанности пользователя</h4>
                            <p>2.1. Пользователь имеет право:</p>
                            <ul>
                                <li>Получать информацию о лизинговых продуктах и услугах</li>
                                <li>Оформлять заявки на лизинг</li>
                                <li>Использовать калькулятор для расчета лизинговых платежей</li>
                                <li>Обращаться в компанию по указанным на сайте контактным данным</li>
                            </ul>
                            
                            <p>2.2. Пользователь обязуется:</p>
                            <ul>
                                <li>Предоставлять достоверную информацию при заполнении форм на сайте</li>
                                <li>Не нарушать работоспособность сайта</li>
                                <li>Не использовать сайт для распространения информации рекламного или коммерческого характера без согласования с администрацией сайта</li>
                                <li>Не использовать сайт для каких-либо противоправных действий</li>
                            </ul>
                            
                            <h4 class="mt-4">3. Ответственность</h4>
                            <p>3.1. Компания "2Leasing" не несет ответственности за временные технические сбои и перерывы в работе сайта, а также за их последствия.</p>
                            <p>3.2. Компания "2Leasing" не несет ответственности за любые убытки, которые пользователь может понести в результате использования или невозможности использования сайта.</p>
                            <p>3.3. Пользователь несет полную ответственность за сохранность своих учетных данных и за последствия, которые могут возникнуть из-за их утраты или несанкционированного использования.</p>
                            
                            <h4 class="mt-4">4. Интеллектуальная собственность</h4>
                            <p>4.1. Все материалы, размещенные на сайте "2Leasing", включая тексты, графику, логотипы, изображения, а также их подборка и расположение, являются интеллектуальной собственностью компании и защищены законодательством Российской Федерации.</p>
                            <p>4.2. Любое использование материалов сайта без согласия правообладателя не допускается.</p>
                            
                            <h4 class="mt-4">5. Заключительные положения</h4>
                            <p>5.1. Настоящие Условия регулируются и толкуются в соответствии с законодательством Российской Федерации.</p>
                            <p>5.2. Любые споры, возникающие из настоящих Условий или в связи с ними, подлежат разрешению в соответствии с законодательством Российской Федерации.</p>
                            <p>5.3. По всем вопросам, связанным с настоящими Условиями, пользователь может обратиться в компанию по контактным данным, указанным на сайте.</p>
                            
                            <div class="mt-5">
                                <a href="index.php" class="btn btn-outline-primary rounded-pill">
                                    <i class="fas fa-arrow-left me-2"></i> Вернуться на главную
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Главная страница
function includeHomePage() {
    global $vehicles;
    global $realEstate;
    
    echo '<section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 fade-in">
                    <h1 class="display-4 fw-bold mb-4">Универсальный<br><span class="text-primary">лизинг</span> для всех</h1>
                    <p class="lead mb-4">Лизинг недвижимости и транспорта на выгодных условиях с индивидуальным подходом для физических и юридических лиц</p>
                    <div class="hero-cta mt-5 d-flex flex-wrap gap-3">
                        <a href="index.php?page=register" class="btn btn-primary btn-lg rounded-pill px-4 py-3 shadow-lg">
                            <i class="fas fa-rocket me-2"></i> Начать сейчас
                        </a>
                        <a href="#calculator" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                            <i class="fas fa-calculator me-2"></i> Рассчитать лизинг
                        </a>
                    </div>
                    <div class="mt-5 d-flex gap-4">
                        <div class="text-center">
                            <h3 class="text-white fw-bold mb-0">10+</h3>
                            <p class="text-white-50 small mb-0">лет опыта</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-white fw-bold mb-0">5000+</h3>
                            <p class="text-white-50 small mb-0">клиентов</p>
                        </div>
                        <div class="text-center">
                            <h3 class="text-white fw-bold mb-0">1000+</h3>
                            <p class="text-white-50 small mb-0">объектов</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-image position-relative mt-5">
                        <div class="card shadow-lg rounded-3 border-0 p-4 bg-white position-absolute" style="top: 0; right: 0;">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">Автомобили</h5>
                                    <p class="text-muted small mb-0">от 20 000 ₽/мес</p>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="card shadow-lg rounded-3 border-0 p-4 bg-white position-absolute" style="bottom: 0; left: 0;">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-accent rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-0">Недвижимость</h5>
                                    <p class="text-muted small mb-0">от 50 000 ₽/мес</p>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Секция калькулятора лизинга -->
    <section id="calculator" class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h2 class="text-center mb-4">Рассчитайте ваш лизинг</h2>
                            
                            <ul class="nav nav-pills nav-justified mb-4" id="leasingTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="vehicle-tab" data-bs-toggle="pill" data-bs-target="#vehicle-calc" type="button" role="tab" aria-controls="vehicle-calc" aria-selected="true">
                                        <i class="fas fa-car me-2"></i>Транспорт
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="realestate-tab" data-bs-toggle="pill" data-bs-target="#realestate-calc" type="button" role="tab" aria-controls="realestate-calc" aria-selected="false">
                                        <i class="fas fa-home me-2"></i>Недвижимость
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="leasingTabsContent">
                                <!-- Калькулятор для транспорта -->
                                <div class="tab-pane fade show active" id="vehicle-calc" role="tabpanel" aria-labelledby="vehicle-tab">
                                    <form id="vehicleCalcForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="vehiclePrice" class="form-label">Стоимость транспорта (₽)</label>
                                                <input type="range" class="form-range" id="vehiclePriceRange" min="500000" max="10000000" step="100000" value="3000000" oninput="updateVehiclePrice()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="vehiclePrice" value="3000000" min="500000" max="10000000" oninput="updateVehiclePriceRange()">
                                                    <span class="input-group-text">₽</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="vehicleDownPayment" class="form-label">Первоначальный взнос (%)</label>
                                                <input type="range" class="form-range" id="vehicleDownPaymentRange" min="10" max="49" value="20" oninput="updateVehicleDownPayment()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="vehicleDownPayment" value="20" min="10" max="49" oninput="updateVehicleDownPaymentRange()">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="vehicleTerm" class="form-label">Срок лизинга (месяцев)</label>
                                                <input type="range" class="form-range" id="vehicleTermRange" min="12" max="60" step="12" value="36" oninput="updateVehicleTerm()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="vehicleTerm" value="36" min="12" max="60" step="12" oninput="updateVehicleTermRange()">
                                                    <span class="input-group-text">мес.</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="vehicleType" class="form-label">Тип транспорта</label>
                                                <select class="form-select" id="vehicleType">
                                                    <option value="car">Легковой автомобиль</option>
                                                    <option value="truck">Грузовой автомобиль</option>
                                                    <option value="special">Спецтехника</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-center mt-4">
                                                <button type="button" class="btn btn-primary px-5 rounded-pill" onclick="calculateVehicleLeasing()">Рассчитать</button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div id="vehicleResult" class="mt-4" style="display: none;">
                                        <hr>
                                        <h4 class="text-center">Результаты расчета</h4>
                                        <div class="row mt-3">
                                            <div class="col-md-4 text-center">
                                                <h5 id="vehicleMonthlyPayment">42 300 ₽</h5>
                                                <p class="text-muted">Ежемесячный платеж</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <h5 id="vehicleTotalCost">3 720 000 ₽</h5>
                                                <p class="text-muted">Общая стоимость</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <h5 id="vehicleDownPaymentAmount">600 000 ₽</h5>
                                                <p class="text-muted">Первоначальный взнос</p>
                                            </div>
                                        </div>
                                        
                                        <div id="vehicleCompanies" class="mt-4">
                                            <h5>Подходящие предложения</h5>
                                            <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
                                                <!-- Предложения лизинговых компаний будут добавлены JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Калькулятор для недвижимости -->
                                <div class="tab-pane fade" id="realestate-calc" role="tabpanel" aria-labelledby="realestate-tab">
                                    <form id="realEstateCalcForm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="realEstatePrice" class="form-label">Стоимость объекта (₽)</label>
                                                <input type="range" class="form-range" id="realEstatePriceRange" min="2000000" max="50000000" step="1000000" value="10000000" oninput="updateRealEstatePrice()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="realEstatePrice" value="10000000" min="2000000" max="50000000" oninput="updateRealEstatePriceRange()">
                                                    <span class="input-group-text">₽</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="realEstateDownPayment" class="form-label">Первоначальный взнос (%)</label>
                                                <input type="range" class="form-range" id="realEstateDownPaymentRange" min="20" max="70" value="30" oninput="updateRealEstateDownPayment()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="realEstateDownPayment" value="30" min="20" max="70" oninput="updateRealEstateDownPaymentRange()">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="realEstateTerm" class="form-label">Срок лизинга (месяцев)</label>
                                                <input type="range" class="form-range" id="realEstateTermRange" min="12" max="120" step="12" value="60" oninput="updateRealEstateTerm()">
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="realEstateTerm" value="60" min="12" max="120" step="12" oninput="updateRealEstateTermRange()">
                                                    <span class="input-group-text">мес.</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="realEstateType" class="form-label">Тип недвижимости</label>
                                                <select class="form-select" id="realEstateType">
                                                    <option value="apartment">Квартира</option>
                                                    <option value="house">Частный дом</option>
                                                    <option value="commercial">Коммерческая недвижимость</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-center mt-4">
                                                <button type="button" class="btn btn-primary px-5 rounded-pill" onclick="calculateRealEstateLeasing()">Рассчитать</button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div id="realEstateResult" class="mt-4" style="display: none;">
                                        <hr>
                                        <h4 class="text-center">Результаты расчета</h4>
                                        <div class="row mt-3">
                                            <div class="col-md-4 text-center">
                                                <h5 id="realEstateMonthlyPayment">126 700 ₽</h5>
                                                <p class="text-muted">Ежемесячный платеж</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <h5 id="realEstateTotalCost">12 600 000 ₽</h5>
                                                <p class="text-muted">Общая стоимость</p>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <h5 id="realEstateDownPaymentAmount">3 000 000 ₽</h5>
                                                <p class="text-muted">Первоначальный взнос</p>
                                            </div>
                                        </div>
                                        
                                        <div id="realEstateCompanies" class="mt-4">
                                            <h5>Подходящие предложения</h5>
                                            <div class="row row-cols-1 row-cols-md-3 g-4 mt-2">
                                                <!-- Предложения лизинговых компаний будут добавлены JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="features-section py-5">
        <div class="container">
            <h2 class="text-center mb-5">Почему выбирают 2Leasing</h2>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card shadow-sm rounded h-100">
                        <div class="feature-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3>Выгодные условия</h3>
                        <p>Минимальные первоначальные взносы и гибкие условия финансирования</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card shadow-sm rounded h-100">
                        <div class="feature-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Недвижимость</h3>
                        <p>Квартиры, дома и коммерческие объекты с выгодными лизинговыми программами</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card shadow-sm rounded h-100">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Транспорт</h3>
                        <p>Более 500 моделей автомобилей и спецтехники от ведущих производителей</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card shadow-sm rounded h-100">
                        <div class="feature-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <h3>Быстрое оформление</h3>
                        <p>Одобрение заявки за 1 день и доставка автомобиля в течение недели</p>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Примеры автомобилей для демонстрации
    $sampleVehicles = [
        [
            'id' => 1,
            'make' => 'BMW',
            'model' => 'X5',
            'image_url' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 7500000,
            'monthly_payment' => 125000,
            'short_description' => 'Роскошный внедорожник с высокой динамикой и премиальным уровнем комфорта',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Бензин',
            'features' => ['Кожаный салон', 'Панорамная крыша', 'Адаптивный круиз-контроль', 'Система кругового обзора']
        ],
        [
            'id' => 2,
            'make' => 'Mercedes-Benz',
            'model' => 'E-Class',
            'image_url' => 'https://images.unsplash.com/photo-1617813480365-b7510ea4931f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 6800000,
            'monthly_payment' => 115000,
            'short_description' => 'Элегантный седан бизнес-класса с инновационными технологиями',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Дизель',
            'features' => ['Мультиконтурные сиденья', 'MBUX', 'Цифровая приборная панель', 'Беспроводная зарядка']
        ],
        [
            'id' => 3,
            'make' => 'Tesla',
            'model' => 'Model 3',
            'image_url' => 'https://images.unsplash.com/photo-1612496910393-fda882c319e1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 5200000,
            'monthly_payment' => 85000,
            'short_description' => 'Инновационный электромобиль с высокой автономностью и динамикой',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Электро',
            'features' => ['Автопилот', 'Запас хода 500 км', 'Разгон до 100 км/ч за 3.3 сек', 'Стеклянная крыша']
        ],
        [
            'id' => 4,
            'make' => 'Toyota',
            'model' => 'Land Cruiser',
            'image_url' => 'https://images.unsplash.com/photo-1594214575758-6e8982e5ce00?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 8200000,
            'monthly_payment' => 135000,
            'short_description' => 'Надежный внедорожник с легендарной проходимостью и комфортом',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Дизель',
            'features' => ['Полный привод', 'Система Kinetic Dynamic Suspension', '7 мест', 'Мультитерренный режим движения']
        ],
        [
            'id' => 5,
            'make' => 'Audi',
            'model' => 'A6',
            'image_url' => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 6300000,
            'monthly_payment' => 105000,
            'short_description' => 'Технологичный бизнес-седан с прогрессивным дизайном и комфортом',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Бензин',
            'features' => ['Виртуальная приборная панель', 'Матричные фары', 'Адаптивная подвеска', 'B&O Sound System']
        ],
        [
            'id' => 6,
            'make' => 'Volkswagen',
            'model' => 'Tiguan',
            'image_url' => 'https://images.unsplash.com/photo-1612770128014-99577bea7d84?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 3900000,
            'monthly_payment' => 65000,
            'short_description' => 'Компактный кроссовер с высоким уровнем комфорта и безопасности',
            'year' => 2023,
            'mileage' => 0,
            'fuel_type' => 'Бензин',
            'features' => ['Адаптивный круиз-контроль', 'Цифровая приборная панель', 'Парктроник', 'Система контроля слепых зон']
        ]
    ];
    
    echo '<section class="vehicles-section py-5" id="vehicles">
        <div class="container">
            <h2 class="text-center mb-5">Популярные автомобили</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
            
    foreach ($sampleVehicles as $vehicle) {
        echo '<div class="col">
                <div class="card vehicle-card h-100 shadow-sm border-0 rounded overflow-hidden">
                    <img src="' . htmlspecialchars($vehicle['image_url']) . '" alt="' . htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) . '" class="vehicle-img">
                    <div class="card-body p-4">
                        <h5 class="card-title">' . htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) . '</h5>
                        <p class="vehicle-price mb-2 fs-5 fw-bold text-primary">' . number_format($vehicle['monthly_payment'], 0, '.', ' ') . ' ₽/мес</p>
                        <p class="small text-muted">Цена: ' . number_format($vehicle['price'], 0, '.', ' ') . ' ₽</p>
                        <ul class="vehicle-features mt-3">';
                        
        // Выводим до 4 характеристик
        $featureCount = 0;
        foreach ($vehicle['features'] as $feature) {
            if ($featureCount < 4) {
                echo '<li><i class="fas fa-check-circle text-success me-2"></i>' . htmlspecialchars($feature) . '</li>';
                $featureCount++;
            }
        }
                        
        echo '</ul>
                    </div>
                    <div class="card-footer border-0 bg-white p-4">
                        <a href="index.php?page=vehicle&id=' . $vehicle['id'] . '" class="btn btn-outline-primary rounded-pill w-100">Подробнее</a>
                    </div>
                </div>
            </div>';
    }
            
    echo '</div>
            <div class="text-center mt-5">
                <a href="index.php?page=marketplace" class="btn btn-outline-primary btn-lg">Смотреть все автомобили</a>
            </div>
        </div>
    </section>';
    
    // Популярная недвижимость
    // Примеры объектов недвижимости для демонстрации
    $sampleRealEstate = [
        [
            'id' => 1,
            'title' => 'Квартира в центре города',
            'image_url' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 15000000,
            'short_description' => 'Современная 3-комнатная квартира с дизайнерским ремонтом в центре города',
            'square_meters' => 85,
            'location' => 'Москва, ул. Тверская',
            'type' => 'Квартира'
        ],
        [
            'id' => 2,
            'title' => 'Загородный дом',
            'image_url' => 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 25000000,
            'short_description' => 'Просторный загородный дом с участком и всеми коммуникациями',
            'square_meters' => 220,
            'location' => 'Московская область, 15 км от МКАД',
            'type' => 'Дом'
        ],
        [
            'id' => 3,
            'title' => 'Коммерческое помещение',
            'image_url' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 35000000,
            'short_description' => 'Помещение для бизнеса в новом торговом центре с высокой проходимостью',
            'square_meters' => 150,
            'location' => 'Москва, Кутузовский проспект',
            'type' => 'Коммерческое'
        ],
        [
            'id' => 4,
            'title' => 'Студия в новостройке',
            'image_url' => 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 7500000,
            'short_description' => 'Уютная студия с современной отделкой в новом жилом комплексе бизнес-класса',
            'square_meters' => 45,
            'location' => 'Москва, ул. Ленинская',
            'type' => 'Квартира'
        ],
        [
            'id' => 5,
            'title' => 'Таунхаус в коттеджном поселке',
            'image_url' => 'https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 12000000,
            'short_description' => 'Таунхаус 150 м² с гаражом и террасой в охраняемом коттеджном поселке',
            'square_meters' => 150,
            'location' => 'Московская область, Одинцовский район',
            'type' => 'Таунхаус'
        ],
        [
            'id' => 6,
            'title' => 'Пентхаус с панорамным видом',
            'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
            'price' => 60000000,
            'short_description' => 'Двухуровневый пентхаус с панорамными окнами и террасой на крыше',
            'square_meters' => 280,
            'location' => 'Москва, Пресненская набережная',
            'type' => 'Пентхаус'
        ]
    ];
    
    echo '<section class="realestate-section py-5" id="realestate">
        <div class="container">
            <h2 class="text-center mb-5">Популярная недвижимость</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
            
    foreach ($sampleRealEstate as $property) {
        echo '<div class="col">
                <div class="card realestate-card h-100 shadow-sm border-0 rounded overflow-hidden">
                    <img src="' . htmlspecialchars($property['image_url']) . '" class="realestate-img" alt="' . htmlspecialchars($property['title']) . '">
                    <div class="card-body p-4">
                        <h5 class="card-title">' . htmlspecialchars($property['title']) . '</h5>
                        <p class="realestate-price mb-3 fs-5 fw-bold text-primary">' . number_format($property['price'], 0, '.', ' ') . ' ₽</p>
                        <p class="card-text">' . htmlspecialchars($property['short_description']) . '</p>
                        <ul class="realestate-features mt-3">
                            <li><i class="fas fa-ruler-combined me-2 text-primary"></i>' . htmlspecialchars($property['square_meters']) . ' м²</li>
                            <li><i class="fas fa-map-marker-alt me-2 text-primary"></i>' . htmlspecialchars($property['location']) . '</li>
                            <li><i class="fas fa-home me-2 text-primary"></i>' . htmlspecialchars($property['type']) . '</li>
                        </ul>
                    </div>
                    <div class="card-footer border-0 bg-white p-4">
                        <a href="index.php?page=real-estate-item&id=' . intval($property['id']) . '" class="btn btn-outline-primary rounded-pill w-100">Подробнее</a>
                    </div>
                </div>
            </div>';
    }
    
    echo '</div>
            <div class="text-center mt-5">
                <a href="index.php?page=real-estate" class="btn btn-accent rounded-pill px-4 py-2">Смотреть все объекты недвижимости</a>
            </div>
        </div>
    </section>';

    // О компании
    echo '<section class="py-5 bg-light" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="О нас" class="img-fluid rounded shadow-lg">
                </div>
                <div class="col-md-6">
                    <h2 class="mb-4">О компании 2Leasing</h2>
                    <p class="lead">Мы помогаем клиентам получить недвижимость и транспорт мечты на выгодных условиях с 2010 года.</p>
                    <p>Компания 2Leasing специализируется на предоставлении услуг лизинга недвижимости и транспорта для физических и юридических лиц. Мы сотрудничаем со всеми крупнейшими застройщиками и автопроизводителями, предлагая широкий выбор объектов недвижимости и автомобилей различных категорий.</p>
                    <p>Наша команда профессионалов готова подобрать индивидуальное решение, учитывая ваши потребности и финансовые возможности.</p>
                    <div class="d-flex mt-4">
                        <div class="me-4 text-center">
                            <h3 class="fw-bold text-primary">10+</h3>
                            <p>лет опыта</p>
                        </div>
                        <div class="me-4 text-center">
                            <h3 class="fw-bold text-primary">5000+</h3>
                            <p>довольных клиентов</p>
                        </div>
                        <div class="text-center">
                            <h3 class="fw-bold text-primary">1000+</h3>
                            <p>объектов</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Призыв к действию
    echo '<section class="call-to-action">
        <div class="container">
            <h2 class="mb-4">Готовы приобрести недвижимость или транспорт в лизинг?</h2>
            <p class="lead mb-4">Заполните заявку и наши менеджеры свяжутся с вами в ближайшее время</p>
            <a href="index.php?page=register" class="btn btn-accent btn-lg rounded-pill px-5">Подать заявку</a>
        </div>
    </section>';

    // Контакты
    echo '<section class="py-5" id="contact">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Свяжитесь с нами</h2>
                <p class="lead text-muted">Задайте вопрос или запишитесь на консультацию</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <form id="contactForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="name" placeholder="Ваше имя" required>
                                            <label for="name">Ваше имя</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating mb-3">
                                            <input type="tel" class="form-control" id="phone" placeholder="Телефон" required>
                                            <label for="phone">Телефон</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" placeholder="Email" required>
                                    <label for="email">Email</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="subject" required>
                                        <option value="" selected disabled>Выберите тему</option>
                                        <option value="transport">Лизинг транспорта</option>
                                        <option value="realestate">Лизинг недвижимости</option>
                                        <option value="other">Другое</option>
                                    </select>
                                    <label for="subject">Тема обращения</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="message" style="height: 120px" placeholder="Сообщение" required></textarea>
                                    <label for="message">Сообщение</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="agree" required>
                                    <label class="form-check-label small" for="agree">
                                        Я согласен на обработку персональных данных в соответствии с <a href="#" class="text-decoration-none">политикой конфиденциальности</a>
                                    </label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">Отправить сообщение</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle text-white" style="width: 50px; height: 50px;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-1">Адрес офиса</h4>
                                    <p class="mb-0 text-muted">г. Москва, ул. Тверская, д. 10, офис 305</p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle text-white" style="width: 50px; height: 50px;">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-1">Телефон</h4>
                                    <p class="mb-0 text-muted">+7 (495) 123-45-67</p>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle text-white" style="width: 50px; height: 50px;">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-1">Email</h4>
                                    <p class="mb-0 text-muted">info@2leasing.ru</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle text-white" style="width: 50px; height: 50px;">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <h4 class="mb-1">Режим работы</h4>
                                    <p class="mb-0 text-muted">Пн-Пт: 9:00 - 20:00<br>Сб: 10:00 - 18:00<br>Вс: выходной</p>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top">
                                <h5 class="mb-3">Мы в социальных сетях</h5>
                                <div class="d-flex gap-3">
                                    <a href="#" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fab fa-vk"></i>
                                    </a>
                                    <a href="#" class="btn btn-outline-primary rounded-circle" style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fab fa-telegram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница входа
function includeLoginPage() {
    global $error;
    
    echo '<section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="text-center mb-4">
                        <h2>Вход в личный кабинет</h2>
                        <p class="text-muted">Войдите, чтобы получить доступ к информации о ваших заявках и предложениях</p>
                    </div>
                    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                        <div class="card-body p-4 p-md-5">';
                        
    if (isset($error)) {
        echo '<div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($error) . '
              </div>';
    }
                        
    echo '<form method="post">
                                <input type="hidden" name="action" value="login">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="login-email" name="email" placeholder="name@example.com" required>
                                    <label for="login-email"><i class="fas fa-envelope me-2 text-muted"></i>Email</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="login-password" name="password" placeholder="Password" required>
                                    <label for="login-password"><i class="fas fa-lock me-2 text-muted"></i>Пароль</label>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                                        <label class="form-check-label" for="remember-me">Запомнить меня</label>
                                    </div>
                                    <a href="#" class="text-decoration-none small">Забыли пароль?</a>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill">Войти</button>
                                </div>
                                <div class="text-center">
                                    <p class="mb-0">Нет аккаунта? <a href="index.php?page=register" class="text-decoration-none fw-semibold">Зарегистрироваться</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2 text-primary"></i>Демо-доступ</h5>
                                <p class="small mb-2">Для тестирования различных уровней доступа:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold small">Клиент:</td>
                                                <td class="small">client@2leasing.ru / password</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold small">Менеджер:</td>
                                                <td class="small">manager1@2leasing.ru / password</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold small">Администратор:</td>
                                                <td class="small">admin@2leasing.ru / password</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Страница регистрации
function includeRegisterPage() {
    global $error;
    
    echo '<section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-4">
                        <h2>Регистрация</h2>
                        <p class="text-muted">Создайте аккаунт для доступа к услугам лизинга</p>
                    </div>
                    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                        <div class="card-body p-4 p-md-5">';
                        
    if (isset($error)) {
        echo '<div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>' . htmlspecialchars($error) . '
              </div>';
    }
                        
    echo '<form class="row g-3" method="post">
                                <input type="hidden" name="action" value="register">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="register-fname" name="first_name" placeholder="Имя" required>
                                        <label for="register-fname"><i class="fas fa-user me-2 text-muted"></i>Имя</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="register-lname" name="last_name" placeholder="Фамилия" required>
                                        <label for="register-lname"><i class="fas fa-user me-2 text-muted"></i>Фамилия</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="email" class="form-control" id="register-email" name="email" placeholder="Email" required>
                                        <label for="register-email"><i class="fas fa-envelope me-2 text-muted"></i>Email</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="tel" class="form-control" id="register-phone" name="phone" placeholder="Телефон" required>
                                        <label for="register-phone"><i class="fas fa-phone me-2 text-muted"></i>Телефон</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="register-password" name="password" placeholder="Пароль" required>
                                        <label for="register-password"><i class="fas fa-lock me-2 text-muted"></i>Пароль</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="password" class="form-control" id="register-confirm-password" name="password_confirm" placeholder="Подтверждение пароля" required>
                                        <label for="register-confirm-password"><i class="fas fa-lock me-2 text-muted"></i>Подтверждение пароля</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mb-4">
                                        <input class="form-check-input" type="checkbox" id="agree-terms" name="terms" required>
                                        <label class="form-check-label" for="agree-terms">
                                            Я согласен с <a href="#" class="text-decoration-none">условиями использования</a> и <a href="#" class="text-decoration-none">политикой конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg rounded-pill">Зарегистрироваться</button>
                                    </div>
                                </div>
                                <div class="col-12 text-center mt-3">
                                    <p class="mb-0">Уже есть аккаунт? <a href="index.php?page=login" class="text-decoration-none fw-semibold">Войти</a></p>
                                </div>
                            </form>
                            
                            <div class="mt-4 pt-3 border-top">
                                <div class="text-center">
                                    <h5 class="mb-3">Преимущества регистрации</h5>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="feature p-3">
                                                <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                                    <i class="fas fa-clipboard-list"></i>
                                                </div>
                                                <h6>Отслеживание заявок</h6>
                                                <p class="small text-muted mb-0">Следите за статусом своих заявок в реальном времени</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature p-3">
                                                <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                                    <i class="fas fa-calculator"></i>
                                                </div>
                                                <h6>Персональные предложения</h6>
                                                <p class="small text-muted mb-0">Получайте индивидуальные условия лизинга</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="feature p-3">
                                                <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                                    <i class="fas fa-bell"></i>
                                                </div>
                                                <h6>Уведомления</h6>
                                                <p class="small text-muted mb-0">Получайте уведомления о новых предложениях</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';
}

// Маршрутизация
outputHeader();
outputNavigation();

// Проверка авторизации для защищенных страниц
$protectedClientPages = ['dashboard-client'];
$protectedManagerPages = ['dashboard-manager', 'application-details', 'vehicles-admin', 'add-vehicle'];
$protectedAdminPages = ['dashboard-admin', 'managers', 'add-manager'];

// Редирект неавторизованных пользователей
if (in_array($page, $protectedClientPages) && !$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Редирект пользователей без прав менеджера
if (in_array($page, $protectedManagerPages) && !$auth->isManager()) {
    header('Location: index.php?page=login');
    exit;
}

// Редирект пользователей без прав администратора
if (in_array($page, $protectedAdminPages) && !$auth->isAdmin()) {
    header('Location: index.php?page=login');
    exit;
}

// Отображение страницы
switch ($page) {
    case 'login':
        includeLoginPage();
        break;
    case 'register':
        includeRegisterPage();
        break;
    case 'dashboard-client':
        include 'pages/dashboard-client.php';
        break;
    case 'dashboard-manager':
        include 'pages/dashboard-manager.php';
        break;
    case 'dashboard-admin':
        include 'pages/dashboard-admin.php';
        break;
    case 'marketplace':
        include 'pages/marketplace.php';
        break;
    case 'vehicle':
        include 'pages/vehicle.php';
        break;
    case 'real-estate':
        include 'pages/real-estate.php';
        break;
    case 'real-estate-item':
        include 'pages/real-estate-item.php';
        break;
    case 'dashboard-parsers':
        include 'pages/dashboard-parsers.php';
        break;
    case 'profile':
        includeProfilePage();
        break;
    case 'applications':
        includeApplicationsPage();
        break;

    case 'careers':
        includeCareersPage();
        break;
    case 'blog':
        includeBlogPage();
        break;
    case 'personal':
        includePersonalLeasingPage();
        break;
    case 'business':
        includeBusinessLeasingPage();
        break;
    case 'fleet':
        includeFleetLeasingPage();
        break;
    case 'insurance':
        includeInsurancePage();
        break;
    case 'privacy':
        includePrivacyPage();
        break;
    case 'terms':
        includeTermsPage();
        break;
    default:
        includeHomePage();
        break;
}

outputFooter();
?>