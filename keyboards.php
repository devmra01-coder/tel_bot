<?php

/**
 * Generators for reply and inline keyboards.
 * Use helper functions to get JSON-ready markup.
 */

function keyboard_json(array $keyboard, $resize = true, $remove = true)
{
    return json_encode([
        'keyboard' => $keyboard,
        'resize_keyboard' => $resize,
        'remove_keyboard' => $remove
    ], JSON_UNESCAPED_UNICODE);
}

function inline_json(array $inline)
{
    return json_encode([
        'inline_keyboard' => $inline
    ], JSON_UNESCAPED_UNICODE);
}

function admin_panel()
{
    $keyboard = [
        [[['text' => "👤 مدیریت شخصیت ها"], ['text' => "💎 مدیریت آیتم ها"]][0][0], []],
    ];
    // Simpler explicit panels below
    $keyboard = [
        [[['text' => "👤 مدیریت شخصیت ها"], ['text' => "💎 مدیریت آیتم ها"]]],
        [[['text' => "🛡 مدیریت سرباز ها"]]],
        [[['text' => "⛺️ مدیریت کمپ های نظامی"], ['text' => "🏯 مدیریت ساختمان ها"]]],
        [[['text' => "❌پاکسازی همگانی❌"]]]
    ];
    // convert inner structure to expected shape
    $kb = [];
    foreach ($keyboard as $row) {
        $r = [];
        foreach ($row as $cell) {
            $r[] = ['text' => $cell[0]['text']];
        }
        $kb[] = $r;
    }
    return keyboard_json($kb);
}

function items_panel()
{
    $kb = [
        [['text' => "[❌]- حذف آیتم"], ['text' => "[➕]- افزودن آیتم"]],
        [['text' => "🔙"]]
    ];
    return keyboard_json($kb);
}

function soldier_panel()
{
    $kb = [
        [['text' => "[❌]- حذف سرباز"], ['text' => "[⚔️]- افزودن سرباز"]],
        [['text' => "🔙"]]
    ];
    return keyboard_json($kb);
}

function people_panel()
{
    $kb = [
        [['text' => "[❌]- حذف شخصیت"], ['text' => "[👤]- افزودن شخصیت"]],
        [['text' => "🔙"]]
    ];
    return keyboard_json($kb);
}

function building_panel()
{
    $kb = [
        [['text' => "[❌]- حذف ساختمان"], ['text' => "[🏗️]- افزودن ساختمان"]],
        [['text' => "🔙"]]
    ];
    return keyboard_json($kb);
}

function camps_panel()
{
    $kb = [
        [['text' => "[❌]- حذف کمپ"], ['text' => "[⛺️]- افزودن کمپ"]],
        [['text' => "🔙"]]
    ];
    return keyboard_json($kb);
}

function management_panel()
{
    $inline = [
        [
            ['text' => "[👑]- تنظیم نام شهربان", 'callback_data' => "Set lord name"],
            ['text' => "[🏯]- تنظیم نام شهر", 'callback_data' => "Set city name"]
        ]
    ];
    return inline_json($inline);
}

function player_panel()
{
    $inline = [
        [['text' => "[💰]- مشاهده دارایی", 'callback_data' => "show financial"]],
        [[['text' => "[⚒]- ارتقا", 'callback_data' => "upgrade"], ['text' => "[📦]- تجارت", 'callback_data' => "trading"]]]
    ];
    // normalize
    $inline2 = [];
    foreach ($inline as $row) {
        $r = [];
        foreach ($row as $cell) {
            $r[] = $cell;
        }
        $inline2[] = $r;
    }
    return inline_json($inline2);
}

function admin_back()
{
    return keyboard_json([[['text' => "🔙"]]]);
}

function admin_does_not_need()
{
    return keyboard_json([[['text' => "♨️ نیاز نیست"]], [['text' => "🔙"]]]);
}

function admin_yes_no()
{
    return keyboard_json([[['text' => "❌"], ['text' => "✅"]]]);
}

function inline_yes_no()
{
    return inline_json([[['text' => "خیر", 'callback_data' => "no"], ['text' => "بله", 'callback_data' => "yes"]]]);
}

function upgrade_panel($upgradeKeyboard)
{
    return inline_json($upgradeKeyboard ?: []);
}

function trading_yes_no($chat_id, $sendItem = '', $sendItemNum = '', $getItem = '', $getItemNum = '')
{
    $inline = [[['text' => "❌", 'callback_data' => "NoSendding&$chat_id"], ['text' => "✅", 'callback_data' => "send&$sendItem&$sendItemNum&$getItem&$getItemNum&$chat_id"]]];
    return inline_json($inline);
}

// Backward-compatible variables (JSON strings)
$adminPanel = admin_panel();
$itemsPanel = items_panel();
$soldierPanel = soldier_panel();
$peoplePanel = people_panel();
$buildingPanel = building_panel();
$campsPanel = camps_panel();
$managementPanel = management_panel();
$playerPanel = player_panel();
$adminBack = admin_back();
$adminDoseNotNeedIt = admin_does_not_need();
$adminYesOrNo = admin_yes_no();
$inlineYesOrNo = inline_yes_no();

?>
