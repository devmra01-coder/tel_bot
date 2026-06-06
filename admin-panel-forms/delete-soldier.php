<?php
if ($text === "[❌]- حذف سرباز" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "🏯 نام انگلیسی سرباز را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'delete-soldier');
} elseif ($stepManager->isInStep($from_id, 'delete-soldier') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $soldier = $soldierManager->getSoldier($english);
    if (!$soldier) {
        SendMessage($chat_id, "هیچ سربازی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.", "HTML", $message_id, $adminBack);
    } else {
        $soldierManager->deleteSoldier($english);
        $citySoldiers = $db->get('citySoldiers') ?: [];
        foreach ($citySoldiers as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('citySoldiers', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "سرباز حذف شد.", "HTML", $message_id, $adminBack);
        $stepManager->resetStep($from_id);
    }
}