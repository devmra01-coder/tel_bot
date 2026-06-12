<?php

if ($text == "upgrade") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "[⚒]- کدام ساختمان را ارتقا می دهید؟",
        'reply_markup' => $upgradePanel,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade-1' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "upgrade-1" && $stop == "No" && $text) {
    $buildingsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$text}' LIMIT 1"));
    $campsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$text}' LIMIT 1"));
    $upgradeItemsNums_1 = $buildingsTable ? $buildingsTable["upgrade items numbers 1"] : $campsTable["upgrade items numbers 1"];
    $upgradeItemsNums_1 = ($upgradeItemsNums_1 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن اول :\n\n $upgradeItemsNums_1" : "";
    $upgradeItemsNums_2 = $buildingsTable ? $buildingsTable["upgrade items numbers 2"] : $campsTable["upgrade items numbers 2"];
    $upgradeItemsNums_2 = ($upgradeItemsNums_2 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن دوم :\n\n $upgradeItemsNums_2" : "";
    $upgradeItemsNums_3 = $buildingsTable ? $buildingsTable["upgrade items numbers 3"] : $campsTable["upgrade items numbers 3"];
    $upgradeItemsNums_3 = ($upgradeItemsNums_3 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن سوم :\n\n $upgradeItemsNums_3" : "";
    $inlineKeyboard = json_encode([
        'inline_keyboard' => [
            [['text' => "[🔖]- پلن اول ", 'callback_data' => "plan-1"]],
            [['text' => "🔙", 'callback_data' => "back"]]
        ]
    ]);
    if ($upgradeItemsNums_2 != "") {
        $inlineKeyboard = json_encode([
            'inline_keyboard' => [
                [['text' => "[🔖]- پلن دوم ", 'callback_data' => "plan-2"], ['text' => "[🔖]- پلن اول ", 'callback_data' => "plan-1"]],
                [['text' => "🔙", 'callback_data' => "back"]]
            ]
        ]);
    }
    if ($upgradeItemsNums_3 != "") {
        $inlineKeyboard = json_encode([
            'inline_keyboard' => [
                [['text' => "[🔖]- پلن سوم ", 'callback_data' => "plan-3"], ['text' => "[🔖]- پلن دوم ", 'callback_data' => "plan-2"], ['text' => "[🔖]- پلن اول ", 'callback_data' => "plan-1"]],
                [['text' => "🔙", 'callback_data' => "back"]]
            ]
        ]);
    }
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "
[💸]- شما می توانید با پرداخت یکی از پلن های هزینه زیر اقدام به ارتقا این ساختمان کنید :
$upgradeItemsNums_1 $upgradeItemsNums_2 $upgradeItemsNums_3

📌 اگر مایل به انجام این ارتقا هستید، یکی از پلن های زیر را انتخاب کنید و در غیر این صورت بر روی دکمه «🔙» کلیک کنید.
",
        'reply_markup' => $inlineKeyboard,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`= 'upgrade-2@$text' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if (strpos($playerStep, "upgrade-2@") !== false && $stop == "No" && $text) {
    $bName = str_replace("upgrade-2@", '', $playerStep);
    $inlineYesOrNo = json_encode([
        'inline_keyboard' => [
            [['text' => "خیر", 'callback_data' => "No"], ['text' => "بله", 'callback_data' => "Yes-$text"]]
        ]
    ]);
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "
[⁉️]- آیا تایید می کنید ؟
",
        'reply_markup' => $inlineYesOrNo,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade-3@$bName' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if (strpos($playerStep, "upgrade-3@") !== false && $stop == "No" && $text) {
    $bName = str_replace("upgrade-3@", '', $playerStep);
    if (strpos($text, "Yes-") !== false) {
        $plan = str_replace("Yes-", '', $text);

        $buildingsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));
        $campsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));
        $nums = "";
        switch ($plan) {
            case 'plan-1':
                $nums = $buildingsTable ? $buildingsTable["upgrade items numbers 1"] : $campsTable["upgrade items numbers 1"];

                break;
            case 'plan-2':
                $nums = $buildingsTable ? $buildingsTable["upgrade items numbers 2"] : $campsTable["upgrade items numbers 2"];

                break;
            case 'plan-3':
                $nums = $buildingsTable ? $buildingsTable["upgrade items numbers 3"] : $campsTable["upgrade items numbers 3"];

                break;

            default:
                $nums = $buildingsTable ? $buildingsTable["upgrade items numbers 1"] : $campsTable["upgrade items numbers 1"];
                break;
        }
        $buildingEnglishName = $buildingsTable ? $buildingsTable["english name"] : $campsTable["english name"];
        $campOrBuilding = $buildingsTable ? $cityBuildingsTable : $cityCampsTable;
        $costsArray = explode("\n", $nums);
        $result = [];
        foreach ($costsArray as $vlue) {
            $vlue = explode("=>", $vlue);
            $itemPersianName = trim($vlue[0]);
            $number = trim($vlue[1]);
            $theItemsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
            $itemEnglishName = $theItemsTable["english name"];
            $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $item = $cityItems[$itemEnglishName];
            if ($item) {
                $itemArray = explode("@", $item);
                if ($vlue[1] <= $itemArray[1]) {
                    $newNumber = $itemArray[1] - $vlue[1];
                    $string = "$itemEnglishName@$newNumber";
                    array_push($result, $string);
                } else {
                    $string = "false";
                    array_push($result, $string);
                }
            } else {
                ## nothing
            }
        }

        foreach ($costsArray as $vlue) {
            $vlue = explode("=>", $vlue);
            $itemPersianName = trim($vlue[0]);
            $number = trim($vlue[1]);
            $thePeopleTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
            $personEnglishName = $thePeopleTable["english name"];
            $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $person = $cityPeople[$personEnglishName];
            $itemArray = explode("@", $person);
            if ($person) {
                if ($vlue[1] <= $itemArray[1]) {
                    $newNumber = $itemArray[1] - $vlue[1];
                    $string = "$personEnglishName@$newNumber";
                    array_push($result, $string);
                } else {
                    $string = "false";
                    array_push($result, $string);
                }
            } else {
                ## nothing
            }
        }

        if (in_array("false", $result)) {
            EditMessageText($chat_id, $message_id, "منابع کافی نیست");
        } else {
            foreach ($result as $value) {
                $value = explode("@", $value);
                $englishName = $value[0];
                $newNum = $value[1];
                $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $item = $cityItems["$englishName"];
                $itemArray = explode("@", $item);
                $itemPersianName = $itemArray[0];
                $save = "$itemPersianName@$newNum";
                $conn->query("UPDATE `$cityItemsTable` SET `{$englishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            foreach ($result as $value) {
                $value = explode("@", $value);
                $englishName = $value[0];
                $newNum = $value[1];
                $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $item = $cityItems["$englishName"];
                $itemArray = explode("@", $item);
                $itemPersianName = $itemArray[0];
                $save = "$itemPersianName@$newNum";
                $conn->query("UPDATE `$cityPeopleTable` SET `{$englishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            $cityBuildings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campOrBuilding` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $building = $cityBuildings["$buildingEnglishName"];
            $buildingArray = explode("@", $building);
            $buildingPersianName = $buildingArray[0];
            $newLevel = $buildingArray[1] + 1;
            $save = "$buildingPersianName@$newLevel";
            $conn->query("UPDATE `$campOrBuilding` SET `{$buildingEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            EditMessageText($chat_id, $message_id, "Done!");
        }
    }
    if ($text == "No") {
        EditMessageText($chat_id, $message_id, "این ارتقا لغو شد قربان!");
    }
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}
