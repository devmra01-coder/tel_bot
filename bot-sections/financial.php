<?php

/**
 * ========================================
 * بخش دارایی شهر (Financial Panel)
 * ========================================
 * نمایش تمام منابع، آیتم‌ها، شخصیت‌ها، سربازان، ساختمان‌ها و کمپ‌های نظامی
 */

function buildFinancialMessage($db, $chat_id) {
    // دریافت اطلاعات شهر
    $cityData = $db->findOne('cities', 'city id', $chat_id);
    if (!$cityData) {
        return "⚠️ <b>شهری برای این شناسه پیدا نشد!</b>";
    }
    
    $cityName = htmlspecialchars($cityData['city name'] ?? "[بدون نام]", ENT_QUOTES, 'UTF-8');
    $lordName = htmlspecialchars($cityData['lord name'] ?? "[بدون فرمانده]", ENT_QUOTES, 'UTF-8');
    
    // بخش‌های مختلف دارایی
    $financialItems = "";
    $financialPeople = "";
    $financialSoldiers = "";
    $financialBuildings = "";
    $financialCamps = "";
    
    // ========================================
    // 📜 نمایش آیتم‌های دارایی
    // ========================================
    $allItems = $db->getAll('items') ?: [];
    if (!empty($allItems)) {
        foreach ($allItems as $item) {
            $englishName = $item['english name'] ?? "";
            $persianName = htmlspecialchars($item['persian name'] ?? $englishName, ENT_QUOTES, 'UTF-8');
            
            // دریافت مقدار آیتم در این شهر
            $cityItems = $db->findOne('cityItems', 'city id', $chat_id);
            if ($cityItems && isset($cityItems[$englishName])) {
                $itemData = $cityItems[$englishName];
                $itemArray = explode("@", $itemData);
                
                if (isset($itemArray[1])) {
                    $amount = intval($itemArray[1]);
                    if ($amount > 0) {
                        $financialItems .= "🔹 " . $persianName . " : <code>" . number_format($amount) . "</code>\n";
                    }
                }
            }
        }
    }
    
    // ========================================
    // 👥 نمایش شخصیت‌ها
    // ========================================
    $allPeople = $db->getAll('people') ?: [];
    if (!empty($allPeople)) {
        foreach ($allPeople as $person) {
            $englishName = $person['english name'] ?? "";
            $persianName = htmlspecialchars($person['persian name'] ?? $englishName, ENT_QUOTES, 'UTF-8');
            
            $cityPeople = $db->findOne('cityPeople', 'city id', $chat_id);
            if ($cityPeople && isset($cityPeople[$englishName])) {
                $personData = $cityPeople[$englishName];
                $personArray = explode("@", $personData);
                
                if (isset($personArray[1])) {
                    $amount = intval($personArray[1]);
                    if ($amount > 0) {
                        $financialPeople .= "👤 " . $persianName . " : <code>" . number_format($amount) . "</code>\n";
                    }
                }
            }
        }
    }
    
    // ========================================
    // ⚔️ نمایش سربازان
    // ========================================
    $allSoldiers = $db->getAll('soldiers') ?: [];
    if (!empty($allSoldiers)) {
        foreach ($allSoldiers as $soldier) {
            $englishName = $soldier['english name'] ?? "";
            $persianName = htmlspecialchars($soldier['persian name'] ?? $englishName, ENT_QUOTES, 'UTF-8');
            
            $citySoldiers = $db->findOne('citySoldiers', 'city id', $chat_id);
            if ($citySoldiers && isset($citySoldiers[$englishName])) {
                $soldierData = $citySoldiers[$englishName];
                $soldierArray = explode("@", $soldierData);
                
                if (isset($soldierArray[1])) {
                    $amount = intval($soldierArray[1]);
                    if ($amount > 0) {
                        $financialSoldiers .= "⚔️ " . $persianName . " : <code>" . number_format($amount) . "</code>\n";
                    }
                }
            }
        }
    }
    
    // ========================================
    // 🏯 نمایش ساختمان‌ها (با محاسبه کارایی)
    // ========================================
    $allBuildings = $db->getAll('buildings') ?: [];
    if (!empty($allBuildings)) {
        foreach ($allBuildings as $building) {
            $englishName = $building['english name'] ?? "";
            $persianName = htmlspecialchars($building['persian name'] ?? $englishName, ENT_QUOTES, 'UTF-8');
            $efficiencyNumber = intval($building['efficiency number'] ?? 1);
            
            $cityBuildings = $db->findOne('cityBuildings', 'city id', $chat_id);
            if ($cityBuildings && isset($cityBuildings[$englishName])) {
                $buildingData = $cityBuildings[$englishName];
                $buildingArray = explode("@", $buildingData);
                
                if (isset($buildingArray[1])) {
                    $level = intval($buildingArray[1]);
                    if ($level > 0) {
                        $efficiency = $level * $efficiencyNumber;
                        $financialBuildings .= "🏯 " . $persianName . " [Lvl.<code>" . $level . "</code>] → <code>" . number_format($efficiency) . "</code>\n";
                    }
                }
            }
        }
    }
    
    // ========================================
    // ⛺️ نمایش کمپ‌های نظامی (با محاسبه کارایی)
    // ========================================
    $allCamps = $db->getAll('camps') ?: [];
    if (!empty($allCamps)) {
        foreach ($allCamps as $camp) {
            $englishName = $camp['english name'] ?? "";
            $persianName = htmlspecialchars($camp['persian name'] ?? $englishName, ENT_QUOTES, 'UTF-8');
            $efficiencyNumber = intval($camp['efficiency number'] ?? 1);
            
            $cityCamps = $db->findOne('cityCamps', 'city id', $chat_id);
            if ($cityCamps && isset($cityCamps[$englishName])) {
                $campData = $cityCamps[$englishName];
                $campArray = explode("@", $campData);
                
                if (isset($campArray[1])) {
                    $level = intval($campArray[1]);
                    if ($level > 0) {
                        $efficiency = $level * $efficiencyNumber;
                        $financialCamps .= "⛺️ " . $persianName . " [Lvl.<code>" . $level . "</code>] → <code>" . number_format($efficiency) . "</code>\n";
                    }
                }
            }
        }
    }
    
    // ========================================
    // ساخت پیام نهایی
    // ========================================
    $messageText = "╔════════════════════════════════╗\n";
    $messageText .= "║ 🗺 <b>معلومات شهر</b>\n";
    $messageText .= "╚════════════════════════════════╝\n\n";
    
    $messageText .= "🏛 <b>نام شهر :</b> " . $cityName . "\n";
    $messageText .= "👑 <b>فرمانده :</b> " . $lordName . "\n";
    $messageText .= "\n────────────────────────────────\n\n";
    
    // آیتم‌های دارایی
    if (!empty($financialItems)) {
        $messageText .= "📜 <b>آیتم‌های دارایی :</b>\n" . $financialItems . "\n";
    }
    
    // شخصیت‌ها
    if (!empty($financialPeople)) {
        $messageText .= "👥 <b>جمعیت :</b>\n" . $financialPeople . "\n";
    }
    
    // ساختمان‌ها
    if (!empty($financialBuildings)) {
        $messageText .= "🏯 <b>ساختمان‌ها :</b>\n" . $financialBuildings . "\n";
    }
    
    // سربازان
    if (!empty($financialSoldiers)) {
        $messageText .= "⚔️ <b>ارتش :</b>\n" . $financialSoldiers . "\n";
    }
    
    // کمپ‌های نظامی
    if (!empty($financialCamps)) {
        $messageText .= "⛺️ <b>پایگاه‌های نظامی :</b>\n" . $financialCamps . "\n";
    }
    
    // اگر هیچ دارایی نبود
    if (empty($financialItems) && empty($financialPeople) && empty($financialSoldiers) && 
        empty($financialBuildings) && empty($financialCamps)) {
        $messageText .= "⚠️ <b>هیچ دارایی ثبت نشده است!</b>\n\n";
    }
    
    $messageText .= "╚════════════════════════════════╝";
    
    return $messageText;
}

// ========================================
// 🔘 دستور کالبک (Inline Button)
// ========================================
if ($data == "show financial" && $playerStep == "none") {
    $messageText = buildFinancialMessage($db, $chat_id);
    
    $backButton = json_encode([
        'inline_keyboard' => [[
            ['text' => "🔙 بازگشت", 'callback_data' => "back"]
        ]]
    ], JSON_UNESCAPED_UNICODE);
    
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $messageText,
        'parse_mode' => "HTML",
        'reply_markup' => $backButton,
    ]);
}

// ========================================
// 💬 دستور متنی (Text Command)
// ========================================
if ($text === "دارایی" && $playerStep == "none") {
    $messageText = buildFinancialMessage($db, $chat_id);
    
    $backButton = json_encode([
        'inline_keyboard' => [[
            ['text' => "🔙 بازگشت", 'callback_data' => "back"]
        ]]
    ], JSON_UNESCAPED_UNICODE);
    
    SendMessage($chat_id, $messageText, "HTML", $message_id, $backButton);
}

?>