<?php

// Admin panel using JSON DB and helpers
if (Config::getInstance()->isAdmin($from_id)) {
    $currentStep = $stepManager->getStep($from_id);

    if ($text === "پنل" || $text === "🔙") {
        SendMessage($chat_id, "👤|به پنل ادمین خوش آمدید", "HTML", $message_id, $adminPanel);
        $stepManager->resetStep($from_id);
    }

    if ($text === "💎 مدیریت آیتم ها" && $currentStep === 'none') {
        SendMessage($chat_id, "📙 به بخش مدیریت آیتم ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.", "HTML", $message_id, $itemsPanel);
    }

    if ($text === "👤 مدیریت شخصیت ها" && $currentStep === 'none') {
        SendMessage($chat_id, "📘 به بخش مدیریت شخصیت ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.", "HTML", $message_id, $peoplePanel);
    }

    if ($text === "🛡 مدیریت سرباز ها" && $currentStep === 'none') {
        SendMessage($chat_id, "📕 به بخش مدیریت سرباز ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.", "HTML", $message_id, $soldierPanel);
    }

    if ($text === "🏯 مدیریت ساختمان ها" && $currentStep === 'none') {
        SendMessage($chat_id, "📗 به بخش مدیریت ساختمان ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.", "HTML", $message_id, $buildingPanel);
    }

    if ($text === "⛺️ مدیریت کمپ های نظامی" && $currentStep === 'none') {
        SendMessage($chat_id, "📒 به بخش مدیریت کمپ های نظامی خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.", "HTML", $message_id, $campsPanel);
    }

    // delete-all flow
    if ($text === "❌پاکسازی همگانی❌" && $currentStep === 'none') {
        SendMessage($chat_id, "هشدار!\n\n با تایید این دستور، تمام اطلاعات بات شما پاک می شود. آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
        $stepManager->setStep($from_id, 'delete-all');
    } elseif ($stepManager->isInStep($from_id, 'delete-all')) {
        if ($text === "✅") {
            // truncate all tables in JSON DB
            $db->truncate('items');
            $db->truncate('people');
            $db->truncate('buildings');
            $db->truncate('soldiers');
            $db->truncate('camps');
            // preserve system cities if needed; here we reset cities
            $db->truncate('cities');
            $db->truncate('cityItems');
            $db->truncate('cityBuildings');
            $db->truncate('citySoldiers');
            $db->truncate('cityPeople');
            $db->truncate('cityCamps');

            SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
            $stepManager->resetStep($from_id);
        } elseif ($text === "❌") {
            SendMessage($chat_id, "انصراف ثبت شد.", "HTML", $message_id, $adminBack);
            $stepManager->resetStep($from_id);
        }
    }

    // include admin form handlers (they will use $db and manager classes)
    include_once __DIR__ . '/admin-panel-forms/add-item.php';
    include_once __DIR__ . '/admin-panel-forms/delete-item.php';
    include_once __DIR__ . '/admin-panel-forms/add-soldier.php';
    include_once __DIR__ . '/admin-panel-forms/delete-soldier.php';
    include_once __DIR__ . '/admin-panel-forms/add-person.php';
    include_once __DIR__ . '/admin-panel-forms/delete-person.php';
    include_once __DIR__ . '/admin-panel-forms/add-building.php';
    include_once __DIR__ . '/admin-panel-forms/delete-building.php';
    include_once __DIR__ . '/admin-panel-forms/add-camp.php';
    include_once __DIR__ . '/admin-panel-forms/delete-camp.php';
}
