<?php
if ($text == "[⛺️]- افزودن کمپ" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی کمپ را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-1' WHERE `id`='{$from_id}'LIMIT 1");
} else if ($theAdminStep == "add-camp-1" && $text != "🔙") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام فارسی کمپ را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-2-$text' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("ALTER TABLE `$cityCampsTable` ADD `{$text}` varchar(255)");
    sendDataForDb($campsTable,  "english name", $text);
} else if (strpos($theAdminStep, "add-camp-2-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-2-", '', $theAdminStep);
    $itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
    $peopleList = mysqli_query($conn, "SELECT `persian name` FROM `$peopleTable`");

    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsList)) {
        array_push($myItemList, $row["persian name"]);
    }
    while ($row = mysqli_fetch_assoc($peopleList)) {
        array_push($myItemList, $row["persian name"]);
    }
    $strItemList = implode("=>\n", $myItemList);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌هزینه ارتقا اول را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این کمپ مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-3-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `persian name`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-3-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-3-", '', $theAdminStep);
    $itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
    $peopleList = mysqli_query($conn, "SELECT `persian name` FROM `$peopleTable`");

    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsList)) {
        array_push($myItemList, $row["persian name"]);
    }
    while ($row = mysqli_fetch_assoc($peopleList)) {
        array_push($myItemList, $row["persian name"]);
    }
    $strItemList = implode("=>\n", $myItemList);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌هزینه ارتقا دوم را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این کمپ مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-4-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 1`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-4-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-4-", '', $theAdminStep);
    $itemsList = mysqli_query($conn, "SELECT `persian name` FROM `$itemsTable`");
    $peopleList = mysqli_query($conn, "SELECT `persian name` FROM `$peopleTable`");

    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsList)) {
        array_push($myItemList, $row["persian name"]);
    }
    while ($row = mysqli_fetch_assoc($peopleList)) {
        array_push($myItemList, $row["persian name"]);
    }
    $strItemList = implode("=>\n", $myItemList);

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌هزینه ارتقا سوم را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این کمپ مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-5-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 2`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-5-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-5-", '', $theAdminStep);
    $mySoldiersList = [];
    while ($row = mysqli_fetch_assoc($soldiersList)) {
        array_push($mySoldiersList, $row["english name"]);
    }
    $strSoldiersList = implode("\n", $mySoldiersList);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 این کمپ چه سربازی بازدهی می دهد؟\n\n $strSoldiersList",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-6-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 3`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-6-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-6-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 میزان بازدهی در هر لول را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-7-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `efficiency soldier`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-7-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-7-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول اولیه این ساختمان در دارایی را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-8-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `efficiency number`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-8-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-8-", '', $theAdminStep);
    $campsT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $campsT["persian name"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول نهایی این ساختمان در دارایی را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $str = "$persianName@$text";
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-9-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `first level`='{$text}' WHERE `english name`='$idm'LIMIT 1");
    $conn->query("UPDATE `$cityCampsTable` SET `{$idm}`='{$str}'");
} else if (strpos($theAdminStep, "add-camp-9-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-9-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-camp-10-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$campsTable` SET `last level`='{$text}' WHERE `english name`='$idm'LIMIT 1");
} else if (strpos($theAdminStep, "add-camp-10-") !== false && $text != "🔙") {
    $idm = str_replace("add-camp-10-", '', $theAdminStep);
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
        $conn->query("DELETE FROM `$campsTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityCampsTable` DROP COLUMN `{$idm}`");
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
} else {
    if ($text == "🔙") {
        $idm;
        switch ($theAdminStep) {
            case strpos($theAdminStep, "add-camp-2-") !== false:
                $idm = str_replace("add-camp-2-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-3-") !== false:
                $idm = str_replace("add-camp-3-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-4-") !== false:
                $idm = str_replace("add-camp-4-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-5-") !== false:
                $idm = str_replace("add-camp-5-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-6-") !== false:
                $idm = str_replace("add-camp-6-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-7-") !== false:
                $idm = str_replace("add-camp-7-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-8-") !== false:
                $idm = str_replace("add-camp-8-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-9-") !== false:
                $idm = str_replace("add-camp-9-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-camp-10-") !== false:
                $idm = str_replace("add-camp-10-", '', $theAdminStep);
                break;
        }
        $conn->query("DELETE FROM `$campsTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityCampsTable` DROP COLUMN `{$idm}`");
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
}
