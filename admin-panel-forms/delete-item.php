<?php
if ($text === "[❌]- حذف آیتم" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "🏯 نام انگلیسی آیتم را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'delete-item');
} elseif ($stepManager->isInStep($from_id, 'delete-item') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $item = $itemManager->getItem($english);
    if (!$item) {
        SendMessage($chat_id, "هیچ آیتمی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.", "HTML", $message_id, $adminBack);
    } else {
        // remove from items and from city items
        $db->delete('items', 'english name', $english);
        $cityItems = $db->get('cityItems') ?: [];
        foreach ($cityItems as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityItems', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "آیتم حذف شد.", "HTML", $message_id, $adminBack);
        $stepManager->resetStep($from_id);
    }
}