<?php

// ==================== ویرایش سرباز ====================
if ($text == "[✏️]- ویرایش سرباز" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی سرباز را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit-soldier-1' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit-soldier-1" && $text != "🔙") {
    $idm = trim($text);
    $soldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$idm}' LIMIT 1"));

    if (!$soldier) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ سربازی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ سرباز یافت شد.\n\nنام فارسی جدید را وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-soldier-2-$idm', `thing`='{$idm}' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۲: نام فارسی
else if (strpos($theAdminStep, "edit-soldier-2-") !== false && $text != "🔙") {
    $idm = str_replace("edit-soldier-2-", '', $theAdminStep);
    $conn->query("UPDATE `$soldiersTable` SET `persian name`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیتم مصرفی جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-soldier-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۳: آیتم مصرفی
else if (strpos($theAdminStep, "edit-soldier-3-") !== false && $text != "🔙") {
    $idm = str_replace("edit-soldier-3-", '', $theAdminStep);
    $conn->query("UPDATE `$soldiersTable` SET `consumable item`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-soldier-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۴: مقدار اولیه
else if (strpos($theAdminStep, "edit-soldier-4-") !== false && $text != "🔙") {
    $idm = str_replace("edit-soldier-4-", '', $theAdminStep);
    $soldiersT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $soldiersT["persian name"] ?? $idm;
    $str = "$persianName@$text";

    $conn->query("UPDATE `$soldiersTable` SET `first number`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");
    $conn->query("UPDATE `$citySoldiersTable` SET `{$idm}`='{$str}' WHERE `city id`='{$chat_id}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تأیید می‌کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-soldier-5-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۵: تأیید نهایی
else if (strpos($theAdminStep, "edit-soldier-5-") !== false && $text != "🔙") {
    $idm = str_replace("edit-soldier-5-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ سرباز با موفقیت ویرایش شد!",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ ویرایش لغو شد.",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminBack,
        ]);
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
}
