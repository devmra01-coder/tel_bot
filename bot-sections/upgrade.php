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
            [['text' => "خیر", 'callback_data' => "No"], ['text' => "بله" , 'callback_data' => '{$text}']]
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
}else if (strpos($playerStep, "upgrade-3@") !== false && $stop == "No" && $text) {
    $bName = str_replace("upgrade-3@", '', $playerStep);
    
    // ۱. دریافت اطلاعات ساختمان/کمپ
    $bQ = mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$bName}' LIMIT 1");
    $buildingsRow = mysqli_fetch_assoc($bQ);
    
    $cQ = mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$bName}' LIMIT 1");
    $campsRow = mysqli_fetch_assoc($cQ);

    $isBuilding = ($buildingsRow) ? true : false;
    $targetData = $isBuilding ? $buildingsRow : $campsRow;
    $campOrBuildingTable = $isBuilding ? $cityBuildingsTable : $cityCampsTable;
    
    // انتخاب پلن
    $planKey = "upgrade items numbers " . (str_replace('plan-', '', $text) ?: '1');
    $nums = $targetData[$planKey] ?? $targetData["upgrade items numbers 1"];
    
    // ۲. بررسی منابع (فقط یک لیست برای آیتم‌ها و مردم)
    $costsArray = explode("\n", trim($nums));
    $canAfford = true;
    $updates = []; // ذخیره تغییرات به شکل [ 'table' =>, 'col' =>, 'newVal' => ]

    foreach ($costsArray as $line) {
        if (strpos($line, '=>') === false) continue;
        list($itemPersianName, $neededNum) = array_map('trim', explode("=>", $line));
        
        // پیدا کردن اینکه این منبع کجاست (آیتم یا مردم)
        $itemInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `persian name` = '$itemPersianName' LIMIT 1"));
        $targetTable = $cityItemsTable;
        
        if (!$itemInfo) {
            $itemInfo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `persian name` = '$itemPersianName' LIMIT 1"));
            $targetTable = $cityPeopleTable;
        }

        if ($itemInfo) {
            $engName = $itemInfo['english name'];
            $cityData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$targetTable` WHERE `city id` = '$chat_id' LIMIT 1"));
            
            $rawVal = $cityData[$engName];
            $currentParts = explode("@", $rawVal);
            $currentNum = intval($currentParts[1]);

            if ($currentNum < $neededNum) {
                $canAfford = false; break;
            } else {
                $updates[] = [
                    'table' => $targetTable,
                    'col' => $engName,
                    'newName' => $currentParts[0],
                    'newNum' => $currentNum - $neededNum
                ];
            }
        }
    }

    // ۳. اعمال تغییرات
    if (!$canAfford) {
        EditMessageText($chat_id, $message_id, "❌ منابع کافی نیست!");
    } else {
        // آپدیت منابع
        foreach ($updates as $u) {
            $newVal = $u['newName'] . "@" . $u['newNum'];
            $conn->query("UPDATE `{$u['table']}` SET `{$u['col']}` = '$newVal' WHERE `city id` = '$chat_id'");
        }

        // ارتقای سطح ساختمان
        $engName = $targetData['english name'];
        $bData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$campOrBuildingTable` WHERE `city id` = '$chat_id' LIMIT 1"));
        $parts = explode("@", $bData[$engName]);
        $newLevel = intval($parts[1]) + 1;
        $newBVal = $parts[0] . "@" . $newLevel;
        
        $conn->query("UPDATE `$campOrBuildingTable` SET `{$engName}` = '$newBVal' WHERE `city id` = '$chat_id'");

        EditMessageText($chat_id, $message_id, "✅ ارتقا با موفقیت انجام شد!");
    }

    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'");
}

