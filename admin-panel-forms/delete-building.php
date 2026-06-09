<?php
 if ($text ==  "[❌]- حذف ساختمان" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🏯 نام انگلیسی ساختمان را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-building' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "delete-building" && $text != "🔙") {
    $building = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `english name` = '{$text}' LIMIT 1"));
    if (!$building) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "هیچ ساختمانی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "ساختمان حذف شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$buildingsTable` WHERE `english name` = '{$text}'");
        $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$text}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}