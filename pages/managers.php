<?php
// Получаем данные текущего пользователя
$currentUser = $auth->getCurrentUser();

// Получаем список менеджеров
$managersList = $users->getManagers();
?>

<!-- Страница управления менеджерами -->
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
                <h4 class="mb-0">Управление менеджерами</h4>
            </div>
            <div class="col-md-6 text-md-end">
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
    
    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Список менеджеров</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addManagerModal">
                Добавить менеджера
            </button>
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
                    <?php foreach ($managersList as $manager): ?>
                    <tr>
                        <td><?= $manager['id'] ?></td>
                        <td><?= htmlspecialchars($manager['first_name']) ?></td>
                        <td><?= htmlspecialchars($manager['last_name']) ?></td>
                        <td><?= htmlspecialchars($manager['email']) ?></td>
                        <td><?= htmlspecialchars($manager['phone'] ?? 'Не указан') ?></td>
                        <td><?= isset($manager['created_at']) ? date('d.m.Y', strtotime($manager['created_at'])) : 'Не указана' ?></td>
                        <td>
                            <?php if (isset($manager['is_active']) && $manager['is_active']): ?>
                                <span class="badge bg-success">Активен</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Заблокирован</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?page=manager-applications&manager_id=<?= $manager['id'] ?>" class="btn btn-outline-primary">Заявки</a>
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editManagerModal_<?= $manager['id'] ?>">
                                    Изменить
                                </button>
                            </div>
                            
                            <!-- Модальное окно редактирования менеджера -->
                            <div class="modal fade" id="editManagerModal_<?= $manager['id'] ?>" tabindex="-1" aria-labelledby="editManagerModalLabel_<?= $manager['id'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editManagerModalLabel_<?= $manager['id'] ?>">Редактирование менеджера</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit_manager">
                                                <input type="hidden" name="manager_id" value="<?= $manager['id'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="first_name_<?= $manager['id'] ?>" class="form-label">Имя</label>
                                                    <input type="text" class="form-control" id="first_name_<?= $manager['id'] ?>" name="first_name" value="<?= htmlspecialchars($manager['first_name']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="last_name_<?= $manager['id'] ?>" class="form-label">Фамилия</label>
                                                    <input type="text" class="form-control" id="last_name_<?= $manager['id'] ?>" name="last_name" value="<?= htmlspecialchars($manager['last_name']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="email_<?= $manager['id'] ?>" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email_<?= $manager['id'] ?>" name="email" value="<?= htmlspecialchars($manager['email']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="phone_<?= $manager['id'] ?>" class="form-label">Телефон</label>
                                                    <input type="tel" class="form-control" id="phone_<?= $manager['id'] ?>" name="phone" value="<?= htmlspecialchars($manager['phone'] ?? '') ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="password_<?= $manager['id'] ?>" class="form-label">Новый пароль (оставьте пустым, чтобы не менять)</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="password_<?= $manager['id'] ?>" name="password">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_<?= $manager['id'] ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-primary">Сохранить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Модальное окно добавления менеджера -->
    <div class="modal fade" id="addManagerModal" tabindex="-1" aria-labelledby="addManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addManagerModalLabel">Добавление нового менеджера</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_manager">
                        
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>