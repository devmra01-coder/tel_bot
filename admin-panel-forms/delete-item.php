<?php
 if ($text == "[❌]- حذف آیتم" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🏯 نام انگلیسی آیتم را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-item' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "delete-item" && $text != "🔙") {
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `english name` = '{$text}' LIMIT 1"));
    if (!$item) {
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "هیچ آیتمی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "آیتم حذف شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$itemsTable` WHERE `english name` = '{$text}'");
        $conn->query("ALTER TABLE `$cityItemsTable` DROP COLUMN `{$text}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}
