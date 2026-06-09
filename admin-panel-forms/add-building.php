<?php
 if ($text == "[🏗️]- افزودن ساختمان" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی ساختمان را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-1' WHERE `id`='{$from_id}' LIMIT 1");
} else if ($theAdminStep == "add-building-1" && $text != "🔙") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام فارسی ساختمان را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-2-$text' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$adminsTable` SET `thing`='{$text}' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("ALTER TABLE `$cityBuildingsTable` ADD `{$text}` varchar(255)");
    sendDataForDb($buildingsTable,  "english name", $text);
} else if (strpos($theAdminStep, "add-building-2-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-2-", '', $theAdminStep);
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
        'text' => "📌هزینه ارتقا اول را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این ساختمان مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `persian name`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-3-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-3-", '', $theAdminStep);
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
        'text' => "📌هزینه ارتقا دوم را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این ساختمان مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 1`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-4-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-4-", '', $theAdminStep);
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
        'text' => "📌هزینه ارتقا سوم را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این ساختمان مورد نیاز است؟\n\n <code>$strItemList=></code>",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-5-$idm' WHERE `id`='{$from_id}'LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 2`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-5-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-5-", '', $theAdminStep);
    $myItemList = [];
    while ($row = mysqli_fetch_assoc($itemsListEn)) {
        array_push($myItemList, $row["english name"]);
    }
    while ($row = mysqli_fetch_assoc($peopleListEn)) {
        array_push($myItemList, $row["english name"]);
    }
    $strItemList = implode("\n", $myItemList);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 این ساختمان چه آیتمی بازدهی می دهد؟\n\n $strItemList",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-6-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 3`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-6-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-6-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 میزان بازدهی در هر لول را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-7-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `efficiency item`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-7-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-7-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول اولیه این ساختمان در دارایی را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-8-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `efficiency number`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-8-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-8-", '', $theAdminStep);
    $buildingT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $buildingT["persian name"];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول نهایی این ساختمان در دارایی را وارد کنید :",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
    $str = "$persianName@$text";
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-9-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `first level`='{$text}' WHERE `english name`='$idm' LIMIT 1");
    $conn->query("UPDATE `$cityBuildingsTable` SET `{$idm}`='{$str}'");
} else if (strpos($theAdminStep, "add-building-9-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-9-", '', $theAdminStep);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add-building-10-$idm' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("UPDATE `$buildingsTable` SET `last level`='{$text}' WHERE `english name`='$idm' LIMIT 1");
} else if (strpos($theAdminStep, "add-building-10-") !== false && $text != "🔙") {
    $idm = str_replace("add-building-10-", '', $theAdminStep);
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
        $conn->query("DELETE FROM `$buildingsTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$idm}`");
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
} else {
    if ($text == "🔙") {
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        $idm;
        switch ($theAdminStep) {
            case strpos($theAdminStep, "add-building-2-") !== false:
                $idm = str_replace("add-building-2-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-3-") !== false:
                $idm = str_replace("add-building-3-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-4-") !== false:
                $idm = str_replace("add-building-4-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-5-") !== false:
                $idm = str_replace("add-building-5-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-6-") !== false:
                $idm = str_replace("add-building-6-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-7-") !== false:
                $idm = str_replace("add-building-7-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-8-") !== false:
                $idm = str_replace("add-building-8-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-9-") !== false:
                $idm = str_replace("add-building-9-", '', $theAdminStep);
                break;
            case strpos($theAdminStep, "add-building-10-") !== false:
                $idm = str_replace("add-building-10-", '', $theAdminStep);
                break;
        }
        $conn->query("DELETE FROM `$buildingsTable` WHERE `english name` = '{$idm}'");
        $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$idm}`");
    }
}
