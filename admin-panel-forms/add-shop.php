<?php
// ===============================================
//            پنل ادمین — افزودن خرید و ارتقا
// ===============================================

// ==================== افزودن به فروشگاه خرید ====================
if ($text == "[🛒]- افزودن آیتم خرید" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم را وارد کنید (مثال: wood، soldier، factory):\n\nاز آیتم‌های موجود در دیتابیس استفاده کنید.",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "add_shop_1" && $text != "🔙") {
    $enName = trim($text);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه خرید این آیتم را به فرمت JSON وارد کنید:\n\nمثال:\n<code>{\"gold\":150, \"wood\":40, \"stone\":25}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_2', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: هزینه‌ها
else if ($theAdminStep == "add_shop_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 محدودیت‌ها را وارد کنید (هر خط یکی):\nmax_limit=0\ndaily_limit=0\none_time=0\n\n(۰ یعنی بدون محدودیت)",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_3', `temp_data`='{$text}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله وارد کردن محدودیت‌ها
else if ($theAdminStep == "add_shop_3" && $text != "🔙") {
    $adminData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$adminsTable` WHERE `id`='{$from_id}' LIMIT 1"));
    $enName = $adminData['thing'] ?? '';
    $costs  = $adminData['temp_data'] ?? '{}';

    if (empty($enName)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ خطا: نام آیتم پیدا نشد. دوباره از اول شروع کنید.",
            'parse_mode' => "HTML",
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    // پردازش محدودیت‌ها
    $max_limit = 0;
    $daily_limit = 0;
    $one_time = 0;

    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'max_limit=') !== false) {
            $max_limit = (int)str_replace('max_limit=', '', $line);
        } elseif (strpos($line, 'daily_limit=') !== false) {
            $daily_limit = (int)str_replace('daily_limit=', '', $line);
        } elseif (strpos($line, 'one_time=') !== false) {
            $one_time = (int)str_replace('one_time=', '', $line);
        }
    }

    // ذخیره نهایی
    $conn->query("INSERT INTO `shop_items` (`item_name`, `persian_name`, `costs`, `max_limit`, `daily_limit`, `one_time`) 
                  VALUES (
                    '{$enName}', 
                    COALESCE((SELECT `persian name` FROM `$itemsTable` WHERE `english name`='{$enName}' LIMIT 1),
                             (SELECT `persian name` FROM `$peopleTable` WHERE `english name`='{$enName}' LIMIT 1),
                             (SELECT `persian name` FROM `$soldiersTable` WHERE `english name`='{$enName}' LIMIT 1),
                             '{$enName}'),
                    '{$costs}',
                    {$max_limit},
                    {$daily_limit},
                    {$one_time}
                  ) 
                  ON DUPLICATE KEY UPDATE 
                    `costs` = '{$costs}',
                    `max_limit` = {$max_limit},
                    `daily_limit` = {$daily_limit},
                    `one_time` = {$one_time}");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم خرید با موفقیت اضافه/بروزرسانی شد!\n\nمحدودیت‌ها اعمال شد.",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `temp_data`='', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}

// ==================== افزودن به سیستم ارتقا ====================
if ($text == "[⚒]- افزودن آیتم ارتقا" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم ارتقا را وارد کنید (مثال: factory، barracks):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱ ارتقا
else if ($theAdminStep == "add_upgrade_1" && $text != "🔙") {
    $enName = trim($text);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطوح را به فرمت JSON وارد کنید:\n\nمثال:\n<code>{\"1\":{\"gold\":500,\"wood\":200}, \"2\":{\"gold\":900,\"wood\":400}}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_2', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: هزینه ارتقا
else if ($theAdminStep == "add_upgrade_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 محدودیت‌ها را وارد کنید:\nmax_limit=0\ndaily_limit=0\none_time=0",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_3' WHERE `id`='{$from_id}' LIMIT 1");
    $conn->query("INSERT INTO `upgrade_list` (`item_name`, `upgrade_costs`) VALUES ('{$enName}', '{$text}') ON DUPLICATE KEY UPDATE `upgrade_costs`='{$text}'");
}

// مرحله نهایی
else if ($theAdminStep == "add_upgrade_3" && $text != "🔙") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم ارتقا با موفقیت اضافه شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
}
?>
