<?php


// ==================== افزودن به سیستم ارتقا ====================
if ($text == "افزودن آیتم ارتقا"  && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم ارتقا را وارد کنید (مثال: factory، barracks، mine):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_1' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۱: نام انگلیسی + ایجاد رکورد اولیه
else if ($theAdminStep == "add_upgrade_1" && $text != "🔙") {
    $enName = trim($text);
    
    // ایجاد رکورد اولیه
    $conn->query("INSERT INTO `upgrade_list` (`item_name`, `persian_name`, `upgrade_costs`, `max_limit`, `daily_limit`, `one_time`) 
                  VALUES ('{$enName}', '{$enName}', '{}', 0, 0, 0)
                  ON DUPLICATE KEY UPDATE `item_name`='{$enName}'");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم ایجاد شد.\n\n📌 حالا هزینه ارتقا سطوح را به فرمت JSON وارد کنید:\n\nمثال:\n<code>{\"1\":{\"gold\":500,\"wood\":200}, \"2\":{\"gold\":900,\"wood\":400}}</code>",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_2', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲: هزینه ارتقا
else if ($theAdminStep == "add_upgrade_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];

    $conn->query("UPDATE `upgrade_list` SET `upgrade_costs` = '" . mysqli_real_escape_string($conn, $text) . "' 
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ هزینه ارتقا ثبت شد.\n\n📌 حالا محدودیت‌ها را وارد کنید (هر خط یکی):\nmax_limit=0\ndaily_limit=0\none_time=0",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='add_upgrade_3' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳: محدودیت‌ها + اتمام
else if ($theAdminStep == "add_upgrade_3" && $text != "🔙") {
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

    $conn->query("UPDATE `upgrade_list` SET 
                    `max_limit` = {$max_limit},
                    `daily_limit` = {$daily_limit},
                    `one_time` = {$one_time}
                  WHERE `item_name`='{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🎉 آیتم ارتقا با موفقیت کامل شد و ذخیره گردید!",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='none', `thing`='' WHERE `id`='{$from_id}' LIMIT 1");
}
