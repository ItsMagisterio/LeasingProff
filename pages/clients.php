<?php
// Проверка авторизации и уровня доступа
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Если пользователь не админ - перенаправляем на главную
    header('Location: index.php');
    exit;
}

// Получаем список всех клиентов
require_once 'modules/Users.php';
$users = new Users();
$clientsList = $users->getUsersByRole('client');

// Отображение сообщений об ошибках и уведомлений
$messages = [];
if (isset($_SESSION['message'])) {
    $messages[] = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Обработка действий с клиентами
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Блокировка/Разблокировка клиента
    if ($action === 'toggle_client_status' && isset($_POST['client_id'])) {
        $clientId = $_POST['client_id'];
        $client = $users->getUserById($clientId);
        
        if ($client && $client['role'] === 'client') {
            $newStatus = isset($client['is_active']) && $client['is_active'] ? 0 : 1;
            $success = $users->updateUserStatus($clientId, $newStatus);
            
            if ($success) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Статус клиента успешно изменен'
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'danger',
                    'text' => 'Ошибка при изменении статуса клиента'
                ];
            }
        }
        header('Location: index.php?page=clients');
        exit;
    }
}
?>

<div class="container-fluid dashboard my-4">
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Управление клиентами</h1>
                <div class="dashboard-nav">
                    <a href="index.php?page=dashboard-admin" class="<?= $page === 'dashboard-admin' ? 'active' : '' ?>">Обзор</a>
                    <a href="index.php?page=user-rights" class="<?= $page === 'user-rights' ? 'active' : '' ?>">Права пользователей</a>
                    <a href="index.php?page=leasing-companies" class="<?= $page === 'leasing-companies' ? 'active' : '' ?>">Лизинговые компании</a>
                    <a href="index.php?page=managers" class="<?= $page === 'managers' ? 'active' : '' ?>">Менеджеры</a>
                    <a href="index.php?page=clients" class="<?= $page === 'clients' ? 'active' : '' ?>">Клиенты</a>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="btn btn-link text-white p-0 ms-3">Выход</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <?php if (is_array($message)): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mt-3" role="alert">
                    <?= $message['text'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php else: ?>
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Список клиентов</h5>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Дата регистрации</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientsList as $client): ?>
                    <tr>
                        <td><?= $client['id'] ?></td>
                        <td><?= htmlspecialchars($client['first_name']) ?></td>
                        <td><?= htmlspecialchars($client['last_name']) ?></td>
                        <td><?= htmlspecialchars($client['email']) ?></td>
                        <td><?= htmlspecialchars($client['phone'] ?? 'Не указан') ?></td>
                        <td><?= isset($client['created_at']) ? date('d.m.Y', strtotime($client['created_at'])) : 'Не указана' ?></td>
                        <td>
                            <?php if (isset($client['is_active']) && $client['is_active']): ?>
                                <span class="badge bg-success">Активен</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Заблокирован</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?page=application-details&client_id=<?= $client['id'] ?>" class="btn btn-outline-primary">Заявки</a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите <?= (isset($client['is_active']) && $client['is_active']) ? 'заблокировать' : 'активировать' ?> этого клиента?');">
                                    <input type="hidden" name="action" value="toggle_client_status">
                                    <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                                    <button type="submit" class="btn btn-outline-<?= (isset($client['is_active']) && $client['is_active']) ? 'danger' : 'success' ?>">
                                        <?= (isset($client['is_active']) && $client['is_active']) ? 'Блокировать' : 'Активировать' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>