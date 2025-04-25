<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Загружаем текущие настройки системы
$currentSettings = loadSettings();
?>

<!-- Страница настроек системы -->
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
                <h4 class="mb-0">Настройки системы</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-admin" class="<?= $page === 'dashboard-admin' ? 'active' : '' ?>">Обзор</a>
                <a href="index.php?page=user-rights" class="<?= $page === 'user-rights' ? 'active' : '' ?>">Права пользователей</a>
                <a href="index.php?page=leasing-companies" class="<?= $page === 'leasing-companies' ? 'active' : '' ?>">Лизинговые компании</a>
                <a href="index.php?page=managers" class="<?= $page === 'managers' ? 'active' : '' ?>">Менеджеры</a>
                <a href="index.php?page=dashboard-clients" class="<?= $page === 'dashboard-clients' ? 'active' : '' ?>">Клиенты</a>
                <a href="index.php?page=settings" class="<?= $page === 'settings' ? 'active' : '' ?>">Настройки</a>
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-link text-white p-0 ms-3">Выход</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="dashboard-card mt-4">
        <form method="post">
            <input type="hidden" name="action" value="update_settings">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Общие настройки</h5>
                    
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Название сайта</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($currentSettings['site_name'] ?? 'Лизинг Платформа') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Описание сайта</label>
                        <textarea class="form-control auto-resize" id="site_description" name="site_description" rows="3"><?= htmlspecialchars($currentSettings['site_description'] ?? 'Платформа для лизинга автомобилей и недвижимости') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Контактный email</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($currentSettings['contact_email'] ?? 'contact@leasing.org') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Контактный телефон</label>
                        <input type="tel" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($currentSettings['contact_phone'] ?? '+7 (123) 456-78-90') ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5>Настройки лизинга</h5>
                    
                    <div class="mb-3">
                        <label for="min_vehicle_price" class="form-label">Минимальная стоимость автомобиля (₽)</label>
                        <input type="number" class="form-control" id="min_vehicle_price" name="min_vehicle_price" value="<?= (int)($currentSettings['min_vehicle_price'] ?? 500000) ?>" min="0" step="1000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_vehicle_price" class="form-label">Максимальная стоимость автомобиля (₽)</label>
                        <input type="number" class="form-control" id="max_vehicle_price" name="max_vehicle_price" value="<?= (int)($currentSettings['max_vehicle_price'] ?? 20000000) ?>" min="0" step="1000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="min_real_estate_price" class="form-label">Минимальная стоимость недвижимости (₽)</label>
                        <input type="number" class="form-control" id="min_real_estate_price" name="min_real_estate_price" value="<?= (int)($currentSettings['min_real_estate_price'] ?? 1000000) ?>" min="0" step="1000" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_real_estate_price" class="form-label">Максимальная стоимость недвижимости (₽)</label>
                        <input type="number" class="form-control" id="max_real_estate_price" name="max_real_estate_price" value="<?= (int)($currentSettings['max_real_estate_price'] ?? 100000000) ?>" min="0" step="1000" required>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5>Настройки уведомлений</h5>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" <?= isset($currentSettings['email_notifications']) && $currentSettings['email_notifications'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="email_notifications">Email-уведомления</label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" value="1" <?= isset($currentSettings['sms_notifications']) && $currentSettings['sms_notifications'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="sms_notifications">SMS-уведомления</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Сохранить настройки</button>
                </div>
            </div>
        </form>
    </div>
</div>