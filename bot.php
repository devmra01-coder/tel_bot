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
 
$serverName = "acela.proxy.rlwy.net";
$userName = "root";
$password = "IRHMjKYYiWvjxEOnoStdxKHHGLdGFkzr";
$dbName = "railway";
 
$conn = mysqli_connect($serverName, $userName, $password, $dbName);

 
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
