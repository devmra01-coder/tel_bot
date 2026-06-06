<?php
if ($text === "[❌]- حذف ساختمان" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "🏯 نام انگلیسی ساختمان را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'delete-building');
} elseif ($stepManager->isInStep($from_id, 'delete-building') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $building = $buildingManager->getBuilding($english);
    if (!$building) {
        SendMessage($chat_id, "هیچ ساختمانی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.", "HTML", $message_id, $adminBack);
    } else {
        $buildingManager->deleteBuilding($english);
        $cityBuildings = $db->get('cityBuildings') ?: [];
        foreach ($cityBuildings as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityBuildings', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "ساختمان حذف شد.", "HTML", $message_id, $adminBack);
        $stepManager->resetStep($from_id);
    }
}