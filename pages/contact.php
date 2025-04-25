<?php
// Страница контактов и формы обратной связи

// Обработка формы при отправке
$formSubmitted = false;
$success = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $formSubmitted = true;
    
    // Получаем данные из формы
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    $subject = $_POST['subject'] ?? 'Общий вопрос';
    
    // Валидация данных
    if (empty($name)) {
        $errorMsg = 'Пожалуйста, введите ваше имя';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Пожалуйста, введите корректный email';
    } elseif (empty($phone)) {
        $errorMsg = 'Пожалуйста, введите ваш номер телефона';
    } elseif (empty($message)) {
        $errorMsg = 'Пожалуйста, введите сообщение';
    } else {
        // В реальном приложении здесь будет код для отправки сообщения
        // Например, через mail() или через API CRM-системы
        
        // Для демонстрации просто считаем, что сообщение успешно отправлено
        $success = true;
    }
}

// Подготавливаем параметры для заголовка страницы
$pageTitle = 'Связаться с нами';
outputHeader($pageTitle);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="text-center mb-5">Свяжитесь с нами</h1>
            
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body p-4">
                            <h3 class="h4 mb-4">Наши контакты</h3>
                            
                            <div class="d-flex mb-4">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Адрес офиса</h5>
                                    <p class="mb-0">г. Москва, ул. Ленинская слобода, 19, офис 21</p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-4">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-phone-alt fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Телефон</h5>
                                    <p class="mb-0">
                                        <a href="tel:+78001234567" class="text-decoration-none">8 (800) 123-45-67</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-4">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Email</h5>
                                    <p class="mb-0">
                                        <a href="mailto:info@2leasing.ru" class="text-decoration-none">info@2leasing.ru</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <div class="me-3 text-primary">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Время работы</h5>
                                    <p class="mb-0">Пн-Пт: 9:00 - 19:00<br>Сб-Вс: Выходной</p>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="h6 mb-3">Мы в социальных сетях</h5>
                            <div class="d-flex">
                                <a href="#" class="me-3 text-primary fs-4"><i class="fab fa-vk"></i></a>
                                <a href="#" class="me-3 text-primary fs-4"><i class="fab fa-telegram"></i></a>
                                <a href="#" class="me-3 text-primary fs-4"><i class="fab fa-whatsapp"></i></a>
                                <a href="#" class="text-primary fs-4"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="card border-0 shadow">
                        <div class="card-body p-4">
                            <h3 class="h4 mb-4">Напишите нам</h3>
                            
                            <?php if ($formSubmitted && $success): ?>
                                <div class="alert alert-success">
                                    <h5 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Сообщение отправлено!</h5>
                                    <p class="mb-0">Спасибо за обращение! Наши специалисты свяжутся с вами в ближайшее время.</p>
                                </div>
                            <?php else: ?>
                                <?php if ($formSubmitted && !$success): ?>
                                    <div class="alert alert-danger">
                                        <?= htmlspecialchars($errorMsg) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Ваше имя</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Телефон</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="subject" class="form-label">Тема обращения</label>
                                            <select class="form-select" id="subject" name="subject">
                                                <option value="Общий вопрос" <?= (isset($_POST['subject']) && $_POST['subject'] === 'Общий вопрос') ? 'selected' : '' ?>>Общий вопрос</option>
                                                <option value="Лизинг транспорта" <?= (isset($_POST['subject']) && $_POST['subject'] === 'Лизинг транспорта') ? 'selected' : '' ?>>Лизинг транспорта</option>
                                                <option value="Лизинг недвижимости" <?= (isset($_POST['subject']) && $_POST['subject'] === 'Лизинг недвижимости') ? 'selected' : '' ?>>Лизинг недвижимости</option>
                                                <option value="Сотрудничество" <?= (isset($_POST['subject']) && $_POST['subject'] === 'Сотрудничество') ? 'selected' : '' ?>>Сотрудничество</option>
                                                <option value="Претензия" <?= (isset($_POST['subject']) && $_POST['subject'] === 'Претензия') ? 'selected' : '' ?>>Претензия</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="message" class="form-label">Сообщение</label>
                                            <textarea class="form-control" id="message" name="message" rows="5" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="dataAgree" name="data_agree" required>
                                                <label class="form-check-label" for="dataAgree">
                                                    Я согласен с <a href="index.php?page=privacy" target="_blank">политикой обработки персональных данных</a>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12 text-center mt-4">
                                            <button type="submit" name="submit_contact" class="btn btn-primary btn-lg px-5 rounded-pill">Отправить сообщение</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-5">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Как нас найти</h3>
                        <div class="ratio ratio-21x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2246.7370764510365!2d37.62954947639225!3d55.71025439963791!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54b0738d93353%3A0x1208ee3843a1d5f8!2z0YPQuy4g0JvQtdC90LjQvdGB0LrQsNGPINCh0LvQvtCx0L7QtNCwLCAxOSwg0JzQvtGB0LrQstCwLCAxMTUyODA!5e0!3m2!1sru!2sru!4v1619431334602!5m2!1sru!2sru" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Маска для телефона (простая реализация)
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '+' + x[1] + ' (' + x[2] + ') ' + (x[3] ? x[3] + '-' + x[4] : x[3]);
        });
    }
});
</script>

<?php outputFooter(); ?>