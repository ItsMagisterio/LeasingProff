<?php
// Получаем ID объекта недвижимости из параметров запроса
$propertyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

// Получаем данные объекта недвижимости
$property = $realEstate->getRealEstateById($propertyId);

// Если объект не найден, показываем сообщение об ошибке
if (!$property) {
    $error = 'Объект недвижимости не найден.';
} else {
    // Определяем название типа для отображения
    $typeLabel = '';
    switch ($property['type']) {
        case 'apartment':
            $typeLabel = 'Квартира';
            break;
        case 'house':
            $typeLabel = 'Дом';
            break;
        case 'commercial':
            $typeLabel = 'Коммерческая недвижимость';
            break;
        default:
            $typeLabel = ucfirst($property['type']);
    }
    
    // Формируем массив особенностей для отображения
    $features = !empty($property['features']) ? explode(',', $property['features']) : [];
    
    // Определяем стандартный срок лизинга
    $defaultTerm = 60; // 5 лет (60 месяцев)
    
    // Рассчитываем стандартный первоначальный взнос (20% от стоимости)
    $defaultInitialPayment = $property['price'] * 0.2;
    
    // Для простоты используем значение monthly_payment из базы данных
    $monthlyPayment = $property['monthly_payment'];
    
    // Устанавливаем заголовок страницы
    outputHeader($property['title'] . ' - ' . $typeLabel);
} 

// Если ошибка, устанавливаем заголовок по умолчанию
if ($error) {
    outputHeader('Ошибка');
}


?>

<div class="container py-5">
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <h1 class="h4">Ошибка</h1>
            <p><?= htmlspecialchars($error) ?></p>
            <div class="mt-3">
                <a href="index.php?page=real-estate" class="btn btn-primary">Вернуться к списку недвижимости</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>" class="img-fluid w-100" style="max-height: 500px; object-fit: cover;">
                        <div class="p-4">
                            <h1 class="h2 mb-3"><?= htmlspecialchars($property['title']) ?></h1>
                            
                            <div class="d-flex align-items-center mb-3 flex-wrap">
                                <span class="badge bg-secondary me-2"><?= htmlspecialchars($typeLabel) ?></span>
                                <span class="me-3"><i class="fas fa-ruler-combined me-1"></i> <?= number_format($property['area'], 1, ',', ' ') ?> м²</span>
                                <?php if ($property['rooms']): ?>
                                    <span class="me-3"><i class="fas fa-door-open me-1"></i> <?= $property['rooms'] ?> комн.</span>
                                <?php endif; ?>
                                <?php if ($property['floor']): ?>
                                    <span class="me-3"><i class="fas fa-building me-1"></i> Этаж <?= $property['floor'] ?>/<?= $property['total_floors'] ?></span>
                                <?php endif; ?>
                                <?php if ($property['build_year']): ?>
                                    <span><i class="fas fa-calendar-alt me-1"></i> <?= $property['build_year'] ?> г.п.</span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($property['address']) ?></p>
                            
                            <h2 class="h4 mt-4 mb-3">Описание</h2>
                            <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
                            
                            <?php if (!empty($features)): ?>
                                <h2 class="h4 mt-4 mb-3">Особенности и удобства</h2>
                                <ul class="list-group list-group-flush mb-4">
                                    <?php foreach ($features as $feature): ?>
                                        <li class="list-group-item px-0">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?= htmlspecialchars(trim($feature)) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0">Лизинг недвижимости</h3>
                    </div>
                    <div class="card-body">
                        <div class="price-details mb-4">
                            <h4 class="text-primary mb-3"><?= number_format($property['price'], 0, ',', ' ') ?> ₽</h4>
                            <p class="text-muted mb-0">
                                <span class="fw-bold"><?= number_format($property['monthly_payment'], 0, ',', ' ') ?> ₽/месяц</span><br> 
                                ориентировочный платеж при сроке лизинга <?= $defaultTerm ?> мес.
                            </p>
                        </div>
                        
                        <?php if ($auth->isLoggedIn()): ?>
                            <form method="post" action="index.php">
                                <input type="hidden" name="action" value="submit_real_estate_application">
                                <input type="hidden" name="real_estate_id" value="<?= $property['id'] ?>">
                                
                                <div class="mb-3">
                                    <label for="initial_payment" class="form-label">Первоначальный взнос</label>
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" id="initial_payment" name="initial_payment" 
                                            value="<?= $defaultInitialPayment ?>" min="<?= $property['price'] * 0.1 ?>" max="<?= $property['price'] * 0.7 ?>" step="10000" required>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                    <small class="text-muted">от 10% до 70% стоимости объекта</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="term_months" class="form-label">Срок лизинга</label>
                                    <select class="form-select" id="term_months" name="term_months" required>
                                        <option value="24" <?= $defaultTerm == 24 ? 'selected' : '' ?>>24 месяца (2 года)</option>
                                        <option value="36" <?= $defaultTerm == 36 ? 'selected' : '' ?>>36 месяцев (3 года)</option>
                                        <option value="48" <?= $defaultTerm == 48 ? 'selected' : '' ?>>48 месяцев (4 года)</option>
                                        <option value="60" <?= $defaultTerm == 60 ? 'selected' : '' ?>>60 месяцев (5 лет)</option>
                                        <option value="72" <?= $defaultTerm == 72 ? 'selected' : '' ?>>72 месяца (6 лет)</option>
                                        <option value="84" <?= $defaultTerm == 84 ? 'selected' : '' ?>>84 месяца (7 лет)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="monthly_payment" class="form-label">Ежемесячный платеж</label>
                                    <div class="input-group mb-3">
                                        <input type="number" class="form-control" id="monthly_payment" name="monthly_payment" 
                                            value="<?= $monthlyPayment ?>" readonly>
                                        <span class="input-group-text">₽</span>
                                    </div>
                                    <small class="text-muted">расчет ориентировочный</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="comments" class="form-label">Комментарий к заявке</label>
                                    <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Укажите дополнительную информацию по заявке"></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Подать заявку</button>
                                </div>
                                
                                <div class="mt-3 small text-muted">
                                    Нажимая кнопку "Подать заявку", вы соглашаетесь с условиями лизинга и политикой обработки персональных данных.
                                </div>
                            </form>
                            
                            <script>
                                // Простой калькулятор для расчета ежемесячного платежа
                                document.addEventListener('DOMContentLoaded', function() {
                                    const initialPaymentInput = document.getElementById('initial_payment');
                                    const termMonthsSelect = document.getElementById('term_months');
                                    const monthlyPaymentInput = document.getElementById('monthly_payment');
                                    
                                    // Общая стоимость объекта
                                    const totalPrice = <?= $property['price'] ?>;
                                    
                                    // Функция расчета ежемесячного платежа
                                    function calculateMonthlyPayment() {
                                        const initialPayment = parseFloat(initialPaymentInput.value) || 0;
                                        const termMonths = parseInt(termMonthsSelect.value) || 60;
                                        
                                        // Рассчитываем сумму кредита
                                        const loanAmount = totalPrice - initialPayment;
                                        
                                        // Простой расчет платежа (для демонстрации)
                                        // В реальном калькуляторе нужно учитывать процентную ставку и другие параметры
                                        const interestRate = 0.12; // Годовая процентная ставка (12%)
                                        const monthlyRate = interestRate / 12;
                                        
                                        // Формула аннуитетного платежа
                                        const payment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, termMonths)) / 
                                                       (Math.pow(1 + monthlyRate, termMonths) - 1);
                                        
                                        // Округляем до целых
                                        return Math.round(payment);
                                    }
                                    
                                    // Обновление ежемесячного платежа при изменении параметров
                                    function updateMonthlyPayment() {
                                        monthlyPaymentInput.value = calculateMonthlyPayment();
                                    }
                                    
                                    // Добавляем обработчики событий
                                    initialPaymentInput.addEventListener('input', updateMonthlyPayment);
                                    termMonthsSelect.addEventListener('change', updateMonthlyPayment);
                                    
                                    // Первоначальный расчет
                                    updateMonthlyPayment();
                                });
                            </script>
                        <?php else: ?>
                            <div class="alert alert-info mb-3">
                                <p class="mb-2">Чтобы подать заявку на лизинг, необходимо авторизоваться.</p>
                                <div class="d-grid gap-2">
                                    <a href="index.php?page=login" class="btn btn-primary">Войти</a>
                                    <a href="index.php?page=register" class="btn btn-outline-primary">Зарегистрироваться</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="call-options">
                            <p class="mb-2"><i class="fas fa-headset me-2"></i> <a href="tel:+78001234567" class="text-decoration-none">8 (800) 123-45-67</a></p>
                            <p class="mb-0"><i class="far fa-envelope me-2"></i> <a href="mailto:webmaster@лизинг.орг" class="text-decoration-none">webmaster@лизинг.орг</a></p>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h4 class="h5 mb-3">Почему выбирают лизинг недвижимости?</h4>
                        <ul class="list-unstyled benefits-list">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Меньший первоначальный взнос, чем при ипотеке
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Гибкие условия и сроки договора
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Отсутствие залога и дополнительного обеспечения
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Налоговые преимущества для юридических лиц
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Быстрое решение по заявке
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="index.php?page=real-estate" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Вернуться к списку недвижимости
            </a>
        </div>
    <?php endif; ?>
</div>

<?php outputFooter(); ?>