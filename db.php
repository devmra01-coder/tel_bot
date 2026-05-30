<?php


//-------------------
$city = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citiesTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
$playerCheck = $city["Check"];
$playerStep = $city["step"];
$playerId = $city["player id"];
$cityName = $city["city name"];
$lordName = $city["lord name"];
$maghsad = $city['maghsad'];
$sendItem = $city['sendItem'];
$sendItemNum = $city['sendItemNum'];
$getItem = $city['getItem'];
$getItemNum = $city['getItemNum'];
//--------------------
$myAdmins = mysqli_query($conn, "SELECT * FROM `$adminsTable` WHERE `id` = '{$from_id}' LIMIT 1");
$getAdmins = mysqli_fetch_assoc($myAdmins);
$theAdminStep = $getAdmins["step"];
$theAdminThing = $getAdmins["thing"];
//-------------------
$itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
$itemsListEn = mysqli_query($conn, "SELECT `english name` FROM `$itemsTable`");
$peopleListEn = mysqli_query($conn, "SELECT `english name` FROM `$peopleTable`");
$buildingsList = mysqli_query($conn, "SELECT `english name` FROM `$buildingsTable`");
$soldiersList = mysqli_query($conn, "SELECT `english name` FROM `$soldiersTable`");
$campsList = mysqli_query($conn, "SELECT `english name` FROM `$campsTable`");



mysqli_query(
    $conn,
    "CREATE TABLE `$itemsTable` (
        `persian name` varchar(100) NOT NULL,
        `english name` varchar(50) NOT NULL PRIMARY KEY,
        `first number` varchar(60) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$soldiersTable` (
        `persian name` varchar(100) NOT NULL,
        `english name` varchar(50) NOT NULL PRIMARY KEY,
        `consumable item` TEXT NOT NULL PRIMARY KEY,
        `first number` varchar(60) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$peopleTable` (
        `persian name` varchar(100) NOT NULL,
        `english name` varchar(50) NOT NULL PRIMARY KEY,
        `consumable item` TEXT NOT NULL PRIMARY KEY,
        `first number` varchar(60) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$buildingsTable` (
        `persian name` varchar(100) NOT NULL,
        `english name` varchar(50) NOT NULL PRIMARY KEY,
        `upgrade items numbers 1` TEXT NOT NULL,
        `upgrade items numbers 2` TEXT NOT NULL,
        `upgrade items numbers 3` TEXT NOT NULL,
        `efficiency item` varchar(60) NOT NULL,
        `efficiency number` varchar(60) NOT NULL,
        `first level` varchar(60) NOT NULL,
        `last level` varchar(60) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$campsTable` (
        `persian name` varchar(100) NOT NULL,
        `english name` varchar(50) NOT NULL PRIMARY KEY,
        `upgrade items numbers 1` TEXT NOT NULL,
        `upgrade items numbers 2` TEXT NOT NULL,
        `upgrade items numbers 3` TEXT NOT NULL,
        `efficiency soldier` varchar(60) NOT NULL,
        `efficiency number` varchar(60) NOT NULL,
        `first level` varchar(60) NOT NULL,
        `last level` varchar(60) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$citiesTable` (
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
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$adminsTable` (
        `id` varchar(50) NOT NULL PRIMARY KEY,
        `step` varchar(50) NOT NULL,
        `thing` varchar(50) NOT NULL
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$cityBuildingsTable` (
        `city id` varchar(50) NOT NULL PRIMARY KEY
        )"
);


mysqli_query(
    $conn,
    "CREATE TABLE `$cityItemsTable` (
        `city id` varchar(50) NOT NULL PRIMARY KEY
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$citySoldiersTable` (
        `city id` varchar(50) NOT NULL PRIMARY KEY
        )"
);

mysqli_query(
    $conn,
    "CREATE TABLE `$cityPeopleTable` (
        `city id` varchar(50) NOT NULL PRIMARY KEY
        )"
);



mysqli_query(
    $conn,
    "CREATE TABLE `$cityCampsTable` (
        `city id` varchar(50) NOT NULL PRIMARY KEY
        )"
);
