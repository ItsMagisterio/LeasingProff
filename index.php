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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <script src="js/leasing-calculator.js" defer></script>
        <style>
            /* Основные стили */
            :root {
                --primary-color: #0056b3;
                --secondary-color: #004494;
                --accent-color: #ff9800;
                --light-color: #f8f9fa;
                --dark-color: #343a40;
            }
            
            body {
                font-family: "Roboto", sans-serif;
                color: #333;
            }
            
            .navbar {
                background-color: var(--primary-color);
                padding: 1rem 0;
            }
            
            .navbar-brand {
                font-weight: 700;
                font-size: 1.5rem;
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
    global $auth;
    
    echo '<nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">2Leasing</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=marketplace">Автомобили</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=real-estate">Недвижимость</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">О нас</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Контакты</a>
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
                <div class="d-flex">';
    
    if ($auth->isLoggedIn()) {
        echo '<span class="text-white me-3 d-none d-md-inline">Привет, ' . htmlspecialchars($auth->getUserName()) . '</span>
              <form method="post">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-outline-light">Выход</button>
              </form>';
    } else {
        echo '<a href="index.php?page=login" class="btn btn-outline-light me-2">Вход</a>
              <a href="index.php?page=register" class="btn btn-light">Регистрация</a>';
    }
    
    echo '</div>
            </div>
        </div>
    </nav>';
}

// Подвал
function outputFooter() {
    echo '<footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>2Leasing</h5>
                    <p>Лизинг недвижимости и транспорта на выгодных условиях для физических и юридических лиц</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-vk"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-telegram"></i></a>
                    </div>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5>Компания</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">О нас</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Команда</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Карьера</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Блог</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Контакты</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5>Услуги</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">Лизинг недвижимости</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Лизинг транспорта</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Лизинг для физлиц</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Лизинг для юрлиц</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Автопарк под ключ</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Страхование</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Документы</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none">Договор лизинга</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Политика конфиденциальности</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Условия использования</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Лицензии</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4 bg-light">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; ' . date('Y') . ' 2Leasing. Все права защищены.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Разработано с <i class="fas fa-heart text-danger"></i> компанией 2Leasing</p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}

// Главная страница
function includeHomePage() {
    global $vehicles;
    global $realEstate;
    
    echo '<section class="hero-section">
        <div class="container">
            <h1>Универсальный лизинг 2Leasing</h1>
            <p class="lead mb-4">Лизинг недвижимости и транспорта на выгодных условиях</p>
            <div class="mt-5">
                <a href="index.php?page=register" class="btn btn-primary btn-lg me-2 rounded-pill">Начать сейчас</a>
                <a href="#calculator" class="btn btn-outline-light btn-lg rounded-pill">Рассчитать лизинг</a>
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
    default:
        includeHomePage();
        break;
}

outputFooter();
?>