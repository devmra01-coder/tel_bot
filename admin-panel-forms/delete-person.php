<?php
  if ($text == "[❌]- حذف شخصیت" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "👤 نام انگلیسی شخصیت را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-person' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "delete-person" && $text != "🔙") {
    $soldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$text}' LIMIT 1"));
    if (!$soldier) {
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "هیچ شخصیتی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "شخصیت حذف شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$peopleTable` WHERE `english name` = '{$text}'");
        $conn->query("ALTER TABLE `$cityPeopleTable` DROP COLUMN `{$text}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}
