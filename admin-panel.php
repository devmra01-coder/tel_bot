<?php

if (in_array($from_id, $admins)) {
    if ($text == "پنل" || $text == "🔙") {
        // این کد به شما می‌گوید آیا اصلاً ردیفی برای این ID وجود دارد یا خیر
        $check = $conn->query("SELECT * FROM `$adminsTable` WHERE `id`='{$from_id}'");
        if($check->num_rows == 0) {
            echo "کاربر در دیتابیس پیدا نشد!";
        } else {
            echo "کاربر پیدا شد، تعداد ردیف‌ها: " . $check->num_rows;
        }

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "👤|به پنل ادمین خوش آمدید",
            'parse_mode' => "HTML",
            'reply_to_message_id' => $message_id,
            'reply_markup' => $adminPanel,
        ]);
        if (!$adminsDb) {
            sendDataForDb($adminsTable, "id", $from_id);
        }
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "💎 مدیریت آیتم ها" && $theAdminStep == "none") {
        $theText = "📙 به بخش مدیریت آیتم ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $itemsPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "👤 مدیریت شخصیت ها" && $theAdminStep == "none") {
        $theText = "📘 به بخش مدیریت شخصیت ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $peoplePanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🛡 مدیریت سرباز ها" && $theAdminStep == "none") {
        $theText = "📕 به بخش مدیریت سرباز ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $soldierPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "🏯 مدیریت ساختمان ها" && $theAdminStep == "none") {
        $theText = "📗 به بخش مدیریت ساختمان ها خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $buildingPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }
    if ($text == "⛺️ مدیریت کمپ های نظامی" && $theAdminStep == "none") {
        $theText = "📒 به بخش مدیریت کمپ های نظامی خوش آمدید. لطفا از منوی زیر عملیات مورد نظر خود را انتخاب کنید.";
        SendMessage($chat_id, $theText, "HTML", $message_id, $campsPanel);
        $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
    }

    if ($text == "❌پاکسازی همگانی❌" && $theAdminStep == "none") {
        $theText = "هشدار!\n\n با تایید این دستور، تمام اطلاعات بات شما پاک می شود. آیا تایید می کنید؟";
        SendMessage($chat_id, $theText, "HTML", $message_id, $adminYesOrNo);
        $conn->query("UPDATE `$adminsTable` SET `step`='delete-all' WHERE `id`='{$from_id}'LIMIT 1");
    } else if ($theAdminStep == "delete-all") {
        if ($text == "✅") {
            $conn->query("DELETE FROM `$itemsTable`");
            $conn->query("DELETE FROM `$peopleTable`");
            $conn->query("DELETE FROM `$buildingsTable`");
            $conn->query("DELETE FROM `$soldiersTable`");
            $conn->query("DELETE FROM `$campsTable`");
            $conn->query("DELETE FROM `$citiesTable` WHERE `city id` != '-1002243456561'");
            //--------------------------------------
            $cityItems = mysqli_query($conn, "SELECT * FROM `$cityItemsTable`");
            $cityBuildings = mysqli_query($conn, "SELECT * FROM `$cityBuildingsTable`");
            $citySoldiers = mysqli_query($conn, "SELECT * FROM `$citySoldiersTable`");
            $cityPeople = mysqli_query($conn, "SELECT * FROM `$cityPeopleTable`");
            $cityCamps = mysqli_query($conn, "SELECT * FROM `$cityCampsTable`");
            $conn->query("DELETE FROM `$cityItemsTable` WHERE `city id` != '-1002243456561'");
            $conn->query("DELETE FROM `$cityBuildingsTable` WHERE `city id` != '-1002243456561'");
            $conn->query("DELETE FROM `$citySoldiersTable` WHERE `city id` != '-1002243456561'");
            $conn->query("DELETE FROM `$cityPeopleTable` WHERE `city id` != '-1002243456561'");
            $conn->query("DELETE FROM `$cityCampsTable` WHERE `city id` != '-1002243456561'");

            foreach ($cityItems as $values) {
                foreach ($values as $key => $value) {
                    if ($key != "city id") {
                        $conn->query("ALTER TABLE `$cityItemsTable` DROP COLUMN `{$key}`");
                    }
                }
            }
            foreach ($cityBuildings as $values) {
                foreach ($values as $key => $value) {
                    if ($key != "city id") {
                        $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$key}`");
                    }
                }
            }
            foreach ($citySoldiers as $values) {
                foreach ($values as $key => $value) {
                    if ($key != "city id") {
                        $conn->query("ALTER TABLE `$citySoldiersTable` DROP COLUMN `{$key}`");
                    }
                }
            }
            foreach ($cityPeople as $values) {
                foreach ($values as $key => $value) {
                    if ($key != "city id") {
                        $conn->query("ALTER TABLE `$cityPeopleTable` DROP COLUMN `{$key}`");
                    }
                }
            }
            foreach ($cityCamps as $values) {
                foreach ($values as $key => $value) {
                    if ($key != "city id") {
                        $conn->query("ALTER TABLE `$cityCampsTable` DROP COLUMN `{$key}`");
                    }
                }
            }

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Done!",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
                'reply_markup' => $adminBack,
            ]);
            $conn->query("UPDATE `$adminsTable` SET `step`='none' WHERE `id`='{$from_id}'LIMIT 1");
        }

        if ($text == "❌") {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Ah shit",
                'parse_mode' => "HTML",
                'reply_to_message_id' => $message_id,
                'reply_markup' => $adminBack,
            ]);
            $conn->query("ALTER TABLE `$cityBuildingsTable` DROP COLUMN `{$idm}`");
        }
    }
    //----------
    include './admin-panel-forms/add-item.php';
    include './admin-panel-forms/delete-item.php';
    //----------
    include './admin-panel-forms/add-soldier.php';
    include './admin-panel-forms/delete-soldier.php';
    //----------
    include './admin-panel-forms/add-person.php';
    include './admin-panel-forms/delete-person.php';
    //----------
    include './admin-panel-forms/add-building.php';
    include './admin-panel-forms/delete-building.php';
    //----------
    include './admin-panel-forms/add-camp.php';
    include './admin-panel-forms/delete-camp.php';
    //----------   
}
