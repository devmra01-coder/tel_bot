<?php
if ($text === "[❌]- حذف شخصیت" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "👤 نام انگلیسی شخصیت را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'delete-person');
} elseif ($stepManager->isInStep($from_id, 'delete-person') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $person = $personManager->getPerson($english);
    if (!$person) {
        SendMessage($chat_id, "هیچ شخصیتی با این نام در ربات وجود ندارد! \n لطفا نام وارده را برسی کنید و در صورت وجود خطا در آن، نام صحیح را در اینجا وارد کنید.", "HTML", $message_id, $adminBack);
    } else {
        $personManager->deletePerson($english);
        $cityPeople = $db->get('cityPeople') ?: [];
        foreach ($cityPeople as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityPeople', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "شخصیت حذف شد.", "HTML", $message_id, $adminBack);
        $stepManager->resetStep($from_id);
    }
}