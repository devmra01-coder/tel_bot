<?php

// --- اصلاح جداول اصلی ---

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$itemsTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `first number` varchar(60) NOT NULL DEFAULT '0'
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$soldiersTable` (
    `persian name` varchar(100) NOT NULL DEFAULT '',
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `consumable item` TEXT NOT NULL DEFAULT '',
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
    `last level` varchar(60) NOT NULL DEFAULT '1'
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
    `last level` varchar(60) NOT NULL DEFAULT '1'
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

// جداول مربوط به شهرها
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityBuildingsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityItemsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$citySoldiersTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityPeopleTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$cityCampsTable` (`city id` varchar(50) NOT NULL PRIMARY KEY)");


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
