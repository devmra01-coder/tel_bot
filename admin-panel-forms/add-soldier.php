<?php

if ($text == "[⚔️]- افزودن سرباز" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی سرباز را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-soldier-1' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "add-soldier-1" && $text != "🔙") {

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام فارسی سرباز را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-soldier-2-$text' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("ALTER TABLE `$citySoldiersTable` ADD `{$text}` varchar(255)");
    sendDataForDb($soldiersTable,  "english name", $text);
} else if (strpos($theAdminStep, "add-soldier-2-") !== false && $text != "🔙") {
    $idm = str_replace("add-soldier-2-", '', $theAdminStep);
    $itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsList)) {
        array_push($myItemList, $row["persian name"]);
    }
    $strItemList = implode("=>\n", $myItemList);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 این سرباز چه آیتمی مصرف می کند؟\n\n<code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-soldier-3-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$soldiersTable` SET `persian name`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-soldier-3-") !== false && $text != "🔙") {
    $idm = str_replace("add-soldier-3-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه سرباز را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-soldier-4-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$soldiersTable` SET `consumable item`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-soldier-4-") !== false && $text != "🔙") {
    $idm = str_replace("add-soldier-4-", '', $theAdminStep);
    $soldiersT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $soldiersT["persian name"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);
    $str = "$persianName@$text";
    $conn->query("UPDATE `$adminsTable` SET `step`='add-soldier-5-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$soldiersTable` SET `first number`='{$text}' WHERE `english name`='$idm'LIMIT 1");
    $conn->query("UPDATE `$citySoldiersTable` SET `{$idm}`='{$str}'");
} else if (strpos($theAdminStep, "add-soldier-5-") !== false && $text != "🔙") {
    $idm = str_replace("add-soldier-5-", '', $theAdminStep);
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
        $conn->query("DELETE FROM `$soldiersTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$citySoldiersTable` DROP COLUMN `{$idm}`");
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
} else {
    if ($text == "🔙") {
        $idm;
        switch ($theAdminStep) {
            case strpos($theAdminStep, "add-soldier-2-") !== false:
                $idm = str_replace("add-soldier-2-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-soldier-3-") !== false:
                $idm = str_replace("add-soldier-3-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-soldier-4-") !== false:
                $idm = str_replace("add-soldier-4-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-soldier-5-") !== false:
                $idm = str_replace("add-soldier-5-", '', $theAdminStep);
                break;
        }
        $conn->query("DELETE FROM `$soldiersTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$citySoldiersTable` DROP COLUMN `{$idm}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}
