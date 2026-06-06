<?php

// Add soldier flow using managers
if ($text === "[⚔️]- افزودن سرباز" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "📌 نام انگلیسی سرباز را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-soldier-1');
} elseif ($stepManager->isInStep($from_id, 'add-soldier-1') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $db->insert('soldiers', ['english name' => $english, 'persian name' => '', 'consumable item' => '', 'first number' => '0']);
    $stepManager->setStep($from_id, 'add-soldier-2-' . $english, $english);
    SendMessage($chat_id, "📌 نام فارسی سرباز را وارد کنید :", "HTML", $message_id, $adminBack);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-soldier-2-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-soldier-2-', '', $stepManager->getStep($from_id));
    $persian = Helpers::sanitize($text);
    $db->update('soldiers', 'english name', $english, ['persian name' => $persian]);
    // ask consumable item
    $items = $itemManager->getAllItems();
    $list = array_map(function($i){ return $i['persian name'] ?? $i['english name']; }, $items);
    $strList = implode("=>\n", $list);
    SendMessage($chat_id, "📌 این سرباز چه آیتمی مصرف می کند؟\n\n<code>$strList=></code>", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-soldier-3-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-soldier-3-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-soldier-3-', '', $stepManager->getStep($from_id));
    $consumable = Helpers::sanitize($text);
    $db->update('soldiers', 'english name', $english, ['consumable item' => $consumable]);
    SendMessage($chat_id, "📌 مقدار اولیه سرباز را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-soldier-4-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-soldier-4-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-soldier-4-', '', $stepManager->getStep($from_id));
    $initial = Helpers::sanitize($text);
    $db->update('soldiers', 'english name', $english, ['first number' => (string)$initial]);
    // set default soldier counts for cities
    $citySoldiers = $db->get('citySoldiers') ?: [];
    foreach ($citySoldiers as $cityId => $vals) {
        $vals[$english] = ($db->get('soldiers')[$english]['persian name'] ?? $english) . '@' . $initial;
        $db->set('citySoldiers', $cityId, $vals);
    }
    SendMessage($chat_id, "📌 آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
    $stepManager->setStep($from_id, 'add-soldier-5-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-soldier-5-') && $text !== "🔙") {
    $english = str_replace('add-soldier-5-', '', $stepManager->getStep($from_id));
    if ($text === "✅") {
        SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
    } elseif ($text === "❌") {
        $db->delete('soldiers', 'english name', $english);
        $citySoldiers = $db->get('citySoldiers') ?: [];
        foreach ($citySoldiers as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('citySoldiers', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "عملیات لغو شد.", "HTML", $message_id, $adminBack);
    }
    $stepManager->resetStep($from_id);
}
