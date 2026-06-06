<?php

if ($data == "upgrade") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "[⚒]- کدام ساختمان یا کمپ را ارتقا می‌دهید؟",
        'reply_markup' => $upgradePanel,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade-1' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "upgrade-1" && $stop == "No" && $data) {
    $buildingResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$data}' LIMIT 1"));
    $campResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$data}' LIMIT 1"));
    
    if (!$buildingResult && !$campResult) {
        EditMessageText($chat_id, $message_id, "[❌]- این ساختمان یا کمپ پیدا نشد!");
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
        return;
    }
    
    $upgradeItemsNums_1 = $buildingResult ? ($buildingResult["upgrade items numbers 1"] ?? "") : ($campResult["upgrade items numbers 1"] ?? "");
    $upgradeItemsNums_1 = ($upgradeItemsNums_1 && $upgradeItemsNums_1 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن اول :\n" . htmlspecialchars($upgradeItemsNums_1) : "";
    
    $upgradeItemsNums_2 = $buildingResult ? ($buildingResult["upgrade items numbers 2"] ?? "") : ($campResult["upgrade items numbers 2"] ?? "");
    $upgradeItemsNums_2 = ($upgradeItemsNums_2 && $upgradeItemsNums_2 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن دوم :\n" . htmlspecialchars($upgradeItemsNums_2) : "";
    
    $upgradeItemsNums_3 = $buildingResult ? ($buildingResult["upgrade items numbers 3"] ?? "") : ($campResult["upgrade items numbers 3"] ?? "");
    $upgradeItemsNums_3 = ($upgradeItemsNums_3 && $upgradeItemsNums_3 != "♨️ نیاز نیست") ? "\n\n[🔖]- پلن سوم :\n" . htmlspecialchars($upgradeItemsNums_3) : "";
    
    $inlineKeyboard = [
        [['text' => "[🔖]- پلن اول", 'callback_data' => "plan-1"]],
        [['text' => "🔙", 'callback_data' => "back"]]
    ];
    
    if ($upgradeItemsNums_2 != "") {
        $inlineKeyboard = [
            [['text' => "[🔖]- پلن دوم", 'callback_data' => "plan-2"], ['text' => "[🔖]- پلن اول", 'callback_data' => "plan-1"]],
            [['text' => "🔙", 'callback_data' => "back"]]
        ];
    }
    
    if ($upgradeItemsNums_3 != "") {
        $inlineKeyboard = [
            [['text' => "[🔖]- پلن سوم", 'callback_data' => "plan-3"], ['text' => "[🔖]- پلن دوم", 'callback_data' => "plan-2"], ['text' => "[🔖]- پلن اول", 'callback_data' => "plan-1"]],
            [['text' => "🔙", 'callback_data' => "back"]]
        ];
    }
    
    $messageText = "[💸]- شما می‌توانید با پرداخت یکی از پلن‌های هزینه زیر اقدام به ارتقاء این ساختمان کنید:\n";
    $messageText .= $upgradeItemsNums_1 . $upgradeItemsNums_2 . $upgradeItemsNums_3;
    $messageText .= "\n\n📌 اگر مایل به انجام این ارتقاء هستید، یکی از پلن‌های زیر را انتخاب کنید.";
    
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $messageText,
        'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard], JSON_UNESCAPED_UNICODE),
    ]);
    
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade-2@$data' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if (strpos($playerStep, "upgrade-2@") !== false && $stop == "No" && $data) {
    $bName = str_replace("upgrade-2@", '', $playerStep);
    $inlineYesOrNo = json_encode([
        'inline_keyboard' => [
            [['text' => "خیر", 'callback_data' => "No"], ['text' => "بله", 'callback_data' => "Yes-$data"]]
        ]
    ], JSON_UNESCAPED_UNICODE);
    
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "[⁉️]- آیا تایید می‌کنید؟",
        'reply_markup' => $inlineYesOrNo,
    ]);
    
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade-3@$bName' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if (strpos($playerStep, "upgrade-3@") !== false && $stop == "No" && $data) {
    $bName = str_replace("upgrade-3@", '', $playerStep);
    
    if (strpos($data, "Yes-") !== false) {
        $plan = str_replace("Yes-", '', $data);
        
        $buildingResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));
        $campResult = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));
        
        $nums = "";
        switch ($plan) {
            case 'plan-1':
                $nums = $buildingResult ? ($buildingResult["upgrade items numbers 1"] ?? "") : ($campResult["upgrade items numbers 1"] ?? "");
                break;
            case 'plan-2':
                $nums = $buildingResult ? ($buildingResult["upgrade items numbers 2"] ?? "") : ($campResult["upgrade items numbers 2"] ?? "");
                break;
            case 'plan-3':
                $nums = $buildingResult ? ($buildingResult["upgrade items numbers 3"] ?? "") : ($campResult["upgrade items numbers 3"] ?? "");
                break;
            default:
                $nums = $buildingResult ? ($buildingResult["upgrade items numbers 1"] ?? "") : ($campResult["upgrade items numbers 1"] ?? "");
                break;
        }
        
        $buildingEnglishName = $buildingResult ? ($buildingResult["english name"] ?? "") : ($campResult["english name"] ?? "");
        $campOrBuilding = $buildingResult ? $cityBuildingsTable : $cityCampsTable;
        $costsArray = explode("\n", $nums);
        $result = [];
        $hasEnough = true;
        
        // بررسی موجودی آیتم‌ها و شخصیت‌ها
        foreach ($costsArray as $vlue) {
            if (!$vlue) continue;
            $vlue = explode("=>", $vlue);
            $itemPersianName = trim($vlue[0] ?? "");
            $number = intval(trim($vlue[1] ?? "0"));
            
            if (!$itemPersianName || $number <= 0) continue;
            
            // بررسی آیتم‌ها
            $theItemsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
            if ($theItemsTable) {
                $itemEnglishName = $theItemsTable["english name"];
                $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $item = isset($cityItems[$itemEnglishName]) ? $cityItems[$itemEnglishName] : null;
                
                if ($item) {
                    $itemArray = explode("@", $item);
                    if (isset($itemArray[1]) && intval($itemArray[1]) >= $number) {
                        $newNumber = intval($itemArray[1]) - $number;
                        $string = "$itemEnglishName@$newNumber";
                        array_push($result, $string);
                    } else {
                        $hasEnough = false;
                        break;
                    }
                } else {
                    $hasEnough = false;
                    break;
                }
            } else {
                // بررسی شخصیت‌ها
                $thePeopleTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
                if ($thePeopleTable) {
                    $personEnglishName = $thePeopleTable["english name"];
                    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $person = isset($cityPeople[$personEnglishName]) ? $cityPeople[$personEnglishName] : null;
                    
                    if ($person) {
                        $personArray = explode("@", $person);
                        if (isset($personArray[1]) && intval($personArray[1]) >= $number) {
                            $newNumber = intval($personArray[1]) - $number;
                            $string = "$personEnglishName@$newNumber";
                            array_push($result, $string);
                        } else {
                            $hasEnough = false;
                            break;
                        }
                    } else {
                        $hasEnough = false;
                        break;
                    }
                }
            }
        }
        
        if (!$hasEnough || empty($result)) {
            EditMessageText($chat_id, $message_id, "[❌]- متأسفانه منابع کافی برای ارتقاء وجود ندارد!");
        } else {
            // به‌روزرسانی آیتم‌ها
            foreach ($result as $value) {
                $parts = explode("@", $value);
                if (count($parts) != 2) continue;
                $englishName = $parts[0];
                $newNum = $parts[1];
                
                $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                if (isset($cityItems[$englishName])) {
                    $item = $cityItems[$englishName];
                    $itemArray = explode("@", $item);
                    $itemPersianName = $itemArray[0];
                    $save = "$itemPersianName@$newNum";
                    $conn->query("UPDATE `$cityItemsTable` SET `{$englishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
            }
            
            // به‌روزرسانی شخصیت‌ها
            foreach ($result as $value) {
                $parts = explode("@", $value);
                if (count($parts) != 2) continue;
                $englishName = $parts[0];
                $newNum = $parts[1];
                
                $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                if (isset($cityPeople[$englishName])) {
                    $person = $cityPeople[$englishName];
                    $personArray = explode("@", $person);
                    $personPersianName = $personArray[0];
                    $save = "$personPersianName@$newNum";
                    $conn->query("UPDATE `$cityPeopleTable` SET `{$englishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
            }
            
            // به‌روزرسانی ساختمان/کمپ
            $cityBuildings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campOrBuilding` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            if (isset($cityBuildings[$buildingEnglishName])) {
                $building = $cityBuildings[$buildingEnglishName];
                $buildingArray = explode("@", $building);
                $buildingPersianName = $buildingArray[0];
                $newLevel = intval($buildingArray[1]) + 1;
                $save = "$buildingPersianName@$newLevel";
                $conn->query("UPDATE `$campOrBuilding` SET `{$buildingEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                EditMessageText($chat_id, $message_id, "[✅]- ارتقاء با موفقیت انجام شد!");
            }
        }
    } 
    else if ($data == "No") {
        EditMessageText($chat_id, $message_id, "[❌]- ارتقاء لغو شد!");
    }
    
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
}
?>