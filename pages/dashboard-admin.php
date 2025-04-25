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
    
    <!-- Уже добавили вывод сообщений в верхней части страницы -->
    
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
                                            <?= htmlspecialchars((isset($application['vehicle_make']) ? $application['vehicle_make'] : 'Не указано') . ' ' . (isset($application['vehicle_model']) ? $application['vehicle_model'] : '')) ?>
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
                                                        <form method="post" action="index.php?page=dashboard-admin">
                                                            <input type="hidden" name="action" value="assign_manager">
                                                            <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="manager_id_<?= $application['id'] ?>" class="form-label">Выберите менеджера</label>
                                                                <select name="manager_id" id="manager_id_<?= $application['id'] ?>" class="form-select" required>
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
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                        <i class="fas fa-car me-2"></i> Добавить автомобиль
                    </button>
                    <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addRealEstateModal">
                        <i class="fas fa-building me-2"></i> Добавить недвижимость
                    </button>
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportReportModal">
                        <i class="fas fa-file-export me-2"></i> Экспорт отчета
                    </button>
                    <button class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#systemSettingsModal">
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
                <form method="post" action="index.php?page=dashboard-admin">
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

<!-- Модальное окно для добавления автомобиля -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить новый автомобиль</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="index.php?page=dashboard-admin">
                    <input type="hidden" name="action" value="add_vehicle">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vehicle_make" class="form-label">Марка</label>
                            <input type="text" class="form-control" id="vehicle_make" name="make" required>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_model" class="form-label">Модель</label>
                            <input type="text" class="form-control" id="vehicle_model" name="model" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="vehicle_year" class="form-label">Год выпуска</label>
                            <input type="number" class="form-control" id="vehicle_year" name="year" min="1900" max="2030" required>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_engine" class="form-label">Двигатель</label>
                            <input type="text" class="form-control" id="vehicle_engine" name="engine" required>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_power" class="form-label">Мощность (л.с.)</label>
                            <input type="number" class="form-control" id="vehicle_power" name="power" min="1" max="2000" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="vehicle_drive_type" class="form-label">Привод</label>
                            <select class="form-select" id="vehicle_drive_type" name="drive_type" required>
                                <option value="">Выберите тип привода</option>
                                <option value="front">Передний</option>
                                <option value="rear">Задний</option>
                                <option value="all">Полный</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_transmission" class="form-label">Коробка передач</label>
                            <select class="form-select" id="vehicle_transmission" name="transmission" required>
                                <option value="">Выберите тип КПП</option>
                                <option value="manual">Механическая</option>
                                <option value="automatic">Автоматическая</option>
                                <option value="robot">Робот</option>
                                <option value="variator">Вариатор</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="vehicle_color" class="form-label">Цвет</label>
                            <input type="text" class="form-control" id="vehicle_color" name="color" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_interior" class="form-label">Тип обивки салона</label>
                        <input type="text" class="form-control" id="vehicle_interior" name="interior" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_features" class="form-label">Особенности и опции</label>
                        <textarea class="form-control" id="vehicle_features" name="features" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_image_url" class="form-label">URL изображения</label>
                        <input type="url" class="form-control" id="vehicle_image_url" name="image_url" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vehicle_price" class="form-label">Стоимость (₽)</label>
                            <input type="number" class="form-control" id="vehicle_price" name="price" min="0" step="1000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_monthly_payment" class="form-label">Ежемесячный платеж (₽)</label>
                            <input type="number" class="form-control" id="vehicle_monthly_payment" name="monthly_payment" min="0" step="100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="vehicle_status" class="form-label">Статус</label>
                        <select class="form-select" id="vehicle_status" name="status" required>
                            <option value="available">Доступен</option>
                            <option value="reserved">Зарезервирован</option>
                            <option value="sold">Продан</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success">Добавить автомобиль</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для добавления недвижимости -->
<div class="modal fade" id="addRealEstateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить объект недвижимости</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="index.php?page=dashboard-admin">
                    <input type="hidden" name="action" value="add_real_estate">
                    
                    <div class="mb-3">
                        <label for="real_estate_title" class="form-label">Название объекта</label>
                        <input type="text" class="form-control" id="real_estate_title" name="title" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="real_estate_type" class="form-label">Тип недвижимости</label>
                            <select class="form-select" id="real_estate_type" name="type" required>
                                <option value="">Выберите тип недвижимости</option>
                                <option value="apartment">Квартира</option>
                                <option value="house">Дом</option>
                                <option value="commercial">Коммерческая недвижимость</option>
                                <option value="land">Земельный участок</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="real_estate_status" class="form-label">Статус</label>
                            <select class="form-select" id="real_estate_status" name="status" required>
                                <option value="available">Доступен</option>
                                <option value="reserved">Зарезервирован</option>
                                <option value="sold">Продан</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="real_estate_area" class="form-label">Площадь (м²)</label>
                            <input type="number" class="form-control" id="real_estate_area" name="area" min="1" step="0.1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="real_estate_rooms" class="form-label">Количество комнат</label>
                            <input type="number" class="form-control" id="real_estate_rooms" name="rooms" min="0">
                        </div>
                        <div class="col-md-4">
                            <label for="real_estate_floor" class="form-label">Этаж</label>
                            <input type="number" class="form-control" id="real_estate_floor" name="floor" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="real_estate_address" class="form-label">Адрес</label>
                        <input type="text" class="form-control" id="real_estate_address" name="address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="real_estate_description" class="form-label">Описание</label>
                        <textarea class="form-control" id="real_estate_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="real_estate_features" class="form-label">Особенности и удобства</label>
                        <textarea class="form-control" id="real_estate_features" name="features" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="real_estate_image_url" class="form-label">URL изображения</label>
                        <input type="url" class="form-control" id="real_estate_image_url" name="image_url" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="real_estate_price" class="form-label">Стоимость (₽)</label>
                            <input type="number" class="form-control" id="real_estate_price" name="price" min="0" step="1000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="real_estate_monthly_payment" class="form-label">Ежемесячный платеж (₽)</label>
                            <input type="number" class="form-control" id="real_estate_monthly_payment" name="monthly_payment" min="0" step="100" required>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-info text-white">Добавить объект</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для экспорта отчета -->
<div class="modal fade" id="exportReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Экспорт отчета</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="index.php?page=dashboard-admin">
                    <input type="hidden" name="action" value="export_report">
                    
                    <div class="mb-3">
                        <label for="report_type" class="form-label">Тип отчета</label>
                        <select class="form-select" id="report_type" name="report_type" required>
                            <option value="">Выберите тип отчета</option>
                            <option value="applications">Заявки</option>
                            <option value="vehicles">Автомобили</option>
                            <option value="real_estate">Недвижимость</option>
                            <option value="users">Пользователи</option>
                            <option value="managers">Менеджеры</option>
                        </select>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_from" class="form-label">Дата начала</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_to" class="form-label">Дата окончания</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Формат экспорта</label>
                        <select class="form-select" id="export_format" name="export_format" required>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Экспортировать</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для настроек системы -->
<div class="modal fade" id="systemSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Настройки системы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="index.php?page=dashboard-admin">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Название сайта</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="лизинг.орг" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Email администратора</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" value="admin@2leasing.ru" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="default_language" class="form-label">Язык по умолчанию</label>
                        <select class="form-select" id="default_language" name="default_language" required>
                            <option value="ru" selected>Русский</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="items_per_page" class="form-label">Элементов на странице</label>
                        <input type="number" class="form-control" id="items_per_page" name="items_per_page" value="10" min="5" max="100" required>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1">
                        <label class="form-check-label" for="maintenance_mode">Режим обслуживания</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="enable_registration" name="enable_registration" value="1" checked>
                        <label class="form-check-label" for="enable_registration">Разрешить регистрацию</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" value="1">
                        <label class="form-check-label" for="debug_mode">Режим отладки</label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-warning text-dark">Сохранить настройки</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>