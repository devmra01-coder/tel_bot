<?php
if ($text == "[➕]- افزودن آیتم" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-item-1' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "add-item-1" && $text != "🔙") {

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام فارسی آیتم را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-item-2-$text' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$adminsTable` SET `thing`='{$text}' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("ALTER TABLE `$cityItemsTable` ADD `{$text}` varchar(255)");
    sendDataForDb($itemsTable,  "english name", $text);
} else if (strpos($theAdminStep, "add-item-2-") !== false && $text != "🔙") {
    $idm = str_replace("add-item-2-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه آیتم را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-item-3-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$itemsTable` SET `persian name`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-item-3-") !== false && $text != "🔙") {
    $idm = str_replace("add-item-3-", '', $theAdminStep);
    $itemsT = mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `english name` = '{$idm}' LIMIT 1");
    $getItemsT = mysqli_fetch_assoc($itemsT);
    $persianName = $getItemsT["persian name"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);
    $str = "$persianName@$text";
    $conn->query("UPDATE `$adminsTable` SET `step`='add-item-4-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$itemsTable` SET `first number`='{$text}' WHERE `english name`='$idm'LIMIT 1");
    $conn->query("UPDATE `$cityItemsTable` SET `{$idm}`='{$str}'");
} else if (strpos($theAdminStep, "add-item-4-") !== false && $text != "🔙") {
    $idm = str_replace("add-item-4-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "Done!",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    }

    if ($text == "❌") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "Ah shit",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
        $conn->query("DELETE FROM `$itemsTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityItemsTable` DROP COLUMN `{$idm}`");
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
}