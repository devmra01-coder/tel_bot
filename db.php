<?php

// --- اصلاح جداول اصلی ---
// --- جداول اصلی شما ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$itemsTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `first number` varchar(60) NOT NULL DEFAULT '0'
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$soldiersTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `consumable item` VARCHAR(500) NOT NULL DEFAULT '',
    `first number` varchar(60) NOT NULL DEFAULT '0'
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$peopleTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `consumable item` VARCHAR(500) NOT NULL DEFAULT '',
    `first number` varchar(60) NOT NULL DEFAULT '0'
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$buildingsTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `upgrade items numbers 1` VARCHAR(500) NOT NULL DEFAULT '',
    `upgrade items numbers 2` VARCHAR(500) NOT NULL DEFAULT '',
    `upgrade items numbers 3` VARCHAR(500) NOT NULL DEFAULT '',
    `efficiency item` varchar(60) NOT NULL DEFAULT '',
    `efficiency number` varchar(60) NOT NULL DEFAULT '0',
    `first level` varchar(60) NOT NULL DEFAULT '1',
    `last level` varchar(60) NOT NULL DEFAULT '1',
    `upgrade_costs` TEXT DEFAULT NULL,  
    `max_limit` INT DEFAULT 0,
    `one_time` TINYINT(1) DEFAULT 0,
    `daily_limit` INT DEFAULT 0
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$campsTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `upgrade items numbers 1` VARCHAR(500) NOT NULL DEFAULT '',
    `upgrade items numbers 2` VARCHAR(500) NOT NULL DEFAULT '',
    `upgrade items numbers 3` VARCHAR(500) NOT NULL DEFAULT '',
    `efficiency soldier` varchar(60) NOT NULL DEFAULT '',
    `efficiency number` varchar(60) NOT NULL DEFAULT '0',
    `first level` varchar(60) NOT NULL DEFAULT '1',
    `last level` varchar(60) NOT NULL DEFAULT '1',
    `upgrade_costs` TEXT DEFAULT NULL,    
    `max_limit` INT DEFAULT 0,
    `one_time` TINYINT(1) DEFAULT 0,
    `daily_limit` INT DEFAULT 0
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$citiesTable` (
    `city id` varchar(50) NOT NULL PRIMARY KEY,
    `player id` varchar(50) NOT NULL DEFAULT '',
    `step` varchar(50) NOT NULL DEFAULT '',
    `Check` varchar(50) NOT NULL DEFAULT '',
    `family` varchar(50) NOT NULL DEFAULT '',
    `city name` VARCHAR(500) NOT NULL DEFAULT '',
    `lord name` VARCHAR(500) NOT NULL DEFAULT '',
    `maghsad` varchar(50) NOT NULL DEFAULT '',
    `sendItem` varchar(50) NOT NULL DEFAULT '',
    `sendItemNum` varchar(50) NOT NULL DEFAULT '0',
    `getItem` varchar(50) NOT NULL DEFAULT '',
    `getItemNum` varchar(50) NOT NULL DEFAULT '0'
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$adminsTable` (
    `id` varchar(50) NOT NULL PRIMARY KEY,
    `step` varchar(50) NOT NULL DEFAULT '',
    `thing` varchar(50) NOT NULL DEFAULT ''
)");

// --- جداول شهرها ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityBuildingsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityItemsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$citySoldiersTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityPeopleTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityCampsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");

// --- جداول سیستم خرید ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `shop_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_name` VARCHAR(50) NOT NULL,
    `persian_name` VARCHAR(100) NOT NULL,
    `price_gold` INT DEFAULT 0,
    `is_limited` TINYINT(1) DEFAULT 0,
    `max_limit` INT DEFAULT 0,
    `one_time` TINYINT(1) DEFAULT 0,
    `daily_limit` INT DEFAULT 0,
    `requirements` TEXT DEFAULT NULL,
    `costs` TEXT NOT NULL,
    `category` VARCHAR(30) DEFAULT 'resource',
    `active` TINYINT(1) DEFAULT 1,
    UNIQUE KEY `unique_item` (`item_name`)
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `shop_daily_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `city_id` VARCHAR(50) NOT NULL,
    `item_name` VARCHAR(50) NOT NULL,
    `date` DATE NOT NULL,
    `quantity` INT NOT NULL,
    UNIQUE KEY `daily_unique` (`city_id`, `item_name`, `date`)
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `shop_one_time_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `city_id` VARCHAR(50) NOT NULL,
    `item_name` VARCHAR(50) NOT NULL,
    `purchase_date` DATETIME NOT NULL,
    UNIQUE KEY `one_time_unique` (`city_id`, `item_name`)
)");

// --- جداول سیستم ارتقا ---
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `upgrade_daily_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `city_id` VARCHAR(50) NOT NULL,
    `item_name` VARCHAR(100) NOT NULL,
    `date` DATE NOT NULL,
    UNIQUE KEY `daily_upgrade` (`city_id`, `item_name`, `date`)
)");

// ۲. حالا که جداول آماده هستند، اطلاعات را دریافت می‌کنیم
//-------------------
$cityResult = mysqli_query($conn, "SELECT * FROM `$citiesTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$city = mysqli_fetch_assoc($cityResult);

$playerCheck  = $city["Check"] ?? null;
$playerStep   = $city["step"] ?? null;
$playerId     = $city["player id"] ?? null;
$cityName     = $city["city name"] ?? null;
$lordName     = $city["lord name"] ?? null;
$maghsad      = $city['maghsad'] ?? null;
$sendItem     = $city['sendItem'] ?? null;
$sendItemNum  = $city['sendItemNum'] ?? null;
$getItem      = $city['getItem'] ?? null;
$getItemNum   = $city['getItemNum'] ?? null;

//--------------------
$myAdmins = mysqli_query($conn, "SELECT * FROM `$adminsTable` WHERE `id` = '{$from_id}' LIMIT 1");
$getAdmins = mysqli_fetch_assoc($myAdmins);
$theAdminStep = $getAdmins["step"] ?? null;
$theAdminThing = $getAdmins["thing"] ?? null;

//------------------- دریافت لیست‌ها
$itemsList       = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`") ?: [];
$itemsListEn     = mysqli_query($conn, "SELECT `english name` FROM `$itemsTable`") ?: [];
$peopleListEn    = mysqli_query($conn, "SELECT `english name` FROM `$peopleTable`") ?: [];
$buildingsList   = mysqli_query($conn, "SELECT `english name` FROM `$buildingsTable`") ?: [];
$soldiersList    = mysqli_query($conn, "SELECT `english name` FROM `$soldiersTable`") ?: [];
$campsList       = mysqli_query($conn, "SELECT `english name` FROM `$campsTable`") ?: [];

?>
