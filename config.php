<?php
/**
 * Файл конфигурации
 * 
 * Содержит настройки сайта и константы
 */

// Определяем базовый URL сайта
define('BASE_URL', '/');

// Названия статусов заявок
define('APPLICATION_STATUS', [
    'new' => 'Новая',
    'in_progress' => 'На рассмотрении',
    'approved' => 'Одобрена',
    'rejected' => 'Отклонена',
    'signed' => 'Подписана',
    'completed' => 'Завершена'
]);

// Пользовательские роли
define('USER_ROLES', [
    'client' => 'Клиент',
    'manager' => 'Менеджер',
    'admin' => 'Администратор'
]);

// Настройки сессии
ini_set('session.gc_maxlifetime', 3600); // 1 час
session_set_cookie_params(3600); // 1 час

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Запускаем сессию
session_start();

// Подключаем класс для работы с базой данных
require_once 'database.php';

// Функция для загрузки модулей
function autoloadModules($className) {
    $filename = "modules/{$className}.php";
    if (file_exists($filename)) {
        require_once $filename;
    }
}
spl_autoload_register('autoloadModules');
?>