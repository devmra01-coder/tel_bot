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
    $cityBuildings = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityBuildingsTable` WHERE `city id` = {$chat_id}");
    $btns = [[]];

    foreach ($cityBuildings as $buildings) {
        foreach ($buildings as $building) {
            $building = explode('@', $building);
            $a = $building[0];
            $theBuilding = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$a}' LIMIT 1"));
            if ($building[1] < $theBuilding["last level"]) {
                $a = ['text' => $building[0], 'callback_data' => $building[0]];
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
        }
    }
    $cityCamps = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityCampsTable` WHERE `city id` = {$chat_id}");
    foreach ($cityCamps as $camps) {
        foreach ($camps as $camp) {
            $camp = explode('@', $camp);
            $a = $camp[0];
            $theCamp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `persian name` = '{$a}' LIMIT 1"));
            if ($camp[1] < $theCamp["last level"]) {
                $a = ['text' => $camp[0], 'callback_data' => $camp[0]];
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
        }
    }
    // sort($btns);
    array_push($btns, [['text' => "🔙", 'callback_data' => "back"]]);
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
