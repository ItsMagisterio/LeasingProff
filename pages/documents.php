<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Определение ролей пользователей
$USER_ROLES = [
    'admin' => 'Администратор',
    'manager' => 'Менеджер',
    'client' => 'Клиент'
];

// Получаем заявки пользователя для отображения документов
$userApplications = $applications->getAllApplications(0, 0, ['user_id' => $currentUser['id']]);

// Имитация списка документов для каждой заявки
$applicationDocuments = [];
if ($userApplications) {
    foreach ($userApplications as $application) {
        $applicationDocuments[$application['id']] = [
            [
                'name' => 'Договор лизинга',
                'date' => date('Y-m-d', strtotime($application['created_at'] . ' +3 days')),
                'status' => $application['status'] == 'approved' || $application['status'] == 'signed' ? 'available' : 'pending'
            ],
            [
                'name' => 'Акт приема-передачи',
                'date' => date('Y-m-d', strtotime($application['created_at'] . ' +5 days')),
                'status' => $application['status'] == 'signed' ? 'available' : 'pending'
            ],
            [
                'name' => 'График платежей',
                'date' => date('Y-m-d', strtotime($application['created_at'] . ' +3 days')),
                'status' => $application['status'] == 'approved' || $application['status'] == 'signed' ? 'available' : 'pending'
            ]
        ];
    }
}
?>

<!-- Страница документов пользователя -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Панель управления</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-client">Заявки</a>
                <a href="index.php?page=profile">Профиль</a>
                <a href="index.php?page=documents" class="active">Документы</a>
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-link text-white p-0 ms-3">Выход</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Мои документы</h5>
            <?php if (!empty($userApplications)): ?>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="downloadOptions" data-bs-toggle="dropdown" aria-expanded="false">
                    Скачать все
                </button>
                <ul class="dropdown-menu" aria-labelledby="downloadOptions">
                    <li><a class="dropdown-item" href="#">В формате PDF</a></li>
                    <li><a class="dropdown-item" href="#">В формате ZIP</a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($userApplications) && !empty($applicationDocuments)): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Название документа</th>
                            <th>Заявка</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userApplications as $application): 
                            if (isset($applicationDocuments[$application['id']])):
                                foreach ($applicationDocuments[$application['id']] as $document):
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($document['name']) ?></td>
                                <td>
                                    <?php if ($application['type'] === 'real_estate' && !empty($application['real_estate_title'])): ?>
                                        <?= htmlspecialchars($application['real_estate_title']) ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($application['vehicle_make'] . ' ' . $application['vehicle_model']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y', strtotime($document['date'])) ?></td>
                                <td>
                                    <?php if ($document['status'] === 'available'): ?>
                                        <span class="badge bg-success">Доступен</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Ожидание</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($document['status'] === 'available'): ?>
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-sm btn-outline-primary">Просмотр</a>
                                            <a href="#" class="btn btn-sm btn-outline-success">Скачать</a>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>Недоступен</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>У вас пока нет документов для просмотра. Они появятся после одобрения ваших заявок на лизинг.</p>
                <a href="index.php" class="btn btn-primary mt-3">Перейти к калькулятору лизинга</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="dashboard-card mt-4">
        <h5>Загрузка документов</h5>
        <p class="text-muted">Загрузите необходимые документы для ускорения рассмотрения ваших заявок</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="passportScan" class="form-label">Скан паспорта (первая страница)</label>
                    <input class="form-control" type="file" id="passportScan">
                </div>
                
                <div class="mb-3">
                    <label for="passportRegistration" class="form-label">Скан паспорта (прописка)</label>
                    <input class="form-control" type="file" id="passportRegistration">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="incomeCertificate" class="form-label">Справка о доходах</label>
                    <input class="form-control" type="file" id="incomeCertificate">
                </div>
                
                <div class="mb-3">
                    <label for="additionalDocument" class="form-label">Дополнительный документ</label>
                    <input class="form-control" type="file" id="additionalDocument">
                </div>
            </div>
        </div>
        
        <button type="button" class="btn btn-primary mt-2">Загрузить документы</button>
    </div>
</div>