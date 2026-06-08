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


error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/error.log');

// لاگ رو توی خروجی هم نشون بده
$logFile = fopen('/tmp/debug.log', 'a');
fwrite($logFile, "[" . date('Y-m-d H:i:s') . "] Started\n");
fclose($logFile);

// تابع برای لاگ کردن
function logMsg($msg) {
    $logFile = fopen('/tmp/debug.log', 'a');
    fwrite($logFile, "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n");
    fclose($logFile);
}

// تست - اول لاگ رو چاپ کن
echo "Debug: Bot starting...\n";
logMsg("Bot starting...");


//---------------------------------------------------------------------------------------------
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db-json.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/keyboards.php';
require_once __DIR__ . '/admin-panel.php';
// convenience aliases
$admins = Config::getInstance()->getAdminIds();
$pastName = BOT_PREFIX;
$adminsGap = ADMIN_GAP_ID;
// db instance is $db from db-json.php
//---------------------------

// route includes depending on chat type
if ($tc == "private") {
    include_once __DIR__ . '/admin-panel.php';
} else {
    include_once __DIR__ . '/management-panel.php';
    include_once __DIR__ . '/bot-sections/financial.php';
    if (isset($playerId) && $from_id == $playerId) {
        include_once __DIR__ . '/bot-sections/player-panel.php';
        include_once __DIR__ . '/bot-sections/upgrade.php';
        include_once __DIR__ . '/bot-sections/trading.php';
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
@unlink(__DIR__ . '/error_log');
