<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Получаем заявки пользователя
$userApplications = $applications->getAllApplications(0, 0, ['user_id' => $currentUser['id']]);

// Получаем статистику заявок
$applicationsStats = $applications->getApplicationsCountByStatus($currentUser['id']);

// Определение ролей пользователей
$USER_ROLES = [
    'admin' => 'Администратор',
    'manager' => 'Менеджер',
    'client' => 'Клиент'
];
?>

<!-- Панель управления клиента -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Панель управления</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-client" class="active">Заявки</a>
                <a href="index.php?page=profile">Профиль</a>
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
    
    <div class="row">
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($USER_ROLES[$currentUser['role']]) ?></p>
                <hr>
                <p><i class="fas fa-envelope me-2 text-muted"></i> <?= htmlspecialchars($currentUser['email']) ?></p>
                <p><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($currentUser['phone']) ?></p>
                <p><i class="fas fa-calendar me-2 text-muted"></i> С нами с <?= date('d.m.Y', strtotime($currentUser['created_at'])) ?></p>
                <div class="d-grid">
                    <a href="index.php?page=profile" class="btn btn-outline-primary">Редактировать профиль</a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h5>Уведомления</h5>
                <?php if ($userApplications): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($userApplications as $index => $application): 
                            if ($index >= 3) break; // Показываем только 3 последних заявки
                            
                            $notificationText = '';
                            $badgeClass = '';
                            
                            if ($application['status'] == 'approved') {
                                $notificationText = 'Заявка одобрена';
                                $badgeClass = 'bg-success';
                            } elseif ($application['status'] == 'in_progress') {
                                $notificationText = 'Заявка на рассмотрении';
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($application['status'] == 'rejected') {
                                $notificationText = 'Заявка отклонена';
                                $badgeClass = 'bg-danger';
                            } else {
                                $notificationText = 'Заявка принята';
                                $badgeClass = 'bg-primary';
                            }
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($notificationText) ?>
                                <span class="badge <?= $badgeClass ?> rounded-pill">Новое</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">У вас пока нет заявок и уведомлений</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="dashboard-card">
                <h5>Мои заявки</h5>
                <?php if ($userApplications): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Объект лизинга</th>
                                    <th>Дата заявки</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userApplications as $application): 
                                    $statusClass = '';
                                    $statusText = '';
                                    
                                    switch ($application['status']) {
                                        case 'approved':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Одобрено';
                                            break;
                                        case 'in_progress':
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = 'На рассмотрении';
                                            break;
                                        case 'rejected':
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Отклонено';
                                            break;
                                        case 'signed':
                                            $statusClass = 'bg-info';
                                            $statusText = 'Подписано';
                                            break;
                                        case 'completed':
                                            $statusClass = 'bg-secondary';
                                            $statusText = 'Завершено';
                                            break;
                                        default:
                                            $statusClass = 'bg-primary';
                                            $statusText = 'Новая';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($application['id']) ?></td>
                                    <td>
                                        <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                            <?= htmlspecialchars($application['real_estate_title']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($application['created_at'])) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span></td>
                                    <td><a href="#" class="btn btn-sm btn-primary">Подробнее</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">У вас пока нет заявок на лизинг. Выберите автомобиль и подайте заявку.</div>
                <?php endif; ?>
            </div>
            
            <?php if ($userApplications && count($userApplications) > 0): 
                // Показываем детали последней заявки
                $latestApplication = $userApplications[0];
                
                // Определяем процент выполнения
                $progress = 0;
                switch ($latestApplication['status']) {
                    case 'new':
                        $progress = 20;
                        break;
                    case 'in_progress':
                        $progress = 40;
                        break;
                    case 'approved':
                        $progress = 60;
                        break;
                    case 'signed':
                        $progress = 80;
                        break;
                    case 'completed':
                        $progress = 100;
                        break;
                }
            ?>
            <div class="dashboard-card">
                <h5>Статус текущей заявки</h5>
                <div class="progress mb-4" style="height: 30px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"><?= $progress ?>%</div>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <div>
                            <h6 class="mb-0">Заявка подана</h6>
                            <small class="text-muted"><?= date('d.m.Y', strtotime($latestApplication['created_at'])) ?></small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($progress >= 40): ?>
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <?php elseif ($progress >= 20): ?>
                            <i class="fas fa-circle text-primary me-3 fs-4"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0">Первичное одобрение</h6>
                            <?php if ($progress >= 40): ?>
                                <small class="text-muted"><?= date('d.m.Y', strtotime('+2 days', strtotime($latestApplication['created_at']))) ?></small>
                            <?php elseif ($progress >= 20): ?>
                                <small class="text-muted">В процессе</small>
                            <?php else: ?>
                                <small class="text-muted">Ожидается</small>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($progress >= 60): ?>
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <?php elseif ($progress >= 40): ?>
                            <i class="fas fa-circle text-primary me-3 fs-4"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0">Проверка документов</h6>
                            <?php if ($progress >= 60): ?>
                                <small class="text-muted"><?= date('d.m.Y', strtotime('+4 days', strtotime($latestApplication['created_at']))) ?></small>
                            <?php elseif ($progress >= 40): ?>
                                <small class="text-muted">В процессе</small>
                            <?php else: ?>
                                <small class="text-muted">Ожидается</small>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($progress >= 80): ?>
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <?php elseif ($progress >= 60): ?>
                            <i class="fas fa-circle text-primary me-3 fs-4"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0">Подготовка договора</h6>
                            <?php if ($progress >= 80): ?>
                                <small class="text-muted"><?= date('d.m.Y', strtotime('+6 days', strtotime($latestApplication['created_at']))) ?></small>
                            <?php elseif ($progress >= 60): ?>
                                <small class="text-muted">В процессе</small>
                            <?php else: ?>
                                <small class="text-muted">Ожидается</small>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($progress >= 100): ?>
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <?php elseif ($progress >= 80): ?>
                            <i class="fas fa-circle text-primary me-3 fs-4"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0">Подписание документов</h6>
                            <?php if ($progress >= 100): ?>
                                <small class="text-muted"><?= date('d.m.Y', strtotime('+8 days', strtotime($latestApplication['created_at']))) ?></small>
                            <?php elseif ($progress >= 80): ?>
                                <small class="text-muted">В процессе</small>
                            <?php else: ?>
                                <small class="text-muted">Ожидается</small>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <?php if ($progress >= 100): ?>
                            <i class="fas fa-check-circle text-success me-3 fs-4"></i>
                        <?php else: ?>
                            <i class="fas fa-circle text-secondary me-3 fs-4"></i>
                        <?php endif; ?>
                        <div>
                            <h6 class="mb-0">Передача автомобиля</h6>
                            <?php if ($progress >= 100): ?>
                                <small class="text-muted"><?= date('d.m.Y', strtotime('+10 days', strtotime($latestApplication['created_at']))) ?></small>
                            <?php else: ?>
                                <small class="text-muted">Ожидается</small>
                            <?php endif; ?>
                        </div>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-card">
                <h5>Подать новую заявку</h5>
                <p>Выберите объект из нашего каталога и подайте новую заявку на лизинг.</p>
                <div class="row">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="d-grid">
                            <a href="index.php?page=marketplace" class="btn btn-primary">Каталог автомобилей</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-grid">
                            <a href="index.php?page=real-estate" class="btn btn-outline-primary">Каталог недвижимости</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>