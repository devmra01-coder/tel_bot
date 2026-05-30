<?php
 if ($text ==  "[❌]- حذف کمپ" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🏯 نام انگلیسی کمپ را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-camp' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "delete-camp" && $text != "🔙") {
    $camp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `english name` = '{$text}' LIMIT 1"));
    if (!$camp) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "هیچ کمپی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "کمپ حذف شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$campsTable` WHERE `english name` = '{$text}'");
        $conn->query("ALTER TABLE `$cityCampsTable` DROP COLUMN `{$text}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}