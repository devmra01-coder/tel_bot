<?php
// ==================== ویرایش شخصیت ====================
if ($text == "[✏️]- ویرایش شخصیت" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی شخصیت را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit-person-1' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit-person-1" && $text != "🔙") {
    $idm = trim($text);
    $person = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$idm}' LIMIT 1"));

    if (!$person) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ شخصیتی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ شخصیت یافت شد.\n\nنام فارسی جدید را وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-person-2-$idm', `thing`='{$idm}' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۲: نام فارسی
else if (strpos($theAdminStep, "edit-person-2-") !== false && $text != "🔙") {
    $idm = str_replace("edit-person-2-", '', $theAdminStep);
    $conn->query("UPDATE `$peopleTable` SET `persian name`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیتم مصرفی جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-person-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۳: آیتم مصرفی
else if (strpos($theAdminStep, "edit-person-3-") !== false && $text != "🔙") {
    $idm = str_replace("edit-person-3-", '', $theAdminStep);
    $conn->query("UPDATE `$peopleTable` SET `consumable item`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-person-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۴: مقدار اولیه
else if (strpos($theAdminStep, "edit-person-4-") !== false && $text != "🔙") {
    $idm = str_replace("edit-person-4-", '', $theAdminStep);
    $personsT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $personsT["persian name"] ?? $idm;
    $str = "$persianName@$text";

    $conn->query("UPDATE `$peopleTable` SET `first number`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");
    $conn->query("UPDATE `$cityPeopleTable` SET `{$idm}`='{$str}' WHERE `city id`='{$chat_id}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تأیید می‌کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-person-5-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۵: تأیید نهایی
else if (strpos($theAdminStep, "edit-person-5-") !== false && $text != "🔙") {
    $idm = str_replace("edit-person-5-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ شخصیت با موفقیت ویرایش شد!",
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
