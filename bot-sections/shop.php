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
    $conn->query("UPDATE `$citiesTable` SET `step`='upgrade_1' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// انتخاب آیتم ارتقا
else if ($playerStep == "upgrade_1" && $data) {
    $itemName = $data;   // مستقیم نام آیتم

    $item = getUpgradeItem($conn, $itemName);
    if (!$item) {
        bot('answerCallbackQuery', ['callback_query_id' => $callback_id ?? '', 'text' => "❌ آیتم یافت نشد", 'show_alert' => true]);
        return;
    }

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
        $conn->query("UPDATE `$citiesTable` SET `step`='upgrade_2@{$itemName}@{$nextLevel}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }

    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $text, 'parse_mode' => 'HTML', 'reply_markup' => $keyboard]);
}

// تأیید نهایی ارتقا
else if (strpos($playerStep, "upgrade_2@") !== false) {
    $parts = explode("@", $playerStep);
    $itemName = $parts[1] ?? '';
    $nextLevel = (int)($parts[2] ?? 0);

    if ($text == "yes") {
        $item = getUpgradeItem($conn, $itemName);
        if (!$item) {
            bot('EditMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "❌ آیتم یافت نشد.",
                'parse_mode' => 'HTML'
            ]);
        } else {
            $result = executeUpgrade($conn, $chat_id, $item, $nextLevel);

            if ($result['success']) {
                bot('EditMessageText', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                    'text' => "✅ ارتقا با موفقیت انجام شد!\n{ ".($item['persian_name'] ?? $itemName )."} به سطح {$nextLevel} رسید.",
                    'parse_mode' => 'HTML'
                ]);
            } else {
                bot('EditMessageText', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                    'text' => "❌ " . ($result['message'] ?? 'خطای ناشناخته'),
                    'parse_mode' => 'HTML'
                ]);
            }
        }
    } 
    else if ($text == "no") {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "❌ ارتقا لغو شد.",
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
else if ($playerStep == "shop_buy_1" && $data) {
    $itemName = $data;

    $item = getShopItem($conn, $itemName);
    if (!$item) {
        bot('answerCallbackQuery', ['callback_query_id' => $callback_id ?? '', 'text' => "❌ آیتم یافت نشد", 'show_alert' => true]);
        return;
    }

    $totalCost = calculateTotalCost($item, 1);
    $status = checkShopItemStatus($conn, $chat_id, $item);
    $text = "📦 <b>" . ($item['persian_name'] ?? $itemName) . "</b>\n\n";
    $text .=  formatCosts($totalCost) . "\n\n";

    if (!$status['can_buy']) {
        $text .= "❌ امکان خرید وجود ندارد:\n" . $status['message'];
        $keyboard = json_encode([['inline_keyboard' => [[['text' => '🔙 بازگشت', 'callback_data' => 'shop_buy']]]]]);
    } else {
        $text .= "🧮 **چند واحد می‌خواهید بخرید؟**";
        $keyboard = $back;
        $conn->query("UPDATE `$citiesTable` SET `step`='shop_buy_2@{$itemName}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }

    EditMessageText($chatId, $messageId, $text, "HTML", $keyboard);
}

// وارد کردن تعداد
else if (strpos($playerStep, "shop_buy_2@") !== false && is_numeric($text) && (int)$text > 0) {
    $itemName = str_replace("shop_buy_2@", '', $playerStep);
    $quantity = (int)$text;

    $item = getShopItem($conn, $itemName);
    if (!$item) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ آیتم یافت نشد."]);
        return;
    }

    $status = checkShopItemStatus($conn, $chat_id, $item, $quantity);
    if (!$status['can_buy']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ " . $status['message'], 'reply_markup' => $back]);
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

    $conn->query("UPDATE `$citiesTable` SET `step`='shop_buy_3@{$itemName}@{$quantity}' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// تأیید نهایی خرید
else if (strpos($playerStep, "shop_buy_3@") !== false) {
    $parts = explode("@", $playerStep);
    $itemName = $parts[1] ?? '';
    $qty = (int)($parts[2] ?? 0);
    
    if ($text == "yes") {
        $item = getShopItem($conn, $itemName);
        if (!$item) {
            bot('EditMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "❌ آیتم یافت نشد.",
                'parse_mode' => 'HTML'
            ]);
        } else {
            $result = executePurchase($conn, $chat_id, $item, $qty, $cityItemsTable, $cityBuildingsTable, $cityPeopleTable, $citySoldiersTable, $cityCampsTable);

            if ($result['success']) {
                bot('EditMessageText', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                    'text' => "🎉 خرید با موفقیت انجام شد!\n{$qty} واحد " . ($item['persian_name'] ?? $itemName) . " به انبار اضافه شد.",
                    'parse_mode' => 'HTML'
                ]);
            } else {
                bot('EditMessageText', [
                    'chat_id' => $chat_id,
                    'message_id' => $message_id,
                    'text' => "❌ " . ($result['message'] ?? 'خطای ناشناخته'),
                    'parse_mode' => 'HTML'
                ]);
            }
        }
    } 
    else if ($text == "no") {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "❌ خرید لغو شد.",
            'parse_mode' => 'HTML'
        ]);
    }

    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
}
