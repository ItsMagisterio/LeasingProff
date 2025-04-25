<?php
// Страница для просмотра деталей заявки на лизинг
// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Проверяем авторизацию
if (!$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Получаем текущего пользователя
$currentUser = $auth->getCurrentUser();

// Получаем ID заявки
$applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получаем данные заявки
$application = $applications->getApplicationById($applicationId);

// Если заявка не найдена или не принадлежит текущему пользователю (и пользователь не админ/менеджер)
if (!$application || 
    (!$auth->isAdmin() && !$auth->isManager() && $application['user_id'] != $currentUser['id'])) {
    header('Location: index.php?page=applications');
    exit;
}

// Определяем статус заявки и его цвет
$statusClass = '';
$statusText = '';

switch ($application['status']) {
    case 'new':
        $statusClass = 'bg-primary';
        $statusText = 'Новая';
        break;
    case 'in_progress':
        $statusClass = 'bg-warning text-dark';
        $statusText = 'На рассмотрении';
        break;
    case 'approved':
        $statusClass = 'bg-success';
        $statusText = 'Одобрена';
        break;
    case 'rejected':
        $statusClass = 'bg-danger';
        $statusText = 'Отклонена';
        break;
    case 'signed':
        $statusClass = 'bg-info';
        $statusText = 'Подписана';
        break;
    case 'completed':
        $statusClass = 'bg-secondary';
        $statusText = 'Завершена';
        break;
    default:
        $statusClass = 'bg-primary';
        $statusText = 'Новая';
}

// Определяем тип заявки для отображения соответствующих данных
$isVehicle = $application['type'] === 'vehicle';
$isRealEstate = $application['type'] === 'real_estate';

// Устанавливаем заголовок страницы
outputHeader('Заявка №' . $applicationId);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Заявка №<?= $applicationId ?></h3>
                    <span class="badge <?= $statusClass ?> fs-6"><?= htmlspecialchars($statusText) ?></span>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">Основная информация</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th scope="row" class="ps-0">Тип лизинга</th>
                                    <td><?= $isVehicle ? 'Транспорт' : 'Недвижимость' ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="ps-0">Дата подачи</th>
                                    <td><?= date('d.m.Y', strtotime($application['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" class="ps-0">Статус</th>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span></td>
                                </tr>
                                <?php if (!empty($application['leasing_company'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Лизинговая компания</th>
                                    <td><?= htmlspecialchars($application['leasing_company']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Финансовые детали</h5>
                            <table class="table table-borderless">
                                <?php if ($isVehicle && isset($application['vehicle_price'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Стоимость транспорта</th>
                                    <td><?= number_format($application['vehicle_price'], 0, '.', ' ') ?> ₽</td>
                                </tr>
                                <?php elseif ($isRealEstate && isset($application['real_estate_price'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Стоимость недвижимости</th>
                                    <td><?= number_format($application['real_estate_price'], 0, '.', ' ') ?> ₽</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th scope="row" class="ps-0">Первоначальный взнос</th>
                                    <td><?= number_format($application['initial_payment'], 0, '.', ' ') ?> ₽</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="ps-0">Срок лизинга</th>
                                    <td><?= $application['term_months'] ?> месяцев</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="ps-0">Ежемесячный платеж</th>
                                    <td><?= number_format($application['monthly_payment'], 0, '.', ' ') ?> ₽</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($isVehicle): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Информация о транспорте</h5>
                            <table class="table table-borderless">
                                <?php if (isset($application['vehicle_type'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Тип транспорта</th>
                                    <td>
                                        <?php 
                                        switch($application['vehicle_type']) {
                                            case 'car': echo 'Легковой автомобиль'; break;
                                            case 'truck': echo 'Грузовой автомобиль'; break;
                                            case 'special': echo 'Спецтехника'; break;
                                            default: echo htmlspecialchars($application['vehicle_type']); 
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (isset($application['vehicle_make']) && isset($application['vehicle_model'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Марка и модель</th>
                                    <td><?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php elseif ($isRealEstate): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Информация о недвижимости</h5>
                            <table class="table table-borderless">
                                <?php if (isset($application['real_estate_type'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Тип недвижимости</th>
                                    <td>
                                        <?php 
                                        switch($application['real_estate_type']) {
                                            case 'apartment': echo 'Квартира'; break;
                                            case 'house': echo 'Дом'; break;
                                            case 'commercial': echo 'Коммерческая недвижимость'; break;
                                            default: echo htmlspecialchars($application['real_estate_type']); 
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (isset($application['real_estate_title'])): ?>
                                <tr>
                                    <th scope="row" class="ps-0">Наименование</th>
                                    <td><?= htmlspecialchars($application['real_estate_title']) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($application['comments'])): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Комментарии</h5>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($application['comments'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($application['status'] === 'approved' || $application['status'] === 'signed' || $application['status'] === 'completed'): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Документы</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Название документа</th>
                                        <th>Дата</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Договор лизинга №<?= $applicationId ?>-ДЛ</td>
                                        <td><?= date('d.m.Y', strtotime('+2 days', strtotime($application['created_at']))) ?></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-primary" title="Скачать документ"><i class="fas fa-download"></i></button></td>
                                    </tr>
                                    <tr>
                                        <td>График платежей</td>
                                        <td><?= date('d.m.Y', strtotime('+2 days', strtotime($application['created_at']))) ?></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-primary" title="Скачать документ"><i class="fas fa-download"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Кнопки действий в зависимости от статуса -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <a href="index.php?page=applications" class="btn btn-outline-primary rounded-pill me-2">Назад к списку заявок</a>
                            
                            <?php if ($application['status'] === 'new' || $application['status'] === 'in_progress'): ?>
                            <button type="button" class="btn btn-outline-danger rounded-pill me-2">Отменить заявку</button>
                            <?php endif; ?>
                            
                            <?php if ($application['status'] === 'rejected'): ?>
                            <button type="button" class="btn btn-outline-success rounded-pill">Повторить заявку</button>
                            <?php endif; ?>
                            
                            <?php if ($application['status'] === 'approved'): ?>
                            <button type="button" class="btn btn-success rounded-pill">Подписать документы</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>