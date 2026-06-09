<?php

// ۱. ابتدا ساخت تمامی جداول (اگر وجود نداشته باشند)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$itemsTable` (
    `persian name` varchar(100) NOT NULL,
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `first number` varchar(60) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$soldiersTable` (
    `persian name` varchar(100) NOT NULL,
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `consumable item` TEXT NOT NULL,
    `first number` varchar(60) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$peopleTable` (
    `persian name` varchar(100) NOT NULL,
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `consumable item` TEXT NOT NULL,
    `first number` varchar(60) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$buildingsTable` (
    `persian name` varchar(100) NOT NULL,
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `upgrade items numbers 1` TEXT NOT NULL,
    `upgrade items numbers 2` TEXT NOT NULL,
    `upgrade items numbers 3` TEXT NOT NULL,
    `efficiency item` varchar(60) NOT NULL,
    `efficiency number` varchar(60) NOT NULL,
    `first level` varchar(60) NOT NULL,
    `last level` varchar(60) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$campsTable` (
    `persian name` varchar(100) NOT NULL,
    `english name` varchar(50) NOT NULL PRIMARY KEY,
    `upgrade items numbers 1` TEXT NOT NULL,
    `upgrade items numbers 2` TEXT NOT NULL,
    `upgrade items numbers 3` TEXT NOT NULL,
    `efficiency soldier` varchar(60) NOT NULL,
    `efficiency number` varchar(60) NOT NULL,
    `first level` varchar(60) NOT NULL,
    `last level` varchar(60) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$citiesTable` (
    `city id` varchar(50) NOT NULL PRIMARY KEY,
    `player id` varchar(50) NOT NULL,
    `step` varchar(50) NOT NULL,
    `Check` varchar(50) NOT NULL,
    `family` varchar(50) NOT NULL,
    `city name` text NOT NULL,
    `lord name` text NOT NULL,
    `maghsad` varchar(50) NOT NULL,
    `sendItem` varchar(50) NOT NULL,
    `sendItemNum` varchar(50) NOT NULL,
    `getItem` varchar(50) NOT NULL,
    `getItemNum` varchar(50) NOT NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `$adminsTable` (
    `id` varchar(50) NOT NULL PRIMARY KEY,
    `step` varchar(50) NOT NULL,
    `thing` varchar(50) NOT NULL
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
