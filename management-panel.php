<?php

if (in_array($from_id, $admins)) {
    if ($text == "مدیریت" || $text == "🔙") {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👤|به پنل مدیریت خوش آمدید",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $managementPanel,
        ]);
    }
    //---------------------------------------------------------

    if ($text == "Set gap") {
        if (!$city) {
            SendMessage($chat_id, "Done!", "HTML", $message_id);

            $conn->query("INSERT INTO $citiesTable (`city id`,`step`) VALUES ($chat_id,'none')");
            sendDataForDb($cityBuildingsTable, "city id", $chat_id);
            sendDataForDb($cityItemsTable, "city id", $chat_id);
            sendDataForDb($citySoldiersTable, "city id", $chat_id);
            sendDataForDb($cityPeopleTable, "city id", $chat_id);
            sendDataForDb($cityCampsTable, "city id", $chat_id);
            $adminGapItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $cityItemsTable WHERE `city id` = '{$adminsGap}' LIMIT 1"));
            $adminGapBuildings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $cityBuildingsTable WHERE `city id` = '{$adminsGap}' LIMIT 1"));
            $adminGapSoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $citySoldiersTable WHERE `city id` = '{$adminsGap}' LIMIT 1"));
            $adminGapPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $cityPeopleTable WHERE `city id` = '{$adminsGap}' LIMIT 1"));
            $adminGapCamps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $cityCampsTable WHERE `city id` = '{$adminsGap}' LIMIT 1"));
            
            foreach ($adminGapItems as $key => $value) {
                if ($key != "city id") {
                    $conn->query("UPDATE `$cityItemsTable` SET `{$key}`='{$value}' WHERE `city id`='{$chat_id}'LIMIT 1");
                }
            }
            foreach ($adminGapBuildings as $key => $value) {
                if ($key != "city id") {
                    $conn->query("UPDATE `$cityBuildingsTable` SET `{$key}`='{$value}' WHERE `city id`='{$chat_id}'LIMIT 1");
                }
            }
            foreach ($adminGapSoldiers as $key => $value) {
                if ($key != "city id") {
                    $conn->query("UPDATE `$citySoldiersTable` SET `{$key}`='{$value}' WHERE `city id`='{$chat_id}'LIMIT 1");
                }
            }
            foreach ($adminGapCamps as $key => $value) {
                if ($key != "city id") {
                    $conn->query("UPDATE `$cityCampsTable` SET `{$key}`='{$value}' WHERE `city id`='{$chat_id}'LIMIT 1");
                }
            }
            foreach ($adminGapPeople as $key => $value) {
                if ($key != "city id") {
                    $conn->query("UPDATE `$cityPeopleTable` SET `{$key}`='{$value}' WHERE `city id`='{$chat_id}'LIMIT 1");
                }
            }
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "آیا من شبیه دلقک پدرتم؟",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
            ]);
        }
    }
    //---------------------------------------------------------
    if ($reply && $text == "Set lord") {
        if (!$city) {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "سید اول باید گپو به عنوان شهر ست کنی",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
            ]);
        } else {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "The Lord is set.",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
            ]);
            $conn->query("UPDATE `$citiesTable` SET `player id`='{$reply_From_id}' WHERE `city id`='{$chat_id}'LIMIT 1");
        }
    }
    //---------------------------------------------------------
    if ($data == "Set city name") {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "نام شهر را وارد کنید",
        ]);
        $conn->query("UPDATE `$citiesTable` SET `step`='set-city-name-1' WHERE `city id`='{$chat_id}' LIMIT 1");
    } else if ($playerStep == "set-city-name-1" && $stop == "No") {
        SendMessage($chat_id, "Done!", "HTML", $message_id);
        $conn->query("UPDATE `$citiesTable` SET `step`='none', `city name`='{$text}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }
    //---------------------------------------------------------
    if ($data == "Set lord name") {
        bot('EditMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => "نام فرمانده را وارد کنید",
        ]);
        $conn->query("UPDATE `$citiesTable` SET `step`='set-lord-name-1' WHERE `city id`='{$chat_id}'LIMIT 1");
    } else if ($playerStep == "set-lord-name-1" && $stop == "No") {
        SendMessage($chat_id, "Done!", "HTML", $message_id);
        $conn->query("UPDATE `$citiesTable` SET `step`='none', `lord name`='{$text}' WHERE `city id`='{$chat_id}' LIMIT 1");
    }
    //---------------------------------------------------------
    if ($text == "Open") {
        SendMessage($chat_id, "Done!", "HTML", $message_id);
        $conn->query("UPDATE `$citiesTable` SET `step`='none' WHERE `city id`='{$chat_id}'LIMIT 1");
    }
    //---------------------------------------------------------
    if ($text == "Delete gap") {
        SendMessage($chat_id, "Done!", "HTML", $message_id);
        $conn->query("DELETE FROM `$citiesTable` WHERE `city id` = '{$chat_id}'");
    }
    //---------------------------------------------------------
    $editSelector = [];
    while ($row = mysqli_fetch_assoc($itemsListEn)) {
        array_push($editSelector, $row["english name"]);
    }
    while ($row = mysqli_fetch_assoc($buildingsList)) {
        array_push($editSelector, $row["english name"]);
    }
    while ($row = mysqli_fetch_assoc($soldiersList)) {
        array_push($editSelector, $row["english name"]);
    }
    while ($row = mysqli_fetch_assoc($campsList)) {
        array_push($editSelector, $row["english name"]);
    }
    while ($row = mysqli_fetch_assoc($peopleListEn)) {
        array_push($editSelector, $row["english name"]);
    }

    if (strpos($text, "set ") !== false) {
        $idm = str_replace("set ", '', $text);
        $selector = preg_replace('/\d+/u', '', $idm);
        $selector = trim($selector);
        $number = preg_match_all('/\d+/i', $idm, $number_2);
        $number_2 = $number_2[0][0];
        //----------
        if (in_array($selector, $editSelector)) {
            $itemSelected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `english name` = '{$selector}' LIMIT 1"));
            $buildingSelected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `english name` = '{$selector}' LIMIT 1"));
            $soldierSelected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$selector}' LIMIT 1"));
            $campSelected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `english name` = '{$selector}' LIMIT 1"));
            $peopleSelected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$selector}' LIMIT 1"));

            if ($itemSelected) {
                $persianName = $itemSelected["persian name"];
                $save = "$persianName@$number_2";
                $conn->query("UPDATE `$cityItemsTable` SET `{$selector}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            if ($buildingSelected) {
                $persianName = $buildingSelected["persian name"];
                $save = "$persianName@$number_2";
                $conn->query("UPDATE `$cityBuildingsTable` SET `{$selector}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            if ($soldierSelected) {
                $persianName = $soldierSelected["persian name"];
                $save = "$persianName@$number_2";
                $conn->query("UPDATE `$citySoldiersTable` SET `{$selector}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            if ($campSelected) {
                $persianName = $campSelected["persian name"];
                $save = "$persianName@$number_2";
                $conn->query("UPDATE `$cityCampsTable` SET `{$selector}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            if ($peopleSelected) {
                $persianName = $peopleSelected["persian name"];
                $save = "$persianName@$number_2";
                $conn->query("UPDATE `$cityPeopleTable` SET `{$selector}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
            }
            SendMessage($chat_id, "Done!", "HTML", $message_id);
        }
    }
    //------------------------Update---------------------------------
    if ($text == "/up") {
        $theBuildingsTable = mysqli_query($conn, "SELECT `english name` FROM `$buildingsTable`");
        $thecampsTable = mysqli_query($conn, "SELECT `english name` FROM `$campsTable`");
        $thePeopleTable = mysqli_query($conn, "SELECT * FROM `$peopleTable`");
        $theSoldiersTable = mysqli_query($conn, "SELECT * FROM `$soldiersTable`");
        $citySoldiers = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
        $cityPeople = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
        //----------
        $result = [];
        $Fate = [false];
        $died = 0;
        $mySoldiersNum = 1;
        $myPeopleNum = 1;

        foreach ($citySoldiers as $soldiers) {
            foreach ($soldiers as $soldier) {
                $soldier = explode('@', $soldier);
                $persianName = $soldier[0];
                $num = 0;
                $theSoldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `persian name` = '{$persianName}' LIMIT 1"));
                $soldierConsumableItems = $theSoldier["consumable item"];
                $consumableItems = explode("\n", $soldierConsumableItems);
                foreach ($consumableItems as $consumableItem) {
                    $value = explode("=>", $consumableItem);
                    $itemPersianName = trim($value[0]);
                    $number = trim($value[1]);
                    if ($number > 0) {
                        $num += 0;
                    }
                }
                if ($num > 0) {
                    if ($soldier[1] > 0) {
                        $mySoldiersNum += 1;
                    }
                }
            }
        }
        foreach ($cityPeople as $People) {
            foreach ($People  as $persno) {
                $persno = explode('@', $persno);
                $persianName = $persno[0];
                $num = 0;
                $thePersno = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `persian name` = '{$persianName}' LIMIT 1"));
                $personConsumableItems = $thePersno["consumable item"];
                $consumableItems = explode("\n", $personConsumableItems);
                foreach ($consumableItems as $consumableItem) {
                    $value = explode("=>", $consumableItem);
                    $itemPersianName = trim($value[0]);
                    $number = trim($value[1]);
                    if ($number > 0) {
                        $num += 0;
                    }
                }
                if ($num > 0) {
                    if ($soldier[1] > 0) {
                        $myPeopleNum += 1;
                    }
                }
            }
        }


        foreach ($theBuildingsTable as $buildingsEnglishName) {
            foreach ($buildingsEnglishName as $buildingEnglishName) {
                $buildings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `english name` = '{$buildingEnglishName}' LIMIT 1"));
                $buildingEfficiencyItem = $buildings["efficiency item"];
                $buildingEfficiencyNumber = $buildings["efficiency number"];
                $cityBuildings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityBuildingsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $building = $cityBuildings["$buildingEnglishName"];
                $buildingArray = explode("@", $building);
                $buildingEfficiency = $buildingEfficiencyNumber * $buildingArray[1];
                $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $item = $cityItems["$buildingEfficiencyItem"];
                $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $person = $cityPeople[$buildingEfficiencyItem];
                if ($item) {
                    $itemArray = explode("@", $item);
                    $itemPersianName = $itemArray[0];
                    $itemNewValue = $itemArray[1] + $buildingEfficiency;
                    $save = "$itemPersianName@$itemNewValue";
                    $conn->query("UPDATE `$cityItemsTable` SET `{$buildingEfficiencyItem}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
                if ($person) {
                    $personArray = explode("@", $person);
                    $personPersianName = $personArray[0];
                    $personNewValue = $personArray[1] + $buildingEfficiency;
                    $save = "$personPersianName@$personNewValue";
                    $conn->query("UPDATE `$cityPeopleTable` SET `{$buildingEfficiencyItem}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
            }
        }
        //----------
        foreach ($thecampsTable as $campsEnglishName) {
            foreach ($campsEnglishName as $campEnglishName) {
                $camps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$campsTable` WHERE `english name` = '{$campEnglishName}' LIMIT 1"));
                $campEfficiencySoldier = $camps["efficiency soldier"];
                $campEfficiencyNumber = $camps["efficiency number"];
                $cityCamps = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityCampsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $camp = $cityCamps["$campEnglishName"];
                $campArray = explode("@", $camp);
                $campEfficiency = (int)$campEfficiencyNumber * (int)$campArray[1];
                $citySoldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $soldier = $citySoldier["$campEfficiencySoldier"];
                $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                $person = $cityPeople[$buildingEfficiencyItem];
                if ($soldier) {
                    $soldierArray = explode("@", $soldier);
                    $soldierPersianName = $soldierArray[0];
                    $soldierNewValue = $soldierArray[1] + $campEfficiency;
                    $save = "$soldierPersianName@$soldierNewValue";
                    $conn->query("UPDATE `$citySoldiersTable` SET `{$campEfficiencySoldier}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
                if ($person) {
                    $personArray = explode("@", $person);
                    $personPersianName = $personArray[0];
                    $personNewValue = $personArray[1] + $buildingEfficiency;
                    $save = "$personPersianName@$personNewValue";
                    $conn->query("UPDATE `$cityPeopleTable` SET `{$buildingEfficiencyItem}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
            }
        }
        //----------
        foreach ($thePeopleTable as $person) {
            $consumableItems = explode("\n", $person["consumable item"]);
            $soldierEnglishName = $person["english name"];
            foreach ($consumableItems as $consumableItem) {
                $value = explode("=>", $consumableItem);
                $itemPersianName = trim($value[0]);
                $number = trim($value[1]);
                if ($number > 0) {
                    $theItemsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
                    $itemEnglishName = $theItemsTable["english name"];
                    //---
                    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $item = $cityItems[$itemEnglishName];
                    //---
                    $cityPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $personNum = $cityPeople[$soldierEnglishName];
                    if ($item) {
                        $personArray = explode("@", $personNum);
                        if ($personArray[1] > 0) {
                            $itemArray = explode("@", $item);
                            $itemPersianName = $itemArray[0];
                            $number = $number *  $personArray[1];
                            if ($number <= $itemArray[1]) {
                                $newNumber = $itemArray[1] - $number;
                                $newNumber = ($newNumber < 0) ? 0 : $newNumber;
                                $save = "$itemPersianName@$newNumber";
                                $conn->query("UPDATE `$cityItemsTable` SET `{$itemEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                                array_push($Fate, "good");
                            } else {
                                $newNumber = $itemArray[1] - $number;
                                $saveNum = ($newNumber < 0) ? 0 : $newNumber;
                                $save = "$itemPersianName@$saveNum";
                                $conn->query("UPDATE `$cityItemsTable` SET `{$itemEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                                $newNumber = (trim($value[1]) > 1) ? $newNumber / 2 : $newNumber;
                                $string = "$itemEnglishName@$newNumber";
                                // SendMessage($chat_id, "shit string : $string", "HTML", $message_id);
                                array_push($result, $string);
                                array_push($Fate, "shit");
                            }
                        }
                    }
                }
            }
        }
        if (in_array("shit", $Fate)) {
            foreach ($result as $value) {
                $value = explode("@", $value);
                $englishName = $value[0];
                $newNum = $value[1];
                $died += $newNum;
            }
        }
        if (in_array("shit", $Fate)) {
            foreach ($thePeopleTable as $person) {
                $personEName =  $person["english name"];
                $died = ($died < 0) ? $died : ($died * (-1));
                $num = round($died) / $myPeopleNum;
                $personNum = 0;
                $thePerson = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$peopleTable` WHERE `english name` = '{$personEName}' LIMIT 1"));
                $soldierConsumableItems = $thePerson["consumable item"];
                $consumableItems = explode("\n", $soldierConsumableItems);
                foreach ($consumableItems as $consumableItem) {
                    $value = explode("=>", $consumableItem);
                    $itemPersianName = trim($value[0]);
                    $number = trim($value[1]);
                    if ($number > 0) {
                        $personNum += 1;
                    }
                }
                if ($personNum > 0) {
                    $cityJPeople = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $person = $cityJPeople[$personEName];
                    $personArray = explode("@", $person);
                    $itemPersianName = $personArray[0];
                    $newNum = intval($personArray[1] + $num);
                    $newNum = ($newNum > 0) ? $newNum : 0;
                    $save = "$itemPersianName@$newNum";
                    // SendMessage($chat_id, "died : $died", "HTML", $message_id);
                    // SendMessage($chat_id, "mySoldiersNum : $mySoldiersNum", "HTML", $message_id);
                    // SendMessage($chat_id, "test : $save", "HTML", $message_id);
                    $conn->query("UPDATE `$cityPeopleTable` SET `{$personEName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
            }
        }
        //----------
        foreach ($theSoldiersTable as $soldier) {
            $consumableItems = explode("\n", $soldier["consumable item"]);
            $soldierEnglishName = $soldier["english name"];

            foreach ($consumableItems as $consumableItem) {

                $value = explode("=>", $consumableItem);
                $itemPersianName = trim($value[0]);
                $number = trim($value[1]);
                if ($number > 0) {
                    $theItemsTable = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$itemsTable` WHERE `persian name` = '{$itemPersianName}' LIMIT 1"));
                    $itemEnglishName = $theItemsTable["english name"];
                    //---
                    $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $item = $cityItems[$itemEnglishName];
                    //---
                    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $soldierNum = $citySoldiers[$soldierEnglishName];
                    if ($item) {
                        $soldierArray = explode("@", $soldierNum);
                        if ($soldierArray[1] > 0) {
                            $itemArray = explode("@", $item);
                            $itemPersianName = $itemArray[0];
                            $number = $number *  $soldierArray[1];
                            if ($number <= $itemArray[1]) {
                                $newNumber = $itemArray[1] - $number;
                                $newNumber = ($newNumber < 0) ? 0 : $newNumber;
                                $save = "$itemPersianName@$newNumber";
                                // SendMessage($chat_id, "number : $number", "HTML", $message_id);
                                // SendMessage($chat_id, "string : $save", "HTML", $message_id);
                                $conn->query("UPDATE `$cityItemsTable` SET `{$itemEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                                array_push($Fate, "good");
                            } else {
                                $newNumber = $itemArray[1] - $number;
                                $saveNum = ($newNumber < 0) ? 0 : $newNumber;
                                $save = "$itemPersianName@$saveNum";
                                $conn->query("UPDATE `$cityItemsTable` SET `{$itemEnglishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                                $newNumber = (trim($value[1]) > 1) ? $newNumber / 2 : $newNumber;
                                $string = "$itemEnglishName@$newNumber";
                                // SendMessage($chat_id, "shit string : $string", "HTML", $message_id);
                                array_push($result, $string);
                                array_push($Fate, "shit");
                            }
                        }
                    }
                }
            }
        }
        if (in_array("shit", $Fate)) {
            foreach ($result as $value) {
                $died = 0;
                $value = explode("@", $value);
                $englishName = $value[0];
                $newNum = $value[1];
                $died += $newNum;
            }
        }


        // foreach ($result as $value) {
        //     $value = explode("@", $value);
        //     $englishName = $value[0];
        //     $v = $value[1];
        //     $newNum = ($value[1] < 0) ? 0 : $value[1];
        //     $cityItems = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
        //     $item = $cityItems["$englishName"];
        //     $itemArray = explode("@", $item);
        //     $itemPersianName = $itemArray[0];
        //     $save = "$itemPersianName@$newNum";
        //     SendMessage($chat_id, "test : $v", "HTML", $message_id);
        //     if ($value[1] < 0) {
        //         array_push($Fate, "shit");
        //     }
        //     $conn->query("UPDATE `$cityItemsTable` SET `{$englishName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
        // }

        if (in_array("shit", $Fate)) {
            foreach ($theSoldiersTable as $soldier) {
                $soldierEName =  $soldier["english name"];
                $died = ($died < 0) ? $died : ($died * (-1));
                $num = intval($died) / intval($mySoldiersNum);
                $soldiersNum = 0;
                $theSoldier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$soldiersTable` WHERE `english name` = '{$soldierEName}' LIMIT 1"));
                $soldierConsumableItems = $theSoldier["consumable item"];
                $consumableItems = explode("\n", $soldierConsumableItems);
                foreach ($consumableItems as $consumableItem) {
                    $value = explode("=>", $consumableItem);
                    $itemPersianName = trim($value[0]);
                    $number = trim($value[1]);
                    if ($number > 0) {
                        $soldiersNum += 1;
                    }
                }
                if($soldiersNum > 0){
                    $citySoldiers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1"));
                    $soldier = $citySoldiers[$soldierEName];
                    $soldierArray = explode("@", $soldier);
                    $itemPersianName = $soldierArray[0];
                    $newNum = intval($soldierArray[1] + $num);
                    $newNum = ($newNum > 0) ? $newNum : 0;
                    $save = "$itemPersianName@$newNum";
                    // SendMessage($chat_id, "died : $died", "HTML", $message_id);
                    // SendMessage($chat_id, "mySoldiersNum : $mySoldiersNum", "HTML", $message_id);
                    // SendMessage($chat_id, "test : $save", "HTML", $message_id);
                    $conn->query("UPDATE `$citySoldiersTable` SET `{$soldierEName}` = '{$save}' WHERE `city id` = '{$chat_id}' LIMIT 1");
                }
                
            }
        }

        //-----

        SendMessage($chat_id, "Done!", "HTML", $message_id);
    }
}
