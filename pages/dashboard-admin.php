<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Получаем статистику пользователей
$usersStats = $users->getUsersCountByRole();

// Получаем статистику заявок
$applicationsStats = $applications->getApplicationsCountByStatus();

// Получаем распределение заявок по менеджерам
$managersApplications = $applications->getApplicationsCountByManager();

// Получаем список менеджеров
$managersList = $users->getManagers();

// Получаем нераспределенные заявки
$unassignedApplications = $applications->getUnassignedApplications();
?>

<!-- Панель управления администратора -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Панель администратора</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-admin" class="active">Обзор</a>
                <a href="index.php?page=user-rights">Права пользователей</a>
                <a href="index.php?page=leasing-companies">Лизинговые компании</a>
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
    
    <div class="row g-4">
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-users text-primary" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $usersStats['client'] ?></h2>
                <p class="text-muted">Клиентов</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-file-alt text-success" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $applicationsStats['total'] ?></h2>
                <p class="text-muted">Заявок</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-car text-info" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $vehicles->getVehiclesCount() ?></h2>
                <p class="text-muted">Автомобилей</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card text-center">
                <div class="py-3">
                    <i class="fas fa-user-tie text-warning" style="font-size: 3rem;"></i>
                </div>
                <h2 class="mb-0"><?= $usersStats['manager'] ?></h2>
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
                
                <?php if ($managersApplications): ?>
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
                                <?php foreach ($managersApplications as $manager): ?>
                                <tr>
                                    <td><?= htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']) ?></td>
                                    <td><?= (int)$manager['total'] ?></td>
                                    <td><?= (int)$manager['new'] ?></td>
                                    <td><?= (int)$manager['in_progress'] ?></td>
                                    <td><?= (int)$manager['approved'] ?></td>
                                    <td><?= (int)$manager['rejected'] ?></td>
                                    <td><button class="btn btn-sm btn-primary">Подробнее</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Нет данных о распределении заявок</div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-card mt-4">
                <h5>Нераспределенные заявки</h5>
                
                <?php if ($unassignedApplications): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Клиент</th>
                                    <th>Объект лизинга</th>
                                    <th>Дата заявки</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unassignedApplications as $application): ?>
                                <tr>
                                    <td>A-<?= $application['id'] ?></td>
                                    <td><?= htmlspecialchars($application['client_first_name'] . ' ' . $application['client_last_name']) ?></td>
                                    <td>
                                        <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                            <?= htmlspecialchars($application['real_estate_title']) ?> 
                                            <span class="badge bg-info">Недвижимость</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?>
                                            <span class="badge bg-secondary">Авто</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y', strtotime($application['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal<?= $application['id'] ?>">
                                            Назначить менеджера
                                        </button>
                                        
                                        <!-- Модальное окно для назначения менеджера -->
                                        <div class="modal fade" id="assignModal<?= $application['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Назначить менеджера на заявку A-<?= $application['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="post">
                                                            <input type="hidden" name="action" value="assign_manager">
                                                            <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="manager_id" class="form-label">Выберите менеджера</label>
                                                                <select name="manager_id" id="manager_id" class="form-select" required>
                                                                    <option value="">-- Выберите менеджера --</option>
                                                                    <?php foreach ($managersList as $manager): ?>
                                                                    <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-between">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                                <button type="submit" class="btn btn-primary">Назначить</button>
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
                <?php else: ?>
                    <div class="alert alert-success">Все заявки распределены</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="dashboard-card">
                <h5>Статистика объектов лизинга</h5>
                <ul class="nav nav-tabs mb-3" id="leasingStats" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-controls="vehicles" aria-selected="true">Автомобили</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="real-estate-tab" data-bs-toggle="tab" data-bs-target="#real-estate" type="button" role="tab" aria-controls="real-estate" aria-selected="false">Недвижимость</button>
                    </li>
                </ul>
                <div class="tab-content" id="leasingStatsContent">
                    <div class="tab-pane fade show active" id="vehicles" role="tabpanel" aria-labelledby="vehicles-tab">
                <?php
                // Получаем популярные марки
                $vehicleMakes = $vehicles->getVehicleMakes();
                $makeStats = [];
                
                // Подсчитываем количество автомобилей каждой марки
                foreach ($vehicleMakes as $make) {
                    $makeStats[$make] = 0;
                }
                
                $allVehicles = $vehicles->getAllVehicles();
                $totalVehicles = count($allVehicles);
                
                foreach ($allVehicles as $vehicle) {
                    if (isset($makeStats[$vehicle['make']])) {
                        $makeStats[$vehicle['make']]++;
                    }
                }
                
                // Сортируем по популярности (убыванию)
                arsort($makeStats);
                
                // Вычисляем проценты
                $makePercentages = [];
                foreach ($makeStats as $make => $count) {
                    $makePercentages[$make] = $totalVehicles > 0 ? round(($count / $totalVehicles) * 100) : 0;
                }
                
                // Ограничиваем количество показываемых марок
                $makePercentages = array_slice($makePercentages, 0, 5);
                
                // Назначаем классы Bootstrap для прогресс-баров
                $progressColors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'];
                ?>
                
                <div class="mb-3">
                    <label class="form-label">Самые запрашиваемые марки</label>
                    <?php $colorIndex = 0; ?>
                    <?php foreach ($makePercentages as $make => $percentage): ?>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar <?= $progressColors[$colorIndex] ?>" role="progressbar" 
                                 style="width: <?= $percentage ?>%;" 
                                 aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= htmlspecialchars($make) ?> (<?= $percentage ?>%)
                            </div>
                        </div>
                        <?php $colorIndex = ($colorIndex + 1) % count($progressColors); ?>
                    <?php endforeach; ?>
                    </div>
                    <div class="tab-pane fade" id="real-estate" role="tabpanel" aria-labelledby="real-estate-tab">
                        <?php
                        // Получаем статистику по недвижимости
                        $realEstateTypes = ['apartment' => 'Квартиры', 'house' => 'Дома', 'commercial' => 'Коммерческая', 'land' => 'Земельные участки'];
                        $realEstateStats = [];
                        
                        // Инициализируем массив со статистикой
                        foreach ($realEstateTypes as $type => $typeName) {
                            $realEstateStats[$type] = 0;
                        }
                        
                        // Считаем количество объектов недвижимости по типам
                        $allRealEstateObjects = isset($realEstate) ? $realEstate->getAllRealEstate() : [];
                        $totalRealEstate = count($allRealEstateObjects);
                        
                        foreach ($allRealEstateObjects as $reObject) {
                            if (isset($realEstateStats[$reObject['type']])) {
                                $realEstateStats[$reObject['type']]++;
                            }
                        }
                        
                        // Вычисляем проценты
                        $typePercentages = [];
                        foreach ($realEstateStats as $type => $count) {
                            $typePercentages[$type] = $totalRealEstate > 0 ? round(($count / $totalRealEstate) * 100) : 0;
                        }
                        
                        // Назначаем классы Bootstrap для прогресс-баров
                        $reProgressColors = ['bg-info', 'bg-success', 'bg-warning', 'bg-danger'];
                        ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Статистика по типам недвижимости</label>
                            <?php $colorIndex = 0; ?>
                            <?php foreach ($typePercentages as $type => $percentage): ?>
                                <div class="progress mb-2" style="height: 20px;">
                                    <div class="progress-bar <?= $reProgressColors[$colorIndex] ?>" role="progressbar" 
                                         style="width: <?= $percentage ?>%;" 
                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= htmlspecialchars($realEstateTypes[$type]) ?> (<?= $percentage ?>%)
                                    </div>
                                </div>
                                <?php $colorIndex = ($colorIndex + 1) % count($reProgressColors); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-card mt-4">
                <h5>Недавние действия</h5>
                
                <?php
                // Получаем последние обновленные заявки
                $allApplications = $applications->getAllApplications(10);
                
                // Сортируем по дате обновления
                usort($allApplications, function($a, $b) {
                    return strtotime($b['updated_at']) - strtotime($a['updated_at']);
                });
                
                // Ограничиваем количество показываемых действий
                $allApplications = array_slice($allApplications, 0, 4);
                ?>
                
                <ul class="list-group list-group-flush">
                    <?php foreach ($allApplications as $application): 
                        $actionText = '';
                        
                        if (empty($application['manager_id'])) {
                            $actionText = 'Новая заявка без менеджера';
                            $detailText = 'Заявка A-' . $application['id'] . ' ожидает назначения';
                        } else {
                            switch ($application['status']) {
                                case 'new':
                                    $actionText = 'Менеджер назначен';
                                    $detailText = $application['manager_first_name'] . ' ' . $application['manager_last_name'] . ' назначен на заявку A-' . $application['id'];
                                    break;
                                case 'in_progress':
                                    $actionText = 'Заявка в обработке';
                                    $detailText = 'Заявка A-' . $application['id'] . ' взята в работу менеджером';
                                    break;
                                case 'approved':
                                    $actionText = 'Заявка одобрена';
                                    $detailText = 'Заявка A-' . $application['id'] . ' одобрена менеджером';
                                    break;
                                case 'rejected':
                                    $actionText = 'Заявка отклонена';
                                    $detailText = 'Заявка A-' . $application['id'] . ' отклонена менеджером';
                                    break;
                                default:
                                    $actionText = 'Обновление статуса';
                                    $detailText = 'Статус заявки A-' . $application['id'] . ' изменен на ' . APPLICATION_STATUS[$application['status']];
                                    break;
                            }
                        }
                    ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong><?= htmlspecialchars($actionText) ?></strong>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($detailText) ?></p>
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
            </div>
            
            <div class="dashboard-card mt-4">
                <h5>Быстрые действия</h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addManagerModal">
                        <i class="fas fa-user-plus me-2"></i> Добавить менеджера
                    </button>
                    <button class="btn btn-success">
                        <i class="fas fa-car me-2"></i> Добавить автомобиль
                    </button>
                    <button class="btn btn-info text-white">
                        <i class="fas fa-building me-2"></i> Добавить недвижимость
                    </button>
                    <button class="btn btn-secondary">
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

<!-- Модальное окно для добавления менеджера -->
<div class="modal fade" id="addManagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить нового менеджера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="add_manager">
                    
                    <div class="mb-3">
                        <label for="manager_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="manager_email" name="email" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col">
                            <label for="manager_fname" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="manager_fname" name="first_name" required>
                        </div>
                        <div class="col">
                            <label for="manager_lname" class="form-label">Фамилия</label>
                            <input type="text" class="form-control" id="manager_lname" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manager_phone" class="form-label">Телефон</label>
                        <input type="tel" class="form-control" id="manager_phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="manager_password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="manager_password" name="password" required>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Добавить менеджера</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>