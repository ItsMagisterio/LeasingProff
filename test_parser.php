<?php
// Подключаем необходимые файлы
require_once 'database.php';
require_once 'modules/ParserManager.php';

// Создаем экземпляр ParserManager
$parserManager = new ParserManager();

// Получаем список парсеров
$parsers = $parserManager->getParsers();
echo "Доступные парсеры:\n";
foreach ($parsers as $key => $parser) {
    echo "- {$parser['name']} ({$parser['url']}): " . ($parser['enabled'] ? 'включен' : 'выключен') . "\n";
}

// Тестируем парсинг одного сайта
echo "\nТестирование парсинга одного сайта (europlan):\n";
$result = $parserManager->parseSite('europlan');
var_dump($result);

// Тестируем парсинг всех сайтов
echo "\nТестирование парсинга всех сайтов:\n";
$results = $parserManager->parseAll();
var_dump($results);

echo "\nТестирование завершено.\n";
?>