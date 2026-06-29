<?php
// ==================== افزودن آیتم به ارتقا ====================
if ($text == "[⚒]- افزودن آیتم ارتقا" && $theAdminStep == "none") {
    $allItems = getAllGameItems($conn); // همان تابع قبلی

    $keyboard = ['inline_keyboard' => []];
    foreach ($allItems as $item) {
        $keyboard['inline_keyboard'][] = [[
            'text' => $item['persian'] . " (" . $item['english'] . ")",
            'callback_data' => "upgadd_" . $item['english']
        ]];
    }
    $keyboard['inline_keyboard'][] = [['text' => '🔙 بازگشت', 'callback_data' => 'admin_back']];

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📋 **انتخاب آیتم برای افزودن به سیستم ارتقا:**",
        'parse_mode' => "HTML",
        'reply_markup' => json_encode($keyboard)
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='upgrade_add_select' WHERE `id`='{$from_id}' LIMIT 1");
}

// انتخاب آیتم ارتقا
else if ($theAdminStep == "upgrade_add_select" && strpos($data, 'upgadd_') === 0) {
    $englishName = str_replace('upgadd_', '', $data);
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم انتخاب شد: <b>$englishName</b>\n\nحالا هزینه ارتقا هر سطح را مشخص کنید.",
        'parse_mode' => "HTML",
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='upgrade_add_cost', `thing`='{$englishName}', `temp_data`='' WHERE `id`='{$from_id}' LIMIT 1");
    
    // شروع انتخاب هزینه برای سطح ۱
    showUpgradeCostSelection($conn, $chat_id, $englishName, 1);
}

// انتخاب هزینه برای سطح خاص
else if ($theAdminStep == "upgrade_add_cost" && strpos($data, 'upgcost_') === 0) {
    $parts = explode('_', $data);
    $level = (int)$parts[1];
    $costItem = $parts[2];

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 تعداد $costItem برای ارتقا به سطح $level را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='upgrade_add_cost_qty', `thing2`='{$level}_{$costItem}' WHERE `id`='{$from_id}' LIMIT 1");
}

// وارد کردن تعداد هزینه
else if ($theAdminStep == "upgrade_add_cost_qty" && is_numeric($text)) {
    $data = $getAdmins['thing2'];
    list($level, $costItem) = explode('_', $data);

    $current = $getAdmins['temp_data'] ? json_decode($getAdmins['temp_data'], true) : [];
    $current[$level][$costItem] = (int)$text;

    $conn->query("UPDATE `$adminsTable` SET `temp_data`='" . json_encode($current) . "' WHERE `id`='{$from_id}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ هزینه سطح $level اضافه شد.\n\nبرای سطح بعدی یا تمام شدن، انتخاب کنید.",
        'parse_mode' => "HTML",
    ]);

    // پیشنهاد سطح بعدی
    $nextLevel = $level + 1;
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "سطح $nextLevel را هم می‌خواهید هزینه تعریف کنید؟",
        'reply_markup' => json_encode([
            'inline_keyboard' => [
                [['text' => "بله - سطح $nextLevel", 'callback_data' => "upg_nextlevel_$nextLevel"]],
                [['text' => "تمام شد", 'callback_data' => "upg_done"]]
            ]
        ])
    ]);
}

// تمام شدن و ذخیره
else if ($theAdminStep == "upgrade_add_cost" && $data == "upg_done") {
    $englishName = $getAdmins['thing'];
    $costsJson = $getAdmins['temp_data'] ?: '{}';

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 محدودیت‌ها را وارد کنید (max_limit, daily_limit, one_time):",
        'parse_mode' => "HTML",
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='upgrade_add_limits', `temp_data`='{$costsJson}' WHERE `id`='{$from_id}' LIMIT 1");
}

// ذخیره نهایی
else if ($theAdminStep == "upgrade_add_limits" && $text != "🔙") {
    $englishName = $getAdmins['thing'];
    $costs = $getAdmins['temp_data'];

    $conn->query("INSERT INTO `upgrade_list` (`item_name`, `persian_name`, `upgrade_costs`) 
                  VALUES ('{$englishName}', 
                          (SELECT `persian name` FROM `$buildingsTable` WHERE `english name`='{$englishName}' LIMIT 1),
                          '{$costs}') 
                  ON DUPLICATE KEY UPDATE `upgrade_costs`='{$costs}'");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎉 آیتم ارتقا با موفقیت اضافه شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
}

