<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Определение ролей пользователей
$USER_ROLES = [
    'admin' => 'Администратор',
    'manager' => 'Менеджер',
    'client' => 'Клиент'
];

// Обработка формы редактирования профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    // Проверка данных
    if (empty($first_name) || empty($last_name) || empty($phone)) {
        $error = 'Все поля должны быть заполнены';
    } else {
        // Обновляем профиль
        $users = new Users();
        $result = $users->updateUser($currentUser['id'], [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone
        ]);
        
        if ($result) {
            $success = 'Ваш профиль успешно обновлен';
            // Обновляем данные пользователя в сессии
            $currentUser = $auth->getCurrentUser(true);
        } else {
            $error = 'Ошибка при обновлении профиля';
        }
    }
}
?>

<!-- Профиль пользователя -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Панель управления</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-client">Заявки</a>
                <a href="index.php?page=profile" class="active">Профиль</a>
                <a href="index.php?page=documents">Документы</a>
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
                <p class="text-muted"><?= isset($currentUser['role']) && isset($USER_ROLES[$currentUser['role']]) ? htmlspecialchars($USER_ROLES[$currentUser['role']]) : 'Пользователь' ?></p>
                <hr>
                <p><i class="fas fa-envelope me-2 text-muted"></i> <?= htmlspecialchars($currentUser['email']) ?></p>
                <p><i class="fas fa-phone me-2 text-muted"></i> <?= htmlspecialchars($currentUser['phone']) ?></p>
                <p><i class="fas fa-calendar me-2 text-muted"></i> С нами с <?= date('d.m.Y', strtotime($currentUser['created_at'])) ?></p>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="dashboard-card">
                <h5>Редактирование профиля</h5>
                <form method="post" action="index.php?page=profile">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly disabled>
                        <div class="form-text">Email используется для входа и не может быть изменен</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($currentUser['first_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Фамилия</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($currentUser['last_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($currentUser['phone']) ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </form>
            </div>
            
            <div class="dashboard-card mt-4">
                <h5>Изменение пароля</h5>
                <form method="post" action="index.php?page=profile">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Текущий пароль</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <div class="form-text">Пароль должен содержать не менее 8 символов</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтверждение пароля</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Изменить пароль</button>
                </form>
            </div>
        </div>
    </div>
</div>