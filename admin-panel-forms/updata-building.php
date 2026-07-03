// ==================== ویرایش ساختمان ====================
if ($text == "[✏️]- ویرایش ساختمان" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی ساختمان را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-1' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit-building-1" && $text != "🔙") {
    $idm = trim($text);
    $building = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `english name` = '{$idm}' LIMIT 1"));

    if (!$building) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ ساختمانی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ ساختمان یافت شد.\n\nنام فارسی جدید را وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-2-$idm', `thing`='{$idm}' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۲: نام فارسی
else if (strpos($theAdminStep, "edit-building-2-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-2-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `persian name`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۱ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۳: هزینه سطح ۱
else if (strpos($theAdminStep, "edit-building-3-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-3-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 1`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۲ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۴: هزینه سطح ۲
else if (strpos($theAdminStep, "edit-building-4-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-4-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 2`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 هزینه ارتقا سطح ۳ جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminDoseNotNeedIt,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-5-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۵: هزینه سطح ۳
else if (strpos($theAdminStep, "edit-building-5-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-5-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `upgrade items numbers 3`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیتم بازدهی جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-6-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۶: آیتم بازدهی
else if (strpos($theAdminStep, "edit-building-6-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-6-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `efficiency item`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 میزان بازدهی در هر لول را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-7-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۷: میزان بازدهی
else if (strpos($theAdminStep, "edit-building-7-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-7-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `efficiency number`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول اولیه را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-8-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۸: لول اولیه
else if (strpos($theAdminStep, "edit-building-8-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-8-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `first level`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 لول نهایی را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-9-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۹: لول نهایی
else if (strpos($theAdminStep, "edit-building-9-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-9-", '', $theAdminStep);
    $conn->query("UPDATE `$buildingsTable` SET `last level`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تأیید می‌کنید؟",
        'parse_mode' => "HTML",
        'reply_markup' => $adminYesOrNo,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-building-10-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱۰: تأیید نهایی
else if (strpos($theAdminStep, "edit-building-10-") !== false && $text != "🔙") {
    $idm = str_replace("edit-building-10-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ ساختمان با موفقیت ویرایش شد!",
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
