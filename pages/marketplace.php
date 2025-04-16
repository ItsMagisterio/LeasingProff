<?php
// Получаем все автомобили
$filters = [];

// Применяем фильтры из GET-параметров
if (isset($_GET['make']) && $_GET['make']) {
    $filters['make'] = $_GET['make'];
}

if (isset($_GET['model']) && $_GET['model']) {
    $filters['model'] = $_GET['model'];
}

if (isset($_GET['min_price']) && $_GET['min_price']) {
    $filters['min_price'] = (float)$_GET['min_price'];
}

if (isset($_GET['max_price']) && $_GET['max_price']) {
    $filters['max_price'] = (float)$_GET['max_price'];
}

// Показываем только доступные автомобили
$filters['status'] = 'available';

// Пагинация
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Получаем автомобили с учетом пагинации
$allVehicles = $vehicles->getAllVehicles($perPage, $offset, $filters);

// Получаем общее количество автомобилей для пагинации
$totalVehicles = $vehicles->getVehiclesCount($filters);
$totalPages = ceil($totalVehicles / $perPage);

// Получаем все марки для фильтра
$allMakes = $vehicles->getVehicleMakes();

// Получаем модели для выбранной марки
$models = [];
if (isset($filters['make'])) {
    $models = $vehicles->getModelsByMake($filters['make']);
}
?>

<!-- Маркетплейс автомобилей -->
<div class="container py-5">
    <h1 class="mb-4">Каталог автомобилей</h1>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3" method="get" action="index.php">
                        <input type="hidden" name="page" value="marketplace">
                        
                        <div class="col-md-3">
                            <label class="form-label">Марка</label>
                            <select class="form-select" name="make" id="make-select" onchange="this.form.submit()">
                                <option value="">Все марки</option>
                                <?php foreach ($allMakes as $make): ?>
                                <option value="<?= htmlspecialchars($make) ?>" <?= isset($filters['make']) && $filters['make'] == $make ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($make) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Модель</label>
                            <select class="form-select" name="model" id="model-select" <?= empty($models) ? 'disabled' : '' ?>>
                                <option value="">Все модели</option>
                                <?php foreach ($models as $model): ?>
                                <option value="<?= htmlspecialchars($model) ?>" <?= isset($filters['model']) && $filters['model'] == $model ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($model) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Цена от</label>
                            <input type="number" class="form-control" name="min_price" placeholder="₽" value="<?= isset($filters['min_price']) ? $filters['min_price'] : '' ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Цена до</label>
                            <input type="number" class="form-control" name="max_price" placeholder="₽" value="<?= isset($filters['max_price']) ? $filters['max_price'] : '' ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Найти</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($allVehicles): ?>
        <div class="row g-4">
            <?php foreach ($allVehicles as $vehicle): 
                // Разбиваем строку с характеристиками на массив
                $features = explode(',', $vehicle['features']);
            ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 vehicle-card">
                        <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" alt="<?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?>" class="card-img-top vehicle-img">
                        <div class="card-body">
                            <h4><?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?></h4>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="vehicle-price mb-0"><?= number_format($vehicle['monthly_payment'], 0, ',', ' ') ?> ₽/мес</p>
                                <span class="badge bg-secondary"><?= number_format($vehicle['price'], 0, ',', ' ') ?> ₽</span>
                            </div>
                            <p class="text-muted">
                                <?= htmlspecialchars($vehicle['year']) ?> г., 
                                <?= htmlspecialchars($vehicle['engine']) ?>, 
                                <?= htmlspecialchars($vehicle['power']) ?> л.с., 
                                <?= htmlspecialchars($vehicle['transmission']) ?>
                            </p>
                            <h6 class="mt-3">Характеристики:</h6>
                            <ul class="vehicle-features">
                                <?php 
                                // Показываем до 4 характеристик
                                $featureCount = 0;
                                foreach ($features as $feature) {
                                    if ($featureCount < 4) {
                                        echo '<li><i class="fas fa-check-circle text-success me-2"></i>' . htmlspecialchars(trim($feature)) . '</li>';
                                        $featureCount++;
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-grid">
                                <a href="index.php?page=vehicle&id=<?= $vehicle['id'] ?>" class="btn btn-primary">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=marketplace&p=<?= $page - 1 ?><?= isset($filters['make']) ? '&make=' . urlencode($filters['make']) : '' ?><?= isset($filters['model']) ? '&model=' . urlencode($filters['model']) : '' ?><?= isset($filters['min_price']) ? '&min_price=' . $filters['min_price'] : '' ?><?= isset($filters['max_price']) ? '&max_price=' . $filters['max_price'] : '' ?>">Предыдущая</a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=marketplace&p=<?= $i ?><?= isset($filters['make']) ? '&make=' . urlencode($filters['make']) : '' ?><?= isset($filters['model']) ? '&model=' . urlencode($filters['model']) : '' ?><?= isset($filters['min_price']) ? '&min_price=' . $filters['min_price'] : '' ?><?= isset($filters['max_price']) ? '&max_price=' . $filters['max_price'] : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=marketplace&p=<?= $page + 1 ?><?= isset($filters['make']) ? '&make=' . urlencode($filters['make']) : '' ?><?= isset($filters['model']) ? '&model=' . urlencode($filters['model']) : '' ?><?= isset($filters['min_price']) ? '&min_price=' . $filters['min_price'] : '' ?><?= isset($filters['max_price']) ? '&max_price=' . $filters['max_price'] : '' ?>">Следующая</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-info">
            По вашему запросу автомобили не найдены. Пожалуйста, измените критерии поиска.
        </div>
    <?php endif; ?>
</div>

<script>
// Обработчик изменения марки автомобиля
document.getElementById('make-select').addEventListener('change', function() {
    // Обновляем форму при выборе марки
    this.form.submit();
});
</script>