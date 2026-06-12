<?php
if ($data == "trading") {
    $tradingKeyboard = json_encode([
        'inline_keyboard' => tradingInlineButton($conn, $citiesTable, $chat_id),
        'resize_keyboard' => true
    ]);
    $theText = "[🏰]- قصد تجارت با کدام شهر را دارید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $tradingKeyboard);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-1' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "trading-1" && $text && $stop == "No") {
    $tradingItemList = json_encode([
        'inline_keyboard' =>  tradingKeyboard($conn, $itemsTable, $peopleTable, $soldiersTable),
        'resize_keyboard' => true
    ]);
    $theText = "[📤]- چه کالایی را برای این شهر ارسال می کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $tradingItemList);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-2', `maghsad`='{$text}' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "trading-2" && $text && $stop == "No") {
    $theText = "[🧮]- چه میزان از این کالا را ارسال می کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $back);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-3', `sendItem`='{$text}' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "trading-3" && $stop == "No") {
    $itemNum = "";
    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityPeopleTable}` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citySoldiersTable}` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    if ($cityItems[$sendItem]) {
        $itemNum = explode("@", $cityItems[$sendItem]);
    } else if ($cityPeople[$sendItem]) {
        $itemNum = explode("@", $cityPeople[$sendItem]);
    } else if ($citySoldiers[$sendItem]) {
        $itemNum = explode("@", $citySoldiers[$sendItem]);
    }
    if ($text < 0) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "
لطفا یک عدد بزرگ تر از 0 وارد کنید.
            ",
            'message_id' => $message_id,
            'parse_mode' => "HTML",
            'reply_markup' => $back,
        ]);
    } else {
        if ($itemNum[1] >= $text) {
            $tradingItemList = json_encode([
                'inline_keyboard' => tradingKeyboard($conn, $itemsTable, $peopleTable, $soldiersTable),
                'resize_keyboard' => true
            ]);
            $theText = "[📥]- در ازای این کالا چه چیزی دریافت می کنید؟";
            SendMessage($chat_id, $theText, "HTML", $message_id, $tradingItemList);
            $conn->query("UPDATE `$citiesTable` SET `step`='trading-4', `sendItemNum`='{$text}' WHERE `city id`='{$chat_id}'LIMIT 1");
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "
    [⚠️]- ما این مقدار از منابع را در انبار خود نداریم!\n\n[♻️]- لطفا مقدار دیگری را وارد کنید و یا اگر از این تجارت پشیمان شدید، روی دکمه زیر کلیک کنید.
                    ",
                'message_id' => $message_id,
                'parse_mode' => "HTML",
                'reply_markup' => $back,
            ]);
        }
    }
} else if ($playerStep == "trading-4" && $text && $stop == "No") {
    $theText = "[⚖️]- چه مقدار از این کالا را از طرف مقابل طلب می کنید؟";
    EditMessageText($chatId, $messageId, $theText, "HTML", $back);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-5', `getItem`='{$text}' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "trading-5" && $stop == "No") {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "[⁉️]- آیا تایید می کنید؟",
        'parse_mode' => "HTML",
        'message_id' => $message_id,
        'reply_markup' => $inlineYesOrNo,
    ]);
    $conn->query("UPDATE `$citiesTable` SET `step`='trading-6', `getItemNum`='{$text}' WHERE `city id`='{$chat_id}'LIMIT 1");
} else if ($playerStep == "trading-6" && $text && $stop == "No") {
    $tradingMaghsadCastle = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citiesTable}` WHERE `city id` = '{$maghsad}' LIMIT 1"));
    $tradinMaghsad = isset($tradingMaghsadCastle['city name']) ? $tradingMaghsadCastle['city name'] : $maghsad;
    $persianSendItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $sendItem);
    $persianGetItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $getItem);

    if ($text == "yes") {
        bot('sendMessage', [
            'chat_id' => $maghsad,
            'text' => "
[📬]- یک درخواست تجارت از طرف لرد شهر $cityName برای ما ارسال شد. این درخواست به شرح زیر می باشد :

[🧮]- ایشان مایل هستند تا $sendItemNum $persianSendItem برای ما ارسال کنند و در ازای آن $getItemNum $persianGetItem دریافت کنند.

[❓]- آیا این تجارت را تایید می کنید؟       
            ",
            'message_id' => $message_id,
            'parse_mode' => "HTML",
            'reply_markup' => $tradingYesOrNo,
        ]);
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "[✔️]- درخواست تجارت شما با موفقیت به شهر $tradinMaghsad ارسال شد. منتظر پاسخ از طرف لرد این شهر باشید.",
            'parse_mode' => "HTML",
        ]);
    } else if ($text == "no") {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "",
            'parse_mode' => "HTML",
            'reply_markup' => $back,
        ]);
    }
    $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
}
//----------------------------------------
if (strpos($text, 'send&') !== false) {
    $soldier = str_replace('send&', '', $text);
    $tradingArray = explode('&', $soldier);
    $PsendItem = $user[$tradingArray[0]] + $tradingArray[1];
    $MgetItem = $user[$tradingArray[2]] - $tradingArray[3];
    $sendItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $tradingArray[0]);
    $sendItemNum = $tradingArray[1];
    $getItem = itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $tradingArray[2]);
    $getItemNum = $tradingArray[3];
    $sender = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citiesTable}` WHERE `city id` = '{$tradingArray[4]}' LIMIT 1"));
    $tradeLimitation = $sender["tradeLimitation"];
    $sender = $sender["castle"];
    $geter = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citiesTable}` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $geter = $geter["castle"];
    $itemNum = 0;
    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $itemNum_1 = explode("@", $cityItems[$tradingArray[2]]);

    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $itemNum_2 = explode("@", $cityPeople[$tradingArray[2]]);

    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
    $itemNum_3 = explode("@", $citySoldiers[$tradingArray[2]]);
    if ($itemNum_1[1]) {
        $itemNum = $itemNum_1;
    } else if ($itemNum_2[1]) {
        $itemNum = $itemNum_2;
    } else if ($itemNum_3[1]) {
        $itemNum = $itemNum_3;
    }

    if ($itemNum[1] >= intval($tradingArray[3])) {
        $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$tradingArray[4]}' LIMIT 1"));
        $senderItemNum_1 = explode("@", $cityItems[$tradingArray[0]]);

        $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$tradingArray[4]}' LIMIT 1"));
        $senderItemNum_2 = explode("@", $cityPeople[$tradingArray[0]]);

        $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$tradingArray[4]}' LIMIT 1"));
        $senderItemNum_3 = explode("@", $citySoldiers[$tradingArray[0]]);
        $senderItemNum = 0;
        if ($senderItemNum_1[1]) {
            $senderItemNum = $senderItemNum_1;
        } else if ($senderItemNum_2[1]) {
            $senderItemNum = $senderItemNum_2;
        } else if ($senderItemNum_3[1]) {
            $senderItemNum = $senderItemNum_3;
        }
        if ($senderItemNum[1] >= $tradingArray[1]) {
            tradingFunction($conn, $cityItemsTable, $cityPeopleTable, $citySoldiersTable, $tradingArray[4], $chat_id, $tradingArray[0], $tradingArray[2], $tradingArray[1], $tradingArray[3]);

            bot('EditMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "[✔️]- این معامله با موفقیت انجام شد سرورم.",
                'parse_mode' => "HTML",
            ]);

            bot('sendMessage', [
                'chat_id' => $tradingArray[4],
                'text' => "[✔️]- درخواست تجارت شما توسط لرد شهر $cityName پذیرفته شد.",
                'parse_mode' => "HTML",
            ]);
            bot('sendMessage', [
                'chat_id' => $tradeGap,
                'text' => "[🍱]- شهر $sender مقادر $sendItemNum $sendItem  برای شهر $geter فرستاد و $getItemNum $getItem تحویل گرفت.",
                'parse_mode' => "HTML",
            ]);
        } else {
            $city = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citiesTable` WHERE `city id` = '{$tradingArray[4]}' LIMIT 1"));
            $senderCityName = $city["city name"];
            bot('EditMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => "[‼️]-گستاختی بزرگی رخ داده سرورم!\n با اینکه فرمانروای $senderCityName درخواست این تجارت را کرده بودند، اما مشخص شد که ایشان مقدار کالای مورد نیاز برای انجام آن را ندارند!\n\n این یک بی احترامی بزرگ به ماست...",
                'parse_mode' => "HTML",
            ]);
            bot('sendMessage', [
                'chat_id' => $tradingArray[4],
                'text' => "[‼️]- شما دارای منابع مورد نیاز برای تجارت با شهر $cityName نبودید و این تجارت لغو شد!",
                'parse_mode' => "HTML",
            ]);
        }
    } else {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "[🚫]- متاسفانه سرورم، ما منابع کافی برای انجام این معامله تجاری را نداریم.",
            'parse_mode' => "HTML",
        ]);

        bot('sendMessage', [
            'chat_id' => $tradingArray[4],
            'text' => "[‼️]- لرد شهر $cityName قادر به پذیرش درخواست تجارت شما نیست!",
            'parse_mode' => "HTML",
        ]);
    }
}

if (strpos($text, 'NoSendding&') !== false) {
    $idm = str_replace('send&', '', $text);
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "[✔️]- این درخواست رد شد سرورم.",
        'parse_mode' => "HTML",
    ]);

    bot('sendMessage', [
        'chat_id' => $idm,
        'text' => "[❌]- متاسفانه قربان، لرد شهر $castle با درخواست تجارت ما موافقت نکرد.",
        'parse_mode' => "HTML",
    ]);
}
