<?php

file_put_contents('log.txt', file_get_contents('php://input'), FILE_APPEND);

// $telegram_ip_ranges = [
//     ['lower' => '149.154.160.0', 'upper' => '149.154.175.255'], // literally 149.154.160.0/20
//     ['lower' => '91.108.4.0',    'upper' => '91.108.7.255'],    // literally 91.108.4.0/22
// ];
// $ip_dec = (float) sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
// $ok = false;
// foreach ($telegram_ip_ranges as $telegram_ip_range) if (!$ok) {
//     $lower_dec = (float) sprintf("%u", ip2long($telegram_ip_range['lower']));
//     $upper_dec = (float) sprintf("%u", ip2long($telegram_ip_range['upper']));
//     if ($ip_dec >= $lower_dec and $ip_dec <= $upper_dec) $ok = true;
// }
// if (!$ok) die("Sik!");
//--------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------
define('API_KEY', '8973031455:AAFLRPS2L9PBFkZVGNw_zrw09OtxN7Pysxs'); // توکن ربات رو به جای عبارت TOKEN قرار بدید
$admins = [7761540434,7015879742,6707399737,1317073026,6788568011,2022010806,5958639761,6389723091,7165556662]; // آیدی عددی ادمین های ربات رو در این آرایه قرار بدید قرار بدید
   
// اطلاعات اتصال به PostgreSQL
$host_pg = 'dpg-d8juanreo5us738o1fc0-a.oregon-postgres.render.com'; // هاست PostgreSQL
$db_pg   = 'mysql_1v97';                 // نام دیتابیس PostgreSQL
$user_pg = 'root';                       // نام کاربری PostgreSQL
$pass_pg = 'KjBX7PPKH71yB70UeyIcDsrJn6qXQBYY'; // رمز عبور PostgreSQL

// رشته DSN برای اتصال PostgreSQL
$dsn_pg = "pgsql:host=$host_pg;dbname=$db_pg";

try {
    // ایجاد یک شیء PDO برای اتصال به PostgreSQL
    $pdo_pg = new PDO($dsn_pg, $user_pg, $pass_pg, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // برای نمایش خطاها به صورت Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // برای دریافت نتایج به صورت آرایه associative
    ]);

    echo "اتصال به دیتابیس PostgreSQL با موفقیت برقرار شد!";

    // حالا می‌توانید از $pdo_pg برای اجرای کوئری‌های PostgreSQL استفاده کنید.
    // مثال:
    // $stmt = $pdo_pg->query("SELECT version()");
    // print_r($stmt->fetch());

} catch (PDOException $e) {
    // در صورت بروز خطا در اتصال PostgreSQL
    die("خطا در اتصال به دیتابیس PostgreSQL: " . $e->getMessage());
}

echo "<br>"; // خط جداکننده برای نمایش احتمالی خطای اتصال MySQL

// اگر همچنان نیاز به اتصال به دیتابیس MySQL دارید، باید کد مجزایی برای آن بنویسید.
// کد زیر برای اتصال MySQL شماست، اما توجه داشته باشید که این دو اتصال مستقل از هم هستند.
// $serverName = "acela.proxy.rlwy.net";
// $userName = "root";
// $password = "IRHMjKYYiWvjxEOnoStdxKHHGLdGFkzr";
// $dbName = "railway";
//
// $conn_mysql = mysqli_connect($serverName, $userName, $password, $dbName);
//
// if (!$conn_mysql) {
//     die("خطا در اتصال به دیتابیس MySQL: " . mysqli_connect_error());
// } else {
//     echo "اتصال به دیتابیس MySQL نیز برقرار شد!";
// }
 

 
$pastName = "TASMD";
$adminsGap = "-7761540434";
//----------
$adminsTable = "$pastName-admins";
$itemsTable = "$pastName-items";
$soldiersTable = "$pastName-soldiers";
$peopleTable = "$pastName-people";
$buildingsTable = "$pastName-buildings";
$campsTable = "$pastName-camps";
//-----
$citiesTable = "$pastName-cities";
$cityBuildingsTable = "$pastName-cityBuildings";
$cityItemsTable = "$pastName-cityItems";
$citySoldiersTable = "$pastName-citySoldiers";
$cityPeopleTable = "$pastName-cityPeople";
$cityCampsTable = "$pastName-cityCamps";
//----------important files-------------
include './telegram.php';
include './db.php';
include './functions.php';
include './keyboards.php';
//---------------------------

if ($tc == "private") {
    include './admin-panel.php';
} else {
    include './management-panel.php';
    include './bot-sections/financial.php';
    if ($from_id == $playerId) {
        include './bot-sections/player-panel.php';
        include './bot-sections/upgrade.php';
        include './bot-sections/trading.php';
    }
}



//----------get-id-----------
if ($text == "/id") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "<code>$chat_id</code>",
        'parse_mode' => "HTML",
    ]);
}
unlink("error_log");
