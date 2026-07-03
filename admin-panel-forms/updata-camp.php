<?php
// ==================== ویرایش کمپ ====================
if ($text == "[✏️]- ویرایش کمپ" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی کمپ را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-1' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit-camp-1" && $text != "🔙") {
    $idm = trim($text);
    $camp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `english name` = '{$idm}' LIMIT 1"));

    if (!$camp) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ کمپ با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ کمپ یافت شد.\n\nنام فارسی جدید را وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-2-$idm', `thing`='{$idm}' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۲: نام فارسی
else if (strpos($theAdminStep, "edit-camp-2-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-2-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `persian name`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۱ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۳: هزینه سطح ۱
else if (strpos($theAdminStep, "edit-camp-3-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-3-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 1`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۲ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۴: هزینه سطح ۲
else if (strpos($theAdminStep, "edit-camp-4-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-4-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 2`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۳ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-5-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۵: هزینه سطح ۳
else if (strpos($theAdminStep, "edit-camp-5-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-5-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `upgrade items numbers 3`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 سرباز بازدهی جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-6-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۶: سرباز بازدهی
else if (strpos($theAdminStep, "edit-camp-6-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-6-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `efficiency soldier`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 میزان بازدهی در هر لول را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-7-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۷: میزان بازدهی
else if (strpos($theAdminStep, "edit-camp-7-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-7-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `efficiency number`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول اولیه را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-8-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۸: لول اولیه
else if (strpos($theAdminStep, "edit-camp-8-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-8-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `first level`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول نهایی را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-9-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۹: لول نهایی
else if (strpos($theAdminStep, "edit-camp-9-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-9-", '', $theAdminStep);
    $conn->query("UPDATE `$campsTable` SET `last level`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تأیید می‌کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-camp-10-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱۰: تأیید نهایی
else if (strpos($theAdminStep, "edit-camp-10-") !== false && $text != "🔙") {
    $idm = str_replace("edit-camp-10-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ کمپ با موفقیت ویرایش شد!",
            'parse_mode' => "HTML",
            'reply_markup' => $adminBack,
        ]);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ ویرایش لغو شد.",
            'parse_mode' => "HTML",
            'reply_markup' => $adminBack,
        ]);
    }
    $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
}
