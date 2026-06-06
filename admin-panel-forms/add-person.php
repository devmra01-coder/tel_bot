<?php

// Add person flow
if ($text === "[👤]- افزودن شخصیت" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "📌 نام انگلیسی شخصیت را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-person-1');
} elseif ($stepManager->isInStep($from_id, 'add-person-1') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $db->insert('people', ['english name' => $english, 'persian name' => '', 'consumable item' => '', 'first number' => '0']);
    $stepManager->setStep($from_id, 'add-person-2-' . $english, $english);
    SendMessage($chat_id, "📌 نام فارسی شخصیت را وارد کنید :", "HTML", $message_id, $adminBack);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-person-2-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-person-2-', '', $stepManager->getStep($from_id));
    $persian = Helpers::sanitize($text);
    $db->update('people', 'english name', $english, ['persian name' => $persian]);
    // ask consumable
    $items = $itemManager->getAllItems();
    $list = array_map(function($i){ return $i['persian name'] ?? $i['english name']; }, $items);
    $strList = implode("=>\n", $list);
    SendMessage($chat_id, "📌 این شخصیت چه آیتمی مصرف می کند؟\n\n<code>$strList=></code>", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-person-3-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-person-3-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-person-3-', '', $stepManager->getStep($from_id));
    $consumable = Helpers::sanitize($text);
    $db->update('people', 'english name', $english, ['consumable item' => $consumable]);
    SendMessage($chat_id, "📌 مقدار اولیه شخصیت در دارایی را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-person-4-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-person-4-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-person-4-', '', $stepManager->getStep($from_id));
    $initial = Helpers::sanitize($text);
    $db->update('people', 'english name', $english, ['first number' => (string)$initial]);
    $cityPeople = $db->get('cityPeople') ?: [];
    foreach ($cityPeople as $cityId => $vals) {
        $vals[$english] = ($db->findOne('people','english name',$english)['persian name'] ?? $english) . '@' . $initial;
        $db->set('cityPeople', $cityId, $vals);
    }
    SendMessage($chat_id, "📌 آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
    $stepManager->setStep($from_id, 'add-person-5-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-person-5-') && $text !== "🔙") {
    $english = str_replace('add-person-5-', '', $stepManager->getStep($from_id));
    if ($text === "✅") {
        SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
    } elseif ($text === "❌") {
        $db->delete('people', 'english name', $english);
        $cityPeople = $db->get('cityPeople') ?: [];
        foreach ($cityPeople as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityPeople', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "عملیات لغو شد.", "HTML", $message_id, $adminBack);
    }
    $stepManager->resetStep($from_id);
}