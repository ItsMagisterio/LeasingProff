<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Проверяем, что пользователь админ
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit();
}

// Обработка POST запроса для добавления/редактирования компании
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add_company' || $action === 'edit_company') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $logo_url = isset($_POST['logo_url']) ? trim($_POST['logo_url']) : '';
        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
        $min_price = isset($_POST['min_price']) ? (float)$_POST['min_price'] : 0;
        $max_price = isset($_POST['max_price']) ? (float)$_POST['max_price'] : 0;
        $min_down_payment = isset($_POST['min_down_payment']) ? (int)$_POST['min_down_payment'] : 0;
        $max_down_payment = isset($_POST['max_down_payment']) ? (int)$_POST['max_down_payment'] : 0;
        $min_term = isset($_POST['min_term']) ? (int)$_POST['min_term'] : 0;
        $max_term = isset($_POST['max_term']) ? (int)$_POST['max_term'] : 0;
        $rating = isset($_POST['rating']) ? (float)$_POST['rating'] : 0;
        $conditions = isset($_POST['conditions']) ? $_POST['conditions'] : [];
        
        // Валидация
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Название компании обязательно для заполнения';
        }
        
        if (empty($type)) {
            $errors[] = 'Тип лизинга обязательно для заполнения';
        }
        
        if (empty($logo_url)) {
            $errors[] = 'URL логотипа обязательно для заполнения';
        }
        
        if ($min_price <= 0) {
            $errors[] = 'Минимальная стоимость должна быть больше 0';
        }
        
        if ($max_price <= $min_price) {
            $errors[] = 'Максимальная стоимость должна быть больше минимальной';
        }
        
        if ($min_down_payment < 0 || $min_down_payment > 100) {
            $errors[] = 'Минимальный первоначальный взнос должен быть от 0 до 100';
        }
        
        if ($max_down_payment <= $min_down_payment || $max_down_payment > 100) {
            $errors[] = 'Максимальный первоначальный взнос должен быть больше минимального и не более 100';
        }
        
        if ($min_term <= 0) {
            $errors[] = 'Минимальный срок должен быть больше 0';
        }
        
        if ($max_term <= $min_term) {
            $errors[] = 'Максимальный срок должен быть больше минимального';
        }
        
        if ($rating < 0 || $rating > 5) {
            $errors[] = 'Рейтинг должен быть от 0 до 5';
        }
        
        if (count($conditions) < 1) {
            $errors[] = 'Добавьте хотя бы одно условие лизинга';
        }
        
        // Если нет ошибок, добавляем/обновляем компанию
        if (empty($errors)) {
            $company = [
                'name' => $name,
                'logo_url' => $logo_url,
                'type' => $type,
                'min_price' => $min_price,
                'max_price' => $max_price,
                'min_down_payment' => $min_down_payment,
                'max_down_payment' => $max_down_payment,
                'min_term' => $min_term,
                'max_term' => $max_term,
                'rating' => $rating,
                'conditions' => $conditions
            ];
            
            // В реальном приложении здесь будет код для добавления/обновления в БД
            // Для демонстрации выведем сообщение об успехе
            $success = $action === 'add_company' ? 'Лизинговая компания успешно добавлена' : 'Лизинговая компания успешно обновлена';
        } else {
            $error = implode('<br>', $errors);
        }
    } elseif ($action === 'delete_company') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($id > 0) {
            // В реальном приложении здесь будет код для удаления из БД
            $success = 'Лизинговая компания успешно удалена';
        } else {
            $error = 'Неверный ID компании';
        }
    }
}

// Получаем список лизинговых компаний
$leasingCompanies = [
    [
        'id' => 1,
        'name' => 'Сбербанк Лизинг',
        'logo_url' => '/images/logos/sberbank-leasing.svg',
        'type' => 'vehicle',
        'min_price' => 1000000,
        'max_price' => 15000000,
        'min_down_payment' => 10,
        'max_down_payment' => 49,
        'min_term' => 12,
        'max_term' => 60,
        'rating' => 4.8,
        'conditions' => [
            'Быстрое одобрение заявки',
            'Выгодные процентные ставки',
            'Полное КАСКО и страхование',
            'Досрочное погашение без комиссии'
        ]
    ],
    [
        'id' => 2,
        'name' => 'ВТБ Лизинг',
        'logo_url' => '/images/logos/vtb-leasing.svg',
        'type' => 'vehicle',
        'min_price' => 1500000,
        'max_price' => 20000000,
        'min_down_payment' => 15,
        'max_down_payment' => 60,
        'min_term' => 24,
        'max_term' => 84,
        'rating' => 4.6,
        'conditions' => [
            'Специальные программы для бизнеса',
            'Персональный менеджер',
            'Возможность выкупа по остаточной стоимости',
            'Отсутствие скрытых комиссий'
        ]
    ],
    [
        'id' => 3,
        'name' => 'Альфа-Лизинг',
        'logo_url' => '/images/logos/alfa-leasing.svg',
        'type' => 'real_estate',
        'min_price' => 5000000,
        'max_price' => 50000000,
        'min_down_payment' => 20,
        'max_down_payment' => 70,
        'min_term' => 36,
        'max_term' => 120,
        'rating' => 4.5,
        'conditions' => [
            'Индивидуальный график платежей',
            'Ускоренная амортизация',
            'Налоговые преимущества',
            'Обратный лизинг'
        ]
    ]
];

?>

<!-- Страница управления лизинговыми компаниями -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Управление лизинговыми компаниями</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-admin">Обзор</a>
                <a href="index.php?page=user-rights">Права пользователей</a>
                <a href="index.php?page=leasing-companies" class="active">Лизинговые компании</a>
                <a href="index.php?page=managers">Менеджеры</a>
                <a href="index.php?page=dashboard-clients">Клиенты</a>
                <a href="index.php?page=settings">Настройки</a>
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-link text-white p-0 ms-3">Выход</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5>Список лизинговых компаний</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                <i class="fas fa-plus me-2"></i>Добавить компанию
            </button>
        </div>
        
        <ul class="nav nav-tabs mb-4" id="companyTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-companies" type="button" role="tab" aria-controls="all-companies" aria-selected="true">Все</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="vehicle-tab" data-bs-toggle="tab" data-bs-target="#vehicle-companies" type="button" role="tab" aria-controls="vehicle-companies" aria-selected="false">Транспорт</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="realestate-tab" data-bs-toggle="tab" data-bs-target="#realestate-companies" type="button" role="tab" aria-controls="realestate-companies" aria-selected="false">Недвижимость</button>
            </li>
        </ul>
        
        <div class="tab-content" id="companyTabContent">
            <div class="tab-pane fade show active" id="all-companies" role="tabpanel" aria-labelledby="all-tab">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логотип</th>
                                <th>Название</th>
                                <th>Тип лизинга</th>
                                <th>Мин. стоимость</th>
                                <th>Мин. взнос</th>
                                <th>Срок (мес.)</th>
                                <th>Рейтинг</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leasingCompanies as $company): ?>
                                <tr>
                                    <td><?= $company['id'] ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" height="30">
                                    </td>
                                    <td><?= htmlspecialchars($company['name']) ?></td>
                                    <td>
                                        <?php if ($company['type'] === 'vehicle'): ?>
                                            <span class="badge bg-secondary">Транспорт</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Недвижимость</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($company['min_price'], 0, '.', ' ') ?> ₽</td>
                                    <td><?= $company['min_down_payment'] ?>% - <?= $company['max_down_payment'] ?>%</td>
                                    <td><?= $company['min_term'] ?> - <?= $company['max_term'] ?></td>
                                    <td>
                                        <div class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $company['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php elseif ($i - 0.5 <= $company['rating']): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <span class="ms-2"><?= number_format($company['rating'], 1) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCompanyModal<?= $company['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal<?= $company['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-pane fade" id="vehicle-companies" role="tabpanel" aria-labelledby="vehicle-tab">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логотип</th>
                                <th>Название</th>
                                <th>Мин. стоимость</th>
                                <th>Мин. взнос</th>
                                <th>Срок (мес.)</th>
                                <th>Рейтинг</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leasingCompanies as $company): ?>
                                <?php if ($company['type'] === 'vehicle'): ?>
                                    <tr>
                                        <td><?= $company['id'] ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" height="30">
                                        </td>
                                        <td><?= htmlspecialchars($company['name']) ?></td>
                                        <td><?= number_format($company['min_price'], 0, '.', ' ') ?> ₽</td>
                                        <td><?= $company['min_down_payment'] ?>% - <?= $company['max_down_payment'] ?>%</td>
                                        <td><?= $company['min_term'] ?> - <?= $company['max_term'] ?></td>
                                        <td>
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $company['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php elseif ($i - 0.5 <= $company['rating']): ?>
                                                        <i class="fas fa-star-half-alt"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span class="ms-2"><?= number_format($company['rating'], 1) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCompanyModal<?= $company['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal<?= $company['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-pane fade" id="realestate-companies" role="tabpanel" aria-labelledby="realestate-tab">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логотип</th>
                                <th>Название</th>
                                <th>Мин. стоимость</th>
                                <th>Мин. взнос</th>
                                <th>Срок (мес.)</th>
                                <th>Рейтинг</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leasingCompanies as $company): ?>
                                <?php if ($company['type'] === 'real_estate'): ?>
                                    <tr>
                                        <td><?= $company['id'] ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" height="30">
                                        </td>
                                        <td><?= htmlspecialchars($company['name']) ?></td>
                                        <td><?= number_format($company['min_price'], 0, '.', ' ') ?> ₽</td>
                                        <td><?= $company['min_down_payment'] ?>% - <?= $company['max_down_payment'] ?>%</td>
                                        <td><?= $company['min_term'] ?> - <?= $company['max_term'] ?></td>
                                        <td>
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $company['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php elseif ($i - 0.5 <= $company['rating']): ?>
                                                        <i class="fas fa-star-half-alt"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span class="ms-2"><?= number_format($company['rating'], 1) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCompanyModal<?= $company['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal<?= $company['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления компании -->
<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить лизинговую компанию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="addCompanyForm">
                    <input type="hidden" name="action" value="add_company">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Название компании*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="logo_url" class="form-label">URL логотипа*</label>
                            <input type="text" class="form-control" id="logo_url" name="logo_url" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Тип лизинга*</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Выберите тип</option>
                                <option value="vehicle">Транспорт</option>
                                <option value="real_estate">Недвижимость</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="rating" class="form-label">Рейтинг (0-5)*</label>
                            <input type="number" class="form-control" id="rating" name="rating" min="0" max="5" step="0.1" required value="4.0">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_price" class="form-label">Мин. стоимость (₽)*</label>
                            <input type="number" class="form-control" id="min_price" name="min_price" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="max_price" class="form-label">Макс. стоимость (₽)*</label>
                            <input type="number" class="form-control" id="max_price" name="max_price" min="0" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_down_payment" class="form-label">Мин. первоначальный взнос (%)*</label>
                            <input type="number" class="form-control" id="min_down_payment" name="min_down_payment" min="0" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label for="max_down_payment" class="form-label">Макс. первоначальный взнос (%)*</label>
                            <input type="number" class="form-control" id="max_down_payment" name="max_down_payment" min="0" max="100" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="min_term" class="form-label">Мин. срок (месяцев)*</label>
                            <input type="number" class="form-control" id="min_term" name="min_term" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="max_term" class="form-label">Макс. срок (месяцев)*</label>
                            <input type="number" class="form-control" id="max_term" name="max_term" min="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Условия лизинга*</label>
                        <div id="conditions-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="conditions[]" placeholder="Введите условие" required>
                                <button type="button" class="btn btn-outline-secondary remove-condition" disabled>
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-condition">
                            <i class="fas fa-plus me-2"></i>Добавить условие
                        </button>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна для редактирования компаний -->
<?php foreach ($leasingCompanies as $company): ?>
    <div class="modal fade" id="editCompanyModal<?= $company['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактировать <?= htmlspecialchars($company['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="action" value="edit_company">
                        <input type="hidden" name="id" value="<?= $company['id'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name<?= $company['id'] ?>" class="form-label">Название компании*</label>
                                <input type="text" class="form-control" id="name<?= $company['id'] ?>" name="name" required value="<?= htmlspecialchars($company['name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="logo_url<?= $company['id'] ?>" class="form-label">URL логотипа*</label>
                                <input type="text" class="form-control" id="logo_url<?= $company['id'] ?>" name="logo_url" required value="<?= htmlspecialchars($company['logo_url']) ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type<?= $company['id'] ?>" class="form-label">Тип лизинга*</label>
                                <select class="form-select" id="type<?= $company['id'] ?>" name="type" required>
                                    <option value="">Выберите тип</option>
                                    <option value="vehicle" <?= $company['type'] === 'vehicle' ? 'selected' : '' ?>>Транспорт</option>
                                    <option value="real_estate" <?= $company['type'] === 'real_estate' ? 'selected' : '' ?>>Недвижимость</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="rating<?= $company['id'] ?>" class="form-label">Рейтинг (0-5)*</label>
                                <input type="number" class="form-control" id="rating<?= $company['id'] ?>" name="rating" min="0" max="5" step="0.1" required value="<?= $company['rating'] ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="min_price<?= $company['id'] ?>" class="form-label">Мин. стоимость (₽)*</label>
                                <input type="number" class="form-control" id="min_price<?= $company['id'] ?>" name="min_price" min="0" required value="<?= $company['min_price'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="max_price<?= $company['id'] ?>" class="form-label">Макс. стоимость (₽)*</label>
                                <input type="number" class="form-control" id="max_price<?= $company['id'] ?>" name="max_price" min="0" required value="<?= $company['max_price'] ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="min_down_payment<?= $company['id'] ?>" class="form-label">Мин. первоначальный взнос (%)*</label>
                                <input type="number" class="form-control" id="min_down_payment<?= $company['id'] ?>" name="min_down_payment" min="0" max="100" required value="<?= $company['min_down_payment'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="max_down_payment<?= $company['id'] ?>" class="form-label">Макс. первоначальный взнос (%)*</label>
                                <input type="number" class="form-control" id="max_down_payment<?= $company['id'] ?>" name="max_down_payment" min="0" max="100" required value="<?= $company['max_down_payment'] ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="min_term<?= $company['id'] ?>" class="form-label">Мин. срок (месяцев)*</label>
                                <input type="number" class="form-control" id="min_term<?= $company['id'] ?>" name="min_term" min="1" required value="<?= $company['min_term'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="max_term<?= $company['id'] ?>" class="form-label">Макс. срок (месяцев)*</label>
                                <input type="number" class="form-control" id="max_term<?= $company['id'] ?>" name="max_term" min="1" required value="<?= $company['max_term'] ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Условия лизинга*</label>
                            <div id="conditions-container-<?= $company['id'] ?>">
                                <?php foreach ($company['conditions'] as $index => $condition): ?>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="conditions[]" placeholder="Введите условие" required value="<?= htmlspecialchars($condition) ?>">
                                        <button type="button" class="btn btn-outline-secondary remove-condition" <?= count($company['conditions']) <= 1 ? 'disabled' : '' ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm add-condition" data-container="#conditions-container-<?= $company['id'] ?>">
                                <i class="fas fa-plus me-2"></i>Добавить условие
                            </button>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно для удаления компании -->
    <div class="modal fade" id="deleteCompanyModal<?= $company['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Вы действительно хотите удалить компанию "<?= htmlspecialchars($company['name']) ?>"?</p>
                    <p class="text-danger">Это действие невозможно отменить.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="delete_company">
                        <input type="hidden" name="id" value="<?= $company['id'] ?>">
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
// Скрипт для динамического добавления/удаления условий лизинга
document.addEventListener('DOMContentLoaded', function() {
    // Для формы добавления компании
    document.getElementById('add-condition').addEventListener('click', function() {
        const container = document.getElementById('conditions-container');
        const newCondition = document.createElement('div');
        newCondition.className = 'input-group mb-2';
        newCondition.innerHTML = `
            <input type="text" class="form-control" name="conditions[]" placeholder="Введите условие" required>
            <button type="button" class="btn btn-outline-secondary remove-condition">
                <i class="fas fa-minus"></i>
            </button>
        `;
        container.appendChild(newCondition);
        
        // Если это первое условие, активируем кнопку удаления для первого поля
        if (container.children.length === 2) {
            container.querySelector('.remove-condition').disabled = false;
        }
    });
    
    // Для форм редактирования компаний
    document.querySelectorAll('.add-condition').forEach(button => {
        button.addEventListener('click', function() {
            const containerId = this.getAttribute('data-container');
            const container = document.querySelector(containerId);
            const newCondition = document.createElement('div');
            newCondition.className = 'input-group mb-2';
            newCondition.innerHTML = `
                <input type="text" class="form-control" name="conditions[]" placeholder="Введите условие" required>
                <button type="button" class="btn btn-outline-secondary remove-condition">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(newCondition);
            
            // Если это первое условие, активируем кнопку удаления для первого поля
            if (container.children.length === 2) {
                container.querySelector('.remove-condition').disabled = false;
            }
        });
    });
    
    // Делегирование событий для кнопок удаления условий
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-condition')) {
            const button = e.target.closest('.remove-condition');
            const conditionBlock = button.parentElement;
            const container = conditionBlock.parentElement;
            
            conditionBlock.remove();
            
            // Если осталось только одно условие, деактивируем его кнопку удаления
            if (container.children.length === 1) {
                container.querySelector('.remove-condition').disabled = true;
            }
        }
    });
});
</script>