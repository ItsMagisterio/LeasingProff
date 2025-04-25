<?php
// Страница для оформления заявки на лизинг, принимает параметры из калькулятора
// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Для демонстрационных целей отключаем проверку авторизации
// и создаем тестового пользователя
$currentUser = array(
    'id' => 1,
    'name' => 'Тестовый пользователь',
    'email' => 'test@example.com',
    'phone' => '+7 (999) 123-45-67',
    'role' => 'client'
);

// Определяем тип заявки
$applicationType = isset($_GET['type']) ? $_GET['type'] : '';
$companyName = isset($_GET['company']) ? $_GET['company'] : '';
$monthlyPayment = isset($_GET['monthly']) ? $_GET['monthly'] : '';
$vehicleId = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$realEstateId = isset($_GET['real_estate_id']) ? (int)$_GET['real_estate_id'] : 0;

// Настраиваем параметры и заголовок страницы в зависимости от типа заявки
if ($applicationType === 'vehicle') {
    $pageTitle = 'Заявка на лизинг транспорта';
    $objectTypeName = 'транспорта';
    $isVehicle = true;
    $isRealEstate = false;
    $objectTypeOptions = [
        'car' => 'Легковой автомобиль',
        'truck' => 'Грузовой автомобиль',
        'special' => 'Спецтехника'
    ];
    
    // Если указан ID автомобиля, получаем данные о нем
    $vehicle = $vehicleId > 0 ? $vehicles->getVehicleById($vehicleId) : null;
    
} elseif ($applicationType === 'real_estate') {
    $pageTitle = 'Заявка на лизинг недвижимости';
    $objectTypeName = 'недвижимости';
    $isVehicle = false;
    $isRealEstate = true;
    $objectTypeOptions = [
        'apartment' => 'Квартира',
        'house' => 'Дом',
        'commercial' => 'Коммерческая недвижимость'
    ];
    
    // Если указан ID объекта недвижимости, получаем данные о нем
    $realEstate = $realEstateId > 0 ? $realEstate->getRealEstateById($realEstateId) : null;
    
} else {
    // Неизвестный тип заявки
    header('Location: index.php');
    exit;
}

// Устанавливаем заголовок страницы
outputHeader($pageTitle);

// Обработка формы при отправке
$formSubmitted = false;
$success = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    $formSubmitted = true;
    
    // Получаем общие данные из формы
    $objectType = $_POST['object_type'] ?? '';
    $objectPrice = isset($_POST['object_price']) ? (float)$_POST['object_price'] : 0;
    $initialPayment = isset($_POST['initial_payment']) ? (float)$_POST['initial_payment'] : 0;
    $termMonths = isset($_POST['term_months']) ? (int)$_POST['term_months'] : 0;
    $monthlyPayment = isset($_POST['monthly_payment']) ? (float)$_POST['monthly_payment'] : 0;
    $comments = $_POST['comments'] ?? '';
    $selectedCompany = $_POST['leasing_company'] ?? '';
    
    // Валидация данных
    if (empty($objectType)) {
        $errorMsg = 'Выберите тип предмета лизинга';
    } elseif ($objectPrice <= 0) {
        $errorMsg = 'Укажите корректную стоимость предмета лизинга';
    } elseif ($initialPayment <= 0 || $initialPayment >= $objectPrice) {
        $errorMsg = 'Укажите корректный первоначальный взнос';
    } elseif ($termMonths < 12 || $termMonths > 120) {
        $errorMsg = 'Срок лизинга должен быть от 12 до 120 месяцев';
    } elseif ($monthlyPayment <= 0) {
        $errorMsg = 'Укажите корректный ежемесячный платеж';
    } elseif (empty($selectedCompany)) {
        $errorMsg = 'Выберите лизинговую компанию';
    } else {
        // Все данные валидны, создаем заявку
        
        // Подготавливаем данные заявки в зависимости от типа
        if ($isVehicle) {
            $applicationData = [
                'user_id' => $currentUser['id'],
                'vehicle_id' => $vehicleId > 0 ? $vehicleId : 0,
                'vehicle_type' => $objectType,
                'vehicle_price' => $objectPrice,
                'initial_payment' => $initialPayment,
                'term_months' => $termMonths,
                'monthly_payment' => $monthlyPayment,
                'leasing_company' => $selectedCompany,
                'comments' => $comments,
                'type' => 'vehicle'
            ];
            
            // Сохраняем заявку в системе
            $result = $applications->createApplication($applicationData);
            
        } elseif ($isRealEstate) {
            $applicationData = [
                'user_id' => $currentUser['id'],
                'real_estate_id' => $realEstateId > 0 ? $realEstateId : 0,
                'real_estate_type' => $objectType,
                'real_estate_price' => $objectPrice,
                'initial_payment' => $initialPayment,
                'term_months' => $termMonths,
                'monthly_payment' => $monthlyPayment,
                'leasing_company' => $selectedCompany,
                'comments' => $comments,
                'type' => 'real_estate'
            ];
            
            // Сохраняем заявку в системе
            $result = $applications->createApplication($applicationData);
        }
        
        if (isset($result) && $result['success']) {
            $success = true;
        } else {
            $errorMsg = $result['message'] ?? 'Произошла ошибка при создании заявки';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if ($formSubmitted && $success): ?>
                <div class="card border-0 shadow">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-3">Ваша заявка успешно отправлена!</h2>
                        <p class="lead mb-4">Номер заявки: <strong><?= isset($result['application_id']) ? $result['application_id'] : rand(10000, 99999) ?></strong></p>
                        <p class="mb-4">Наш менеджер свяжется с вами в ближайшее время для уточнения деталей и дальнейших шагов оформления лизинга.</p>
                        <div class="row justify-content-center mt-4">
                            <div class="col-md-6">
                                <div class="d-grid gap-3">
                                    <a href="index.php?page=dashboard-client" class="btn btn-primary btn-lg">Перейти в личный кабинет</a>
                                    <a href="index.php" class="btn btn-outline-primary">Вернуться на главную</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white py-3">
                        <h3 class="card-title mb-0">Оформление заявки на лизинг <?= $objectTypeName ?></h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($formSubmitted && !$success): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($companyName)): ?>
                            <div class="alert alert-info">
                                <p class="mb-1">Вы выбрали компанию: <strong><?= htmlspecialchars($companyName) ?></strong></p>
                                <?php if (!empty($monthlyPayment)): ?>
                                    <p class="mb-0">Ежемесячный платеж: <strong><?= htmlspecialchars($monthlyPayment) ?></strong></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="mt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="object_type" class="form-label">Тип предмета лизинга</label>
                                    <select class="form-select" id="object_type" name="object_type" required>
                                        <option value="">Выберите тип <?= $objectTypeName ?></option>
                                        <?php foreach ($objectTypeOptions as $value => $label): ?>
                                            <option value="<?= $value ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="object_price" class="form-label">Стоимость предмета лизинга</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="object_price" name="object_price" min="500000" 
                                            value="<?= $isVehicle && isset($vehicle) ? $vehicle['price'] : ($isRealEstate && isset($realEstate) ? $realEstate['price'] : '3000000') ?>" required>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="initial_payment" class="form-label">Первоначальный взнос</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="initial_payment" name="initial_payment" 
                                            value="<?= $isVehicle ? '600000' : '2000000' ?>" required>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                    <div class="form-text">
                                        <?= $isVehicle ? 'От 10% до 49% от стоимости транспорта' : 'От 20% до 70% от стоимости объекта' ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="term_months" class="form-label">Срок лизинга</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="term_months" name="term_months" min="12" max="120" step="12" 
                                            value="<?= $isVehicle ? '36' : '60' ?>" required>
                                        <span class="input-group-text">мес.</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="monthly_payment" class="form-label">Ежемесячный платеж</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="monthly_payment" name="monthly_payment" 
                                            value="<?= isset($monthlyPayment) && !empty($monthlyPayment) ? preg_replace('/[^0-9]/', '', $monthlyPayment) : ($isVehicle ? '42300' : '126700') ?>" required>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="leasing_company" class="form-label">Лизинговая компания</label>
                                    <select class="form-select" id="leasing_company" name="leasing_company" required>
                                        <option value="">Выберите компанию</option>
                                        <?php if ($isVehicle): ?>
                                            <option value="Сбербанк Лизинг" <?= $companyName === 'Сбербанк Лизинг' ? 'selected' : '' ?>>Сбербанк Лизинг</option>
                                            <option value="ВТБ Лизинг" <?= $companyName === 'ВТБ Лизинг' ? 'selected' : '' ?>>ВТБ Лизинг</option>
                                            <option value="Альфа-Лизинг" <?= $companyName === 'Альфа-Лизинг' ? 'selected' : '' ?>>Альфа-Лизинг</option>
                                            <option value="Газпромбанк Лизинг" <?= $companyName === 'Газпромбанк Лизинг' ? 'selected' : '' ?>>Газпромбанк Лизинг</option>
                                            <option value="Европлан" <?= $companyName === 'Европлан' ? 'selected' : '' ?>>Европлан</option>
                                            <option value="РЕСО-Лизинг" <?= $companyName === 'РЕСО-Лизинг' ? 'selected' : '' ?>>РЕСО-Лизинг</option>
                                        <?php else: ?>
                                            <option value="Сбербанк Лизинг" <?= $companyName === 'Сбербанк Лизинг' ? 'selected' : '' ?>>Сбербанк Лизинг</option>
                                            <option value="ВТБ Лизинг" <?= $companyName === 'ВТБ Лизинг' ? 'selected' : '' ?>>ВТБ Лизинг</option>
                                            <option value="Альфа-Лизинг" <?= $companyName === 'Альфа-Лизинг' ? 'selected' : '' ?>>Альфа-Лизинг</option>
                                            <option value="Газпромбанк Лизинг" <?= $companyName === 'Газпромбанк Лизинг' ? 'selected' : '' ?>>Газпромбанк Лизинг</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <label for="comments" class="form-label">Комментарий к заявке</label>
                                    <textarea class="form-control" id="comments" name="comments" rows="4" placeholder="Укажите дополнительную информацию или пожелания по лизингу"><?= isset($_POST['comments']) ? htmlspecialchars($_POST['comments']) : '' ?></textarea>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="termsAgree" name="terms_agree" required>
                                        <label class="form-check-label" for="termsAgree">
                                            Я согласен с <a href="index.php?page=terms" target="_blank">условиями лизинга</a> и <a href="index.php?page=privacy" target="_blank">политикой обработки персональных данных</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4 text-center">
                                    <button type="submit" name="submit_application" class="btn btn-primary btn-lg px-5 rounded-pill">Отправить заявку</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
