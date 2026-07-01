<?php 

// ===============================================
//         سیستم خرید + ارتقا (کامل)
// ===============================================
// ==================== منوی اصلی خرید و ارتقا ====================
if ($text == "shoping" || $data == "shoping") {
    $keyboard = json_encode([
        'inline_keyboard' => [
            [['text' => '🛒 خرید ', 'callback_data' => 'shop_buy']],
            [['text' => '⚒ ارتقا ', 'callback_data' => 'upgrade_menu']],
            [['text' => '🔙 بازگشت', 'callback_data' => 'back']]
        ]
    ]);

    EditMessageText($chatId, $messageId, "🏪 **بازار و ارتقا**", "Markdown", $keyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='shop_main' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// ==================== ارتقا ====================
else if ($data == "upgrade_menu" || $playerStep == "upgrade_menu") {
    $buttons = getUpgradeButtons($conn);
    $keyboard = json_encode(['inline_keyboard' => $buttons]);

    EditMessageText($chatId, $messageId, "⚒ **انتخاب مورد برای ارتقا:**", "HTML", $keyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='Shop_upgrade_1' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// انتخاب آیتم ارتقا
else if ($playerStep == "Shop_upgrade_1" && strpos($data ?? '', 'Shop_upgrade_') === 0) {
    $itemName = str_replace('Shop_upgrade_', '', $data);
    $item = getUpgradeItem($conn, $itemName);

    if (!$item) return;

    $status = checkUpgradeStatus($conn, $chat_id, $item);
    $currentLevel = getCurrentLevel($conn, $chat_id, $itemName);
    $nextLevel = $currentLevel + 1;

    $costs = getUpgradeCosts($conn, $item, $nextLevel);

    $text = "⚒ ارتقای <b>{$item['persian_name']}</b>\n";
    $text .= "📊 سطح فعلی: {$currentLevel} → {$nextLevel}\n\n";
    $text .= "💰 هزینه ارتقا:\n" . formatCosts($costs) . "\n\n";

    if (!$status['can_upgrade']) {
        $text .= "❌ " . $status['message'];
        $keyboard = json_encode([['inline_keyboard' => [[['text' => '🔙 بازگشت', 'callback_data' => 'upgrade_menu']]]]]);
    } else {
        $text .= "آیا ارتقا انجام شود؟";
        $keyboard = $inlineYesOrNo;
        $conn->query("UPDATE `$citiesTable` SET `step`='Shop_upgrade_2', `sendItem`='{$itemName}', `sendItemNum`='{$nextLevel}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }

    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML', 'reply_markup' => $keyboard]);
}

// تأیید نهایی ارتقا
else if ($playerStep == "Shop_upgrade_2" && $text == "yes") {
    $itemName = $player['sendItem'];
    $nextLevel = (int)$player['sendItemNum'];
    $item = getUpgradeItem($conn, $itemName);

    $result = executeUpgrade($conn, $chat_id, $item, $nextLevel);

    if ($result['success']) {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅ ارتقا با موفقیت انجام شد!\n{$item['persian_name']} به سطح {$nextLevel} رسید.",
            'parse_mode' => 'HTML'
        ]);
    } else {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "❌ " . $result['message'],
            'parse_mode' => 'HTML'
        ]);
    }

    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
}
     
// ==================== نمایش لیست آیتم‌ها ====================
else if ($data == "shop_buy" || $playerStep == "shop_buy") {
    $buttons = getShopBuyButtons($conn, $chat_id);
    $keyboard = json_encode(['inline_keyboard' => $buttons]);

    EditMessageText($chatId, $messageId, "🛒 **انتخاب کالا برای خرید:**", "HTML", $keyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='shop_buy_1' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// انتخاب آیتم از دکمه (اصلاح شده)
else if ($playerStep == "shop_buy_1" && $data) {
    $itemName = $data;
    $item = getShopItem($conn, $itemName);

    if (!$item) {
        bot('sendMessage', [
            'callback_query_id' => $callback_id ?? '',
            'text' => "❌ آیتم یافت نشد",
            'show_alert' => true
        ]);
        return;
    }

    $status = checkShopItemStatus($conn, $chat_id, $item);
    $text = "📦 <b>{$item['persian_name']}</b>\n\n";

    if (!empty($item['price_gold']) && $item['price_gold'] > 0) {
        $text .= "💰 سکه: {$item['price_gold']}\n";
    }
    $text .= getCostsText($item['costs'] ?? '{}') . "\n\n";

    if (!$status['can_buy']) {
        $text .= "❌ امکان خرید وجود ندارد:\n" . $status['message'];
        $keyboard = json_encode([['inline_keyboard' => [[['text' => '🔙 بازگشت', 'callback_data' => 'shop_buy']]]]]);
    } else {
        $text .= "🧮 **چند واحد می‌خواهید بخرید؟**";
        $keyboard = $back;
        $conn->query("UPDATE `$citiesTable` SET `step`='shop_buy_2', `sendItem`='{$itemName}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }

    EditMessageText($chatId, $messageId, $text, "HTML", $keyboard);
}

// وارد کردن تعداد
else if ($playerStep == "shop_buy_2" && is_numeric($text) && (int)$text > 0) {
    $quantity = (int)$text;
    $itemName = $player['sendItem'];
    $item = getShopItem($conn, $itemName);

    $status = checkShopItemStatus($conn, $chat_id, $item, $quantity);

    if (!$status['can_buy']) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ " . $status['message'],
            'parse_mode' => 'HTML',
            'reply_markup' => $back
        ]);
        return;
    }

    $totalCost = calculateTotalCost($item, $quantity);
    $confirmText = "✅ **تأیید خرید**\n\n".
                   "📦 {$quantity} واحد " . ($item['persian_name'] ?? $itemName) . "\n\n".
                   "💰 هزینه کل:\n" . formatCosts($totalCost);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $confirmText,
        'parse_mode' => 'HTML',
        'reply_markup' => $inlineYesOrNo
    ]);

    $conn->query("UPDATE `$citiesTable` SET `step`='shop_buy_3', `sendItemNum`='{$quantity}' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// تأیید نهایی
else if ($playerStep == "shop_buy_3" && $text == "yes") {
    $itemName = $player['sendItem'];
    $qty = (int)$player['sendItemNum'];
    $item = getShopItem($conn, $itemName);

    $result = executePurchase($conn, $chat_id, $item, $qty, $cityItemsTable, $cityBuildingsTable, $cityPeopleTable, $citySoldiersTable, $cityCampsTable);

    if ($result['success']) {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "🎉 خرید با موفقیت انجام شد!\n{$qty} واحد {$item['persian_name']} به انبار شهر اضافه شد.",
            'parse_mode' => 'HTML'
        ]);
    } else {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "❌ " . $result['message'],
            'parse_mode' => 'HTML'
        ]);
    }

    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
}
