<?php

$adminPanel = json_encode([
    'keyboard' => [
        [['text' => "👤 مدیریت شخصیت ها"], ['text' => "💎 مدیریت آیتم ها"]],
        [['text' => "🛡 مدیریت سرباز ها"]],
        [['text' => "⛺️ مدیریت کمپ های نظامی"], ['text' => "🏯 مدیریت ساختمان ها"]],
        [['text' => "🛒 مدیریت ایتم های ارتقا"], ['text' => "🛒 مدیریت ایتم های خرید"]],
        [['text' =>  "❌پاکسازی همگانی❌"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$itemsPanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف آیتم"], ['text' => "[➕]- افزودن آیتم"]],
                          [['text' => "[✏️]- ویرایش آیتم"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$ShopPanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف آیتم خرید"], ['text' => "[🛒]- افزودن آیتم خرید"]],
                         [['text' => "[✏️]- ویرایش آیتم خرید"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$UpgradePanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف آیتم ارتقا"], ['text' => "افزودن آیتم ارتقا"]],
                            [['text' => "[✏️]- ویرایش آیتم ارتقا"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$soldierPanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف سرباز"], ['text' => "[⚔️]- افزودن سرباز"]],[['text' => "[✏️]- ویرایش سرباز"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$peoplePanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف شخصیت"], ['text' => "[👤]- افزودن شخصیت"]],[['text' => "[✏️]- ویرایش شخصیت"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$buildingPanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف ساختمان"], ['text' => "[🏗️]- افزودن ساختمان"]],[['text' => "[✏️]- ویرایش ساختمان"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);
$campsPanel = json_encode([
    'keyboard' => [
        [['text' => "[❌]- حذف کمپ"], ['text' => "[⛺️]- افزودن کمپ"]],[['text' => "[✏️]- ویرایش کمپ"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);

$managementPanel = json_encode([
    'inline_keyboard' => [
        [['text' => "[👑]- تنظیم نام شهربان", 'callback_data' => "Set lord name"],['text' => "[🏯]- تنظیم نام شهر", 'callback_data' => "Set city name"]]
    ]
]);

$playerPanel = json_encode([
    'inline_keyboard' => [
        [['text' => "[💰]- مشاهده دارایی", 'callback_data' => "show financial"]],
        [['text' => "[⚒]- ارتقا", 'callback_data' => "upgrade"],['text' => "[📦]- تجارت", 'callback_data' => "trading"]],
        [['text' => "[🪙]- خرید و ارتقا", 'callback_data' => "shoping"]]
    ]
]);
$Back = json_encode([
    'inline_keyboard' => [
        [['text' => "[🔙]- بازگشت", 'callback_data' => "Back"]]
                        ]
]);

$adminBack = json_encode([
    'keyboard' => [
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);

$adminDoseNotNeedIt = json_encode([
    'keyboard' => [
        [['text' => "♨️ نیاز نیست"]],
        [['text' => "🔙"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);

$adminYesOrNo = json_encode([
    'keyboard' => [
        [['text' => "❌"],['text' => "✅"]],
    ],
    'resize_keyboard' => true,
    'remove_keyboard' => true
]);

$inlineYesOrNo = json_encode([
    'inline_keyboard' => [
        [['text' => "خیر", 'callback_data' => "no"],['text' => "بله", 'callback_data' => "yes"]]
    ]
]);

$upgradePanel = json_encode([
    'inline_keyboard' => $upgradeKeyboard,
    'resize_keyboard' => true
]);

$tradingYesOrNo = json_encode([
    'inline_keyboard' => [
        [['text' => "❌", 'callback_data' => "NoSendding&$chat_id"], ['text' => "✅", 'callback_data' => "send&$sendItem&$sendItemNum&$getItem&$getItemNum&$chat_id"]],
    ],
    'resize_keyboard' => true
]);
