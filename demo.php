<?php
// Демонстрационная версия сайта по аренде автомобилей
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2Leasing - Аренда автомобилей</title>
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
            font-family: 'Roboto', sans-serif;
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
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1604054094957-c5369c2e5f44?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
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
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">2Leasing</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#vehicles">Автомобили</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">О нас</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Контакты</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="?page=login" class="btn btn-outline-light me-2">Вход</a>
                    <a href="?page=register" class="btn btn-light">Регистрация</a>
                </div>
            </div>
        </div>
    </nav>

<?php
// Маршрутизация страниц
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'login':
        include_login_page();
        break;
    case 'register':
        include_register_page();
        break;
    case 'dashboard-client':
        include_dashboard_client();
        break;
    case 'dashboard-manager':
        include_dashboard_manager();
        break;
    case 'dashboard-admin':
        include_dashboard_admin();
        break;
    case 'marketplace':
        include_marketplace();
        break;
    default:
        include_home_page();
        break;
}

// Функции для отображения содержимого страниц
function include_home_page() {
?>
    <!-- Главный блок -->
    <section class="hero-section">
        <div class="container">
            <h1>Лизинг автомобилей для всех</h1>
            <p class="lead mb-4">Получите автомобиль вашей мечты на выгодных условиях</p>
            <a href="?page=register" class="btn btn-accent btn-lg me-2">Начать сейчас</a>
            <a href="#vehicles" class="btn btn-outline-light btn-lg">Смотреть автомобили</a>
        </div>
    </section>

    <!-- Преимущества -->
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
    </section>

    <!-- Автомобили -->
    <section class="vehicles-section" id="vehicles">
        <div class="container">
            <h2 class="text-center mb-5">Популярные автомобили</h2>
            <div class="row g-4">
                <?php
                // Демонстрационные данные автомобилей
                $vehicles = [
                    [
                        'title' => 'BMW X5',
                        'image' => 'https://images.unsplash.com/photo-1556189250-72ba954cfc2b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '85 000 ₽/мес',
                        'features' => ['3.0L 249 л.с.', 'Полный привод', 'Кожаный салон', 'Панорамная крыша']
                    ],
                    [
                        'title' => 'Mercedes-Benz E-Class',
                        'image' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '65 000 ₽/мес',
                        'features' => ['2.0L 197 л.с.', 'Задний привод', 'Мультимедиа', 'Климат-контроль']
                    ],
                    [
                        'title' => 'Audi Q7',
                        'image' => 'https://images.unsplash.com/photo-1608329985118-887191f6dd8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '79 000 ₽/мес',
                        'features' => ['3.0L 249 л.с.', 'Полный привод', 'Кожаный салон', '7 мест']
                    ],
                    [
                        'title' => 'Toyota Camry',
                        'image' => 'https://images.unsplash.com/photo-1621007690695-33e84c0ea918?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '45 000 ₽/мес',
                        'features' => ['2.5L 181 л.с.', 'Передний привод', 'Кожаный салон', 'Климат-контроль']
                    ],
                    [
                        'title' => 'Volkswagen Tiguan',
                        'image' => 'https://images.unsplash.com/photo-1606664914738-f57686f9c8b1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '52 000 ₽/мес',
                        'features' => ['2.0L 180 л.с.', 'Полный привод', 'Панорамная крыша', 'Адаптивный круиз-контроль']
                    ],
                    [
                        'title' => 'KIA Sportage',
                        'image' => 'https://images.unsplash.com/photo-1641844180429-baab126f41b0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                        'price' => '38 000 ₽/мес',
                        'features' => ['2.0L 150 л.с.', 'Полный привод', 'Панорамная крыша', 'Камера заднего вида']
                    ],
                ];

                foreach ($vehicles as $vehicle) {
                    echo '<div class="col-lg-4 col-md-6">';
                    echo '<div class="vehicle-card">';
                    echo '<img src="' . $vehicle['image'] . '" alt="' . $vehicle['title'] . '" class="vehicle-img">';
                    echo '<div class="card-body">';
                    echo '<h4>' . $vehicle['title'] . '</h4>';
                    echo '<p class="vehicle-price">' . $vehicle['price'] . '</p>';
                    echo '<ul class="vehicle-features">';
                    foreach ($vehicle['features'] as $feature) {
                        echo '<li><i class="fas fa-check-circle text-success me-2"></i>' . $feature . '</li>';
                    }
                    echo '</ul>';
                    echo '<a href="?page=register" class="btn btn-primary mt-3">Подать заявку</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="text-center mt-5">
                <a href="?page=marketplace" class="btn btn-outline-primary btn-lg">Смотреть все автомобили</a>
            </div>
        </div>
    </section>

    <!-- О нас -->
    <section class="py-5 bg-light" id="about">
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
    </section>

    <!-- Призыв к действию -->
    <section class="call-to-action">
        <div class="container">
            <h2 class="mb-4">Готовы получить автомобиль мечты?</h2>
            <p class="lead mb-4">Заполните заявку и наши менеджеры свяжутся с вами в ближайшее время</p>
            <a href="?page=register" class="btn btn-accent btn-lg">Подать заявку</a>
        </div>
    </section>

    <!-- Контакты -->
    <section class="py-5" id="contact">
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
    </section>
<?php
}

function include_login_page() {
?>
    <!-- Страница входа -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="text-center mb-0">Вход в личный кабинет</h3>
                        </div>
                        <div class="card-body p-4">
                            <form>
                                <div class="mb-3">
                                    <label for="login-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="login-email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="login-password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="login-password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember-me">
                                    <label class="form-check-label" for="remember-me">Запомнить меня</label>
                                </div>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary" onclick="location.href='?page=dashboard-client'">Войти</button>
                                </div>
                            </form>
                            <div class="text-center mt-3">
                                <a href="#" class="text-decoration-none">Забыли пароль?</a>
                            </div>
                        </div>
                        <div class="card-footer text-center py-3 bg-light">
                            <p class="mb-0">Нет аккаунта? <a href="?page=register" class="text-decoration-none">Зарегистрироваться</a></p>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-info" role="alert">
                            <h5>Демо-доступ</h5>
                            <p class="mb-2">Для тестирования различных уровней доступа:</p>
                            <ul class="mb-0">
                                <li><a href="?page=dashboard-client" class="alert-link">Войти как клиент</a></li>
                                <li><a href="?page=dashboard-manager" class="alert-link">Войти как менеджер</a></li>
                                <li><a href="?page=dashboard-admin" class="alert-link">Войти как администратор</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
}

function include_register_page() {
?>
    <!-- Страница регистрации -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="text-center mb-0">Регистрация</h3>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3">
                                <div class="col-md-6">
                                    <label for="register-fname" class="form-label">Имя</label>
                                    <input type="text" class="form-control" id="register-fname" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-lname" class="form-label">Фамилия</label>
                                    <input type="text" class="form-control" id="register-lname" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="register-email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-phone" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control" id="register-phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="register-password" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="register-confirm-password" class="form-label">Подтверждение пароля</label>
                                    <input type="password" class="form-control" id="register-confirm-password" required>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="agree-terms" required>
                                        <label class="form-check-label" for="agree-terms">
                                            Я согласен с <a href="#" class="text-decoration-none">условиями использования</a> и <a href="#" class="text-decoration-none">политикой конфиденциальности</a>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-grid">
                                        <button type="button" class="btn btn-primary" onclick="location.href='?page=dashboard-client'">Зарегистрироваться</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center py-3 bg-light">
                            <p class="mb-0">Уже есть аккаунт? <a href="?page=login" class="text-decoration-none">Войти</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php
}

function include_dashboard_client() {
?>
    <!-- Панель управления клиента -->
    <div class="container user-dashboard">
        <div class="dashboard-nav">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Панель управления</h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="active">Заявки</a>
                    <a href="#">Профиль</a>
                    <a href="#">Документы</a>
                    <a href="?page=home">Выход</a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>Иван Петров</h5>
                    <p class="text-muted">Клиент</p>
                    <hr>
                    <p><i class="fas fa-envelope me-2 text-muted"></i> ivan@example.com</p>
                    <p><i class="fas fa-phone me-2 text-muted"></i> +7 (123) 456-7890</p>
                    <p><i class="fas fa-calendar me-2 text-muted"></i> С нами с 01.01.2024</p>
                    <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary">Редактировать профиль</a>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <h5>Уведомления</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Заявка одобрена
                            <span class="badge bg-success rounded-pill">Новое</span>
                        </li>
                        <li class="list-group-item">Документы готовы к подписанию</li>
                        <li class="list-group-item">График платежей обновлен</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="dashboard-card">
                    <h5>Мои заявки</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Автомобиль</th>
                                    <th>Дата заявки</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>001</td>
                                    <td>BMW X5</td>
                                    <td>01.04.2025</td>
                                    <td><span class="badge bg-success">Одобрено</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">Подробнее</a></td>
                                </tr>
                                <tr>
                                    <td>002</td>
                                    <td>Mercedes-Benz E-Class</td>
                                    <td>05.04.2025</td>
                                    <td><span class="badge bg-warning text-dark">На рассмотрении</span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">Подробнее</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <h5>Статус текущей заявки</h5>
                    <div class="progress mb-4" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">60%</div>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Заявка подана</h6>
                                <small class="text-muted">01.04.2025</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Первичное одобрение</h6>
                                <small class="text-muted">03.04.2025</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Проверка документов</h6>
                                <small class="text-muted">05.04.2025</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-circle text-primary me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Подготовка договора</h6>
                                <small class="text-muted">В процессе</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Подписание документов</h6>
                                <small class="text-muted">Ожидается</small>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-0">Передача автомобиля</h6>
                                <small class="text-muted">Ожидается</small>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="dashboard-card">
                    <h5>Подать новую заявку</h5>
                    <p>Выберите автомобиль из нашего каталога и подайте новую заявку на лизинг.</p>
                    <div class="d-grid">
                        <a href="?page=marketplace" class="btn btn-primary">Перейти в каталог</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

function include_dashboard_manager() {
?>
    <!-- Панель управления менеджера -->
    <div class="container user-dashboard">
        <div class="dashboard-nav">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Панель менеджера</h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="active">Заявки</a>
                    <a href="#">Клиенты</a>
                    <a href="#">Отчеты</a>
                    <a href="?page=home">Выход</a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>Алексей Смирнов</h5>
                    <p class="text-muted">Менеджер по продажам</p>
                    <hr>
                    <p><i class="fas fa-envelope me-2 text-muted"></i> alexey@example.com</p>
                    <p><i class="fas fa-phone me-2 text-muted"></i> +7 (987) 654-3210</p>
                    <p><i class="fas fa-briefcase me-2 text-muted"></i> ID: M-12345</p>
                </div>
                
                <div class="dashboard-card">
                    <h5>Показатели</h5>
                    <div class="mb-3">
                        <label class="form-label">Выполнение плана</label>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">85%</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Закрытые сделки</label>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">70%</div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Конверсия</label>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <h5>Уведомления</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Новая заявка
                            <span class="badge bg-danger rounded-pill">3</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Требуется обратная связь
                            <span class="badge bg-warning text-dark rounded-pill">5</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Документы ожидают подтверждения
                            <span class="badge bg-primary rounded-pill">2</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Активные заявки</h5>
                        <div>
                            <select class="form-select form-select-sm">
                                <option>Все заявки</option>
                                <option>Новые</option>
                                <option>В обработке</option>
                                <option>Одобренные</option>
                                <option>Отклоненные</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Клиент</th>
                                    <th>Автомобиль</th>
                                    <th>Дата заявки</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-2451</td>
                                    <td>Иван Петров</td>
                                    <td>BMW X5</td>
                                    <td>01.04.2025</td>
                                    <td><span class="badge bg-success">Одобрено</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Просмотр</a></li>
                                                <li><a class="dropdown-item" href="#">Редактировать</a></li>
                                                <li><a class="dropdown-item" href="#">Связаться</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2452</td>
                                    <td>Мария Иванова</td>
                                    <td>Audi Q7</td>
                                    <td>02.04.2025</td>
                                    <td><span class="badge bg-warning text-dark">На рассмотрении</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Просмотр</a></li>
                                                <li><a class="dropdown-item" href="#">Редактировать</a></li>
                                                <li><a class="dropdown-item" href="#">Связаться</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2453</td>
                                    <td>Алексей Кузнецов</td>
                                    <td>Toyota Camry</td>
                                    <td>02.04.2025</td>
                                    <td><span class="badge bg-danger">Отклонено</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Просмотр</a></li>
                                                <li><a class="dropdown-item" href="#">Редактировать</a></li>
                                                <li><a class="dropdown-item" href="#">Связаться</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2454</td>
                                    <td>Елена Сидорова</td>
                                    <td>Volkswagen Tiguan</td>
                                    <td>03.04.2025</td>
                                    <td><span class="badge bg-primary">Новая</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Просмотр</a></li>
                                                <li><a class="dropdown-item" href="#">Редактировать</a></li>
                                                <li><a class="dropdown-item" href="#">Связаться</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2455</td>
                                    <td>Дмитрий Николаев</td>
                                    <td>KIA Sportage</td>
                                    <td>04.04.2025</td>
                                    <td><span class="badge bg-info">Требует внимания</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Просмотр</a></li>
                                                <li><a class="dropdown-item" href="#">Редактировать</a></li>
                                                <li><a class="dropdown-item" href="#">Связаться</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled"><a class="page-link" href="#">Предыдущая</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">Следующая</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="dashboard-card">
                    <h5>Недавние действия</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Заявка A-2451 обновлена</strong>
                                    <p class="mb-0 text-muted">Статус изменен на "Одобрено"</p>
                                </div>
                                <small class="text-muted">Сегодня, 10:23</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Звонок клиенту</strong>
                                    <p class="mb-0 text-muted">Елена Сидорова (A-2454)</p>
                                </div>
                                <small class="text-muted">Сегодня, 09:15</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Заявка A-2453 отклонена</strong>
                                    <p class="mb-0 text-muted">Причина: недостаточный доход</p>
                                </div>
                                <small class="text-muted">Вчера, 16:48</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Новая заявка A-2455</strong>
                                    <p class="mb-0 text-muted">Дмитрий Николаев, KIA Sportage</p>
                                </div>
                                <small class="text-muted">Вчера, 14:30</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php
}

function include_dashboard_admin() {
?>
    <!-- Панель управления администратора -->
    <div class="container user-dashboard">
        <div class="dashboard-nav">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Панель администратора</h4>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="active">Обзор</a>
                    <a href="#">Менеджеры</a>
                    <a href="#">Клиенты</a>
                    <a href="#">Настройки</a>
                    <a href="?page=home">Выход</a>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="py-3">
                        <i class="fas fa-users text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="mb-0">124</h2>
                    <p class="text-muted">Активных клиентов</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="py-3">
                        <i class="fas fa-file-alt text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="mb-0">78</h2>
                    <p class="text-muted">Активных заявок</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="py-3">
                        <i class="fas fa-car text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="mb-0">35</h2>
                    <p class="text-muted">Новых автомобилей</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <div class="py-3">
                        <i class="fas fa-user-tie text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="mb-0">12</h2>
                    <p class="text-muted">Менеджеров</p>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Распределение заявок</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary">Сегодня</button>
                            <button type="button" class="btn btn-sm btn-primary">Неделя</button>
                            <button type="button" class="btn btn-sm btn-outline-primary">Месяц</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Менеджер</th>
                                    <th>Всего заявок</th>
                                    <th>Новые</th>
                                    <th>В обработке</th>
                                    <th>Одобрено</th>
                                    <th>Отклонено</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Алексей Смирнов</td>
                                    <td>24</td>
                                    <td>5</td>
                                    <td>10</td>
                                    <td>7</td>
                                    <td>2</td>
                                    <td><button class="btn btn-sm btn-primary">Подробнее</button></td>
                                </tr>
                                <tr>
                                    <td>Елена Михайлова</td>
                                    <td>18</td>
                                    <td>3</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2</td>
                                    <td><button class="btn btn-sm btn-primary">Подробнее</button></td>
                                </tr>
                                <tr>
                                    <td>Дмитрий Лебедев</td>
                                    <td>21</td>
                                    <td>4</td>
                                    <td>9</td>
                                    <td>6</td>
                                    <td>2</td>
                                    <td><button class="btn btn-sm btn-primary">Подробнее</button></td>
                                </tr>
                                <tr>
                                    <td>Ольга Новикова</td>
                                    <td>15</td>
                                    <td>2</td>
                                    <td>6</td>
                                    <td>5</td>
                                    <td>2</td>
                                    <td><button class="btn btn-sm btn-primary">Подробнее</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-card mt-4">
                    <h5>Нераспределенные заявки</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Клиент</th>
                                    <th>Автомобиль</th>
                                    <th>Дата заявки</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-2460</td>
                                    <td>Сергей Иванов</td>
                                    <td>Audi A6</td>
                                    <td>15.04.2025</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                Назначить менеджера
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Алексей Смирнов</a></li>
                                                <li><a class="dropdown-item" href="#">Елена Михайлова</a></li>
                                                <li><a class="dropdown-item" href="#">Дмитрий Лебедев</a></li>
                                                <li><a class="dropdown-item" href="#">Ольга Новикова</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2461</td>
                                    <td>Наталья Соколова</td>
                                    <td>BMW 5 Series</td>
                                    <td>15.04.2025</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                Назначить менеджера
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Алексей Смирнов</a></li>
                                                <li><a class="dropdown-item" href="#">Елена Михайлова</a></li>
                                                <li><a class="dropdown-item" href="#">Дмитрий Лебедев</a></li>
                                                <li><a class="dropdown-item" href="#">Ольга Новикова</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>A-2462</td>
                                    <td>Андрей Петров</td>
                                    <td>Mercedes-Benz GLC</td>
                                    <td>16.04.2025</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                Назначить менеджера
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Алексей Смирнов</a></li>
                                                <li><a class="dropdown-item" href="#">Елена Михайлова</a></li>
                                                <li><a class="dropdown-item" href="#">Дмитрий Лебедев</a></li>
                                                <li><a class="dropdown-item" href="#">Ольга Новикова</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h5>Статистика по автомобилям</h5>
                    <div class="mb-3">
                        <label class="form-label">Самые запрашиваемые марки</label>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">BMW (85%)</div>
                        </div>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">Mercedes-Benz (75%)</div>
                        </div>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">Audi (65%)</div>
                        </div>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 55%;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100">Toyota (55%)</div>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 45%;" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">Volkswagen (45%)</div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card mt-4">
                    <h5>Недавние действия</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Назначен менеджер</strong>
                                    <p class="mb-0 text-muted">Алексей Смирнов назначен на заявку A-2458</p>
                                </div>
                                <small class="text-muted">Сегодня, 11:45</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Добавлен новый автомобиль</strong>
                                    <p class="mb-0 text-muted">BMW X7 добавлен в каталог</p>
                                </div>
                                <small class="text-muted">Сегодня, 10:30</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Назначен менеджер</strong>
                                    <p class="mb-0 text-muted">Елена Михайлова назначена на заявку A-2459</p>
                                </div>
                                <small class="text-muted">Сегодня, 09:15</small>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Новый менеджер</strong>
                                    <p class="mb-0 text-muted">Дмитрий Лебедев добавлен в систему</p>
                                </div>
                                <small class="text-muted">Вчера, 14:20</small>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="dashboard-card mt-4">
                    <h5>Быстрые действия</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i> Добавить менеджера
                        </button>
                        <button class="btn btn-success">
                            <i class="fas fa-car me-2"></i> Добавить автомобиль
                        </button>
                        <button class="btn btn-info text-white">
                            <i class="fas fa-file-export me-2"></i> Экспорт отчета
                        </button>
                        <button class="btn btn-warning text-dark">
                            <i class="fas fa-cog me-2"></i> Настройки системы
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

function include_marketplace() {
?>
    <!-- Маркетплейс автомобилей -->
    <div class="container py-5">
        <h1 class="mb-4">Каталог автомобилей</h1>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Марка</label>
                                <select class="form-select">
                                    <option selected>Все марки</option>
                                    <option>Audi</option>
                                    <option>BMW</option>
                                    <option>Mercedes-Benz</option>
                                    <option>Toyota</option>
                                    <option>Volkswagen</option>
                                    <option>KIA</option>
                                    <option>Hyundai</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Модель</label>
                                <select class="form-select">
                                    <option selected>Все модели</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Цена от</label>
                                <input type="number" class="form-control" placeholder="₽">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Цена до</label>
                                <input type="number" class="form-control" placeholder="₽">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Найти</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            // Демонстрационные данные автомобилей для маркетплейса
            $marketplace_vehicles = [
                [
                    'title' => 'BMW X5 xDrive40i',
                    'image' => 'https://images.unsplash.com/photo-1556189250-72ba954cfc2b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '85 000 ₽/мес',
                    'total' => '7 650 000 ₽',
                    'description' => 'Роскошный внедорожник с мощным двигателем, полным приводом и премиальным салоном.',
                    'features' => ['3.0L 340 л.с.', 'Полный привод', 'Кожаный салон', '8-ступенчатый автомат', 'Панорамная крыша', 'Адаптивный круиз-контроль']
                ],
                [
                    'title' => 'Mercedes-Benz E-Class E350',
                    'image' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '65 000 ₽/мес',
                    'total' => '5 850 000 ₽',
                    'description' => 'Элегантный седан бизнес-класса с передовыми технологиями и комфортным салоном.',
                    'features' => ['2.0L 258 л.с.', 'Задний привод', 'Кожаный салон', '9-ступенчатый автомат', 'Навигация', 'Подогрев сидений']
                ],
                [
                    'title' => 'Audi Q7 45 TDI quattro',
                    'image' => 'https://images.unsplash.com/photo-1608329985118-887191f6dd8b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '79 000 ₽/мес',
                    'total' => '7 100 000 ₽',
                    'description' => 'Семиместный премиальный внедорожник с просторным салоном и передовыми технологиями.',
                    'features' => ['3.0L 249 л.с.', 'Полный привод', 'Кожаный салон', '8-ступенчатый автомат', '7 мест', 'Виртуальная приборная панель']
                ],
                [
                    'title' => 'Toyota Camry 3.5 V6',
                    'image' => 'https://images.unsplash.com/photo-1621007690695-33e84c0ea918?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '45 000 ₽/мес',
                    'total' => '4 050 000 ₽',
                    'description' => 'Надежный и комфортный седан бизнес-класса с просторным салоном и мощным двигателем.',
                    'features' => ['3.5L 249 л.с.', 'Передний привод', 'Кожаный салон', '8-ступенчатый автомат', 'JBL аудиосистема', 'Круиз-контроль']
                ],
                [
                    'title' => 'Volkswagen Tiguan 2.0 TSI',
                    'image' => 'https://images.unsplash.com/photo-1606664914738-f57686f9c8b1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '52 000 ₽/мес',
                    'total' => '4 680 000 ₽',
                    'description' => 'Компактный кроссовер с высоким уровнем комфорта, отличной управляемостью и современными системами безопасности.',
                    'features' => ['2.0L 180 л.с.', 'Полный привод', '7-ступенчатый DSG', 'Панорамная крыша', 'Адаптивный круиз-контроль', 'Система помощи при парковке']
                ],
                [
                    'title' => 'KIA Sportage 2.0 CRDi',
                    'image' => 'https://images.unsplash.com/photo-1641844180429-baab126f41b0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '38 000 ₽/мес',
                    'total' => '3 420 000 ₽',
                    'description' => 'Стильный и практичный кроссовер с отличным соотношением цены и качества, экономичным дизельным двигателем.',
                    'features' => ['2.0L 185 л.с.', 'Полный привод', '8-ступенчатый автомат', 'Система предотвращения столкновений', 'Панорамная крыша', 'Камера заднего вида']
                ],
                [
                    'title' => 'BMW 5 Series 520d',
                    'image' => 'https://images.unsplash.com/photo-1523983302122-73e869e1f850?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '65 000 ₽/мес',
                    'total' => '5 850 000 ₽',
                    'description' => 'Представительский седан с отличной динамикой, экономичным дизельным двигателем и роскошным салоном.',
                    'features' => ['2.0L 190 л.с. дизель', 'Задний привод', 'Кожаный салон', '8-ступенчатый автомат', 'Навигация', 'Система помощи при парковке']
                ],
                [
                    'title' => 'Mercedes-Benz GLC 300 4MATIC',
                    'image' => 'https://images.unsplash.com/photo-1549062573-edc78a53ffa6?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '68 000 ₽/мес',
                    'total' => '6 120 000 ₽',
                    'description' => 'Премиальный компактный кроссовер с динамичным дизайном, отличной управляемостью и роскошным салоном.',
                    'features' => ['2.0L 249 л.с.', 'Полный привод', 'Кожаный салон', '9-ступенчатый автомат', 'Система MBUX', 'Адаптивная подвеска']
                ],
                [
                    'title' => 'Audi A6 40 TFSI',
                    'image' => 'https://images.unsplash.com/photo-1606152536277-5aa1fd33e150?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80',
                    'price' => '63 000 ₽/мес',
                    'total' => '5 670 000 ₽',
                    'description' => 'Элегантный бизнес-седан с передовым дизайном, высокотехнологичным интерьером и экономичным двигателем.',
                    'features' => ['2.0L 190 л.с.', 'Передний привод', 'Кожаный салон', '7-ступенчатый S tronic', 'Virtual Cockpit', 'Светодиодная оптика']
                ],
            ];

            foreach ($marketplace_vehicles as $vehicle) {
                echo '<div class="col-lg-4 col-md-6">';
                echo '<div class="card h-100 vehicle-card">';
                echo '<img src="' . $vehicle['image'] . '" alt="' . $vehicle['title'] . '" class="card-img-top vehicle-img">';
                echo '<div class="card-body">';
                echo '<h4>' . $vehicle['title'] . '</h4>';
                echo '<div class="d-flex justify-content-between align-items-center mb-3">';
                echo '<p class="vehicle-price mb-0">' . $vehicle['price'] . '</p>';
                echo '<span class="badge bg-secondary">' . $vehicle['total'] . '</span>';
                echo '</div>';
                echo '<p class="text-muted">' . $vehicle['description'] . '</p>';
                echo '<h6 class="mt-3">Характеристики:</h6>';
                echo '<ul class="vehicle-features">';
                foreach ($vehicle['features'] as $feature) {
                    echo '<li><i class="fas fa-check-circle text-success me-2"></i>' . $feature . '</li>';
                }
                echo '</ul>';
                echo '</div>';
                echo '<div class="card-footer bg-white border-top-0">';
                echo '<div class="d-grid">';
                echo '<a href="?page=register" class="btn btn-primary">Подать заявку</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <nav class="mt-5">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#">Предыдущая</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Следующая</a></li>
            </ul>
        </nav>
    </div>
<?php
}
?>

    <!-- Футер -->
    <footer class="footer">
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
                    <p class="mb-0">&copy; 2025 2Leasing. Все права защищены.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Разработано с <i class="fas fa-heart text-danger"></i> компанией 2Leasing</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>