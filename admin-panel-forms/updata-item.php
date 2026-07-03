// ==================== ویرایش آیتم ====================
if ($text == "[✏️]- ویرایش آیتم" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 نام انگلیسی آیتم را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='edit-item-1' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۱: نام انگلیسی
else if ($theAdminStep == "edit-item-1" && $text != "🔙") {
    $idm = trim($text);
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `english name` = '{$idm}' LIMIT 1"));

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        return;
    }

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "✅ آیتم یافت شد.\n\nنام فارسی جدید را وارد کنید (یا همان قبلی را نگه دارید):",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-item-2-$idm', `thing`='{$idm}' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۲: نام فارسی
else if (strpos($theAdminStep, "edit-item-2-") !== false && $text != "🔙") {
    $idm = str_replace("edit-item-2-", '', $theAdminStep);
    $conn->query("UPDATE `$itemsTable` SET `persian name`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 مقدار اولیه جدید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminBack,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-item-3-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۳: مقدار اولیه
else if (strpos($theAdminStep, "edit-item-3-") !== false && $text != "🔙") {
    $idm = str_replace("edit-item-3-", '', $theAdminStep);
    $itemsT = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `english name` = '{$idm}' LIMIT 1"));
    $persianName = $itemsT["persian name"] ?? $idm;
    $str = "$persianName@$text";

    $conn->query("UPDATE `$itemsTable` SET `first number`='{$text}' WHERE `english name`='{$idm}' LIMIT 1");
    $conn->query("UPDATE `$cityItemsTable` SET `{$idm}`='{$str}' WHERE `city id`='{$chat_id}' LIMIT 1");

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "📌 آیا تأیید می‌کنید؟",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
        'reply_markup' => $adminYesOrNo,
    ]);

    $conn->query("UPDATE `$adminsTable` SET `step`='edit-item-4-$idm' WHERE `id`='{$from_id}' LIMIT 1");
} 

// مرحله ۴: تأیید نهایی
else if (strpos($theAdminStep, "edit-item-4-") !== false && $text != "🔙") {
    $idm = str_replace("edit-item-4-", '', $theAdminStep);
    if ($text == "✅") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ آیتم با موفقیت ویرایش شد!",
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
