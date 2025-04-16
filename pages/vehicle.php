<?php
// Получаем ID автомобиля из GET-параметра
$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные автомобиля
$vehicle = $vehicles->getVehicleById($vehicleId);

// Если автомобиль не найден, перенаправляем на маркетплейс
if (!$vehicle) {
    header("Location: index.php?page=marketplace");
    exit;
}

// Разбиваем строку с характеристиками на массив
$features = explode(',', $vehicle['features']);

// Рассчитываем сумму лизинга и ежемесячный платеж для различных сроков
$leasingTerms = [24, 36, 48, 60]; // сроки в месяцах
$initialPaymentPercents = [10, 20, 30]; // проценты первоначального взноса

// Рассчитываем платежи для разных условий
$paymentOptions = [];
foreach ($leasingTerms as $term) {
    foreach ($initialPaymentPercents as $percent) {
        $initialPayment = $vehicle['price'] * ($percent / 100);
        $leasingAmount = $vehicle['price'] - $initialPayment;
        
        // Упрощенный расчет платежа, в реальности используется более сложная формула
        $monthlyPayment = ceil($leasingAmount / $term * 1.1); // +10% за услуги лизинга
        
        $paymentOptions[] = [
            'term' => $term,
            'percent' => $percent,
            'initial_payment' => $initialPayment,
            'monthly_payment' => $monthlyPayment
        ];
    }
}

// Выбранный вариант лизинга (по умолчанию или из формы)
$selectedTerm = isset($_POST['term']) ? (int)$_POST['term'] : 36;
$selectedPercent = isset($_POST['percent']) ? (int)$_POST['percent'] : 20;

// Находим выбранный вариант
$selectedOption = null;
foreach ($paymentOptions as $option) {
    if ($option['term'] == $selectedTerm && $option['percent'] == $selectedPercent) {
        $selectedOption = $option;
        break;
    }
}

// Если не нашли, берем первый вариант
if (!$selectedOption && !empty($paymentOptions)) {
    $selectedOption = $paymentOptions[0];
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?>" style="max-height: 400px; object-fit: cover;">
                <div class="card-body">
                    <h1 class="card-title"><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?> <?= htmlspecialchars($vehicle['year']) ?></h1>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-primary mb-0"><?= number_format($vehicle['price'], 0, ',', ' ') ?> ₽</h3>
                        <h5 class="text-success mb-0">от <?= number_format($vehicle['monthly_payment'], 0, ',', ' ') ?> ₽/мес</h5>
                    </div>
                    
                    <hr>
                    
                    <h4 class="mt-4">Характеристики</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Марка:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['make']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Модель:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['model']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Год выпуска:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['year']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Двигатель:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['engine']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Мощность:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['power']) ?> л.с.</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Привод:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['drive_type']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Трансмиссия:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['transmission']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Цвет:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['color']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Салон:</span>
                                    <span class="text-muted"><?= htmlspecialchars($vehicle['interior']) ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <h4 class="mt-4">Комплектация</h4>
                    <div class="row mt-3">
                        <?php foreach ($features as $feature): ?>
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i> <?= htmlspecialchars(trim($feature)) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px; z-index: 1000;">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Рассчитать лизинг</h4>
                </div>
                <div class="card-body">
                    <?php if ($auth->isLoggedIn()): ?>
                        <form method="post" action="index.php?page=vehicle&id=<?= $vehicleId ?>">
                            <div class="mb-3">
                                <label for="percent" class="form-label">Первоначальный взнос</label>
                                <select class="form-select" id="percent" name="percent">
                                    <?php foreach ($initialPaymentPercents as $percent): ?>
                                        <option value="<?= $percent ?>" <?= $selectedPercent == $percent ? 'selected' : '' ?>>
                                            <?= $percent ?>% (<?= number_format($vehicle['price'] * $percent / 100, 0, ',', ' ') ?> ₽)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="term" class="form-label">Срок лизинга</label>
                                <select class="form-select" id="term" name="term">
                                    <?php foreach ($leasingTerms as $term): ?>
                                        <option value="<?= $term ?>" <?= $selectedTerm == $term ? 'selected' : '' ?>>
                                            <?= $term ?> мес.
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Рассчитать</button>
                            </div>
                        </form>
                        
                        <?php if ($selectedOption): ?>
                            <div class="mt-4 p-3 bg-light rounded">
                                <h5>Ваш расчет:</h5>
                                <ul class="list-unstyled mb-3">
                                    <li><strong>Стоимость автомобиля:</strong> <?= number_format($vehicle['price'], 0, ',', ' ') ?> ₽</li>
                                    <li><strong>Первоначальный взнос (<?= $selectedOption['percent'] ?>%):</strong> <?= number_format($selectedOption['initial_payment'], 0, ',', ' ') ?> ₽</li>
                                    <li><strong>Срок лизинга:</strong> <?= $selectedOption['term'] ?> мес.</li>
                                    <li><strong>Ежемесячный платеж:</strong> <?= number_format($selectedOption['monthly_payment'], 0, ',', ' ') ?> ₽</li>
                                </ul>
                                
                                <form method="post" action="index.php">
                                    <input type="hidden" name="action" value="submit_application">
                                    <input type="hidden" name="vehicle_id" value="<?= $vehicleId ?>">
                                    <input type="hidden" name="initial_payment" value="<?= $selectedOption['initial_payment'] ?>">
                                    <input type="hidden" name="term_months" value="<?= $selectedOption['term'] ?>">
                                    <input type="hidden" name="monthly_payment" value="<?= $selectedOption['monthly_payment'] ?>">
                                    
                                    <div class="mb-3">
                                        <label for="comments" class="form-label">Комментарий к заявке (необязательно)</label>
                                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success">Отправить заявку</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Для подачи заявки на лизинг необходимо авторизоваться.</p>
                            <div class="d-grid gap-2">
                                <a href="index.php?page=login" class="btn btn-primary">Войти</a>
                                <a href="index.php?page=register" class="btn btn-secondary">Зарегистрироваться</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Остались вопросы?</h5>
                    <p class="card-text">Свяжитесь с нами, и мы поможем с выбором автомобиля и условий лизинга.</p>
                    <div class="d-grid">
                        <a href="#" class="btn btn-outline-primary">Связаться с нами</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Похожие автомобили -->
    <?php
    // Получаем похожие автомобили той же марки
    $similarVehicles = $vehicles->getAllVehicles(3, 0, [
        'make' => $vehicle['make'],
        'status' => 'available'
    ]);
    
    // Исключаем текущий автомобиль
    $similarVehicles = array_filter($similarVehicles, function($item) use ($vehicleId) {
        return $item['id'] != $vehicleId;
    });
    
    // Если похожих автомобилей недостаточно, добавляем другие
    if (count($similarVehicles) < 3) {
        $otherVehicles = $vehicles->getAllVehicles(3 - count($similarVehicles), 0, ['status' => 'available']);
        
        // Исключаем текущий автомобиль и уже добавленные похожие
        $otherVehicles = array_filter($otherVehicles, function($item) use ($vehicleId, $similarVehicles) {
            if ($item['id'] == $vehicleId) {
                return false;
            }
            
            foreach ($similarVehicles as $similar) {
                if ($item['id'] == $similar['id']) {
                    return false;
                }
            }
            
            return true;
        });
        
        $similarVehicles = array_merge($similarVehicles, $otherVehicles);
    }
    
    // Если есть похожие автомобили, показываем их
    if (!empty($similarVehicles)):
    ?>
    <div class="mt-5">
        <h3 class="mb-4">Похожие автомобили</h3>
        <div class="row g-4">
            <?php foreach ($similarVehicles as $similarVehicle): 
                // Разбиваем строку с характеристиками на массив
                $similarFeatures = explode(',', $similarVehicle['features']);
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 vehicle-card">
                        <img src="<?= htmlspecialchars($similarVehicle['image_url']) ?>" alt="<?= htmlspecialchars($similarVehicle['make'] . ' ' . $similarVehicle['model']) ?>" class="card-img-top vehicle-img">
                        <div class="card-body">
                            <h4><?= htmlspecialchars($similarVehicle['make'] . ' ' . $similarVehicle['model']) ?></h4>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="vehicle-price mb-0"><?= number_format($similarVehicle['monthly_payment'], 0, ',', ' ') ?> ₽/мес</p>
                                <span class="badge bg-secondary"><?= number_format($similarVehicle['price'], 0, ',', ' ') ?> ₽</span>
                            </div>
                            <p class="text-muted">
                                <?= htmlspecialchars($similarVehicle['year']) ?> г., 
                                <?= htmlspecialchars($similarVehicle['engine']) ?>, 
                                <?= htmlspecialchars($similarVehicle['power']) ?> л.с., 
                                <?= htmlspecialchars($similarVehicle['transmission']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-grid">
                                <a href="index.php?page=vehicle&id=<?= $similarVehicle['id'] ?>" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>