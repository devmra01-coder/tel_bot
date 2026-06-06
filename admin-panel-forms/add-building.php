<?php

// Add building flow (simplified) using BuildingManager
if ($text === "[🏗️]- افزودن ساختمان" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "📌 نام انگلیسی ساختمان را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-1');
} elseif ($stepManager->isInStep($from_id, 'add-building-1') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $db->insert('buildings', ['english name' => $english, 'persian name' => '', 'upgrade items 1' => '', 'upgrade items 2' => '', 'upgrade items 3' => '', 'efficiency item' => '', 'efficiency number' => '', 'first level' => '', 'last level' => '']);
    $stepManager->setStep($from_id, 'add-building-2-' . $english, $english);
    SendMessage($chat_id, "📌 نام فارسی ساختمان را وارد کنید :", "HTML", $message_id, $adminBack);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-2-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-building-2-', '', $stepManager->getStep($from_id));
    $persian = Helpers::sanitize($text);
    $db->update('buildings', 'english name', $english, ['persian name' => $persian]);
    // ask for upgrade costs (first)
    $items = $itemManager->getAllItems();
    $list = array_map(function($i){ return $i['persian name'] ?? $i['english name']; }, $items);
    $strList = implode("=>\n", $list);
    SendMessage($chat_id, "📌هزینه ارتقا اول را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این ساختمان مورد نیاز است؟\n\n <code>$strList=></code>", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-3-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-3-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['upgrade items 1' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌هزینه ارتقا دوم را وارد کنید\n(اگر نیاز ندارد، ارسال '♨️ نیاز نیست')", "HTML", $message_id, $adminDoseNotNeedIt);
    $stepManager->setStep($from_id, 'add-building-4-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-4-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['upgrade items 2' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌هزینه ارتقا سوم را وارد کنید\n(اگر نیاز ندارد، ارسال '♨️ نیاز نیست')", "HTML", $message_id, $adminDoseNotNeedIt);
    $stepManager->setStep($from_id, 'add-building-5-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-5-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['upgrade items 3' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 این ساختمان چه آیتمی بازدهی می دهد؟", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-6-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-6-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['efficiency item' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 میزان بازدهی در هر لول را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-7-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-7-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['efficiency number' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 لول اولیه این ساختمان در دارایی را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-8-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-8-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['first level' => Helpers::sanitize($text)]);
    // set default in cityBuildings
    $cityBuildings = $db->get('cityBuildings') ?: [];
    foreach ($cityBuildings as $cityId => $vals) {
        $vals[$english] = ($db->findOne('buildings','english name',$english)['persian name'] ?? $english) . '@' . Helpers::sanitize($text);
        $db->set('cityBuildings', $cityId, $vals);
    }
    SendMessage($chat_id, "📌 لول نهایی این ساختمان در دارایی را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-building-9-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-9-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('buildings', 'english name', $english, ['last level' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
    $stepManager->setStep($from_id, 'add-building-10-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-building-10-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    if ($text === "✅") {
        SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
    } else {
        $db->delete('buildings', 'english name', $english);
        $cityBuildings = $db->get('cityBuildings') ?: [];
        foreach ($cityBuildings as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityBuildings', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "عملیات لغو شد.", "HTML", $message_id, $adminBack);
    }
    $stepManager->resetStep($from_id);
}