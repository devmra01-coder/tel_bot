<?php 

// ===============================================
//         سیستم خرید + ارتقا (کامل)
// ===============================================

// ورود به منوی خرید و ارتقا
if ($text == "shoping" || $data == "shoping") {
    $keyboard = json_encode([
        'inline_keyboard' => [
            [['text' => '🛒 خرید از بازار', 'callback_data' => 'shop_buy']],
            [['text' => '⚒ ارتقای ساختمان/کمپ', 'callback_data' => 'upgrade_menu']],
            [['text' => '🔙 بازگشت', 'callback_data' => 'Back']]
        ]
    ]);

    EditMessageText($chatId, $messageId, "🏪 **بازار و ارتقا**\n\nچه کاری مایلید انجام دهید؟", "Markdown", $keyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='shop_main' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// ==================== ورود به ارتقا ====================
else if ($data == "upgrade_menu" || $playerStep == "upgrade_menu") {
    $buttons = upgradeKeyboard($conn, $chat_id, $cityBuildingsTable, $buildingsTable, $cityCampsTable, $campsTable);
    $keyboard = json_encode(['inline_keyboard' => $buttons]);

    EditMessageText($chatId, $messageId, "⚒ **انتخاب ساختمان یا کمپ برای ارتقا:**", "HTML", $keyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade_1' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// انتخاب ساختمان برای ارتقا
else if ($playerStep == "upgrade_1" && $data) {
    $upgradeName = $data;
    $item = getUpgradeItem($conn, $upgradeName, $buildingsTable, $campsTable);

    if (!$item) return;

    $status = checkUpgradeStatus($conn, $chat_id, $upgradeName, $item, $cityBuildingsTable, $cityCampsTable);

    $currentLevel = getCurrentLevel($conn, $chat_id, $upgradeName, $cityBuildingsTable, $cityCampsTable);
    $nextLevel = $currentLevel + 1;

    $costs = getUpgradeCosts($conn, $upgradeName, $nextLevel, $buildingsTable, $campsTable);

    $text = "⚒ ارتقای <b>{$upgradeName}</b>\n";
    $text .= "📊 سطح: {$currentLevel} → {$nextLevel}\n\n";
    $text .= "💰 هزینه:\n" . formatCosts($costs) . "\n\n";

    if (!$status['can_upgrade']) {
        $text .= "❌ " . $status['message'];
        $keyboard = json_encode([['inline_keyboard' => [[['text' => '🔙 بازگشت', 'callback_data' => 'upgrade_menu']]]]]);
    } else {
        $text .= "تأیید می‌کنید؟";
        $keyboard = $inlineYesOrNo;
        $conn->query("UPDATE `$citiesTable` SET `step`='upgrade_2', `sendItem`='{$upgradeName}', `sendItemNum`='{$nextLevel}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }

    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML', 'reply_markup' => $keyboard]);
}

// تأیید نهایی ارتقا
else if ($playerStep == "upgrade_2" && $text == "yes") {
    $upgradeName = $player['sendItem'];
    $nextLevel = (int)$player['sendItemNum'];

    $result = executeUpgrade($conn, $chat_id, $upgradeName, $nextLevel, 
                             $cityBuildingsTable, $cityCampsTable, 
                             $buildingsTable, $campsTable,
                             $cityItemsTable, $cityPeopleTable, $citySoldiersTable);

    if ($result['success']) {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "✅ ارتقا با موفقیت انجام شد!\n{$upgradeName} به سطح {$nextLevel} رسید.",
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

// انتخاب آیتم
else if ($playerStep == "shop_buy_1" && strpos($data ?? '', 'buy_') === 0) {
    $itemName = str_replace('buy_', '', $data);
    $item = getShopItem($conn, $itemName);

    if (!$item) {
        bot('answerCallbackQuery', ['callback_query_id' => $callback_id ?? '', 'text' => "❌ آیتم یافت نشد", 'show_alert' => true]);
        return;
    }

    $status = checkShopItemStatus($conn, $chat_id, $item);
    $text = "📦 <b>{$item['persian_name']}</b>\n\n";

    if ($item['price_gold'] > 0) $text .= "💰 سکه: {$item['price_gold']}\n";
    $text .= getCostsText($item['costs']) . "\n\n";

    if (!$status['can_buy']) {
        $text .= "❌ <b>امکان خرید وجود ندارد:</b>\n" . $status['message'];
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
                   "📦 {$quantity} واحد {$item['persian_name']}\n\n".
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
