<?php

// Add camp flow (simplified)
if ($text === "[⛺️]- افزودن کمپ" && $stepManager->getStep($from_id) === 'none') {
    SendMessage($chat_id, "📌 نام انگلیسی کمپ را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-1');
} elseif ($stepManager->isInStep($from_id, 'add-camp-1') && $text !== "🔙") {
    $english = Helpers::sanitize($text);
    $db->insert('camps', ['english name' => $english, 'persian name' => '', 'upgrade items 1' => '', 'upgrade items 2' => '', 'upgrade items 3' => '', 'efficiency soldier' => '', 'efficiency number' => '', 'first level' => '', 'last level' => '']);
    $stepManager->setStep($from_id, 'add-camp-2-' . $english, $english);
    SendMessage($chat_id, "📌 نام فارسی کمپ را وارد کنید :", "HTML", $message_id, $adminBack);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-2-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id) ?: str_replace('add-camp-2-', '', $stepManager->getStep($from_id));
    $persian = Helpers::sanitize($text);
    $db->update('camps', 'english name', $english, ['persian name' => $persian]);
    SendMessage($chat_id, "📌هزینه ارتقا اول را وارد کنید\n\n چه میزان از هر آیتم برای ارتقا این کمپ مورد نیاز است؟\n\n", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-3-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-3-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['upgrade items 1' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌هزینه ارتقا دوم را وارد کنید\n(اگر نیاز ندارد، ارسال '♨️ نیاز نیست')", "HTML", $message_id, $adminDoseNotNeedIt);
    $stepManager->setStep($from_id, 'add-camp-4-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-4-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['upgrade items 2' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌هزینه ارتقا سوم را وارد کنید\n(اگر نیاز ندارد، ارسال '♨️ نیاز نیست')", "HTML", $message_id, $adminDoseNotNeedIt);
    $stepManager->setStep($from_id, 'add-camp-5-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-5-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['upgrade items 3' => Helpers::sanitize($text)]);
    // ask which soldier it produces
    $soldiers = $soldierManager->getAllSoldiers();
    $list = array_map(function($s){ return $s['english name']; }, $soldiers);
    $str = implode("\n", $list);
    SendMessage($chat_id, "📌 این کمپ چه سربازی بازدهی می دهد؟\n\n $str", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-6-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-6-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['efficiency soldier' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 میزان بازدهی در هر لول را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-7-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-7-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['efficiency number' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 لول اولیه این کمپ در دارایی را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-8-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-8-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['first level' => Helpers::sanitize($text)]);
    $cityCamps = $db->get('cityCamps') ?: [];
    foreach ($cityCamps as $cityId => $vals) {
        $vals[$english] = ($db->findOne('camps','english name',$english)['persian name'] ?? $english) . '@' . Helpers::sanitize($text);
        $db->set('cityCamps', $cityId, $vals);
    }
    SendMessage($chat_id, "📌 لول نهایی این کمپ در دارایی را وارد کنید :", "HTML", $message_id, $adminBack);
    $stepManager->setStep($from_id, 'add-camp-9-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-9-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    $db->update('camps', 'english name', $english, ['last level' => Helpers::sanitize($text)]);
    SendMessage($chat_id, "📌 آیا تایید می کنید؟", "HTML", $message_id, $adminYesOrNo);
    $stepManager->setStep($from_id, 'add-camp-10-' . $english, $english);
} elseif ($stepManager->isInStepWithPrefix($from_id, 'add-camp-10-') && $text !== "🔙") {
    $english = $stepManager->getStepData($from_id);
    if ($text === "✅") {
        SendMessage($chat_id, "Done!", "HTML", $message_id, $adminBack);
    } else {
        $db->delete('camps', 'english name', $english);
        $cityCamps = $db->get('cityCamps') ?: [];
        foreach ($cityCamps as $cityId => $vals) {
            if (isset($vals[$english])) {
                unset($vals[$english]);
                $db->set('cityCamps', $cityId, $vals);
            }
        }
        SendMessage($chat_id, "عملیات لغو شد.", "HTML", $message_id, $adminBack);
    }
    $stepManager->resetStep($from_id);
}
