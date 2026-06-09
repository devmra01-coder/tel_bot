<?php

// اطمینان حاصل کنید که $conn با استفاده از PDO به دیتابیس PostgreSQL متصل شده است.
// مثال:
// try {
//     $host = 'your_host';
//     $db   = 'your_db';
//     $user = 'your_user';
//     $pass = 'your_password';
//     $conn = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
// } catch(PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }
// اگر $conn از قبل به درستی تعریف شده، این بخش اتصال را حذف کنید.


// --- نام جداول ---
// در PostgreSQL، نام جداول و ستون‌ها را داخل دابل کوتیشن (") قرار می‌دهیم.
$itemsTable = '"items"';
$soldiersTable = '"soldiers"';
$peopleTable = '"people"';
$buildingsTable = '"buildings"';
$campsTable = '"camps"';
$citiesTable = '"cities"';
$adminsTable = '"admins"';
$cityBuildingsTable = '"cityBuildings"';
$cityItemsTable = '"cityItems"';
$citySoldiersTable = '"citySoldiers"';
$cityPeopleTable = '"cityPeople"';
$cityCampsTable = '"cityCamps"';


// ۱. ابتدا ساخت تمامی جداول (اگر وجود نداشته باشند)
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS $itemsTable (
        \"persian name\" varchar(100) NOT NULL,
        \"english name\" varchar(50) NOT NULL PRIMARY KEY,
        \"first number\" varchar(60) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $soldiersTable (
        \"persian name\" varchar(100) NOT NULL,
        \"english name\" varchar(50) NOT NULL PRIMARY KEY,
        \"consumable item\" TEXT NOT NULL,
        \"first number\" varchar(60) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $peopleTable (
        \"persian name\" varchar(100) NOT NULL,
        \"english name\" varchar(50) NOT NULL PRIMARY KEY,
        \"consumable item\" TEXT NOT NULL,
        \"first number\" varchar(60) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $buildingsTable (
        \"persian name\" varchar(100) NOT NULL,
        \"english name\" varchar(50) NOT NULL PRIMARY KEY,
        \"upgrade items numbers 1\" TEXT NOT NULL,
        \"upgrade items numbers 2\" TEXT NOT NULL,
        \"upgrade items numbers 3\" TEXT NOT NULL,
        \"efficiency item\" varchar(60) NOT NULL,
        \"efficiency number\" varchar(60) NOT NULL,
        \"first level\" varchar(60) NOT NULL,
        \"last level\" varchar(60) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $campsTable (
        \"persian name\" varchar(100) NOT NULL,
        \"english name\" varchar(50) NOT NULL PRIMARY KEY,
        \"upgrade items numbers 1\" TEXT NOT NULL,
        \"upgrade items numbers 2\" TEXT NOT NULL,
        \"upgrade items numbers 3\" TEXT NOT NULL,
        \"efficiency soldier\" varchar(60) NOT NULL,
        \"efficiency number\" varchar(60) NOT NULL,
        \"first level\" varchar(60) NOT NULL,
        \"last level\" varchar(60) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $citiesTable (
        \"city id\" varchar(50) NOT NULL PRIMARY KEY,
        \"player id\" varchar(50) NOT NULL,
        \"step\" varchar(50) NOT NULL,
        \"Check\" varchar(50) NOT NULL,
        \"family\" varchar(50) NOT NULL,
        \"city name\" text NOT NULL,
        \"lord name\" text NOT NULL,
        \"maghsad\" varchar(50) NOT NULL,
        \"sendItem\" varchar(50) NOT NULL,
        \"sendItemNum\" varchar(50) NOT NULL,
        \"getItem\" varchar(50) NOT NULL,
        \"getItemNum\" varchar(50) NOT NULL
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS $adminsTable (
        \"id\" varchar(50) NOT NULL PRIMARY KEY,
        
        \"step\" varchar(50) NOT NULL,
        
        \"thing\" varchar(50) NOT NULL
    )");

    // جداول مربوط به شهرها
    $conn->exec("CREATE TABLE IF NOT EXISTS $cityBuildingsTable (\"city id\" varchar(50) NOT NULL PRIMARY KEY)");
    $conn->exec("CREATE TABLE IF NOT EXISTS $cityItemsTable (\"city id\" varchar(50) NOT NULL PRIMARY KEY)");
    $conn->exec("CREATE TABLE IF NOT EXISTS $citySoldiersTable (\"city id\" varchar(50) NOT NULL PRIMARY KEY)");
    $conn->exec("CREATE TABLE IF NOT EXISTS $cityPeopleTable (\"city id\" varchar(50) NOT NULL PRIMARY KEY)");
    $conn->exec("CREATE TABLE IF NOT EXISTS $cityCampsTable (\"city id\" varchar(50) NOT NULL PRIMARY KEY)");

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
    die();
}


// ۲. حالا که جداول آماده هستند، اطلاعات را دریافت می‌کنیم
//-------------------
$stmtCity = $conn->prepare("SELECT * FROM $citiesTable WHERE \"city id\" = :chat_id LIMIT 1");
$stmtCity->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
$stmtCity->execute();
$city = $stmtCity->fetch();

// استفاده از عملگر null coalescing (??) برای مقادیر پیش‌فرض
$playerCheck  = $city["Check"] ?? null; // اصلاح شده: از "Check" به جای `Check` استفاده شده
$playerStep   = $city["step"] ?? null;   // اصلاح شده: از "step" به جای `step` استفاده شده
$playerId     = $city["player id"] ?? null;
$cityName     = $city["city name"] ?? null;
$lordName     = $city["lord name"] ?? null;
$maghsad      = $city['maghsad'] ?? null;
$sendItem     = $city['sendItem'] ?? null;
$sendItemNum  = $city['sendItemNum'] ?? null;
$getItem      = $city['getItem'] ?? null;
$getItemNum   = $city['getItemNum'] ?? null;

//--------------------
$stmtAdmin = $conn->prepare("SELECT * FROM $adminsTable WHERE \"id\" = :from_id LIMIT 1"); // اصلاح شده: از "id" به جای `id`
$stmtAdmin->bindParam(':from_id', $from_id, PDO::PARAM_STR);
$stmtAdmin->execute();
$getAdmins = $stmtAdmin->fetch();
$theAdminStep = $getAdmins["step"] ?? null; // اصلاح شده: از "step" به جای `step`
$theAdminThing = $getAdmins["thing"] ?? null; // اصلاح شده: از "thing" به جای `thing`

//------------------- دریافت لیست‌ها
try {
    $stmtItemsList = $conn->prepare("SELECT \"persian name\" FROM $itemsTable");
    $stmtItemsList->execute();
    $itemsList = $stmtItemsList->fetchAll(PDO::FETCH_COLUMN, 0);

    $stmtItemsListEn = $conn->prepare("SELECT \"english name\" FROM $itemsTable");
    $stmtItemsListEn->execute();
    $itemsListEn = $stmtItemsListEn->fetchAll(PDO::FETCH_COLUMN, 0);

    $stmtPeopleListEn = $conn->prepare("SELECT \"english name\" FROM $peopleTable");
    $stmtPeopleListEn->execute();
    $peopleListEn = $stmtPeopleListEn->fetchAll(PDO::FETCH_COLUMN, 0);

    $stmtBuildingsList = $conn->prepare("SELECT \"english name\" FROM $buildingsTable");
    $stmtBuildingsList->execute();
    $buildingsList = $stmtBuildingsList->fetchAll(PDO::FETCH_COLUMN, 0);

    $stmtSoldiersList = $conn->prepare("SELECT \"english name\" FROM $soldiersTable");
    $stmtSoldiersList->execute();
    $soldiersList = $stmtSoldiersList->fetchAll(PDO::FETCH_COLUMN, 0);

    $stmtCampsList = $conn->prepare("SELECT \"english name\" FROM $campsTable");
    $stmtCampsList->execute();
    $campsList = $stmtCampsList->fetchAll(PDO::FETCH_COLUMN, 0);

} catch(PDOException $e) {
    echo "Error fetching lists: " . $e->getMessage();
    // die(); // مدیریت خطا
}

?>
