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

// مرحله ۱: نام انگلیسی + نمایش اطلاعات فعلی
else if ($theAdminStep == "edit_shop_1" && $text != "🔙") {
    $enName = trim($text);
    
    $result = mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '{$enName}' LIMIT 1");
    $item = mysqli_fetch_assoc($result);

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام انگلیسی یافت نشد.",
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
                  "📌 نام فارسی جدید را وارد کنید (یا /skip برای عدم تغییر):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_persian', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: ویرایش نام فارسی
else if ($theAdminStep == "edit_shop_persian" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
        $persianName = trim($text);
        $conn->query("UPDATE `shop_items` SET `persian_name` = '" . mysqli_real_escape_string($conn, $persianName) . "'
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

    // نمایش اطلاعات فعلی هزینه و پیش‌نیاز
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '{$enName}' LIMIT 1"));

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه جدید را به فرمت JSON وارد کنید:\n".
                  "فعلی: <code>{$item['costs']}</code>\n\n".
                  "اگر نمی‌خواهید تغییر کند، /skip بزنید.",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_costs' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: ویرایش هزینه‌ها
else if ($theAdminStep == "edit_shop_costs" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
        $conn->query("UPDATE `shop_items` SET `costs` = '" . mysqli_real_escape_string($conn, $text) . "'
                      WHERE `item_name` = '{$enName}' LIMIT 1");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ هزینه‌ها بروزرسانی شد.",
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⏭️ هزینه‌ها تغییر نکرد.",
        ]);
    }

    // نمایش پیش‌نیاز فعلی
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '{$enName}' LIMIT 1"));

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 پیش‌نیازهای جدید را به فرمت JSON وارد کنید:\n".
                  "فعلی: <code>{$item['requirements']}</code>\n\n".
                  "مثال: <code>{\"barracks\":2, \"wood\":100}</code>\n".
                  "اگر نمی‌خواهید تغییر کند، /skip بزنید.",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_requirements' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۴: ویرایش پیش‌نیازها
else if ($theAdminStep == "edit_shop_requirements" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
        $conn->query("UPDATE `shop_items` SET `requirements` = '" . mysqli_real_escape_string($conn, $text) . "'
                      WHERE `item_name` = '{$enName}' LIMIT 1");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ پیش‌نیازها بروزرسانی شد.",
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⏭️ پیش‌نیازها تغییر نکرد.",
        ]);
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 حالا محدودیت‌ها را وارد کنید (هر خط یکی):\n".
                  "max_limit=0\n".
                  "daily_limit=0\n".
                  "one_time=0\n\n".
                  "یا /skip برای عدم تغییر.",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_shop_limits' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۵: محدودیت‌ها
else if ($theAdminStep == "edit_shop_limits" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
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
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم خرید با موفقیت ویرایش شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
