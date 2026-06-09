<?php

$cityItems = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityItemsTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$citySoldiers = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$citySoldiersTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$cityPeople = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityPeopleTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$cityBuildings = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityBuildingsTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$cityCamps = mysqli_query($conn, "SELECT *, NULL AS `city id` FROM `$cityCampsTable` WHERE `city id` = '{$chat_id}' LIMIT 1");
$financialItems = "";
$financialPeople = "";
$financialSoldiers = "";
$financialBuildings = "";
$financialCamps = "";

foreach ($cityItems as $items) {
    foreach ($items as $item) {
        $item = explode('@', $item);
        if ($item[0]) {
            $financialItems .= $item[0] . " : " . $item[1] . "\n";
        }
    }
}

foreach ($citySoldiers as $soldiers) {
    foreach ($soldiers as $soldier) {
        $soldier = explode('@', $soldier);
        if ($soldier[0]) {
            $financialSoldiers .= $soldier[0] . " : " . $soldier[1] . "\n";
        }
    }
}
foreach ($cityPeople as $People) {
    foreach ($People as $person) {
        $person = explode('@', $person);
        if ($person[0]) {
            $financialPeople .= $person[0] . " : " . $person[1] . "\n";
        }
    }
}

foreach ($cityBuildings as $buildings) {
    foreach ($buildings as $building) {
        $building = explode('@', $building);
        $b = $building[0];
        $EfficiencyBuilding = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$buildingsTable` WHERE `persian name` = '{$b}' LIMIT 1"));
        $EfficiencyNum = $EfficiencyBuilding["efficiency number"];
        $buildingEfficiencyNum = (int)$building[1] * (int)$EfficiencyNum;
        if ($building[0]) {
            $financialBuildings .= $building[0] . " [" . $building[1] . "]" . " : " . $buildingEfficiencyNum . "\n";
        }
    }
}
foreach ($cityCamps as $camps) {
    foreach ($camps as $camp) {
        $camp = explode('@', $camp);
        $c = $camp[0];
        $c2 = $camp[1];
        $EfficiencyCamp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT `efficiency number` FROM `$campsTable` WHERE `persian name` = '{$c}' LIMIT 1"));
        $EfficiencyNum = $EfficiencyCamp["efficiency number"];
        $campEfficiencyNum = (int)$c2 * (int)$EfficiencyNum;
        if ($camp[0]) {
            $financialCamps .= $camp[0] . " [" . $camp[1] . "]" . " : " . $campEfficiencyNum . "\n";
        }
    }
}
if ($text == "دارایی") {

    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "
[🗺] شهر : $cityName
[👑]- فرمانده : $lordName
\n[👥]- مردم :
$financialPeople
[📜]- آیتم های دارایی :\n$financialItems
[🏯]- ساختمان ها :\n$financialBuildings      
[⚔️]- ارتش:\n$financialSoldiers 
[⛺️]- کمپ های نظامی :\n$financialCamps
        ",
        'parse_mode' => "HTML",
        'reply_to_message_id' => $message_id,
    ]);
}

if ($data == "show financial") {
    bot('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => "
[🗺] شهر : $cityName
[👑]- فرمانده : $lordName
\n[👥]- مردم :
$financialPeople
[📜]- آیتم های دارایی :\n$financialItems
[🏯]- ساختمان ها :\n$financialBuildings      
[⚔️]- ارتش:\n$financialSoldiers 
[⛺️]- کمپ های نظامی :\n$financialCamps
        ",
    ]);
}
