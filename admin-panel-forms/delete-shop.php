// ==================== حذف آیتم خرید ====================
if ($text == "[❌]- حذف آیتم خرید" && $theAdminStep == "none") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🛒 نام انگلیسی آیتم خرید را وارد کنید:",
        'parse_mode' => "HTML",
        'reply_markup' => $adminBack,
    ]);
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-shop-item' WHERE `id`='{$from_id}' LIMIT 1");
}

// پردازش حذف
else if ($theAdminStep == "delete-shop-item" && $text != "🔙") {
    $itemName = trim($text);

    $result = mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '{$conn->real_escape_string($itemName)}' LIMIT 1");
    $item = mysqli_fetch_assoc($result);

    if (!$item) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "❌ آیتمی با این نام یافت نشد.",
            'reply_markup' => $adminBack,
        ]);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
    } else {
        $conn->query("DELETE FROM `shop_items` WHERE `item_name` = '{$conn->real_escape_string($itemName)}'");

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "✅ آیتم خرید با موفقیت حذف شد.",
            'parse_mode' => "HTML",
            'reply_markup' => $adminBack,
        ]);

        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
    }
}
