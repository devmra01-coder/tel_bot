<?php

if (in_array($from_id, $admins)) {
    if ($text == "پنل" || $text == "🔙") {
        
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👤|به پنل ادمین خوش آمدید",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminPanel,
        ]);
        if (!$adminsDb) {
            sendDataForDb($adminsTable, "id", $from_id);
        }
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "💎 مدیریت آیتم ها" && $theAdminStep == "none") {
        $theText = "📙 به بخش مدیریت آیتم ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $ShopPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🛒 مدیریت ایتم های خرید" && $theAdminStep == "none") {
        $theText = "📙 به بخش مدیریت آیتم ها خرید خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $ShopPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🛒 مدیریت ایتم های ارتقا" && $theAdminStep == "none") {
        $theText = "📙 به بخش مدیریت آیتم ها ارتقا خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $UpgradePanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "👤 مدیریت شخصیت ها" && $theAdminStep == "none") {
        $theText = "📘 به بخش مدیریت شخصیت ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $peoplePanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🛡 مدیریت سرباز ها" && $theAdminStep == "none") {
        $theText = "📕 به بخش مدیریت سرباز ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $soldierPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🏯 مدیریت ساختمان ها" && $theAdminStep == "none") {
        $theText = "📗 به بخش مدیریت ساختمان ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $buildingPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "⛺️ مدیریت کمپ های نظامی" && $theAdminStep == "none") {
        $theText = "📒 به بخش مدیریت کمپ های نظامی خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $campsPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }

    if ($text == "❌پاکسازی همگانی❌" && $theAdminStep == "none") {
        $theText = "هشدار!\n\n با تایید این دستور، تمام اطلاعات بات شما پاک می شود. آیا تایید می کنید؟";
        SendMessage($chat_id, $theText, "HTML", $message_id, $adminYesOrNo);
        $conn->query("UPDATE `$adminsTable` SET `step`='delete-all' WHERE `id`='{$from_id}'LIMIT 1");
    } else if ($theAdminStep == "delete-all") {
        if ($text == "✅") {
            $tables = [
                $itemsTable, 
                $soldiersTable, 
                $peopleTable, 
                $buildingsTable, 
                $campsTable, 
                $citiesTable, 
                $adminsTable, 
                $cityBuildingsTable, 
                $cityItemsTable, 
                $citySoldiersTable, 
                $cityPeopleTable, 
                $cityCampsTable
            ];
             
            foreach ($tables as $table) {
                // اطمینان از خالی نبودن نام جدول برای جلوگیری از خطاهای ناخواسته
                if (!empty($table)) {
                    if (!$conn->query("DROP TABLE IF EXISTS `$table`")) {  
                        bot('sendMessage', [
                            'chat_id' => $chat_id,
                            'text' => "جدول $table به درستی حذف نشد !",
                            'parse_mode' => "HTML",
                            'reply_to_message_id' => $message_id,
                            'reply_markup' => $adminBack,
                        ]);
                    }else {
                        bot('sendMessage', [
                            'chat_id' => $chat_id,
                            'text' => "✅  جدول $table  با موفقیت حذف شد!",
                            'parse_mode' => "HTML",
                            'reply_to_message_id' => $message_id,
                            'reply_markup' => $adminBack,
                        ]);
                    }
                }else {
                    bot('sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => "❌ خطا در حذف جداول: " . $conn->error,
                        'reply_to_message_id' => $message_id,
                    ]);
                    break;
                }
            }  
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}' LIMIT 1");
                
            bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "✅ تمامی جداول با موفقیت حذف شدند!",
                    'parse_mode' => "HTML",
                    'reply_to_message_id' => $message_id,
                    'reply_markup' => $adminBack,
                ]);
             
        }


        if ($text == "❌") {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Ah shit",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
                'reply_markup' => $adminBack,
            ]);
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
            $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$idm}`");
        }
    }
    //----------
    include './admin-panel-forms/add-item.php';
    include './admin-panel-forms/delete-item.php';
    include './admin-panel-forms/updata-item.php';
    //----------
    include './admin-panel-forms/add-soldier.php';
    include './admin-panel-forms/delete-soldier.php';
    include './admin-panel-forms/updata-soldier.php';
    //----------
    include './admin-panel-forms/add-person.php';
    include './admin-panel-forms/delete-person.php';
    include './admin-panel-forms/updata-person.php';
    //----------
    include './admin-panel-forms/add-building.php';
    include './admin-panel-forms/delete-building.php';
    include './admin-panel-forms/updata-building.php';
    //----------
    include './admin-panel-forms/add-camp.php';
    include './admin-panel-forms/delete-camp.php';
    include './admin-panel-forms/updata-camp.php';
    //----------  
    include './admin-panel-forms/add-shop.php';
    include './admin-panel-forms/delete-shop.php';
    include './admin-panel-forms/updata-shop.php';
    //----------   
    include './admin-panel-forms/add-upgrade.php';
    include './admin-panel-forms/delete-upgrade.php';
    include './admin-panel-forms/updata-upgrade.php';
    //----------  
}
