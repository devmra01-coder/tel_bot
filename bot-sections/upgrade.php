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
    $plan = $text;

    // اگر کاربر "No" را انتخاب کرد، پیام لغو را نمایش داده و مرحله را ریست می‌کنیم
    if ($plan === "No") {
        EditMessageText($chat_id, $message_id, "❌ ارتقا لغو شد.");
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
        return;
    }

    // 1. تشخیص موجودیت (ساختمان یا کمپ)
    // از متغیرهای اصلی $buildingsTable و $campsTable برای جستجو استفاده می‌کنیم
    $entityData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $bName) . "' LIMIT 1"));
    
    if (!$entityData) {
        $entityData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $bName) . "' LIMIT 1"));
    }

    if (!$entityData) {
        EditMessageText($chat_id, $message_id, "خطا: ساختمان یا کمپ مورد نظر یافت نشد.");
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
        return;
    }

    // تعیین نام جدول صحیح برای نمایش و آپدیت نهایی
    $entityEnglishName = $entityData["english name"];
    $currentEntityTable = isset($entityData['upgrade items numbers 1']) ? $cityBuildingsTable : $cityCampsTable; // تشخیص بر اساس وجود ستون ارتقا

    // 2. استخراج لیست هزینه‌ها بر اساس پلن انتخاب شده
    $planSuffix = str_replace('plan-', '', $plan);
    // اگر پلن نامعتبر بود، به صورت پیش‌فرض از پلن ۱ استفاده کن
    $planSuffix = ($planSuffix === '' || !ctype_digit($planSuffix)) ? '1' : $planSuffix; 
    $numsConfig = $entityData["upgrade items numbers $planSuffix"];

    if (empty($numsConfig)) {
        EditMessageText($chat_id, $message_id, "این پلن هزینه‌ای ندارد یا نامعتبر است.");
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
        return;
    }

    $costsLines = explode("\n", trim($numsConfig));
    $requiredResources = []; // ذخیره منابع مورد نیاز برای این ارتقا
    $canProceed = true;      // فلگ برای اینکه آیا ارتقا ممکن است یا خیر

    // 3. مرحله بررسی منابع (Validation Phase) - فقط خواندن از دیتابیس
    foreach ($costsLines as $line) {
        if (empty(trim($line)) || strpos($line, '=>') === false) continue;

        list($resourcePersianName, $amountNeeded) = array_map('trim', explode("=>", $line));
        $amountNeeded = intval($amountNeeded);

        $resourceInfo = null;

        // جستجو در آیتم‌ها
        $itemQuery = mysqli_query($conn, "SELECT `english name` FROM `$itemsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $resourcePersianName) . "' LIMIT 1");
        if ($itemRow = mysqli_fetch_assoc($itemQuery)) {
            $engName = $itemRow['english name'];
            $cityItemData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $currentValueStr = $cityItemData[$engName] ?? "0@0"; // مقدار فعلی به صورت "نام@تعداد"
            $currentAmount = intval(explode('@', $currentValueStr)[1]);
            $resourceInfo = ['type' => 'item', 'eng' => $engName, 'persian' => $resourcePersianName, 'needed' => $amountNeeded, 'current' => $currentAmount];
        } 
        // جستجو در افراد (نیروها)
        else {
            $personQuery = mysqli_query($conn, "SELECT `english name` FROM `$peopleTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $resourcePersianName) . "' LIMIT 1");
            if ($personRow = mysqli_fetch_assoc($personQuery)) {
                $engName = $personRow['english name'];
                $cityPersonData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $currentValueStr = $cityPersonData[$engName] ?? "0@0"; // مقدار فعلی به صورت "نام@تعداد"
                $currentAmount = intval(explode('@', $currentValueStr)[1]);
                $resourceInfo = ['type' => 'person', 'eng' => $engName, 'persian' => $resourcePersianName, 'needed' => $amountNeeded, 'current' => $currentAmount];
            }
        }

        // بررسی کافی بودن منبع
        if ($resourceInfo && $resourceInfo['current'] >= $resourceInfo['needed']) {
            $requiredResources[] = $resourceInfo; // اضافه کردن به لیست منابع مورد نیاز
        } else {
            $canProceed = false; // اگر حتی یک منبع کم باشد، ارتقا ممکن نیست
            break; // خروج از حلقه بررسی
        }
    }

    // 4. مرحله اجرا (Execution Phase) - فقط اگر همه منابع کافی بودند
    if (!$canProceed || empty($requiredResources)) {
        EditMessageText($chat_id, $message_id, "❌ منابع کافی نیست!");
    } else {
        // شروع تراکنش (Transaction) برای اطمینان از اینکه یا همه آپدیت‌ها انجام شوند یا هیچکدام
        $conn->begin_transaction();
        try {
            // کسر منابع (آیتم‌ها و افراد)
            foreach ($requiredResources as $resource) {
                $targetTable = ($resource['type'] === 'item') ? $cityItemsTable : $cityPeopleTable;
                $currentTotal = $resource['current'] - $resource['needed'];
                // فرض بر این است که نام فارسی همیشه در ایندکس 0 و تعداد در ایندکس 1 ذخیره شده است
                $currentName = explode('@', mysqli_fetch_assoc(mysqli_query($conn, "SELECT `{$resource['eng']}` FROM `$targetTable` WHERE `city id` = '{$chat_id}' LIMIT 1"))[$resource['eng']])[0];
                $saveString = "{$currentName}@{$currentTotal}";
                
                if (!$conn->query("UPDATE `$targetTable` SET `{$resource['eng']}` = '{$saveString}' WHERE `city id` = '{$chat_id}' LIMIT 1")) {
                    throw new Exception("Failed to update resource: " . $resource['eng']);
                }
            }

            // ارتقاء سطح ساختمان/کمپ
            $entityDataFromDb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$currentEntityTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $currentEntityValue = $entityDataFromDb[$entityEnglishName]; // مثلا "نام@سطح"
            $entityParts = explode('@', $currentEntityValue);
            $newLevel = intval($entityParts[1]) + 1;
            $saveEntityString = "{$entityParts[0]}@{$newLevel}";

            if (!$conn->query("UPDATE `$currentEntityTable` SET `{$entityEnglishName}` = '{$saveEntityString}' WHERE `city id` = '{$chat_id}' LIMIT 1")) {
                throw new Exception("Failed to update entity level: " . $entityEnglishName);
            }

            // اگر همه مراحل موفق بود، تراکنش را نهایی کن
            $conn->commit();
            EditMessageText($chat_id, $message_id, "✅ ارتقا با موفقیت انجام شد!");

        } catch (Exception $e) {
            // در صورت بروز خطا، تراکنش را لغو کن
            $conn->rollback();
            // اینجا می‌توانید خطای دقیق را لاگ کنید
            error_log("Upgrade transaction failed: " . $e->getMessage());
            EditMessageText($chat_id, $message_id, "❌ خطای داخلی در هنگام ارتقا رخ داد. لطفاً دوباره تلاش کنید.");
        }
    }

    // ریست کردن مرحله کاربر پس از پردازش
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}
