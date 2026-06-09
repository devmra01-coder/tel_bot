<?php

$update = file_get_contents('php://input');

if (empty($update)) {
    exit;
}

file_put_contents(
    __DIR__ . '/log.txt',
    date('Y-m-d H:i:s') . PHP_EOL .
    $update . PHP_EOL .
    str_repeat('-', 50) . PHP_EOL,
    FILE_APPEND
);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db-json.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/keyboards.php';
require_once __DIR__ . '/admin-panel.php';

$admins = Config::getInstance()->getAdminIds();
$pastName = BOT_PREFIX;
$adminsGap = ADMIN_GAP_ID;

if (isset($tc) && $tc === "private") {

} else {

    include_once __DIR__ . '/management-panel.php';
    include_once __DIR__ . '/bot-sections/financial.php';

    if (
        isset($playerId) &&
        isset($from_id) &&
        $from_id == $playerId
    ) {
        include_once __DIR__ . '/bot-sections/player-panel.php';
        include_once __DIR__ . '/bot-sections/upgrade.php';
        include_once __DIR__ . '/bot-sections/trading.php';
    }
}

if (
    isset($text) &&
    trim($text) === "/id"
) {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "<code>{$chat_id}</code>",
        'parse_mode' => 'HTML'
    ]);
}