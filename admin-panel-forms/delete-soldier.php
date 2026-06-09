<?php
  if ($text == "[❌]- حذف سرباز" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🏯 نام انگلیسی سرباز را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-soldier' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "delete-soldier" && $text != "🔙") {
    $soldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$text}' LIMIT 1"));
    if (!$soldier) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "هیچ سربازی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "سرباز حذف شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$soldiersTable` WHERE `english name` = '{$text}'");
        $conn->query("ALTER TABLE `$citySoldiersTable` DROP COLUMN `{$text}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}