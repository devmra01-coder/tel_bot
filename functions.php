<?php

function stop($text)
{
    switch ($text) {
        case '/start':
            $a = "Yes";
            break;
        case '🏷 ○ برگشت به منوی اصلی':
            $a = "Yes";
            break;
        case 'پنل':
            $a = "Yes";
            break;
        case 'Open':
            $a = "Yes";
            break;
        case '🔙':
            $a = "Yes";
            break;
        case 'back':
            $a = "Yes";
            break;

        default:
            $a = "No";
            break;
    }
    return $a;
}

$stop = stop($text);
//----------
function sendDataForDb($table, $key, $value)
{
    global $conn;
    $conn->query("INSERT INTO `{$table}` (`$key`) VALUES ('$value')");
}
//----------
function UpdataDataForDb($table, $key, $value, $whereKey, $whereValue)
{
    global $conn;
    $conn->query("UPDATE ($table SET `{$key}`='{$value}' WHERE `{$whereKey}`='{$whereValue}' LIMIT 1");
}
//----------
function upgradeKeyboard($conn, $chat_id, $cityBuildingsTable, $buildingsTable, $cityCampsTable, $campsTable)
{
    $btns = [[]];
    $chat_id = intval($chat_id);

    // تابع کمکی برای افزودن امن دکمه
    $addBtn = function(&$btns, $text) {
        $lastRowIndex = count($btns) - 1;
        if (count($btns[$lastRowIndex]) >= 2) {
            $btns[] = []; // ایجاد ردیف جدید اگر ردیف فعلی پر است
            $lastRowIndex++;
        }
        $btns[$lastRowIndex][] = ['text' => $text, 'callback_data' => $text];
    };

    // پردازش ساختمان‌ها و کمپ‌ها (به صورت یکپارچه)
    $tables = [$cityBuildingsTable => $buildingsTable, $cityCampsTable => $campsTable];

    foreach ($tables as $cityTable => $refTable) {
        $result = mysqli_query($conn, "SELECT * FROM `$cityTable` WHERE `city id` = {$chat_id}");
        if (!$result) continue;

        while ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $colName => $data) {
                if ($colName === 'city id' || empty($data)) continue;

                $parts = explode('@', $data);
                if (count($parts) < 2) continue;

                $name = $parts[0];
                $currentLevel = intval($parts[1]);

                $nameSafe = mysqli_real_escape_string($conn, $name);
                $refQuery = mysqli_query($conn, "SELECT `last level` FROM `$refTable` WHERE `persian name` = '{$nameSafe}' LIMIT 1");
                $refData = mysqli_fetch_assoc($refQuery);

                if ($refData && $currentLevel < intval($refData['last level'])) {
                    $addBtn($btns, $name);
                }
            }
        }
    }

    // حذف آخرین ردیف اگر خالی مانده باشد
    if (empty(end($btns))) array_pop($btns);

    // افزودن دکمه برگشت
    $btns[] = [['text' => "🔙", 'callback_data' => "back"]];
    
    return $btns;
}


$upgradeKeyboard = upgradeKeyboard($conn, $chat_id, $cityBuildingsTable, $buildingsTable, $cityCampsTable, $campsTable);
//----------------tradingInlineButton------------------------
function tradingInlineButton($conn, $citiesTable, $chat_id)
{
    $user = mysqli_query($conn, "SELECT * FROM `{$citiesTable}` WHERE  `city id` !={$chat_id}");
    $btns = [[]];
    foreach ($user as $value) {
        $a = ['text' => $value["city name"], 'callback_data' => $value["city id"]];
        $bc = count($btns);
        for ($counter = 0; $counter < $bc; $counter++) {
            $btnLen = count($btns[$counter]);
            if ($btnLen < 2) {
                array_push($btns[$counter], $a);
            }
            if ($btnLen == 1) {
                array_push($btns, []);
            }
        }
    }
    array_push($btns, [['text' => "🔙", 'callback_data' => "back"]]);
    return $btns;
}
//---------- tradingKeyboard ----------
function tradingKeyboard($conn, $itemsTable, $peopleTable, $soldiersTable)
{
    $items = mysqli_query($conn, "SELECT * FROM `$itemsTable`");
    $btns = [[]];

    foreach ($items as $item) {
        $a = ['text' => $item["persian name"], 'callback_data' => $item["english name"]];
        $bc = count($btns);
        for ($counter = 0; $counter < $bc; $counter++) {
            $btnLen = count($btns[$counter]);
            if ($btnLen < 2) {
                array_push($btns[$counter], $a);
            }
            if ($btnLen == 1) {
                array_push($btns, []);
            }
        }
    }
    $People = mysqli_query($conn, "SELECT * FROM `$peopleTable`");
    foreach ($People as $person) {
        $a = ['text' => $person["persian name"], 'callback_data' => $person["english name"]];
        $bc = count($btns);
        for ($counter = 0; $counter < $bc; $counter++) {
            $btnLen = count($btns[$counter]);
            if ($btnLen < 2) {
                array_push($btns[$counter], $a);
            }
            if ($btnLen == 1) {
                array_push($btns, []);
            }
        }
    }

    $soldiers = mysqli_query($conn, "SELECT * FROM `$soldiersTable`");
    foreach ($soldiers as $soldier) {
        $a = ['text' => $soldier["persian name"], 'callback_data' => $soldier["english name"]];
        $bc = count($btns);
        for ($counter = 0; $counter < $bc; $counter++) {
            $btnLen = count($btns[$counter]);
            if ($btnLen < 2) {
                array_push($btns[$counter], $a);
            }
            if ($btnLen == 1) {
                array_push($btns, []);
            }
        }
    }

    array_push($btns, [['text' => "🔙", 'callback_data' => "back"]]);
    return $btns;
}
//----------------tradingFunction------------------------
function tradingFunction($conn, $cityItemsTable, $cityPeopleTable, $citySoldiersTable, $sender, $getter, $item_1, $item_2, $num_1, $num_2)
{
    $theTable_1 = "";
    $theTable_2 = "";
    $user_1 = [];
    $user_2 = [];
    //----------
    $theTable_3 = "";
    $user_3 = [];
    $theTable_4 = "";
    $user_4 = [];
    //-----Sender-----
    $senderUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityItemsTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    $senderUserPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityPeopleTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    $senderUserSoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citySoldiersTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    //-----Genter-----
    $getterUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityItemsTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    $getterUserPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityPeopleTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    $getterUserSoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citySoldiersTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    //----item_1-----
    $checkItem =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityItemsTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    $checkItem = $checkItem[$item_1];
    $checkPeople =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityPeopleTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    $checkPeople = $checkPeople[$item_1];
    $checkSoldiers =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citySoldiersTable}` WHERE `city id` = '{$sender}' LIMIT 1"));
    $checkSoldiers = $checkSoldiers[$item_1];
 
    //----item_2-----
    $checkItem_2 =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityItemsTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    $checkItem_2 = $checkItem_2[$item_2];
    $checkPeople_2 =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$cityPeopleTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    $checkPeople_2 = $checkPeople_2[$item_2];
    $checkSoldiers_2 =  mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$citySoldiersTable}` WHERE `city id` = '{$getter}' LIMIT 1"));
    $checkSoldiers_2 = $checkSoldiers_2[$item_2];
   
    if ($checkItem) {
        $theTable_1 = $cityItemsTable;
        $user_1 = $senderUser;
        $theTable_3 = $cityItemsTable;
        $user_3 = $getterUser;
    } else if ($checkPeople) {
        $theTable_1 = $cityPeopleTable;
        $user_1 = $senderUserPeople;
        $theTable_3 =  $cityPeopleTable;
        $user_3 = $getterUserPeople;
    } else if ($checkSoldiers) {
        $theTable_1 = $citySoldiersTable;
        $user_1 = $senderUserSoldiers;
        $theTable_3 =  $citySoldiersTable;
        $user_3 = $getterUserSoldiers;
    }
    //----------
    if ($checkItem_2) {
        $theTable_4 = $cityItemsTable;
        $user_4 = $getterUser;
        $theTable_2 = $cityItemsTable;
        $user_2 = $senderUser;
    } else if ($checkPeople_2) {
        $theTable_4 = $cityPeopleTable;
        $user_4 = $getterUserPeople;
        $theTable_2 = $cityPeopleTable;
        $user_2 = $senderUserPeople;
    } else if ($checkSoldiers_2) {
        $theTable_4 = $citySoldiersTable;
        $user_4 = $getterUserSoldiers;
        $theTable_2 = $citySoldiersTable;
        $user_2 = $senderUserSoldiers;
    }

    //تغییرات دارایی ارسال کننده
    $pItem = $user_2[$item_2];
    $pItem = explode("@", $pItem);
    $pItemNum = intval($pItem[1]) + intval($num_2);
    $persianName = $pItem[0];
    $save_1 = "$persianName@$pItemNum";
    $mItem = $user_1[$item_1];
    $mItem = explode("@", $mItem);
    $mItemNum = intval($mItem[1]) - intval($num_1);
    $persianName = $mItem[0];
    $save_2 = "$persianName@$mItemNum";
    $conn->query("UPDATE `{$theTable_1}` SET `{$item_1}`='{$save_2}' WHERE `city id`='{$sender}' LIMIT 1");
    $conn->query("UPDATE `{$theTable_2}` SET `{$item_2}`='{$save_1}' WHERE `city id`='{$sender}' LIMIT 1");
    //----------
    //تغییرات دارایی دریافت کننده
    $pItem = $user_4[$item_2];
    $pItem = explode("@", $pItem);
    $pItemNum = intval($pItem[1]) - intval($num_2);
    $persianName = $pItem[0];
    $save_1 = "$persianName@$pItemNum";
    $mItem = $user_3[$item_1];
    $mItem = explode("@", $mItem);
    $mItemNum = intval($mItem[1]) + intval($num_1);
    $persianName = $mItem[0];
    $save_2 = "$persianName@$mItemNum";
    $conn->query("UPDATE `{$theTable_3}` SET `{$item_1}`='{$save_2}' WHERE `city id`='{$getter}' LIMIT 1");
    $conn->query("UPDATE `{$theTable_4}` SET `{$item_2}`='{$save_1}' WHERE `city id`='{$getter}' LIMIT 1");
}
// ----------- itemsPersianNames ----------
function itemsPersianNames($conn, $itemsTable, $peopleTable, $soldiersTable, $endglishName)
{
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$itemsTable}` WHERE `english name` = '{$endglishName}' LIMIT 1"));
    $person = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$peopleTable}` WHERE `english name` = '{$endglishName}' LIMIT 1"));
    $soldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `{$soldiersTable}` WHERE `english name` = '{$endglishName}' LIMIT 1"));
    if ($item) {
        return "«" . $item["persian name"] . "»";
    } else if ($person) {
        return "«" . $person["persian name"] . "»";
    } else if ($soldier) {
        return "«" . $soldier["persian name"] . "»";
    }
}




function getShopItem($conn, $itemName) {
    $q = mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `item_name` = '$itemName' AND `active`=1 LIMIT 1");
    return mysqli_fetch_assoc($q);
}

function getShopBuyButtons($conn, $city_id) {
    $q = mysqli_query($conn, "SELECT * FROM `shop_items` WHERE `active`=1 ORDER BY category");
    $buttons = [];
    while ($item = mysqli_fetch_assoc($q)) {
        $status = checkShopItemStatus($conn, $city_id, $item);
        $emoji = $status['can_buy'] ? '🟢' : '❌';
        $buttons[] = [['text' => $emoji . " " . $item['persian_name'], 'callback_data' => 'buy_' . $item['item_name']]];
    }
    $buttons[] = [['text' => '🔙 بازگشت', 'callback_data' => 'shoping']];
    return $buttons;
}

function checkShopItemStatus($conn, $city_id, $item, $requestedQty = 1) {
    $status = ['can_buy' => true, 'message' => ''];

    if ($item['one_time'] == 1 && getOneTimePurchaseStatus($conn, $city_id, $item['item_name'])) {
        $status['can_buy'] = false;
        $status['message'] = "این آیتم فقط یک بار قابل خرید است.";
        return $status;
    }

    if ($item['is_limited'] && $item['max_limit'] > 0) {
        $owned = getCityItemTotal($conn, $city_id, $item['item_name']);
        if ($owned + $requestedQty > $item['max_limit']) {
            $status['can_buy'] = false;
            $status['message'] = "حداکثر {$item['max_limit']} واحد قابل خرید است.";
            return $status;
        }
    }

    if ($item['daily_limit'] > 0) {
        $daily = getDailyBought($conn, $city_id, $item['item_name']);
        if ($daily + $requestedQty > $item['daily_limit']) {
            $status['can_buy'] = false;
            $status['message'] = "محدودیت روزانه این آیتم تمام شده است.";
            return $status;
        }
    }

    if (!empty($item['requirements'])) {
        $reqs = json_decode($item['requirements'], true);
        foreach ($reqs as $reqItem => $reqQty) {
            if (getCityItemTotal($conn, $city_id, $reqItem) < $reqQty) {
                $status['can_buy'] = false;
                $status['message'] = "پیش‌نیازها کامل نیست.";
                return $status;
            }
        }
    }

    return $status;
}

function getOneTimePurchaseStatus($conn, $city_id, $item_name) {
    $q = mysqli_query($conn, "SELECT 1 FROM `shop_one_time_log` WHERE `city_id`='{$city_id}' AND `item_name`='{$item_name}' LIMIT 1");
    return mysqli_num_rows($q) > 0;
}

function getDailyBought($conn, $city_id, $item_name) {
    $today = date('Y-m-d');
    $q = mysqli_query($conn, "SELECT SUM(quantity) as total FROM `shop_daily_log` 
                              WHERE `city_id`='{$city_id}' AND `item_name`='{$item_name}' AND `date`='{$today}'");
    $row = mysqli_fetch_assoc($q);
    return (int)($row['total'] ?? 0);
}

function getCityItemTotal($conn, $city_id, $item_name) {
    global $cityItemsTable, $cityPeopleTable, $citySoldiersTable, $cityBuildingsTable, $cityCampsTable;
    $tables = [$cityItemsTable, $cityPeopleTable, $citySoldiersTable, $cityBuildingsTable, $cityCampsTable];
    
    foreach ($tables as $table) {
        $col = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` LIKE '{$item_name}'");
        if (mysqli_num_rows($col) > 0) {
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `{$item_name}` FROM `{$table}` WHERE `city id`='{$city_id}' LIMIT 1"));
            $parts = explode("@", $row[$item_name] ?? "0@0");
            return (int)($parts[1] ?? 0);
        }
    }
    return 0;
}

function calculateTotalCost($item, $quantity) {
    $costs = json_decode($item['costs'], true) ?? [];
    $total = ['gold' => ($item['price_gold'] ?? 0) * $quantity];
    foreach ($costs as $res => $amt) {
        $total[$res] = ($total[$res] ?? 0) + $amt * $quantity;
    }
    return $total;
}

function formatCosts($costs) {
    $str = "";
    if (!empty($costs['gold'])) $str .= "💰 {$costs['gold']} سکه\n";
    foreach ($costs as $res => $amt) {
        if ($res !== 'gold' && $amt > 0) $str .= "• {$res}: {$amt}\n";
    }
    return $str ?: "بدون هزینه اضافی";
}

function executePurchase($conn, $city_id, $item, $quantity, $cityItemsTable, $cityBuildingsTable, $cityPeopleTable, $citySoldiersTable, $cityCampsTable) {
    $status = checkShopItemStatus($conn, $city_id, $item, $quantity);
    if (!$status['can_buy']) {
        return ['success' => false, 'message' => $status['message']];
    }

    $totalCost = calculateTotalCost($item, $quantity);

    if (!deductAllCosts($conn, $city_id, $totalCost, $cityItemsTable, $cityPeopleTable, $citySoldiersTable)) {
        return ['success' => false, 'message' => 'منابع کافی برای پرداخت هزینه‌ها وجود ندارد.'];
    }

    addItemToCity($conn, $city_id, $item['item_name'], $quantity, $cityItemsTable, $cityBuildingsTable, $cityPeopleTable, $citySoldiersTable, $cityCampsTable);

    if ($item['daily_limit'] > 0) {
        logDailyPurchase($conn, $city_id, $item['item_name'], $quantity);
    }
    if ($item['one_time'] == 1) {
        logOneTimePurchase($conn, $city_id, $item['item_name']);
    }

    return ['success' => true];
}

// ===============================================
//     توابع کمکی خرید - کامل و هماهنگ با کد شما
// ===============================================

function deductAllCosts($conn, $city_id, $costs, $cityItemsTable, $cityPeopleTable, $citySoldiersTable)
{
    // $costs مثلاً: ['gold' => 500, 'wood' => 200, 'stone' => 150, ...]

    foreach ($costs as $itemName => $amount) {
        if ($amount <= 0) continue;

        $table = "";
        $currentData = "";

        // پیدا کردن جدول مربوطه
        $checkItem = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$itemName` FROM `$cityItemsTable` WHERE `city id` = '{$city_id}' LIMIT 1"));
        $checkPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$itemName` FROM `$cityPeopleTable` WHERE `city id` = '{$city_id}' LIMIT 1"));
        $checkSoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$itemName` FROM `$citySoldiersTable` WHERE `city id` = '{$city_id}' LIMIT 1"));

        if (!empty($checkItem[$itemName])) {
            $table = $cityItemsTable;
            $currentData = $checkItem[$itemName];
        } elseif (!empty($checkPeople[$itemName])) {
            $table = $cityPeopleTable;
            $currentData = $checkPeople[$itemName];
        } elseif (!empty($checkSoldiers[$itemName])) {
            $table = $citySoldiersTable;
            $currentData = $checkSoldiers[$itemName];
        } else {
            return false; // منبع پیدا نشد
        }

        // پردازش مقدار فعلی (فرمت: name@quantity)
        $parts = explode("@", $currentData);
        $persian = $parts[0] ?? $itemName;
        $currentQty = (int)($parts[1] ?? 0);

        if ($currentQty < $amount) {
            return false; // موجودی کافی نیست
        }

        $newQty = $currentQty - $amount;
        $newValue = "{$persian}@{$newQty}";

        $conn->query("UPDATE `{$table}` SET `{$itemName}` = '{$newValue}' WHERE `city id` = '{$city_id}' LIMIT 1");
    }

    return true;
}

// ===============================================

function addItemToCity($conn, $city_id, $item_name, $quantity, $cityItemsTable, $cityBuildingsTable, $cityPeopleTable, $citySoldiersTable, $cityCampsTable)
{
    $tables = [$cityItemsTable, $cityPeopleTable, $citySoldiersTable, $cityBuildingsTable, $cityCampsTable];
    $targetTable = "";
    $currentData = "";

    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` LIKE '{$item_name}'");
        if (mysqli_num_rows($result) > 0) {
            $targetTable = $table;
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `{$item_name}` FROM `{$table}` WHERE `city id` = '{$city_id}' LIMIT 1"));
            $currentData = $row[$item_name] ?? "";
            break;
        }
    }

    if (empty($targetTable)) {
        return false; // جدول پیدا نشد
    }

    if (empty($currentData)) {
        // اولین بار اضافه می‌شود
        $persianName = getPersianName($conn, $item_name);
        $newValue = "{$persianName}@{$quantity}";
    } else {
        // افزایش مقدار موجود
        $parts = explode("@", $currentData);
        $persian = $parts[0] ?? $item_name;
        $currentQty = (int)($parts[1] ?? 0);
        $newQty = $currentQty + $quantity;
        $newValue = "{$persian}@{$newQty}";
    }

    $conn->query("UPDATE `{$targetTable}` SET `{$item_name}` = '{$newValue}' WHERE `city id` = '{$city_id}' LIMIT 1");
    return true;
}

// تابع کمکی برای گرفتن نام فارسی (در صورت نیاز)
function getPersianName($conn, $englishName)
{
    $tables = ['itemsTable', 'peopleTable', 'soldiersTable', 'buildingsTable', 'campsTable'];
    foreach ($tables as $tableVar) {
        global $$tableVar;
        $table = $$tableVar;
        $q = mysqli_query($conn, "SELECT `persian name` FROM `{$table}` WHERE `english name` = '{$englishName}' LIMIT 1");
        if ($row = mysqli_fetch_assoc($q)) {
            return $row['persian name'];
        }
    }
    return $englishName; // fallback
}

function logDailyPurchase($conn, $city_id, $item_name, $quantity) {
    $today = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO `shop_daily_log` (`city_id`, `item_name`, `date`, `quantity`) 
                         VALUES ('$city_id', '$item_name', '$today', $quantity) 
                         ON DUPLICATE KEY UPDATE `quantity` = `quantity` + $quantity");
}

function logOneTimePurchase($conn, $city_id, $item_name) {
    mysqli_query($conn, "INSERT INTO `shop_one_time_log` (`city_id`, `item_name`, `purchase_date`) 
                         VALUES ('$city_id', '$item_name', NOW())");
}


function getUpgradeButtons($conn) {
    $q = mysqli_query($conn, "SELECT * FROM `upgrade_list` WHERE `active`=1");
    $buttons = [];
    while ($item = mysqli_fetch_assoc($q)) {
        $buttons[] = [['text' => "⚒ " . $item['persian_name'], 'callback_data' => 'upgrade_' . $item['item_name']]];
    }
    $buttons[] = [['text' => '🔙 بازگشت', 'callback_data' => 'shoping']];
    return $buttons;
}

function getUpgradeItem($conn, $itemName) {
    $q = mysqli_query($conn, "SELECT * FROM `upgrade_list` WHERE `item_name` = '{$itemName}' LIMIT 1");
    return mysqli_fetch_assoc($q);
}

function getCurrentLevel($conn, $city_id, $itemName) {
    global $cityBuildingsTable, $cityCampsTable;
    $tables = [$cityBuildingsTable, $cityCampsTable];
    foreach ($tables as $table) {
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `$itemName` FROM `$table` WHERE `city id`='{$city_id}' LIMIT 1"));
        if (!empty($row[$itemName])) {
            $parts = explode("@", $row[$itemName]);
            return (int)($parts[1] ?? 1);
        }
    }
    return 1;
}

function checkUpgradeStatus($conn, $city_id, $item) {
    $status = ['can_upgrade' => true, 'message' => ''];
    $current = getCurrentLevel($conn, $city_id, $item['item_name']);

    if ($item['max_limit'] > 0 && $current >= $item['max_limit']) {
        $status['can_upgrade'] = false;
        $status['message'] = "به حداکثر سطح رسیده است.";
        return $status;
    }

    if ($item['one_time'] == 1 && $current >= 1) {
        $status['can_upgrade'] = false;
        $status['message'] = "این مورد فقط یک بار قابل ارتقا است.";
        return $status;
    }

    if ($item['daily_limit'] > 0) {
        if (getDailyUpgrades($conn, $city_id, $item['item_name']) >= $item['daily_limit']) {
            $status['can_upgrade'] = false;
            $status['message'] = "محدودیت روزانه ارتقا تمام شده است.";
            return $status;
        }
    }

    return $status;
}

function getUpgradeCosts($conn, $item, $nextLevel) {
    $costsJson = json_decode($item['upgrade_costs'], true);
    return $costsJson[$nextLevel] ?? $costsJson[array_key_last($costsJson)] ?? ['gold' => 1000];
}

function executeUpgrade($conn, $city_id, $item, $nextLevel) {
    $costs = getUpgradeCosts($conn, $item, $nextLevel);

    if (!deductAllCosts($conn, $city_id, $costs, $cityItemsTable, $cityPeopleTable, $citySoldiersTable)) {
        return ['success' => false, 'message' => 'منابع کافی نیست.'];
    }

    // ثبت ارتقا
    global $cityBuildingsTable, $cityCampsTable;
    $tables = [$cityBuildingsTable, $cityCampsTable];
    foreach ($tables as $table) {
        if (mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '{$item['item_name']}'")) > 0) {
            $newValue = "{$item['persian_name']}@{$nextLevel}";
            mysqli_query($conn, "UPDATE `$table` SET `{$item['item_name']}` = '{$newValue}' WHERE `city id`='{$city_id}' LIMIT 1");
            break;
        }
    }

    if ($item['daily_limit'] > 0) logUpgrade($conn, $city_id, $item['item_name']);

    return ['success' => true];
}

function getDailyUpgrades($conn, $city_id, $item_name) {
    $today = date('Y-m-d');
    $q = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM `upgrade_daily_log` WHERE `city_id`='{$city_id}' AND `item_name`='{$item_name}' AND `date`='{$today}'");
    $row = mysqli_fetch_assoc($q);
    return (int)$row['cnt'];
}

function logUpgrade($conn, $city_id, $item_name) {
    $today = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO `upgrade_daily_log` (`city_id`,`item_name`,`date`) VALUES ('{$city_id}','{$item_name}','{$today}') 
                         ON DUPLICATE KEY UPDATE `date`=`date`");
}












function getAllGameItems($conn) {
    $items = [];
    $tables = ['itemsTable', 'peopleTable', 'soldiersTable'];
    
    foreach ($tables as $tableVar) {
        global $$tableVar;
        $table = $$tableVar;
        $q = mysqli_query($conn, "SELECT `english name` as english, `persian name` as persian FROM `$table`");
        while ($row = mysqli_fetch_assoc($q)) {
            $items[] = $row;
        }
    }
    return $items;
}

function showCostSelectionKeyboard($conn, $chat_id, $targetItem) {
    $allItems = getAllGameItems($conn);
    $keyboard = ['inline_keyboard' => []];

    foreach ($allItems as $item) {
        $keyboard['inline_keyboard'][] = [[
            'text' => "💰 " . $item['persian'],
            'callback_data' => "cost_" . $targetItem . "_" . $item['english']
        ]];
    }
    $keyboard['inline_keyboard'][] = [['text' => "✅ تمام شد", 'callback_data' => "cost_done_" . $targetItem]];

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "انتخاب کنید کدام آیتم هزینه خرید باشد:",
        'reply_markup' => json_encode($keyboard)
    ]);
}
function showUpgradeCostSelection($conn, $chat_id, $targetItem, $level) {
    $allItems = getAllGameItems($conn);
    $keyboard = ['inline_keyboard' => []];
    foreach ($allItems as $item) {
        $keyboard['inline_keyboard'][] = [[
            'text' => "💰 " . $item['persian'] . " (سطح $level)",
            'callback_data' => "upgcost_{$level}_" . $item['english']
        ]];
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "هزینه‌های سطح $level را انتخاب کنید:",
        'reply_markup' => json_encode($keyboard)
    ]);
}
