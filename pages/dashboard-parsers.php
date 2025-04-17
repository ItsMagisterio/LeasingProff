<?php
// Проверка авторизации
if (!$auth->isLoggedIn() || !($auth->isAdmin() || $auth->isManager())) {
    header('Location: index.php?page=login');
    exit;
}

// Инициализируем парсер
$parserManager = new ParserManager();

// Обработка запросов на парсинг
$parsingResults = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'parse_all') {
        $parsingResults = $parserManager->parseAll();
        $success = "Парсинг выполнен. Обработано сайтов: {$parsingResults['total']}, успешно: {$parsingResults['success']}";
    } 
    elseif ($action === 'parse_site' && isset($_POST['site'])) {
        $siteKey = $_POST['site'];
        $result = $parserManager->parseSite($siteKey);
        
        if ($result['success']) {
            $success = "Сайт успешно обработан: {$result['message']}";
        } else {
            $error = "Ошибка при обработке сайта: {$result['message']}";
        }
    }
}

// Получаем список всех парсеров
$parsers = $parserManager->getParsers();

// Получаем информацию о импортированных автомобилях из разных источников
$query = "SELECT source, COUNT(*) as count FROM vehicles WHERE source IS NOT NULL GROUP BY source ORDER BY count DESC";
$sourceStats = pg_query($db->getConnection(), $query);

// HTML заголовок
outputHeader('Управление парсерами');

?>

<div class="container-fluid user-dashboard">
    <div class="row">
        <div class="col-lg-3">
            <!-- Боковая панель -->
            <div class="dashboard-nav">
                <h4 class="mb-4">Панель управления</h4>
                <ul class="nav flex-column">
                    <?php if ($auth->isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=dashboard-admin">
                            <i class="fas fa-tachometer-alt me-2"></i> Обзор
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=dashboard-manager">
                            <i class="fas fa-tachometer-alt me-2"></i> Обзор
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?page=dashboard-parsers">
                            <i class="fas fa-sync-alt me-2"></i> Парсеры
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=marketplace">
                            <i class="fas fa-car me-2"></i> Автомобили
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=real-estate">
                            <i class="fas fa-home me-2"></i> Недвижимость
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-globe me-2"></i> На сайт
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <form method="post" action="index.php">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                                <i class="fas fa-sign-out-alt me-2"></i> Выход
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="col-lg-9">
            <!-- Основной контент -->
            <div class="dashboard-card mb-4">
                <h2 class="mb-4"><i class="fas fa-sync-alt me-2"></i> Управление парсерами</h2>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Запуск парсинга</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="index.php?page=dashboard-parsers">
                            <input type="hidden" name="action" value="parse_all">
                            <p class="mb-3">Запустить парсинг всех включенных сайтов конкурентов.</p>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sync-alt me-2"></i> Запустить парсинг всех сайтов
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Результаты парсинга -->
                <?php if ($parsingResults): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Результаты парсинга</h5>
                    </div>
                    <div class="card-body">
                        <p>Всего сайтов: <strong><?php echo $parsingResults['total']; ?></strong></p>
                        <p>Успешно обработано: <strong><?php echo $parsingResults['success']; ?></strong></p>
                        <p>Не удалось обработать: <strong><?php echo $parsingResults['failed']; ?></strong></p>
                        
                        <div class="list-group mt-3">
                            <?php foreach ($parsingResults['sites'] as $site => $result): ?>
                                <?php 
                                    $statusClass = $result['success'] ? 'list-group-item-success' : 'list-group-item-danger';
                                    $statusIcon = $result['success'] ? 'check-circle' : 'times-circle';
                                ?>
                                <div class="list-group-item <?php echo $statusClass; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            <i class="fas fa-<?php echo $statusIcon; ?> me-2"></i>
                                            <?php echo htmlspecialchars($parsers[$site]['name']); ?>
                                        </h5>
                                        <?php if ($result['success']): ?>
                                        <small>Добавлено: <?php echo $result['count']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($result['message']); ?></p>
                                    <small><?php echo htmlspecialchars($parsers[$site]['url']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Статистика по источникам -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Статистика по источникам</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Источник</th>
                                        <th>Количество автомобилей</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (pg_num_rows($sourceStats) > 0): ?>
                                        <?php while ($row = pg_fetch_assoc($sourceStats)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['source']); ?></td>
                                                <td><?php echo htmlspecialchars($row['count']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Нет данных о импортированных автомобилях</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Список парсеров -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Доступные парсеры</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($parsers as $key => $parser): ?>
                                <div class="col">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($parser['name']); ?></h5>
                                            <p class="card-text">
                                                <a href="<?php echo htmlspecialchars($parser['url']); ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($parser['url']); ?>
                                                </a>
                                            </p>
                                            <p class="card-text">
                                                Статус: 
                                                <?php if ($parser['enabled']): ?>
                                                    <span class="badge bg-success">Включен</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Отключен</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <form method="post" action="index.php?page=dashboard-parsers">
                                                <input type="hidden" name="action" value="parse_site">
                                                <input type="hidden" name="site" value="<?php echo $key; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-sync-alt me-1"></i> Парсить
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// HTML подвал
include 'footer.php';
?>