<?php
// ۱. شروع عملیات حذف
if ($text == "[❌]- حذف ساختمان" && $theAdminStep == "none") {
    $conn->query("UPDATE `$adminsTable` SET `step`='delete-building' WHERE `id`='{$from_id}' LIMIT 1");
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "🏯 نام انگلیسی ساختمان را وارد کنید:",
        'reply_markup' => $adminBack,
    ]);
} 
// ۲. پردازش نام ساختمان (فقط اگر مرحله واقعاً delete-building باشد)
else if ($theAdminStep == "delete-building") {
    
    // اگر دکمه برگشت زده شد، مرحله را ریست کن
    if ($text == "🔙") {
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "عملیات لغو شد.", 'reply_markup' => $mainMenu]);
    } 
    else {
        // جستجوی ساختمان
        $result = $conn->query("SELECT * FROM `$buildingsTable` WHERE `english name` = '{$conn->real_escape_string($text)}' LIMIT 1");
        $building = mysqli_fetch_assoc($result);

        if (!$building) {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "❌ ساختمانی با این نام یافت نشد. دوباره تلاش کنید یا 🔙 را بزنید.",
            ]);
        } else {
            // حذف ساختمان از جداول
            $buildingName = $building['english name'];
            
            // جلوگیری از خطای سینتکس با استفاده از بک‌تیک
            $conn->query("DELETE FROM `$buildingsTable` WHERE `english name` = '{$conn->real_escape_string($buildingName)}'");
            $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `$buildingName`");
            
            // ریست کردن مرحله به none
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ ساختمان با موفقیت حذف شد.",
                'reply_markup' => $mainMenu,
            ]);
        }
    }
}
