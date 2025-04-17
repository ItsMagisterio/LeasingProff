<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Получаем заявки, назначенные на менеджера
$managerApplications = $applications->getAllApplications(0, 0, ['manager_id' => $currentUser['id']]);

// Получаем статистику заявок
$applicationsStats = $applications->getApplicationsCountByStatus();
?>

<!-- Панель управления менеджера -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Панель менеджера</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-manager" class="active">Заявки</a>
                <a href="index.php?page=dashboard-parsers">Парсеры</a>
                <a href="#">Клиенты</a>
                <a href="#">Отчеты</a>
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
                <p class="text-muted"><?= htmlspecialchars(USER_ROLES[$currentUser['role']]) ?></p>
                <hr>
                <p><i class="fas fa-envelope me-2 text-muted"></i> <?= htmlspecialchars($currentUser['email']) ?></p>
                <p><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($currentUser['phone']) ?></p>
                <p><i class="fas fa-briefcase me-2 text-muted"></i> ID: M-<?= $currentUser['id'] ?></p>
            </div>
            
            <div class="dashboard-card">
                <h5>Показатели</h5>
                <?php
                // Рассчитываем показатели производительности
                $totalApplications = count($managerApplications);
                $planCompletion = min(round(($totalApplications / 20) * 100), 100); // 20 заявок - план
                
                $closedApplications = 0;
                $inProgressApplications = 0;
                foreach ($managerApplications as $app) {
                    if (in_array($app['status'], ['approved', 'signed', 'completed'])) {
                        $closedApplications++;
                    } elseif ($app['status'] == 'in_progress') {
                        $inProgressApplications++;
                    }
                }
                
                $closingRate = $totalApplications > 0 ? round(($closedApplications / $totalApplications) * 100) : 0;
                $conversionRate = $totalApplications > 0 ? round(($closedApplications / $totalApplications) * 100) : 0;
                ?>
                <div class="mb-3">
                    <label class="form-label">Выполнение плана</label>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $planCompletion ?>%;" aria-valuenow="<?= $planCompletion ?>" aria-valuemin="0" aria-valuemax="100"><?= $planCompletion ?>%</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Закрытые сделки</label>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $closingRate ?>%;" aria-valuenow="<?= $closingRate ?>" aria-valuemin="0" aria-valuemax="100"><?= $closingRate ?>%</div>
                    </div>
                </div>
                <div>
                    <label class="form-label">Конверсия</label>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $conversionRate ?>%;" aria-valuenow="<?= $conversionRate ?>" aria-valuemin="0" aria-valuemax="100"><?= $conversionRate ?>%</div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h5>Уведомления</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Новые заявки
                        <span class="badge bg-danger rounded-pill"><?= $applicationsStats['new'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Требуется обратная связь
                        <span class="badge bg-warning text-dark rounded-pill"><?= $inProgressApplications ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Документы ожидают подтверждения
                        <span class="badge bg-primary rounded-pill"><?= $applicationsStats['approved'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Активные заявки</h5>
                    <div>
                        <form method="get" class="d-inline">
                            <input type="hidden" name="page" value="dashboard-manager">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Все заявки</option>
                                <option value="new" <?= isset($_GET['status']) && $_GET['status'] == 'new' ? 'selected' : '' ?>>Новые</option>
                                <option value="in_progress" <?= isset($_GET['status']) && $_GET['status'] == 'in_progress' ? 'selected' : '' ?>>В обработке</option>
                                <option value="approved" <?= isset($_GET['status']) && $_GET['status'] == 'approved' ? 'selected' : '' ?>>Одобренные</option>
                                <option value="rejected" <?= isset($_GET['status']) && $_GET['status'] == 'rejected' ? 'selected' : '' ?>>Отклоненные</option>
                            </select>
                        </form>
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
                                    <th>Дата заявки</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($managerApplications as $application): 
                                    // Фильтр по статусу, если выбран
                                    if (isset($_GET['status']) && $_GET['status'] && $application['status'] != $_GET['status']) {
                                        continue;
                                    }
                                    
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
                                    <td>A-<?= htmlspecialchars($application['id']) ?></td>
                                    <td><?= htmlspecialchars($application['client_first_name'] . ' ' . $application['client_last_name']) ?></td>
                                    <td>
                                        <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                            <?= htmlspecialchars($application['real_estate_title']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($application['created_at'])) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#applicationModal<?= $application['id'] ?>">
                                            Действия
                                        </button>
                                        
                                        <!-- Модальное окно для управления заявкой -->
                                        <div class="modal fade" id="applicationModal<?= $application['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Управление заявкой A-<?= $application['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <h6>Информация о клиенте</h6>
                                                                <p><strong>ФИО:</strong> <?= htmlspecialchars($application['client_first_name'] . ' ' . $application['client_last_name']) ?></p>
                                                                <p><strong>Email:</strong> <?= htmlspecialchars($application['client_email']) ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Информация о заявке</h6>
                                                                <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                                                    <p><strong>Тип заявки:</strong> Недвижимость</p>
                                                                    <p><strong>Объект:</strong> <?= htmlspecialchars($application['real_estate_title']) ?></p>
                                                                    <p><strong>Тип недвижимости:</strong> <?= htmlspecialchars($application['real_estate_type']) ?></p>
                                                                    <?php if (!empty($application['real_estate_address'])): ?>
                                                                    <p><strong>Адрес:</strong> <?= htmlspecialchars($application['real_estate_address']) ?></p>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($application['real_estate_area'])): ?>
                                                                    <p><strong>Площадь:</strong> <?= number_format($application['real_estate_area'], 1, ',', ' ') ?> м²</p>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <p><strong>Тип заявки:</strong> Автомобиль</p>
                                                                    <p><strong>Модель:</strong> <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model'] . ' ' . $application['vehicle_year']) ?></p>
                                                                    <?php if (!empty($application['vehicle_color'])): ?>
                                                                    <p><strong>Цвет:</strong> <?= htmlspecialchars($application['vehicle_color']) ?></p>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                                <p><strong>Ежемесячный платеж:</strong> <?= number_format($application['monthly_payment'], 0, ',', ' ') ?> ₽</p>
                                                                <p><strong>Первоначальный взнос:</strong> <?= number_format($application['initial_payment'], 0, ',', ' ') ?> ₽</p>
                                                                <p><strong>Срок лизинга:</strong> <?= $application['term_months'] ?> мес.</p>
                                                            </div>
                                                        </div>
                                                        
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="update_application_status">
                                                            <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="application_status" class="form-label">Изменить статус заявки</label>
                                                                <select name="status" id="application_status" class="form-select">
                                                                    <option value="new" <?= $application['status'] == 'new' ? 'selected' : '' ?>>Новая</option>
                                                                    <option value="in_progress" <?= $application['status'] == 'in_progress' ? 'selected' : '' ?>>На рассмотрении</option>
                                                                    <option value="approved" <?= $application['status'] == 'approved' ? 'selected' : '' ?>>Одобрена</option>
                                                                    <option value="rejected" <?= $application['status'] == 'rejected' ? 'selected' : '' ?>>Отклонена</option>
                                                                    <option value="signed" <?= $application['status'] == 'signed' ? 'selected' : '' ?>>Подписана</option>
                                                                    <option value="completed" <?= $application['status'] == 'completed' ? 'selected' : '' ?>>Завершена</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="application_comments" class="form-label">Комментарий</label>
                                                                <textarea name="comments" id="application_comments" rows="3" class="form-control"><?= htmlspecialchars($application['comments']) ?></textarea>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-between">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                <?php else: ?>
                    <div class="alert alert-info">
                        На вас не назначено ни одной заявки. Новые заявки будут распределены администратором.
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-card">
                <h5>Недавние действия</h5>
                <?php if ($managerApplications): ?>
                    <ul class="list-group list-group-flush">
                        <?php 
                        // Сортируем заявки по дате обновления
                        usort($managerApplications, function($a, $b) {
                            return strtotime($b['updated_at']) - strtotime($a['updated_at']);
                        });
                        
                        // Показываем последние 4 обновленные заявки
                        $count = 0;
                        foreach ($managerApplications as $application):
                            if ($count >= 4) break;
                            $count++;
                            
                            $actionText = '';
                            switch ($application['status']) {
                                case 'new':
                                    $actionText = 'Новая заявка A-' . $application['id'];
                                    break;
                                case 'in_progress':
                                    $actionText = 'Заявка A-' . $application['id'] . ' в обработке';
                                    break;
                                case 'approved':
                                    $actionText = 'Заявка A-' . $application['id'] . ' одобрена';
                                    break;
                                case 'rejected':
                                    $actionText = 'Заявка A-' . $application['id'] . ' отклонена';
                                    break;
                                case 'signed':
                                    $actionText = 'Документы по заявке A-' . $application['id'] . ' подписаны';
                                    break;
                                case 'completed':
                                    $actionText = 'Заявка A-' . $application['id'] . ' завершена';
                                    break;
                            }
                        ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($actionText) ?></strong>
                                    <p class="mb-0 text-muted">
                                        <?= htmlspecialchars($application['client_first_name'] . ' ' . $application['client_last_name']) ?>, 
                                        <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                            <?= htmlspecialchars($application['real_estate_title']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <small class="text-muted">
                                    <?php
                                    $updatedDate = new DateTime($application['updated_at']);
                                    $now = new DateTime();
                                    $diff = $now->diff($updatedDate);
                                    
                                    if ($diff->days == 0) {
                                        echo 'Сегодня, ' . $updatedDate->format('H:i');
                                    } elseif ($diff->days == 1) {
                                        echo 'Вчера, ' . $updatedDate->format('H:i');
                                    } else {
                                        echo $updatedDate->format('d.m.Y');
                                    }
                                    ?>
                                </small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Нет недавних действий</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>