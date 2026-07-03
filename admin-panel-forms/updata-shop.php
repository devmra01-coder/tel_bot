<?php
// ==================== ویرایش آیتم خرید ====================
if ($text == "[✏️]- ویرایش آیتم خرید" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم خرید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit_shop_1" && $text != "🔙") {
    $enName = trim($text);
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '{$enName}' LIMIT 1"));

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم یافت شد.\n\nهزینه فعلی: {$item['costs']}\n\nهزینه جدید را به فرمت JSON وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_2', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: هزینه جدید
else if ($theAdminStep == "edit_shop_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    $conn->query("UPDATE `shop_items` SET `costs` = '" . mysqli_real_escape_string($conn, $text) . "' 
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ هزینه بروزرسانی شد.\n\nحالا محدودیت‌ها را وارد کنید (هر خط یکی) یا 0 بزنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_3' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: محدودیت‌ها
else if ($theAdminStep == "edit_shop_3" && $text != "🔙") {
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

    $conn->query("UPDATE `shop_items` SET 
                    `max_limit` = {$max_limit},
                    `daily_limit` = {$daily_limit},
                    `one_time` = {$one_time}
                  WHERE `item_name`='{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم خرید با موفقیت ویرایش شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
