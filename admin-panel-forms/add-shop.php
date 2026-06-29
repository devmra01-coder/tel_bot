<?php
// ==================== شروع افزودن آیتم خرید ====================
if ($text == "[🛒]- افزودن آیتم خرید" && $theAdminStep == "none") {
    $allItems = getAllGameItems($conn);

    $keyboard = ['inline_keyboard' => []];
    foreach ($allItems as $item) {
        $keyboard['inline_keyboard'][] = [[
            'text' => $item['persian'] . " (" . $item['english'] . ")",
            'callback_data' => $item['english']
        ]];
    }
    $keyboard['inline_keyboard'][] = [['text' => '🔙 بازگشت', 'callback_data' => '🔙']];

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📋 **انتخاب آیتم برای افزودن به فروشگاه خرید:**",
        'parse_mode' => "HTML",
        'reply_markup' => json_encode($keyboard)
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='shop_add_select' WHERE `id`='{$from_id}' LIMIT 1");
}

// انتخاب آیتم اصلی
else if ($theAdminStep == "shop_add_select") {
    $englishName = $data ;
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم انتخاب شد: <b>$englishName</b>\n\nحالا هزینه‌های خرید را مشخص کنید.",
        'parse_mode' => "HTML",
    ]);

    showCostSelectionKeyboard($conn, $chat_id, $englishName);
    $conn->query("UPDATE `$adminsTable` SET `step`='shop_add_cost', `thing`='{$englishName}', `temp_data`='' WHERE `id`='{$from_id}' LIMIT 1");
}

// انتخاب هزینه (callback)
else if ($theAdminStep == "shop_add_cost" && strpos($data, 'cost_') === 0) {
    $parts = explode('_', $data);
    $target = $parts[1];
    $costItem = $parts[2];

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 تعداد $costItem که برای خرید $target نیاز است را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='shop_add_cost_qty', `thing2`='{$costItem}' WHERE `id`='{$from_id}' LIMIT 1");
}

// وارد کردن تعداد هزینه
else if ($theAdminStep == "shop_add_cost_qty" && is_numeric($text)) {
    $costItem = $getAdmins['thing2'];
    $currentCosts = $getAdmins['temp_data'] ? json_decode($getAdmins['temp_data'], true) : [];
    $currentCosts[$costItem] = (int)$text;

    $conn->query("UPDATE `$adminsTable` SET `temp_data`='" . json_encode($currentCosts) . "' WHERE `id`='{$from_id}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ $costItem با مقدار " . $text . " اضافه شد.\n\nبرای اضافه کردن هزینه بیشتر، آیتم دیگری انتخاب کنید یا روی «تمام شد» بزنید.",
        'parse_mode' => "HTML",
    ]);

    showCostSelectionKeyboard($conn, $chat_id, $getAdmins['thing']);
}

// تمام شدن هزینه‌ها
else if ($theAdminStep == "shop_add_cost" && strpos($data, 'cost_done_') === 0) {
    $englishName = str_replace('cost_done_', '', $data);
    $costsJson = $getAdmins['temp_data'] ?: '{}';

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 حالا محدودیت‌ها را تنظیم کنید:\n\n1. max_limit (حداکثر تعداد قابل خرید)\n2. daily_limit (محدودیت روزانه)\n3. one_time (فقط یک بار)\n\nبرای هر کدام عدد وارد کنید یا 0 بزنید (بدون محدودیت).",
        'parse_mode' => "HTML",
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='shop_add_limits', `temp_data`='{$costsJson}' WHERE `id`='{$from_id}' LIMIT 1");
}

// وارد کردن محدودیت‌ها
else if ($theAdminStep == "shop_add_limits" && $text != "🔙") {
    // اینجا می‌توانید محدودیت‌ها را به صورت چند خطی یا جداگانه بگیرید.
    // برای سادگی، فرض می‌کنیم ادمین JSON محدودیت هم وارد کند
    $limits = json_decode($text, true) ?: ['max_limit' => 0, 'daily_limit' => 0, 'one_time' => 0];

    $enName = $getAdmins['thing'];
    $costs = $getAdmins['temp_data'];

    $conn->query("INSERT INTO `shop_items` (`item_name`, `persian_name`, `costs`, `max_limit`, `daily_limit`, `one_time`) 
                  VALUES ('{$enName}', (SELECT `persian name` FROM `$itemsTable` WHERE `english name`='{$enName}' LIMIT 1), '{$costs}', 
                  '{$limits['max_limit']}', '{$limits['daily_limit']}', '{$limits['one_time']}') 
                  ON DUPLICATE KEY UPDATE `costs`='{$costs}'");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎉 آیتم با موفقیت به فروشگاه اضافه شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `temp_data`='', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}


