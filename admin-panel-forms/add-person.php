<?php
if ($text == "[👤]- افزودن شخصیت" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی شخصیت را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-person-1' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "add-person-1" && $text != "🔙") {

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام فارسی شخصیت را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-person-2-$text' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("ALTER TABLE `$cityPeopleTable` ADD `{$text}` varchar(255)");
    sendDataForDb($peopleTable,  "english name", $text);
} else if (strpos($theAdminStep, "add-person-2-") !== false && $text != "🔙") {
    $idm = str_replace("add-person-2-", '', $theAdminStep);
    $itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsList)) {
        array_push($myItemList, $row["persian name"]);
    }
    $strItemList = implode("=>\n", $myItemList);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 این شخصیت چه آیتمی مصرف می کند؟\n\n<code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-person-3-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$peopleTable` SET `persian name`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-person-3-") !== false && $text != "🔙") {
    $idm = str_replace("add-person-3-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه شخصیت در دارایی را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-person-4-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$peopleTable` SET `consumable item`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-person-4-") !== false && $text != "🔙") {
    $idm = str_replace("add-person-4-", '', $theAdminStep);
    $soldiersT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $soldiersT["persian name"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);
    $str = "$persianName@$text";
    $conn->query("UPDATE `$adminsTable` SET `step`='add-person-5-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$peopleTable` SET `first number`='{$text}' WHERE `english name`='$idm'LIMIT 1");
    $conn->query("UPDATE `$cityPeopleTable` SET `{$idm}`='{$str}'");
} else if (strpos($theAdminStep, "add-person-5-") !== false && $text != "🔙") {
    $idm = str_replace("add-person-5-", '', $theAdminStep);
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
        $conn->query("DELETE FROM `$peopleTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityPeopleTable` DROP COLUMN `{$idm}`");
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
} else {
    if ($text == "🔙") {
        $idm;
        switch ($theAdminStep) {
            case strpos($theAdminStep, "add-person-2-") !== false:
                $idm = str_replace("add-person-2-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-person-3-") !== false:
                $idm = str_replace("add-person-3-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-person-4-") !== false:
                $idm = str_replace("add-person-4-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-person-5-") !== false:
                $idm = str_replace("add-person-5-", '', $theAdminStep);
                break;
        }
        $conn->query("DELETE FROM `$peopleTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityPeopleTable` DROP COLUMN `{$idm}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}