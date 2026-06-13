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
} else if (strpos($playerStep, "upgrade-3@") !== false && $stop == "No" && $text) {
    $bName = str_replace("upgrade-3@", '', $playerStep);
    $plan = $text;

    // 1. تشخیص اینکه با ساختمان روبرو هستیم یا کمپ (فقط یکبار کوئری می‌زنیم)
    $buildingData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));
    $campData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$bName}' LIMIT 1"));

    $entityData = $buildingData ?: $campData; // اگر ساختمان بود استفاده کن، اگر نبود کمپ
    
    if (!$entityData) {
        EditMessageText($chat_id, $message_id, "خطا: موجودیت یافت نشد.");
        return;
    }

    // تعیین نام جدول مربوطه برای آپدیت نهایی
    $currentEntityTable = $buildingData ? $cityBuildingsTable : $cityCampsTable;
    $entityEnglishName = $entityData["english name"];

    // 2. استخراج لیست هزینه‌ها بر اساس پلن
    $planIndex = str_replace('plan-', '', $plan);
    $planIndex = empty($planIndex) ? '1' : $planIndex; // اگر plan بود، تبدیل به شماره کن
    $nums = $entityData["upgrade items numbers $planIndex"] ?? "";

    if (empty($nums)) {
        EditMessageText($chat_id, $message_id, "این پلن هزینه‌ای ندارد یا نامعتبر است.");
        return;
    }

    $costsArray = explode("\n", trim($nums));
    $updateQueue = []; // برای ذخیره موقت تغییرات
    $canProceed = true;

    // 3. مرحله بررسی (Validation) - بدون هیچ آپدیتی در دیتابیس
    foreach ($costsArray as $line) {
        if (empty(trim($line)) || strpos($line, '=>') === false) continue;

        list($itemPersianName, $requiredAmount) = array_map('trim', explode("=>", $line));
        $requiredAmount = intval($requiredAmount);

        // پیدا کردن اطلاعات آیتم/آدم (در اینجا از یک متد تجمیعی استفاده می‌کنیم)
        $itemInfo = null;
        
        // الف) چک کردن در جدول Items
        $itemRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `english name` FROM `$itemsTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
        if ($itemRow) {
            $engName = $itemRow['english name'];
            $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $currentValStr = $cityItems[$engName] ?? "0@0";
            $itemInfo = ['type' => 'item', 'eng' => $engName, 'persian' => explode('@', $currentValStr)[0], 'current' => intval(explode('@', $currentValStr)[1])];
        } 
        // ب) چک کردن در جدول People
        else {
            $personRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `english name` FROM `$peopleTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
            if ($personRow) {
                $engName = $personRow['english name'];
                $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $currentValStr = $cityPeople[$engName] ?? "0@0";
                $itemInfo = ['type' => 'person', 'eng' => $engName, 'persian' => explode('@', $currentValStr)[0], 'current' => intval(explode('@', $currentValStr)[1])];
            }
        }

        if ($itemInfo && $itemInfo['current'] >= $requiredAmount) {
            $updateQueue[] = [
                'type' => $itemInfo['type'],
                'eng' => $itemInfo['eng'],
                'persian' => $itemInfo['persian'],
                'new_val' => $itemInfo['current'] - $requiredAmount
            ];
        } else {
            $canProceed = false;
            break;
        }
    }

    // 4. مرحله اجرا (Execution) - فقط اگر همه چیز اوکی بود
    if (!$canProceed || empty($updateQueue)) {
        EditMessageText($chat_id, $message_id, "❌ منابع کافی نیست!");
    } else {
        // شروع آپدیت‌ها
        foreach ($updateQueue as $upd) {
            $saveString = "{$upd['persian']}@{$upd['new_val']}";
            $targetTable = ($upd['type'] === 'item') ? $cityItemsTable : $cityPeopleTable;
            $conn->query("UPDATE `$targetTable` SET `{$upd['eng']}` = '{$saveString}' WHERE `city id` = '{$chat_id}' LIMIT 1");
        }

        // آپدیت سطح ساختمان/کمپ
        $cityEntity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$currentEntityTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
        $eParts = explode('@', $cityEntity[$entityEnglishName]);
        $newLevel = intval($eParts[1]) + 1;
        $saveEntity = "{$eParts[0]}@$newLevel";
        $conn->query("UPDATE `$currentEntityTable` SET `{$entityEnglishName}` = '{$saveEntity}' WHERE `city id` = '{$chat_id}' LIMIT 1");

        EditMessageText($chat_id, $message_id, "✅ با موفقیت ارتقا یافت!");
    }
}

    if ($text == "No") {
        EditMessageText($chat_id, $message_id, "این ارتقا لغو شد قربان!");
    }
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}
