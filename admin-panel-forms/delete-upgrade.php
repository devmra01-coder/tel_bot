<?php
// ==================== حذف آیتم ارتقا ====================
if ($text == "[❌]- حذف آیتم ارتقا" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "⚒ نام انگلیسی آیتم ارتقا را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-upgrade-item' WHERE `id`='{$from_id}' LIMIT 1");
}

// پردازش حذف آیتم ارتقا
else if ($theAdminStep == "delete-upgrade-item" && $text != "🔙") {
    $itemName = trim($text);

    $result = mysqli_query($conn, "SELECT * FROM `upgrade_list` WHERE `item_name` = '{$conn->real_escape_string($itemName)}' LIMIT 1");
    $item = mysqli_fetch_assoc($result);

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
    } else {
        $conn->query("DELETE FROM `upgrade_list` WHERE `item_name` = '{$conn->real_escape_string($itemName)}'");

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ آیتم ارتقا با موفقیت حذف شد.",
            'parse_mode' => "HTML",
            'reply_markup' => $adminBack,
        ]);

        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
    }
}
