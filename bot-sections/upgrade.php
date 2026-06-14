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
    $planInput = $text; // این همان متنی است که کاربر ارسال کرده (مثلاً "plan-2")

    if ($planInput === "No") {
        EditMessageText($chat_id, $message_id, "❌ ارتقا لغو شد.");
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
        return;
    }

    // ۱. استخراج شماره پلن از ورودی کاربر
    // فرض بر این است که دکمه‌ها مقادیر "plan-1", "plan-2", "plan-3" را می‌فرستند
    $planNumber = str_replace('plan-', '', $planInput);
    if (!ctype_digit($planNumber)) {
        $planNumber = '1'; // مقدار پیش‌فرض
    }

    // ۲. دریافت اطلاعات ساختمان/کمپ
    $entityData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $bName) . "' LIMIT 1"));
    $tableType = 'building';
    if (!$entityData) {
        $entityData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $bName) . "' LIMIT 1"));
        $tableType = 'camp';
    }

    if (!$entityData) {
        EditMessageText($chat_id, $message_id, "خطا: موجودیت یافت نشد.");
        return;
    }

    // ۳. استفاده از شماره پلن برای خواندن ستون هزینه صحیح (مثلاً upgrade items numbers 2)
    $configKey = "upgrade items numbers $planNumber";
    $numsConfig = $entityData[$configKey] ?? null;

    if (!$numsConfig) {
        EditMessageText($chat_id, $message_id, "❌ این پلن برای این سطح تعریف نشده است.");
        return;
    }

    $costsLines = explode("\n", trim($numsConfig));
    $requiredResources = [];
    $canProceed = true;

    // ۴. بررسی منابع
    foreach ($costsLines as $line) {
        if (empty(trim($line)) || strpos($line, '=>') === false) continue;

        list($resName, $amountNeeded) = array_map('trim', explode("=>", $line));
        $amountNeeded = intval($amountNeeded);

        // جستجو در آیتم‌ها و سپس افراد
        $resData = null;
        $qItem = mysqli_query($conn, "SELECT `english name` FROM `$itemsTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $resName) . "' LIMIT 1");
        
        if ($item = mysqli_fetch_assoc($qItem)) {
            $eng = $item['english name'];
            $val = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$eng` FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"))[$eng] ?? "0@0";
            $curr = intval(explode('@', $val)[1]);
            $resData = ['table' => $cityItemsTable, 'eng' => $eng, 'needed' => $amountNeeded, 'current' => $curr, 'original' => $val];
        } else {
            $qPerson = mysqli_query($conn, "SELECT `english name` FROM `$peopleTable` WHERE `persian name` = '" . mysqli_real_escape_string($conn, $resName) . "' LIMIT 1");
            if ($person = mysqli_fetch_assoc($qPerson)) {
                $eng = $person['english name'];
                $val = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$eng` FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"))[$eng] ?? "0@0";
                $curr = intval(explode('@', $val)[1]);
                $resData = ['table' => $cityPeopleTable, 'eng' => $eng, 'needed' => $amountNeeded, 'current' => $curr, 'original' => $val];
            }
        }

        if ($resData && $resData['current'] >= $resData['needed']) {
            $requiredResources[] = $resData;
        } else {
            $canProceed = false;
            break;
        }
    }

    // ۵. اجرای تراکنش
    if ($canProceed && !empty($requiredResources)) {
        $conn->begin_transaction();
        try {
            foreach ($requiredResources as $res) {
                $parts = explode('@', $res['original']);
                $newName = $parts[0];
                $newVal = $res['current'] - $res['needed'];
                $conn->query("UPDATE `{$res['table']}` SET `{$res['eng']}` = '{$newName}@{$newVal}' WHERE `city id` = '{$chat_id}'");
            }

            // ارتقای سطح خود ساختمان/کمپ
            $targetTable = ($tableType == 'building') ? $cityBuildingsTable : $cityCampsTable;
            $engName = $entityData['english name'];
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$engName` FROM `$targetTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
            $currentVal = $row[$engName];
            $parts = explode('@', $currentVal);
            $newLevel = intval($parts[1]) + 1;
            $conn->query("UPDATE `$targetTable` SET `$engName` = '{$parts[0]}@{$newLevel}' WHERE `city id` = '{$chat_id}'");

            $conn->commit();
            EditMessageText($chat_id, $message_id, "✅ ارتقا به سطح $newLevel با موفقیت انجام شد!");
        } catch (Exception $e) {
            $conn->rollback();
            EditMessageText($chat_id, $message_id, "❌ خطا در انجام تراکنش.");
        }
    } else {
        EditMessageText($chat_id, $message_id, "❌ منابع کافی برای این پلن ندارید.");
    }

    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'");
}

