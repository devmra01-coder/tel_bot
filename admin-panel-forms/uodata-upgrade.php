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

// مرحله ۱
else if ($theAdminStep == "edit_upgrade_1" && $text != "🔙") {
    $enName = trim($text);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه جدید ارتقا را به فرمت JSON وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_2', `thing`='{$enName}' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۲
else if ($theAdminStep == "edit_upgrade_2" && $text != "🔙") {
    $enName = $getAdmins['thing'];
    $conn->query("UPDATE `upgrade_list` SET `upgrade_costs` = '" . mysqli_real_escape_string($conn, $text) . "' 
                  WHERE `item_name` = '{$enName}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 محدودیت‌های جدید را وارد کنید (هر خط یکی):",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit_upgrade_3' WHERE `id`='{$from_id}' LIMIT 1");
}

// مرحله ۳
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
