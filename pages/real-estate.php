<?php
outputHeader('Недвижимость в лизинг');
outputNavigation();

// Получаем параметры фильтрации
$page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Фильтры
$type = isset($_GET['type']) ? $_GET['type'] : '';
$minPrice = isset($_GET['min_price']) ? (int) $_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;
$minArea = isset($_GET['min_area']) ? (float) $_GET['min_area'] : 0;
$maxArea = isset($_GET['max_area']) ? (float) $_GET['max_area'] : 0;
$rooms = isset($_GET['rooms']) ? (int) $_GET['rooms'] : 0;

// Формируем массив фильтров
$filters = ['status' => 'available'];

if (!empty($type)) {
    $filters['type'] = $type;
}

if ($minPrice > 0) {
    $filters['min_price'] = $minPrice;
}

if ($maxPrice > 0) {
    $filters['max_price'] = $maxPrice;
}

if ($minArea > 0) {
    $filters['min_area'] = $minArea;
}

if ($maxArea > 0) {
    $filters['max_area'] = $maxArea;
}

if ($rooms > 0) {
    $filters['rooms'] = $rooms;
}

// Получаем данные недвижимости
$properties = $realEstate->getAllRealEstate($perPage, $offset, $filters);
$totalCount = $realEstate->getRealEstateCount($filters);
$totalPages = ceil($totalCount / $perPage);

// Получаем уникальные типы недвижимости для фильтра
$types = $realEstate->getUniqueRealEstateTypes();

// Получаем минимальную и максимальную стоимость и площадь
$priceRange = $realEstate->getRealEstatePriceRange();
$areaRange = $realEstate->getRealEstateAreaRange();

// Минимальная и максимальная цена для фильтра
$filterMinPrice = $priceRange['min'] ?? 0;
$filterMaxPrice = $priceRange['max'] ?? 10000000;

// Минимальная и максимальная площадь для фильтра
$filterMinArea = $areaRange['min'] ?? 0;
$filterMaxArea = $areaRange['max'] ?? 500;

?>

<div class="container py-5">
    <h1 class="mb-4">Недвижимость в лизинг</h1>
    
    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Фильтры</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="index.php">
                        <input type="hidden" name="page" value="real-estate">
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Тип недвижимости</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Все типы</option>
                                <?php foreach ($types as $propertyType): 
                                    $typeLabel = '';
                                    switch ($propertyType) {
                                        case 'apartment':
                                            $typeLabel = 'Квартира';
                                            break;
                                        case 'house':
                                            $typeLabel = 'Дом';
                                            break;
                                        case 'commercial':
                                            $typeLabel = 'Коммерческая недвижимость';
                                            break;
                                        default:
                                            $typeLabel = ucfirst($propertyType);
                                    }
                                ?>
                                    <option value="<?= $propertyType ?>" <?= $type === $propertyType ? 'selected' : '' ?>><?= htmlspecialchars($typeLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="min_price" class="form-label">Стоимость (₽)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" id="min_price" name="min_price" placeholder="От" value="<?= $minPrice > 0 ? $minPrice : '' ?>" min="<?= $filterMinPrice ?>" max="<?= $filterMaxPrice ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="max_price" name="max_price" placeholder="До" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>" min="<?= $filterMinPrice ?>" max="<?= $filterMaxPrice ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="min_area" class="form-label">Площадь (м²)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" id="min_area" name="min_area" placeholder="От" value="<?= $minArea > 0 ? $minArea : '' ?>" min="<?= $filterMinArea ?>" max="<?= $filterMaxArea ?>" step="0.1">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" id="max_area" name="max_area" placeholder="До" value="<?= $maxArea > 0 ? $maxArea : '' ?>" min="<?= $filterMinArea ?>" max="<?= $filterMaxArea ?>" step="0.1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="rooms" class="form-label">Количество комнат</label>
                            <select class="form-select" id="rooms" name="rooms">
                                <option value="">Любое</option>
                                <option value="1" <?= $rooms === 1 ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= $rooms === 2 ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= $rooms === 3 ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= $rooms === 4 ? 'selected' : '' ?>>4+</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Применить</button>
                            <a href="index.php?page=real-estate" class="btn btn-outline-secondary">Сбросить</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if (!empty($properties)): ?>
                <div class="row g-4">
                    <?php foreach ($properties as $property): 
                        // Определяем название типа для отображения
                        $typeLabel = '';
                        switch ($property['type']) {
                            case 'apartment':
                                $typeLabel = 'Квартира';
                                break;
                            case 'house':
                                $typeLabel = 'Дом';
                                break;
                            case 'commercial':
                                $typeLabel = 'Коммерческая недвижимость';
                                break;
                            default:
                                $typeLabel = ucfirst($property['type']);
                        }
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 real-estate-card">
                                <img src="<?= htmlspecialchars($property['image_url']) ?>" alt="<?= htmlspecialchars($property['title']) ?>" class="card-img-top real-estate-img" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h4><?= htmlspecialchars($property['title']) ?></h4>
                                    <p class="text-muted">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($typeLabel) ?></span>
                                        <i class="fas fa-ruler-combined ms-2"></i> <?= number_format($property['area'], 1, ',', ' ') ?> м²
                                        <?php if ($property['rooms']): ?>
                                            <i class="fas fa-door-open ms-2"></i> <?= $property['rooms'] ?> комн.
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-muted">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($property['address']) ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="real-estate-price mb-0"><?= number_format($property['monthly_payment'], 0, ',', ' ') ?> ₽/мес</p>
                                        <span class="badge bg-dark"><?= number_format($property['price'], 0, ',', ' ') ?> ₽</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-grid">
                                        <a href="index.php?page=real-estate-item&id=<?= $property['id'] ?>" class="btn btn-primary">Подробнее</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=real-estate&p=<?= $page - 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?><?= $minPrice > 0 ? '&min_price=' . $minPrice : '' ?><?= $maxPrice > 0 ? '&max_price=' . $maxPrice : '' ?><?= $minArea > 0 ? '&min_area=' . $minArea : '' ?><?= $maxArea > 0 ? '&max_area=' . $maxArea : '' ?><?= $rooms > 0 ? '&rooms=' . $rooms : '' ?>">
                                        <i class="fas fa-chevron-left"></i> Предыдущая
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="index.php?page=real-estate&p=<?= $i ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?><?= $minPrice > 0 ? '&min_price=' . $minPrice : '' ?><?= $maxPrice > 0 ? '&max_price=' . $maxPrice : '' ?><?= $minArea > 0 ? '&min_area=' . $minArea : '' ?><?= $maxArea > 0 ? '&max_area=' . $maxArea : '' ?><?= $rooms > 0 ? '&rooms=' . $rooms : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=real-estate&p=<?= $page + 1 ?><?= !empty($type) ? '&type=' . urlencode($type) : '' ?><?= $minPrice > 0 ? '&min_price=' . $minPrice : '' ?><?= $maxPrice > 0 ? '&max_price=' . $maxPrice : '' ?><?= $minArea > 0 ? '&min_area=' . $minArea : '' ?><?= $maxArea > 0 ? '&max_area=' . $maxArea : '' ?><?= $rooms > 0 ? '&rooms=' . $rooms : '' ?>">
                                        Следующая <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">По заданным параметрам недвижимость не найдена. Попробуйте изменить параметры фильтрации.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3>Лизинг недвижимости для физических лиц</h3>
                <p>Лизинг недвижимости — это современный и удобный финансовый инструмент, позволяющий получить недвижимость в пользование с последующим правом выкупа. Это отличная альтернатива ипотеке, особенно если вы не имеете возможности внести большой первоначальный взнос.</p>
                <h4>Преимущества лизинга недвижимости:</h4>
                <ul>
                    <li>Гибкие условия и сроки договора</li>
                    <li>Уменьшенный первоначальный взнос (от 10%)</li>
                    <li>Возможность получения налоговых льгот</li>
                    <li>Быстрое оформление без длительных проверок</li>
                    <li>Возможность досрочного выкупа без штрафов</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-4">
                <h3>Лизинг недвижимости для бизнеса</h3>
                <p>Для юридических лиц лизинг недвижимости — это оптимальный способ расширить бизнес без единовременных крупных затрат. Мы предлагаем специальные программы для различных видов бизнеса с учетом индивидуальных потребностей.</p>
                <h4>Почему бизнес выбирает лизинг:</h4>
                <ul>
                    <li>Сохранение оборотных средств компании</li>
                    <li>Оптимизация налогообложения</li>
                    <li>Ускоренная амортизация предмета лизинга</li>
                    <li>Баланс улучшается за счет отсутствия кредитных обязательств</li>
                    <li>Гибкий график платежей, адаптированный под ваш бизнес</li>
                </ul>
            </div>
        </div>
    </div>
</div>