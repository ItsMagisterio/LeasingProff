<?php
// Страница блога и новостей

// Определяем запрошенную категорию (если есть)
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Определяем запрошенную статью (если есть)
$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;

// Подготавливаем заголовок страницы
$pageTitle = 'Блог и новости';
if (!empty($category)) {
    switch ($category) {
        case 'news':
            $pageTitle = 'Новости';
            break;
        case 'advice':
            $pageTitle = 'Советы';
            break;
        case 'analytics':
            $pageTitle = 'Аналитика';
            break;
    }
}

// Если открыта конкретная статья
if ($article_id > 0) {
    // В реальном приложении здесь будет код для получения статьи из БД
    // Для демонстрации используем предопределенные статьи
    $articles = [
        1 => [
            'title' => 'Изменения в законодательстве о лизинге в 2025 году',
            'category' => 'news',
            'date' => '15 апреля 2025',
            'image' => 'https://images.unsplash.com/photo-1589391886645-d51941baf7fb?auto=format&fit=crop&w=1200&q=80',
            'content' => '<p>В 2025 году вступили в силу новые изменения в законодательстве о лизинге, которые значительно упрощают процедуру оформления сделок и снижают налоговую нагрузку на лизингополучателей.</p>
                          <p>Основные изменения касаются следующих аспектов:</p>
                          <ul>
                              <li>Упрощение процедуры регистрации договоров лизинга</li>
                              <li>Снижение налоговой ставки на операции по лизингу</li>
                              <li>Введение электронной формы заключения договоров</li>
                              <li>Расширение перечня объектов, доступных для лизинга</li>
                          </ul>
                          <p>Эти изменения направлены на стимулирование развития лизингового рынка и повышение доступности финансирования для малого и среднего бизнеса.</p>
                          <p>По мнению экспертов, новые правила позволят увеличить объем лизинговых сделок на 15-20% уже в текущем году, а также привлечь новых участников на рынок. Особенно положительное влияние ожидается в секторе лизинга недвижимости и высокотехнологичного оборудования.</p>
                          <p>Комитет по финансовому рынку Государственной Думы продолжает работу над дальнейшим совершенствованием законодательства в сфере лизинга. В частности, рассматриваются предложения по введению дополнительных налоговых льгот для лизингополучателей из приоритетных отраслей экономики.</p>'
        ],
        2 => [
            'title' => 'Как выбрать оптимальный срок лизинга для автомобиля',
            'category' => 'advice',
            'date' => '10 апреля 2025',
            'image' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?auto=format&fit=crop&w=1200&q=80',
            'content' => '<p>Срок лизинга является одним из ключевых параметров, влияющих на стоимость сделки. В этой статье мы рассмотрим, как определить оптимальный срок лизинга для автомобиля в зависимости от его класса и ваших потребностей.</p>
                          <p>При выборе срока лизинга важно учитывать следующие факторы:</p>
                          <ol>
                              <li><strong>Класс автомобиля и его остаточная стоимость</strong>. Премиальные автомобили дольше сохраняют свою стоимость, что позволяет выбирать более длительные сроки лизинга.</li>
                              <li><strong>Интенсивность использования</strong>. Чем интенсивнее вы планируете эксплуатировать автомобиль, тем короче должен быть срок лизинга.</li>
                              <li><strong>Финансовые возможности</strong>. Более длительный срок снижает ежемесячный платеж, но увеличивает общую стоимость лизинга.</li>
                              <li><strong>Планы по обновлению автопарка</strong>. Если вы планируете регулярно обновлять автомобиль, короткий срок лизинга будет предпочтительнее.</li>
                          </ol>
                          <p>Для большинства легковых автомобилей оптимальным считается срок лизинга 36-48 месяцев. За это время автомобиль сохраняет хорошее техническое состояние, а ежемесячный платеж остается на приемлемом уровне.</p>
                          <p>Для коммерческого транспорта и спецтехники могут быть оптимальны более длительные сроки — 48-60 месяцев, так как эта техника имеет больший срок полезного использования.</p>
                          <p>Важно помнить, что при выборе минимального срока лизинга (12-24 месяца) ежемесячные платежи будут максимальными, но общая переплата — минимальной. При выборе максимального срока (60-72 месяца) ситуация обратная: ежемесячный платеж ниже, но общая переплата выше.</p>'
        ],
        3 => [
            'title' => 'Тренды рынка лизинга недвижимости в 2025 году',
            'category' => 'analytics',
            'date' => '5 апреля 2025',
            'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=1200&q=80',
            'content' => '<p>Рынок лизинга недвижимости стремительно развивается. В 2025 году мы наблюдаем ряд интересных тенденций, которые могут повлиять на решения инвесторов и предпринимателей.</p>
                          <p>Основные тренды рынка лизинга недвижимости в 2025 году:</p>
                          <ol>
                              <li><strong>Рост популярности лизинга жилой недвижимости</strong>. Все больше граждан рассматривают лизинг как альтернативу ипотеке, особенно при приобретении дорогостоящих объектов.</li>
                              <li><strong>Цифровизация процессов</strong>. Внедрение электронного документооборота и онлайн-платформ для заключения и обслуживания договоров лизинга.</li>
                              <li><strong>Развитие сегмента зеленой недвижимости</strong>. Особые условия лизинга предлагаются для энергоэффективных и экологичных объектов.</li>
                              <li><strong>Увеличение доли лизинга в финансировании коммерческой недвижимости</strong>. По данным аналитиков, до 25% сделок с коммерческой недвижимостью в 2025 году будут проводиться с использованием лизинговых схем.</li>
                              <li><strong>Развитие программ обратного лизинга</strong>. Все больше компаний используют этот инструмент для высвобождения капитала, вложенного в недвижимость.</li>
                          </ol>
                          <p>Эксперты прогнозируют, что объем рынка лизинга недвижимости в России вырастет на 18-20% по итогам 2025 года. Наибольший рост ожидается в сегменте складской и логистической недвижимости, что связано с продолжающимся развитием онлайн-торговли.</p>
                          <p>Интересной тенденцией также является увеличение спроса на лизинг загородной недвижимости. Это связано с изменением предпочтений потребителей после пандемии и развитием удаленной работы.</p>
                          <p>В целом, лизинг недвижимости становится все более привлекательным инструментом финансирования как для бизнеса, так и для частных лиц, предлагая гибкие условия и налоговые преимущества.</p>'
        ]
    ];
    
    // Получаем статью по ID
    $article = isset($articles[$article_id]) ? $articles[$article_id] : null;
    
    // Если статья найдена, устанавливаем заголовок страницы
    if ($article) {
        $pageTitle = $article['title'];
    }
}

outputHeader($pageTitle);
?>

<div class="container py-5">
    <?php if ($article_id > 0 && isset($article)): ?>
        <!-- Отображение конкретной статьи -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=blog">Блог</a></li>
                        <?php if (!empty($article['category'])): ?>
                            <li class="breadcrumb-item"><a href="index.php?page=blog&category=<?= $article['category'] ?>">
                                <?php
                                switch ($article['category']) {
                                    case 'news':
                                        echo 'Новости';
                                        break;
                                    case 'advice':
                                        echo 'Советы';
                                        break;
                                    case 'analytics':
                                        echo 'Аналитика';
                                        break;
                                    default:
                                        echo ucfirst($article['category']);
                                }
                                ?>
                            </a></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($article['title']) ?></li>
                    </ol>
                </nav>
                
                <article class="card border-0 shadow">
                    <img src="<?= $article['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($article['title']) ?>" style="max-height: 500px; object-fit: cover;">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center mb-4">
                            <span class="badge bg-<?= $article['category'] === 'news' ? 'primary' : ($article['category'] === 'advice' ? 'success' : 'info text-dark') ?> me-2">
                                <?php
                                switch ($article['category']) {
                                    case 'news':
                                        echo 'Новости';
                                        break;
                                    case 'advice':
                                        echo 'Советы';
                                        break;
                                    case 'analytics':
                                        echo 'Аналитика';
                                        break;
                                    default:
                                        echo ucfirst($article['category']);
                                }
                                ?>
                            </span>
                            <span class="text-muted"><?= $article['date'] ?></span>
                        </div>
                        
                        <h1 class="mb-4"><?= htmlspecialchars($article['title']) ?></h1>
                        
                        <div class="article-content">
                            <?= $article['content'] ?>
                        </div>
                        
                        <div class="mt-5">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted">Поделиться:</span>
                                    <a href="#" class="ms-2 text-primary"><i class="fab fa-facebook"></i></a>
                                    <a href="#" class="ms-2 text-info"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="ms-2 text-success"><i class="fab fa-whatsapp"></i></a>
                                    <a href="#" class="ms-2 text-primary"><i class="fab fa-telegram"></i></a>
                                </div>
                                <a href="index.php?page=blog" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Вернуться к списку статей
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
                
                <div class="mt-5">
                    <h3 class="mb-4">Рекомендуемые статьи</h3>
                    <div class="row g-4">
                        <?php
                        // Получаем рекомендуемые статьи (исключаем текущую)
                        $recommendedArticles = array_filter($articles, function($item) use ($article_id) {
                            return $item !== $article_id;
                        });
                        
                        // Выводим первые 3 рекомендуемые статьи
                        $count = 0;
                        foreach ($articles as $id => $rec_article) {
                            if ($id == $article_id) continue;
                            if ($count >= 3) break;
                            $count++;
                        ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <img src="<?= $rec_article['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($rec_article['title']) ?>" style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-<?= $rec_article['category'] === 'news' ? 'primary' : ($rec_article['category'] === 'advice' ? 'success' : 'info text-dark') ?> me-2">
                                                <?php
                                                switch ($rec_article['category']) {
                                                    case 'news':
                                                        echo 'Новости';
                                                        break;
                                                    case 'advice':
                                                        echo 'Советы';
                                                        break;
                                                    case 'analytics':
                                                        echo 'Аналитика';
                                                        break;
                                                    default:
                                                        echo ucfirst($rec_article['category']);
                                                }
                                                ?>
                                            </span>
                                            <small class="text-muted"><?= $rec_article['date'] ?></small>
                                        </div>
                                        <h5 class="card-title"><?= htmlspecialchars($rec_article['title']) ?></h5>
                                        <a href="index.php?page=blog&article_id=<?= $id ?>" class="btn btn-outline-primary mt-3">Читать</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Список статей блога -->
        <div class="row">
            <div class="col-lg-8">
                <h1 class="mb-4">
                    <?php
                    switch ($category) {
                        case 'news':
                            echo 'Новости';
                            break;
                        case 'advice':
                            echo 'Советы';
                            break;
                        case 'analytics':
                            echo 'Аналитика';
                            break;
                        default:
                            echo 'Блог и новости';
                    }
                    ?>
                </h1>
                
                <?php if (!empty($category)): ?>
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Главная</a></li>
                            <li class="breadcrumb-item"><a href="index.php?page=blog">Блог</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php
                                switch ($category) {
                                    case 'news':
                                        echo 'Новости';
                                        break;
                                    case 'advice':
                                        echo 'Советы';
                                        break;
                                    case 'analytics':
                                        echo 'Аналитика';
                                        break;
                                    default:
                                        echo ucfirst($category);
                                }
                                ?>
                            </li>
                        </ol>
                    </nav>
                <?php endif; ?>
                
                <?php
                // Фильтруем статьи по категории, если она указана
                $filtered_articles = [];
                foreach ($articles as $id => $article) {
                    if (empty($category) || $article['category'] === $category) {
                        $filtered_articles[$id] = $article;
                    }
                }
                
                // Выводим отфильтрованные статьи
                foreach ($filtered_articles as $id => $article):
                ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="<?= $article['image'] ?>" class="img-fluid rounded-start h-100" alt="<?= htmlspecialchars($article['title']) ?>" style="object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-<?= $article['category'] === 'news' ? 'primary' : ($article['category'] === 'advice' ? 'success' : 'info text-dark') ?> me-2">
                                            <?php
                                            switch ($article['category']) {
                                                case 'news':
                                                    echo 'Новости';
                                                    break;
                                                case 'advice':
                                                    echo 'Советы';
                                                    break;
                                                case 'analytics':
                                                    echo 'Аналитика';
                                                    break;
                                                default:
                                                    echo ucfirst($article['category']);
                                            }
                                            ?>
                                        </span>
                                        <small class="text-muted"><?= $article['date'] ?></small>
                                    </div>
                                    <h3 class="card-title mb-3"><?= htmlspecialchars($article['title']) ?></h3>
                                    <p class="card-text">
                                        <?= substr(strip_tags($article['content']), 0, 200) ?>...
                                    </p>
                                    <a href="index.php?page=blog&article_id=<?= $id ?>" class="btn btn-outline-primary rounded-pill">Читать далее</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($filtered_articles)): ?>
                    <div class="alert alert-info">
                        <p class="mb-0">В данной категории пока нет статей. Пожалуйста, проверьте другие разделы или вернитесь позже.</p>
                    </div>
                <?php endif; ?>
                
                <!-- Пагинация -->
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <?php if (count($filtered_articles) > 5): ?>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <?php endif; ?>
                        <?php if (count($filtered_articles) > 10): ?>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <?php endif; ?>
                        <?php if (count($filtered_articles) > 5): ?>
                            <li class="page-item">
                                <a class="page-link" href="#"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Категории</h4>
                        <div class="list-group list-group-flush">
                            <a href="index.php?page=blog" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= empty($category) ? 'active' : '' ?>">
                                Все статьи
                                <span class="badge bg-primary rounded-pill"><?= count($articles) ?></span>
                            </a>
                            <a href="index.php?page=blog&category=news" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $category === 'news' ? 'active' : '' ?>">
                                Новости
                                <span class="badge bg-primary rounded-pill"><?= count(array_filter($articles, function($article) { return $article['category'] === 'news'; })) ?></span>
                            </a>
                            <a href="index.php?page=blog&category=advice" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $category === 'advice' ? 'active' : '' ?>">
                                Советы
                                <span class="badge bg-primary rounded-pill"><?= count(array_filter($articles, function($article) { return $article['category'] === 'advice'; })) ?></span>
                            </a>
                            <a href="index.php?page=blog&category=analytics" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $category === 'analytics' ? 'active' : '' ?>">
                                Аналитика
                                <span class="badge bg-primary rounded-pill"><?= count(array_filter($articles, function($article) { return $article['category'] === 'analytics'; })) ?></span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Популярные статьи</h4>
                        <div class="list-group list-group-flush">
                            <?php
                            // Выводим первые 5 статей как популярные
                            $count = 0;
                            foreach ($articles as $id => $article) {
                                if ($count >= 5) break;
                                $count++;
                            ?>
                                <a href="index.php?page=blog&article_id=<?= $id ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($article['title']) ?></h6>
                                        <small class="text-muted"><?= $article['date'] ?></small>
                                    </div>
                                    <small class="text-muted">
                                        <span class="badge bg-<?= $article['category'] === 'news' ? 'primary' : ($article['category'] === 'advice' ? 'success' : 'info text-dark') ?>">
                                            <?php
                                            switch ($article['category']) {
                                                case 'news':
                                                    echo 'Новости';
                                                    break;
                                                case 'advice':
                                                    echo 'Советы';
                                                    break;
                                                case 'analytics':
                                                    echo 'Аналитика';
                                                    break;
                                                default:
                                                    echo ucfirst($article['category']);
                                            }
                                            ?>
                                        </span>
                                    </small>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Подписаться на новости</h4>
                        <p class="text-muted">Получайте новые статьи и полезные советы по лизингу на вашу почту</p>
                        <form>
                            <div class="mb-3">
                                <input type="email" class="form-control" placeholder="Ваш email" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Подписаться</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php outputFooter(); ?>