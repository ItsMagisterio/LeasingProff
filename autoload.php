<?php
// Автозагрузка классов
// Подключаем базу данных
require_once 'database.php';

// Подключаем модули
require_once 'modules/Auth.php';
require_once 'modules/Users.php';
require_once 'modules/Vehicles.php';
require_once 'modules/Applications.php';
require_once 'modules/RealEstate.php';
require_once 'modules/ParserManager.php';