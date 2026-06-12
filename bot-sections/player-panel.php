<?php
$botText = "⚜️- به ربات بازی خوش آمدید.";
$teamText = $playerCheck == "Yes" ? "" : "➖➖➖➖➖➖➖➖\n🤖 ساخته شده توسط دوست ممدقلی استاد هکر برنامه نویس اقای مهندس Mr.A\n📣 Channel : چنل دارم ولی لینکشو نمیدم چون دوست ندارم\n👤 سفارش ربات : @Dev_MrA  ";

if ($text == "پنل" && $playerStep == "none") {
    if (!$playerCheck) {
        $conn->query("UPDATE `$citiesTable` SET `Check`='Yes' WHERE `city id`='{$chat_id}'LIMIT 1");
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "$botText\n$teamText",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $playerPanel,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}

if ($text == "back") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $botText,
        'reply_markup' => $playerPanel,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}
