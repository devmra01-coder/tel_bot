<?php

// Add item flow using StepManager and ItemManager
if ($text === "[➕]- افزودن آیتم" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "📌 نام انگلیسی آیتم را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-item-1');
} elseif ($stepManager->isInStep($from_id, 'add-item-1') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    // store english name in step data
    $stepManager->setStep($from_id, 'add-item-2-' . $english, $english);
    SendMessage($chat_id, "📌 نام فارسی آیتم را وارد کنید :", "HTML", $message_id, $adminBack);
    // create base item record
    $db->insert('items', ['english name' => $english, 'persian name' => '', 'first number' => '0']);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-item-2-') && $text !== "🔙") {
    $stepData = $stepManager->getStepData($from_id);
    $english = $stepData ?: str_replace('add-item-2-', '', $stepManager->getStep($from_id));
    $persian = Helpers::sanitize($text);
    $stepManager->setStep($from_id, 'add-item-3-' . $english, $english);
    // update persian name
    $db->update('items', 'english name', $english, ['persian name' => $persian]);
    SendMessage($chat_id, "📌 مقدار اولیه آیتم را وارد کنید :", "HTML", $message_id, $adminBack);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-item-3-') && $text !== "🔙") {
    $english = str_replace('add-item-3-', '', $stepManager->getStep($from_id));
    $initial = Helpers::sanitize($text);
    $item = $itemManager->getItem($english);
    $persian = $item['persian name'] ?? $english;
    // prepare confirmation
    $stepManager->setStep($from_id, 'add-item-4-' . $english, $english);
    $db->update('items', 'english name', $english, ['first number' => (string)$initial]);
    // set default for all cities
    $cityItems = $db->get('cityItems') ?: [];
    foreach ($cityItems as $cityId => $vals) {
        $vals[$english] = $persian . '@' . $initial;
        $db->set('cityItems', $cityId, $vals);
    }
    SendMessage($chat_id, "📌 آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-item-4-') && $text !== "🔙") {
    $english = str_replace('add-item-4-', '', $stepManager->getStep($from_id));
    if ($text === "✅") {
        SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
    } elseif ($text === "❌") {
        // rollback
        $db->delete('items', 'english name', $english);
        $cityItems = $db->get('cityItems') ?: [];
        foreach ($cityItems as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityItems', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "عملیات لغو شد.", "HTML", $message_id, $adminBack);
    }
    $stepManager->resetStep($from_id);
}