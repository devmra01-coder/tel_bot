<?php

/**
 * ========================================
 * پنل بازیکن (Player Panel)
 * ========================================
 * نمایش منوی اصلی بازیکن با گزینه‌های مختلف
 */

// متن خوش‌آمدی
$welcomeText = "⚜️ <b>سلام! به بازی خوش آمدید 🎮</b>\n\n";
$welcomeText .= "در اینجا می‌تونی:\n";
$welcomeText .= "💰 <b>دارایی شهرت رو</b> مشاهده کنی\n";
$welcomeText .= "⚒ <b>ساختمان‌ها و سربازان</b> رو ارتقا دی\n";
$welcomeText .= "📦 <b>با شهرهای دیگر</b> تجارت کنی\n\n";
$welcomeText .= "دکمه‌های زیر رو انتخاب کن 👇";

// متن کریدیت (تنها برای کاربران جدید)
$creditText = "";
$cityData = $db->findOne('cities', 'city id', $chat_id);
$isFirstVisit = !$cityData || ($cityData['check'] != 'Yes');

if ($isFirstVisit) {
    $creditText .= "\n\n═══════════════════════════════════\n";
    $creditText .= "🤖 <b>طراح :</b> Hectorim\n";
    $creditText .= "📢 <b>کانال :</b> @Hector_Bots\n";
    $creditText .= "💼 <b>پشتیبانی :</b> @HectorTMSupport\n";
    $creditText .= "═══════════════════════════════════";
}

// کیبورد دکمه‌های پنل
$playerPanelKeyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => "💰 مشاهده دارایی", 'callback_data' => "show financial"]
        ],
        [
            ['text' => "⚒ ارتقا", 'callback_data' => "upgrade"],
            ['text' => "📦 تجارت", 'callback_data' => "trading"]
        ]
    ]
], JSON_UNESCAPED_UNICODE);

// ========================================
// 🔘 دستور پنل (Panel Command)
// ========================================
if ($text === "پنل" && $playerStep == "none") {
    
    // اگر اولین بار است، این شهر را علامت‌گذاری کن
    if ($isFirstVisit) {
        $cityManager->getOrCreateCity($chat_id);
        $db->update('cities', 'city id', $chat_id, ['check' => 'Yes']);
    }
    
    // ارسال پیام خوش‌آمد
    SendMessage(
        $chat_id,
        $welcomeText . $creditText,
        "HTML",
        $message_id,
        $playerPanelKeyboard
    );
    
    // تنظیم step بازیکن
    $db->update('cities', 'city id', $chat_id, ['step' => 'none']);
}

// ========================================
// 🔙 دستور بازگشت (Back Command)
// ========================================
if ($data == "back" && strpos($playerStep, "none") !== false) {
    
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $welcomeText,
        'parse_mode' => "HTML",
        'reply_markup' => $playerPanelKeyboard,
    ]);
    
    // ریست کردن step
    $db->update('cities', 'city id', $chat_id, ['step' => 'none']);
}

// ========================================
// ⚠️ فیلتر امنیتی: جلوگیری از مختل کردن عملیات
// ========================================
if ($playerStep != "none" && $text === "پنل") {
    SendMessage(
        $chat_id,
        "⚠️ <b>لطفاً عملیات فعلی خود را تکمیل کنید یا با دکمه 🔙 بازگردید.</b>",
        "HTML",
        $message_id
    );
}

// ========================================
// 🎯 دستورات کمکی و راهنما
// ========================================
if ($text === "/help" || $text === "راهنما") {
    $helpText = "📖 <b>راهنمای بازی</b>\n\n";
    $helpText .= "💰 <b>دارایی :</b> تمام منابع شهرت رو اینجا می‌تونی ببینی\n";
    $helpText .= "⚒ <b>ارتقا :</b> ساختمان‌ها و پایگاه‌های نظامی رو بالاتر ببر\n";
    $helpText .= "📦 <b>تجارت :</b> با شهرهای دیگر منابع تبادل کن\n\n";
    $helpText .= "دستورات:\n";
    $helpText .= "/start - شروع بازی\n";
    $helpText .= "پنل - باز کردن منوی اصلی\n";
    $helpText .= "دارایی - مشاهده منابع\n";
    
    SendMessage($chat_id, $helpText, "HTML", $message_id);
}

// ========================================
// 📊 دستور نمایش آمار (Stats)
// ========================================
if ($text === "/stats" || $text === "آمار") {
    $cityData = $db->findOne('cities', 'city id', $chat_id);
    
    $statsText = "📊 <b>آمار شهرت</b>\n\n";
    $statsText .= "🆔 <b>شناسه شهر :</b> <code>" . $chat_id . "</code>\n";
    $statsText .= "🏛 <b>نام :</b> " . htmlspecialchars($cityData['city name'] ?? "[تنظیم نشده]", ENT_QUOTES, 'UTF-8') . "\n";
    $statsText .= "👑 <b>فرمانده :</b> " . htmlspecialchars($cityData['lord name'] ?? "[تنظیم نشده]", ENT_QUOTES, 'UTF-8') . "\n";
    $statsText .= "📅 <b>مرحله فعلی :</b> " . ($cityData['step'] ?? "none") . "\n\n";
    
    $allItems = $db->getAll('items') ?: [];
    $statsText .= "📦 <b>تعداد انواع آیتم :</b> " . count($allItems) . "\n";
    
    $allSoldiers = $db->getAll('soldiers') ?: [];
    $statsText .= "⚔️ <b>تعداد انواع سرباز :</b> " . count($allSoldiers) . "\n";
    
    $allBuildings = $db->getAll('buildings') ?: [];
    $statsText .= "🏯 <b>تعداد انواع ساختمان :</b> " . count($allBuildings) . "\n";
    
    $allCamps = $db->getAll('camps') ?: [];
    $statsText .= "⛺️ <b>تعداد انواع کمپ :</b> " . count($allCamps) . "\n";
    
    SendMessage($chat_id, $statsText, "HTML", $message_id);
}

?>