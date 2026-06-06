<?php

if ($data == "trading") {
    $tradingKeyboard = json_encode([
        'inline_keyboard' => tradingInlineButton($conn, $citiesTable, $chat_id),
        'resize_keyboard' => true
    ], JSON_UNESCAPED_UNICODE);
    
    $theText = "[🏰]- قصد تجارت با کدام شهر را دارید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $tradingKeyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-1' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "trading-1" && $data && $stop == "No") {
    $tradingItemList = json_encode([
        'inline_keyboard' => tradingKeyboard($conn, $itemsTable, $peopleTable, $soldiersTable),
        'resize_keyboard' => true
    ], JSON_UNESCAPED_UNICODE);
    
    $theText = "[📤]- چه کالایی را برای این شهر ارسال می‌کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $tradingItemList);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-2', `maghsad`='{$data}' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "trading-2" && $data && $stop == "No") {
    $theText = "[🧮]- چه میزان از این کالا را ارسال می‌کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $back);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-3', `sendItem`='{$data}' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "trading-3" && $stop == "No") {
    $itemNum = "";
    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    
    if (isset($cityItems[$sendItem]) && $cityItems[$sendItem]) {
        $itemNum = explode("@", $cityItems[$sendItem]);
    } 
    else if (isset($cityPeople[$sendItem]) && $cityPeople[$sendItem]) {
        $itemNum = explode("@", $cityPeople[$sendItem]);
    } 
    else if (isset($citySoldiers[$sendItem]) && $citySoldiers[$sendItem]) {
        $itemNum = explode("@", $citySoldiers[$sendItem]);
    }
    
    if (intval($text) <= 0) {
        SendMessage($chat_id, "[⚠️]- لطفاً یک عدد بزرگ‌تر از 0 وارد کنید.", "HTML", $message_id, $back);
    } 
    else if (isset($itemNum[1]) && intval($itemNum[1]) >= intval($text)) {
        $tradingItemList = json_encode([
            'inline_keyboard' => tradingKeyboard($conn, $itemsTable, $peopleTable, $soldiersTable),
            'resize_keyboard' => true
        ], JSON_UNESCAPED_UNICODE);
        
        $theText = "[📥]- در ازای این کالا چه چیزی دریافت می‌کنید؟";
        SendMessage($chat_id, $theText, "HTML", $message_id, $tradingItemList);
        $conn->query("UPDATE `$citiesTable` SET `step`='trading-4', `sendItemNum`='{$text}' WHERE `city id`='{$chat_id}' LIMIT 1");
    } 
    else {
        SendMessage($chat_id, "[⚠️]- ما این مقدار از منابع را در انبار خود نداریم!\n\n[♻️]- لطفاً مقدار دیگری را وارد کنید.", "HTML", $message_id, $back);
    }
} 
else if ($playerStep == "trading-4" && $data && $stop == "No") {
    $theText = "[⚖️]- چه مقدار از این کالا را از طرف مقابل طلب می‌کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $back);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-5', `getItem`='{$data}' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "trading-5" && $stop == "No") {
    SendMessage($chat_id, "[⁉️]- آیا تایید می‌کنید؟", "HTML", $message_id, $inlineYesOrNo);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-6', `getItemNum`='{$text}' WHERE `city id`='{$chat_id}' LIMIT 1");
} 
else if ($playerStep == "trading-6" && $data && $stop == "No") {
    $tradingMaghsadCastle = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citiesTable` WHERE `city id` = '{$maghsad}' LIMIT 1"));
    $tradinMaghsad = isset($tradingMaghsadCastle['city name']) ? $tradingMaghsadCastle['city name'] : $maghsad;
    
    $persianSendItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $sendItem) ?: "[نامشخص]";
    $persianGetItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $getItem) ?: "[نامشخص]";
    
    if ($data == "yes") {
        $tradingYesOrNo = json_encode([
            'inline_keyboard' => [
                [['text' => "❌", 'callback_data' => "NoSendding&{$chat_id}"], 
                 ['text' => "✅", 'callback_data' => "send&{$sendItem}&{$sendItemNum}&{$getItem}&{$getItemNum}&{$chat_id}"]]
            ]
        ], JSON_UNESCAPED_UNICODE);
        
        SendMessage($maghsad, 
            "[📬]- یک درخواست تجارت از طرف لرد شهر $cityName برای ما ارسال شد.\n\n[🧮]- ایشان مایل هستند تا $sendItemNum $persianSendItem برای ما ارسال کنند و در ازای آن $getItemNum $persianGetItem دریافت کنند.\n\n[❓]- آیا این تجارت را تایید می‌کنید؟",
            "HTML", $message_id, $tradingYesOrNo
        );
        
        EditMessageText($chat_id, $message_id, "[✔️]- درخواست تجارت شما با موفقیت به شهر $tradinMaghsad ارسال شد. منتظر پاسخ از طرف لرد این شهر باشید.", "HTML");
    } 
    else if ($data == "no") {
        EditMessageText($chat_id, $message_id, "[❌]- این درخواست کنسل شد.", "HTML", $back);
    }
    
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}' LIMIT 1");
}

// ========================================
// تایید تجارت توسط طرف مقابل
// ========================================

if (strpos($data, 'send&') !== false) {
    $soldier = str_replace('send&', '', $data);
    $tradingArray = explode('&', $soldier);
    
    if (count($tradingArray) < 5) {
        SendMessage($chat_id, "[❌]- خطا در پردازش تجارت!", "HTML", $message_id);
        return;
    }
    
    $sendItem = $tradingArray[0];
    $sendItemNum = intval($tradingArray[1]);
    $getItem = $tradingArray[2];
    $getItemNum = intval($tradingArray[3]);
    $senderChatId = $tradingArray[4];
    
    $persianSendItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $sendItem) ?: "[نامشخص]";
    $persianGetItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $getItem) ?: "[نامشخص]";
    
    // بررسی موجودی
    $itemNum = 0;
    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    
    // پیدا کردن item
    if (isset($cityItems[$getItem]) && $cityItems[$getItem]) {
        $itemNum_1 = explode("@", $cityItems[$getItem]);
        $itemNum = isset($itemNum_1[1]) ? intval($itemNum_1[1]) : 0;
    } 
    else if (isset($cityPeople[$getItem]) && $cityPeople[$getItem]) {
        $itemNum_2 = explode("@", $cityPeople[$getItem]);
        $itemNum = isset($itemNum_2[1]) ? intval($itemNum_2[1]) : 0;
    } 
    else if (isset($citySoldiers[$getItem]) && $citySoldiers[$getItem]) {
        $itemNum_3 = explode("@", $citySoldiers[$getItem]);
        $itemNum = isset($itemNum_3[1]) ? intval($itemNum_3[1]) : 0;
    }
    
    if ($itemNum >= $getItemNum) {
        // انجام تجارت
        tradingFunction($conn, $cityItemsTable, $cityPeopleTable, $citySoldiersTable, $senderChatId, $chat_id, $sendItem, $getItem, $sendItemNum, $getItemNum);
        
        EditMessageText($chat_id, $message_id, "[✅]- این تجارت با موفقیت انجام شد!", "HTML");
        
        SendMessage($senderChatId, "[✅]- درخواست تجارت شما توسط لرد شهر $cityName پذیرفته شد.", "HTML");
    } 
    else {
        EditMessageText($chat_id, $message_id, "[❌]- متأسفانه شما منابع کافی برای این تجارت ندارید!", "HTML");
        
        SendMessage($senderChatId, "[❌]- متأسفانه لرد شهر $cityName قادر به پذیرفتن درخواست تجارت شما نیست!", "HTML");
    }
}

if (strpos($data, 'NoSendding&') !== false) {
    $senderChatId = str_replace('NoSendding&', '', $data);
    
    EditMessageText($chat_id, $message_id, "[✔️]- این درخواست رد شد.", "HTML");
    SendMessage($senderChatId, "[❌]- متأسفانه لرد شهر $cityName با درخواست تجارت شما موافقت نکرد.", "HTML");
}
?>