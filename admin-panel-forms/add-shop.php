<?php
// ==================== افزودن به فروشگاه خرید ====================
if ($text == "[🛒]- افزودن آیتم خرید" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم را وارد کنید (مثال: wood، soldier، factory):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱: نام انگلیسی + ایجاد رکورد
else if ($theAdminStep == "add_shop_1" && $text != "🔙") {
    $enName = trim($text);
   
    $conn->query("INSERT INTO `shop_items` 
        (`item_name`, `persian_name`, `costs`, `requirements`, `max_limit`, `daily_limit`, `one_time`, `active`) 
        VALUES ('{$enName}', '{$enName}', '{}', '{}', 0, 0, 0, 1)
        ON DUPLICATE KEY UPDATE `item_name`='{$enName}'");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم با نام انگلیسی <b>{$enName}</b> ایجاد شد.\n\n".
                  "📌 حالا **نام فارسی** آیتم را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_persian', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: نام فارسی
else if ($theAdminStep == "add_shop_persian" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    $persianName = trim($text);

    $conn->query("UPDATE `shop_items` SET `persian_name` = '" . mysqli_real_escape_string($conn, $persianName) . "'
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ نام فارسی ثبت شد: <b>{$persianName}</b>\n\n".
                  "📌 حالا **هزینه خرید** را به فرمت JSON وارد کنید:\n\n".
                  "مثال:\n<code>{\"gold\":150, \"wood\":40, \"food\":20}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_costs' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: هزینه‌ها (costs)
else if ($theAdminStep == "add_shop_costs" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    $conn->query("UPDATE `shop_items` SET `costs` = '" . mysqli_real_escape_string($conn, $text) . "'
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ هزینه‌ها ثبت شد.\n\n".
                  "📌 حالا **پیش‌نیازها (requirements)** را وارد کنید:\n\n".
                  "فرمت JSON:\n".
                  "<code>{\"building\":\"barracks\",\"level\":2}</code>\n\n".
                  "یا برای چندین پیش‌نیاز:\n".
                  "<code>{\"building\":\"factory\",\"level\":3, \"tech\":\"military1\":1}</code>\n\n".
                  "اگر پیش‌نیازی ندارد، فقط بنویس: <code>{}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_requirements' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۴: پیش‌نیازها
else if ($theAdminStep == "add_shop_requirements" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    $conn->query("UPDATE `shop_items` SET `requirements` = '" . mysqli_real_escape_string($conn, $text) . "'
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ پیش‌نیازها ثبت شد.\n\n".
                  "📌 حالا محدودیت‌ها را وارد کنید (هر خط یکی):\n".
                  "max_limit=0\n".
                  "daily_limit=0\n".
                  "one_time=0",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_shop_limits' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۵: محدودیت‌ها + اتمام
else if ($theAdminStep == "add_shop_limits" && $text != "🔙") {
    $enName = $getAdmins['thing'];
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

    $conn->query("UPDATE `shop_items` SET
                    `max_limit` = {$max_limit},
                    `daily_limit` = {$daily_limit},
                    `one_time` = {$one_time}
                  WHERE `item_name`='{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎉 آیتم خرید با موفقیت کامل ایجاد و ذخیره شد!\n\n".
                  "نام انگلیسی: <b>{$enName}</b>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
