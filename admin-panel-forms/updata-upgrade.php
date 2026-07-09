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

// مرحله ۱: انتخاب آیتم + نمایش اطلاعات
else if ($theAdminStep == "edit_upgrade_1" && $text != "🔙") {
    $enName = trim($text);
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `upgrade_list` WHERE `item_name` = '{$enName}' LIMIT 1"));

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    $currentCosts = $item['upgrade_costs'] ?? '{}';
    $currentMax = $item['max_limit'] ?? 0;
    $currentDaily = $item['daily_limit'] ?? 0;
    $currentOneTime = $item['one_time'] ?? 0;

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم یافت شد:\n\n".
                  "📍 نام: <b>{$item['persian_name']}</b> ({$enName})\n".
                  "📊 هزینه فعلی: <code>{$currentCosts}</code>\n".
                  "🔢 max_limit: {$currentMax}\n".
                  "📅 daily_limit: {$currentDaily}\n".
                  "🔂 one_time: {$currentOneTime}\n\n".
                  "📌 نام فارسی جدید را وارد کنید (یا /skip بزنید):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_persian', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: نام فارسی
else if ($theAdminStep == "edit_upgrade_persian" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
        $conn->query("UPDATE `upgrade_list` SET `persian_name` = '" . mysqli_real_escape_string($conn, trim($text)) . "' WHERE `item_name` = '{$enName}' LIMIT 1");
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ نام فارسی بروزرسانی شد."]);
    } else {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "⏭️ نام فارسی تغییر نکرد."]);
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه جدید ارتقا را به فرمت JSON وارد کنید (یا /skip بزنید):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_costs' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: هزینه
else if ($theAdminStep == "edit_upgrade_costs" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
        $conn->query("UPDATE `upgrade_list` SET `upgrade_costs` = '" . mysqli_real_escape_string($conn, $text) . "' WHERE `item_name` = '{$enName}' LIMIT 1");
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ هزینه ارتقا بروزرسانی شد."]);
    } else {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "⏭️ هزینه تغییر نکرد."]);
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 محدودیت‌های جدید را وارد کنید (هر خط یکی) یا /skip بزنید:\nmax_limit=0\ndaily_limit=0\none_time=0",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_limits' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۴: محدودیت‌ها
else if ($theAdminStep == "edit_upgrade_limits" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    if (strtolower(trim($text)) !== '/skip') {
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

        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ محدودیت‌ها بروزرسانی شدند."]);
    } else {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "⏭️ محدودیت‌ها تغییر نکردند."]);
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ ویرایش آیتم ارتقا با موفقیت انجام شد!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
