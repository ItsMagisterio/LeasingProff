<?php
// Проверяем, что пользователь авторизован и имеет права администратора
if (!$auth->isAdmin()) {
    header('Location: index.php?page=login');
    exit;
}

// Получаем ID менеджера из URL
$managerId = isset($_GET['manager_id']) ? (int)$_GET['manager_id'] : 0;

if (!$managerId) {
    header('Location: index.php?page=dashboard-admin');
    exit;
}

// Получаем данные менеджера
$manager = $users->getUserById($managerId);

if (!$manager || $manager['role'] !== 'manager') {
    header('Location: index.php?page=dashboard-admin');
    exit;
}

// Определяем период для статистики
$period = isset($_GET['period']) ? $_GET['period'] : 'day';

// Вычисляем даты для периода
$today = date('Y-m-d');
$startDate = $today; // По умолчанию - сегодня
$endDate = $today;

switch ($period) {
    case 'week':
        // Неделя назад от текущей даты
        $startDate = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'month':
        // Месяц назад от текущей даты
        $startDate = date('Y-m-d', strtotime('-30 days'));
        break;
    default:
        // Сегодня (оставляем как есть)
        break;
}

// Получаем заявки менеджера с учетом выбранного периода
$managerApplications = $applications->getApplicationsByManager($managerId, $startDate, $endDate);

// Статистика заявок менеджера
$appStats = array(
    'new' => 0,
    'in_progress' => 0,
    'approved' => 0,
    'rejected' => 0,
    'cancelled' => 0,
    'total' => count($managerApplications)
);

// Подсчитываем статистику
foreach ($managerApplications as $app) {
    if (isset($app['status']) && isset($appStats[$app['status']])) {
        $appStats[$app['status']]++;
    }
}
?>

<!-- Страница заявок менеджера -->
<div class="container user-dashboard">
    <!-- Вывод сообщений об успехе и ошибках -->
    <?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Заявки менеджера: <?= htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']) ?></h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-admin" class="<?= $page === 'dashboard-admin' ? 'active' : '' ?>">Обзор</a>
                <a href="index.php?page=user-rights" class="<?= $page === 'user-rights' ? 'active' : '' ?>">Права пользователей</a>
                <a href="index.php?page=leasing-companies" class="<?= $page === 'leasing-companies' ? 'active' : '' ?>">Лизинговые компании</a>
                <a href="index.php?page=managers" class="<?= $page === 'managers' ? 'active' : '' ?>">Менеджеры</a>
                <a href="index.php?page=dashboard-clients" class="<?= $page === 'dashboard-clients' ? 'active' : '' ?>">Клиенты</a>
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-link text-white p-0 ms-3">Выход</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mt-3">
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-file-alt text-primary" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $appStats['total'] ?></h2>
                <p class="text-muted">Всего заявок</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-star text-warning" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $appStats['new'] ?></h2>
                <p class="text-muted">Новых</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $appStats['approved'] ?></h2>
                <p class="text-muted">Одобрено</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $appStats['rejected'] ?></h2>
                <p class="text-muted">Отклонено</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Список заявок менеджера</h5>
            <div class="btn-group">
                <a href="index.php?page=manager-applications&manager_id=<?= $managerId ?>&period=day" class="btn btn-sm <?= (!isset($_GET['period']) || $_GET['period'] === 'day') ? 'btn-primary' : 'btn-outline-primary' ?>">Сегодня</a>
                <a href="index.php?page=manager-applications&manager_id=<?= $managerId ?>&period=week" class="btn btn-sm <?= (isset($_GET['period']) && $_GET['period'] === 'week') ? 'btn-primary' : 'btn-outline-primary' ?>">Неделя</a>
                <a href="index.php?page=manager-applications&manager_id=<?= $managerId ?>&period=month" class="btn btn-sm <?= (isset($_GET['period']) && $_GET['period'] === 'month') ? 'btn-primary' : 'btn-outline-primary' ?>">Месяц</a>
            </div>
        </div>
        
        <?php if ($managerApplications): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Клиент</th>
                        <th>Объект лизинга</th>
                        <th>Статус</th>
                        <th>Дата заявки</th>
                        <th>Дата обновления</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($managerApplications as $application): ?>
                    <tr>
                        <td>A-<?= $application['id'] ?></td>
                        <td><?= htmlspecialchars($application['client_first_name'] . ' ' . $application['client_last_name']) ?></td>
                        <td>
                            <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                <?= htmlspecialchars($application['real_estate_title']) ?> 
                                <span class="badge bg-info">Недвижимость</span>
                            <?php else: ?>
                                <?= htmlspecialchars((isset($application['vehicle_make']) ? $application['vehicle_make'] : 'Не указано') . ' ' . (isset($application['vehicle_model']) ? $application['vehicle_model'] : '')) ?>
                                <span class="badge bg-secondary">Авто</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusClass = '';
                            $statusText = '';
                            
                            switch ($application['status']) {
                                case 'new':
                                    $statusClass = 'bg-primary';
                                    $statusText = 'Новая';
                                    break;
                                case 'in_progress':
                                    $statusClass = 'bg-warning';
                                    $statusText = 'В обработке';
                                    break;
                                case 'approved':
                                    $statusClass = 'bg-success';
                                    $statusText = 'Одобрена';
                                    break;
                                case 'rejected':
                                    $statusClass = 'bg-danger';
                                    $statusText = 'Отклонена';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Отменена';
                                    break;
                                default:
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Не определен';
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </td>
                        <td><?= date('d.m.Y', strtotime($application['created_at'])) ?></td>
                        <td><?= date('d.m.Y', strtotime($application['updated_at'])) ?></td>
                        <td>
                            <a href="index.php?page=application-details&id=<?= $application['id'] ?>" class="btn btn-sm btn-primary">Подробнее</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Нет заявок для данного менеджера за выбранный период</div>
        <?php endif; ?>
    </div>
</div>