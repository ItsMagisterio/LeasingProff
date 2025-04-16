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
function outputHeader($title = 'Лизинг автомобилей') {
    echo '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . ' | 2Leasing</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            
            .vehicles-section {
                padding: 5rem 0;
            }
            
            .vehicle-card {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                overflow: hidden;
                transition: box-shadow 0.3s;
                height: 100%;
            }
            
            .vehicle-card:hover {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
            
            .vehicle-img {
                height: 200px;
                object-fit: cover;
                width: 100%;
            }
            
            .vehicle-price {
                color: var(--accent-color);
                font-weight: 700;
                font-size: 1.2rem;
            }
            
            .vehicle-features {
                list-style: none;
                padding-left: 0;
            }
            
            .vehicle-features li {
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
                    <p>Лизинг автомобилей на выгодных условиях для физических и юридических лиц</p>
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
                        <li><a href="#" class="text-white text-decoration-none">Лизинг для физлиц</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Лизинг для юрлиц</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Автопарк под ключ</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Страхование</a></li>
                        <li><a href="#" class="text-white text-decoration-none">Сервисное обслуживание</a></li>
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
    
    echo '<section class="hero-section">
        <div class="container">
            <h1>Лизинг автомобилей для всех</h1>
            <p class="lead mb-4">Получите автомобиль вашей мечты на выгодных условиях</p>
            <a href="index.php?page=register" class="btn btn-accent btn-lg me-2">Начать сейчас</a>
            <a href="#vehicles" class="btn btn-outline-light btn-lg">Смотреть автомобили</a>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <h2 class="text-center mb-5">Наши преимущества</h2>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card shadow-sm rounded">
                        <div class="feature-icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h3>Выгодные условия</h3>
                        <p>Минимальный первоначальный взнос и гибкие условия финансирования</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card shadow-sm rounded">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Широкий выбор</h3>
                        <p>Более 500 моделей автомобилей различных марок и комплектаций</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card shadow-sm rounded">
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

    // Популярные автомобили
    $popularVehicles = $vehicles->getAllVehicles(6, 0, ['status' => 'available']);
    
    echo '<section class="vehicles-section" id="vehicles">
        <div class="container">
            <h2 class="text-center mb-5">Популярные автомобили</h2>
            <div class="row g-4">';
            
    if ($popularVehicles) {
        foreach ($popularVehicles as $vehicle) {
            $features = explode(',', $vehicle['features']);
            
            echo '<div class="col-lg-4 col-md-6">
                <div class="vehicle-card">
                    <img src="' . htmlspecialchars($vehicle['image_url']) . '" alt="' . htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) . '" class="vehicle-img">
                    <div class="card-body">
                        <h4>' . htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) . '</h4>
                        <p class="vehicle-price">' . number_format($vehicle['monthly_payment'], 0, ',', ' ') . ' ₽/мес</p>
                        <ul class="vehicle-features">';
                        
            // Выводим до 4 характеристик
            $featureCount = 0;
            foreach ($features as $feature) {
                if ($featureCount < 4) {
                    echo '<li><i class="fas fa-check-circle text-success me-2"></i>' . htmlspecialchars($feature) . '</li>';
                    $featureCount++;
                }
            }
                        
            echo '</ul>
                        <a href="index.php?page=vehicle&id=' . $vehicle['id'] . '" class="btn btn-primary mt-3">Подробнее</a>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo '<div class="col-12">
            <div class="alert alert-info">Автомобили не найдены</div>
        </div>';
    }
            
    echo '</div>
            <div class="text-center mt-5">
                <a href="index.php?page=marketplace" class="btn btn-outline-primary btn-lg">Смотреть все автомобили</a>
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
                    <p class="lead">Мы помогаем клиентам получить автомобиль мечты на выгодных условиях с 2010 года.</p>
                    <p>Компания 2Leasing специализируется на предоставлении услуг лизинга автомобилей для физических и юридических лиц. Мы сотрудничаем со всеми крупнейшими автопроизводителями и предлагаем широкий выбор автомобилей различных марок и моделей.</p>
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
                            <h3 class="fw-bold text-primary">500+</h3>
                            <p>автомобилей</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>';

    // Призыв к действию
    echo '<section class="call-to-action">
        <div class="container">
            <h2 class="mb-4">Готовы получить автомобиль мечты?</h2>
            <p class="lead mb-4">Заполните заявку и наши менеджеры свяжутся с вами в ближайшее время</p>
            <a href="index.php?page=register" class="btn btn-accent btn-lg">Подать заявку</a>
        </div>
    </section>';

    // Контакты
    echo '<section class="py-5" id="contact">
        <div class="container">
            <h2 class="text-center mb-5">Свяжитесь с нами</h2>
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Ваше имя</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение</label>
                            <textarea class="form-control" id="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <h4><i class="fas fa-map-marker-alt me-2 text-primary"></i> Адрес</h4>
                        <p>г. Москва, ул. Тверская, д. 10, офис 305</p>
                    </div>
                    <div class="mb-4">
                        <h4><i class="fas fa-phone me-2 text-primary"></i> Телефон</h4>
                        <p>+7 (495) 123-45-67</p>
                    </div>
                    <div class="mb-4">
                        <h4><i class="fas fa-envelope me-2 text-primary"></i> Email</h4>
                        <p>info@2leasing.ru</p>
                    </div>
                    <div>
                        <h4><i class="fas fa-clock me-2 text-primary"></i> Режим работы</h4>
                        <p>Пн-Пт: 9:00 - 20:00<br>Сб: 10:00 - 18:00<br>Вс: выходной</p>
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
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="text-center mb-0">Вход в личный кабинет</h3>
                        </div>
                        <div class="card-body p-4">';
                        
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    }
                        
    echo '<form method="post">
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label for="login-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="login-email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="login-password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="login-password" name="password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
                                    <label class="form-check-label" for="remember-me">Запомнить меня</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Войти</button>
                                </div>
                            </form>
                            <div class="text-center mt-3">
                                <a href="#" class="text-decoration-none">Забыли пароль?</a>
                            </div>
                        </div>
                        <div class="card-footer text-center py-3 bg-light">
                            <p class="mb-0">Нет аккаунта? <a href="index.php?page=register" class="text-decoration-none">Зарегистрироваться</a></p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info" role="alert">
                            <h5>Демо-доступ</h5>
                            <p class="mb-2">Для тестирования различных уровней доступа:</p>
                            <ul class="mb-0">
                                <li>Клиент: client@2leasing.ru / password</li>
                                <li>Менеджер: manager1@2leasing.ru / password</li>
                                <li>Администратор: admin@2leasing.ru / password</li>
                            </ul>
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
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="text-center mb-0">Регистрация</h3>
                        </div>
                        <div class="card-body p-4">';
                        
    if (isset($error)) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    }
                        
    echo '<form class="row g-3" method="post">
                                <input type="hidden" name="action" value="register">
                                <div class="col-md-6">
                                    <label for="register-fname" class="form-label">Имя</label>
                                    <input type="text" class="form-control" id="register-fname" name="first_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-lname" class="form-label">Фамилия</label>
                                    <input type="text" class="form-control" id="register-lname" name="last_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="register-email" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-phone" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control" id="register-phone" name="phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="register-password" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-confirm-password" class="form-label">Подтверждение пароля</label>
                                    <input type="password" class="form-control" id="register-confirm-password" name="password_confirm" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="agree-terms" name="terms" required>
                                        <label class="form-check-label" for="agree-terms">
                                            Я согласен с <a href="#" class="text-decoration-none">условиями использования</a> и <a href="#" class="text-decoration-none">политикой конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3 bg-light">
                            <p class="mb-0">Уже есть аккаунт? <a href="index.php?page=login" class="text-decoration-none">Войти</a></p>
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
    default:
        includeHomePage();
        break;
}

outputFooter();
?>