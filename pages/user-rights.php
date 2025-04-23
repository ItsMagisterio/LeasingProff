<?php
// Проверяем права доступа - только администратор может управлять правами
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

// Получаем список всех пользователей
$allUsers = $users->getAllUsers();

// Обработка изменения прав пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user_rights') {
    $userId = (int)$_POST['user_id'];
    $newRole = $_POST['role'];
    
    // Проверка валидности роли
    if (in_array($newRole, ['client', 'manager', 'admin'])) {
        $result = $users->updateUserRole($userId, $newRole);
        
        if ($result['success']) {
            $success = 'Права пользователя успешно обновлены';
            // Обновляем список пользователей
            $allUsers = $users->getAllUsers();
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Некорректная роль пользователя';
    }
}
?>

<!-- Страница управления правами пользователей -->
<div class="container user-dashboard">
    <div class="dashboard-nav">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0">Управление правами пользователей</h4>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="index.php?page=dashboard-admin">Обзор</a>
                <a href="index.php?page=user-rights" class="active">Права пользователей</a>
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
    
    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Список пользователей</h5>
            <div class="input-group" style="max-width: 300px;">
                <input type="text" class="form-control" id="searchUser" placeholder="Поиск пользователя...">
                <button class="btn btn-outline-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        
        <?php if ($allUsers): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUsers as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : ($user['role'] === 'manager' ? 'bg-success' : 'bg-info') ?>">
                                    <?= htmlspecialchars(ucfirst($user['role'])) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editRightsModal<?= $user['id'] ?>">
                                    <i class="fas fa-edit me-1"></i> Изменить права
                                </button>
                                
                                <!-- Модальное окно для изменения прав -->
                                <div class="modal fade" id="editRightsModal<?= $user['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Изменение прав пользователя</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Пользователь: <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong></p>
                                                <p>Email: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
                                                
                                                <form method="post">
                                                    <input type="hidden" name="action" value="update_user_rights">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Роль пользователя</label>
                                                        <select name="role" class="form-select" required>
                                                            <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Клиент</option>
                                                            <option value="manager" <?= $user['role'] === 'manager' ? 'selected' : '' ?>>Менеджер</option>
                                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="alert alert-warning">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        Внимание! Изменение роли предоставит пользователю соответствующие права доступа.
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
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
            
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Предыдущая</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Следующая</a>
                    </li>
                </ul>
            </nav>
        <?php else: ?>
            <div class="alert alert-info">Пользователи не найдены</div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Скрипт для поиска пользователей
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchUser');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const table = document.querySelector('table');
                const tr = table.getElementsByTagName('tr');
                
                for (let i = 1; i < tr.length; i++) {
                    const td = tr[i].getElementsByTagName('td');
                    let txtValue = '';
                    
                    // Собираем текст из ячеек для поиска
                    for (let j = 0; j < 3; j++) {
                        txtValue += td[j].textContent || td[j].innerText;
                    }
                    
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            });
        }
    });
</script>