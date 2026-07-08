<?php

// ==================== ویرایش آیتم ارتقا ====================
if ($text == "[✏️]- ویرایش آیتم ارتقا" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم ارتقا را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱: نام انگلیسی + نمایش اطلاعات فعلی
else if ($theAdminStep == "edit_upgrade_1" && $text != "🔙") {
    $enName = trim($text);
    
    // دریافت اطلاعات فعلی
    $result = $conn->query("SELECT * FROM `upgrade_list` WHERE `item_name` = '{$enName}' LIMIT 1");
    $item = $result->fetch_assoc();

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام انگلیسی یافت نشد!",
            'parse_mode' => "HTML",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم یافت شد:\n\n".
                  "📍 نام انگلیسی: <b>{$item['item_name']}</b>\n".
                  "📍 نام فارسی: <b>{$item['persian_name']}</b>\n\n".
                  "📌 حالا **نام فارسی جدید** را وارد کنید (یا /skip بزنید تا تغییر نکند):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_persian', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله جدید: ویرایش نام فارسی
else if ($theAdminStep == "edit_upgrade_persian" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower($text) !== '/skip') {
        $persianName = trim($text);
        $conn->query("UPDATE `upgrade_list` SET `persian_name` = '" . mysqli_real_escape_string($conn, $persianName) . "'
                      WHERE `item_name` = '{$enName}' LIMIT 1");
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ نام فارسی به <b>{$persianName}</b> تغییر یافت.",
            'parse_mode' => "HTML",
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⏭️ نام فارسی تغییر نکرد.",
        ]);
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 حالا هزینه جدید ارتقا را به فرمت JSON وارد کنید:\n\nمثال:\n<code>{\"1\":{\"gold\":500,\"wood\":200}, \"2\":{\"gold\":900,\"wood\":400}}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_2' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: هزینه ارتقا
else if ($theAdminStep == "edit_upgrade_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    $conn->query("UPDATE `upgrade_list` SET `upgrade_costs` = '" . mysqli_real_escape_string($conn, $text) . "'
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ هزینه ارتقا ثبت شد.\n\n📌 حالا محدودیت‌های جدید را وارد کنید (هر خط یکی):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_3' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: محدودیت‌ها
else if ($theAdminStep == "edit_upgrade_3" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    $max_limit = 0;
    $daily_limit = 0;
    $one_time = 0;
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'max_limit=') !== false) $max_limit = (int)str_replace('max_limit=', '', $line);
        elseif (strpos($line, 'daily_limit=') !== false) $daily_limit = (int)str_replace('daily_limit=', '', $line);
        elseif (strpos($line, 'one_time=') !== false) $one_time = (int)str_replace('one_time=', '', $line);
    }
    $conn->query("UPDATE `upgrade_list` SET
                    `max_limit` = {$max_limit},
                    `daily_limit` = {$daily_limit},
                    `one_time` = {$one_time}
                  WHERE `item_name`='{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم ارتقا با موفقیت ویرایش شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
