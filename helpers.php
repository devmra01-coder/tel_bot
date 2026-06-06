<?php

/**
 * ========================================
 * توابع عمومی و کمکی
 * ========================================
 */

// کلاس مدیریت توابع کمکی
class Helpers
{
    /**
     * بررسی اینکه آیا پیام یک دستور بازگشت است
     */
    public static function isBackButton($text)
    {
        $backCommands = ['/start', '🏷 ○ برگشت به منوی اصلی', 'پنل', 'Open', '🔙', 'back'];
        return in_array($text, $backCommands);
    }

    /**
     * تقسیم رشته‌ای قالب شده
     */
    public static function explodeData($text, $separator = '@')
    {
        return array_filter(explode($separator, $text));
    }

    /**
     * ترکیب داده‌ها به صورت متن
     */
    public static function implodeData($items, $separator = '@')
    {
        return implode($separator, $items);
    }

    /**
     * پاکسازی و اعتبارسنجی ورودی
     */
    public static function sanitize($input)
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * ایجاد شناسه یکتا
     */
    public static function generateId()
    {
        return uniqid(microtime(true) . '_', true);
    }

    /**
     * فرمت‌بندی اعداد
     */
    public static function formatNumber($number)
    {
        return number_format($number, 0, '.', ',');
    }

    /**
     * بررسی اعتبار شناسه تلگرام
     */
    public static function isValidUserId($userId)
    {
        return is_numeric($userId) && (int)$userId > 0;
    }

    /**
     * تبدیل متن به صورت درست برای JSON
     */
    public static function prepareForJson($text)
    {
        return json_encode($text, JSON_UNESCAPED_UNICODE);
    }
}

// کلاس مدیریت مراحل (steps)
class StepManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * تنظیم مرحله برای کاربر
     */
    public function setStep($userId, $step, $data = null)
    {
        $admin = $this->db->findOne('admins', 'id', (string)$userId);
        if ($admin) {
            $updateData = ['step' => $step];
            if ($data !== null) {
                $updateData['thing'] = $data;
            }
            $this->db->update('admins', 'id', (string)$userId, $updateData);
        } else {
            $this->db->insert('admins', [
                'id' => (string)$userId,
                'step' => $step,
                'thing' => $data ?? ''
            ]);
        }
    }

    /**
     * دریافت مرحله کاربر
     */
    public function getStep($userId)
    {
        $admin = $this->db->findOne('admins', 'id', (string)$userId);
        return $admin['step'] ?? 'none';
    }

    /**
     * دریافت داده ذخیره‌شده برای مرحله
     */
    public function getStepData($userId)
    {
        $admin = $this->db->findOne('admins', 'id', (string)$userId);
        return $admin['thing'] ?? null;
    }

    /**
     * ریست کردن مرحله
     */
    public function resetStep($userId)
    {
        $this->setStep($userId, 'none', null);
    }

    /**
     * بررسی اینکه آیا کاربر در یک مرحله خاص است
     */
    public function isInStep($userId, $step)
    {
        return $this->getStep($userId) === $step;
    }

    /**
     * بررسی اینکه آیا کاربر در یک مرحله است که شامل prefix است
     */
    public function isInStepWithPrefix($userId, $stepPrefix)
    {
        $currentStep = $this->getStep($userId);
        return strpos($currentStep, $stepPrefix) === 0;
    }
}

// کلاس مدیریت شهرها
class CityManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * دریافت یا ایجاد شهر
     */
    public function getOrCreateCity($cityId)
    {
        $city = $this->db->findOne('cities', 'city id', (string)$cityId);
        if (!$city) {
            $city = [
                'city id' => (string)$cityId,
                'player id' => '',
                'step' => 'none',
                'check' => 'no',
                'family' => '',
                'city name' => '',
                'lord name' => '',
                'maghsad' => '',
                'send item' => '',
                'send item num' => '',
                'get item' => '',
                'get item num' => ''
            ];
            $this->db->insert('cities', $city);
        }
        return $city;
    }

    /**
     * به‌روزرسانی شهر
     */
    public function updateCity($cityId, $data)
    {
        $this->db->update('cities', 'city id', (string)$cityId, $data);
    }

    /**
     * تنظیم مرحله شهر
     */
    public function setCityStep($cityId, $step)
    {
        $this->updateCity($cityId, ['step' => $step]);
    }

    /**
     * دریافت مرحله شهر
     */
    public function getCityStep($cityId)
    {
        $city = $this->getOrCreateCity($cityId);
        return $city['step'] ?? 'none';
    }
}

// کلاس مدیریت آیتم‌ها
class ItemManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * اضافه کردن آیتم جدید
     */
    public function addItem($englishName, $persianName, $initialValue)
    {
        $item = [
            'english name' => $englishName,
            'persian name' => $persianName,
            'first number' => (string)$initialValue
        ];
        $this->db->insert('items', $item);
        return $item;
    }

    /**
     * دریافت آیتم
     */
    public function getItem($englishName)
    {
        return $this->db->findOne('items', 'english name', $englishName);
    }

    /**
     * دریافت تمام آیتم‌ها
     */
    public function getAllItems()
    {
        return $this->db->getAll('items');
    }

    /**
     * حذف آیتم
     */
    public function deleteItem($englishName)
    {
        $this->db->delete('items', 'english name', $englishName);
    }

    /**
     * دریافت آیتم‌های شهر
     */
    public function getCityItems($cityId)
    {
        $items = $this->db->get('cityItems');
        return $items[$cityId] ?? [];
    }

    /**
     * تنظیم آیتم‌های شهر
     */
    public function setCityItems($cityId, $items)
    {
        $this->db->set('cityItems', $cityId, $items);
    }
}

// کلاس مدیریت ساختمان‌ها
class BuildingManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * اضافه کردن ساختمان جدید
     */
    public function addBuilding($data)
    {
        $building = [
            'english name' => $data['english_name'] ?? '',
            'persian name' => $data['persian_name'] ?? '',
            'upgrade items 1' => $data['upgrade_items_1'] ?? '',
            'upgrade items 2' => $data['upgrade_items_2'] ?? '',
            'upgrade items 3' => $data['upgrade_items_3'] ?? '',
            'efficiency item' => $data['efficiency_item'] ?? '',
            'efficiency number' => $data['efficiency_number'] ?? '',
            'first level' => $data['first_level'] ?? '',
            'last level' => $data['last_level'] ?? ''
        ];
        $this->db->insert('buildings', $building);
        return $building;
    }

    /**
     * دریافت ساختمان
     */
    public function getBuilding($englishName)
    {
        return $this->db->findOne('buildings', 'english name', $englishName);
    }

    /**
     * دریافت تمام ساختمان‌ها
     */
    public function getAllBuildings()
    {
        return $this->db->getAll('buildings');
    }

    /**
     * حذف ساختمان
     */
    public function deleteBuilding($englishName)
    {
        $this->db->delete('buildings', 'english name', $englishName);
    }
}

// کلاس مدیریت سربازان
class SoldierManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * اضافه کردن سرباز جدید
     */
    public function addSoldier($data)
    {
        $soldier = [
            'english name' => $data['english_name'] ?? '',
            'persian name' => $data['persian_name'] ?? '',
            'consumable item' => $data['consumable_item'] ?? '',
            'first number' => $data['first_number'] ?? ''
        ];
        $this->db->insert('soldiers', $soldier);
        return $soldier;
    }

    /**
     * دریافت سرباز
     */
    public function getSoldier($englishName)
    {
        return $this->db->findOne('soldiers', 'english name', $englishName);
    }

    /**
     * دریافت تمام سربازان
     */
    public function getAllSoldiers()
    {
        return $this->db->getAll('soldiers');
    }

    /**
     * حذف سرباز
     */
    public function deleteSoldier($englishName)
    {
        $this->db->delete('soldiers', 'english name', $englishName);
    }
}

// شروع کلاس‌های مدیریت
$stepManager = new StepManager($db);
$cityManager = new CityManager($db);
$itemManager = new ItemManager($db);
$buildingManager = new BuildingManager($db);
$soldierManager = new SoldierManager($db);

// Person manager
class PersonManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function addPerson($data)
    {
        $person = [
            'english name' => $data['english_name'] ?? '',
            'persian name' => $data['persian_name'] ?? '',
            'consumable item' => $data['consumable_item'] ?? '',
            'first number' => $data['first_number'] ?? ''
        ];
        $this->db->insert('people', $person);
        return $person;
    }

    public function getPerson($englishName)
    {
        return $this->db->findOne('people', 'english name', $englishName);
    }

    public function deletePerson($englishName)
    {
        $this->db->delete('people', 'english name', $englishName);
    }
}

// Camp manager
class CampManager
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function addCamp($data)
    {
        $camp = [
            'english name' => $data['english_name'] ?? '',
            'persian name' => $data['persian_name'] ?? '',
            'upgrade items 1' => $data['upgrade_items_1'] ?? '',
            'upgrade items 2' => $data['upgrade_items_2'] ?? '',
            'upgrade items 3' => $data['upgrade_items_3'] ?? '',
            'efficiency soldier' => $data['efficiency_soldier'] ?? '',
            'efficiency number' => $data['efficiency_number'] ?? '',
            'first level' => $data['first_level'] ?? '',
            'last level' => $data['last_level'] ?? ''
        ];
        $this->db->insert('camps', $camp);
        return $camp;
    }

    public function getCamp($englishName)
    {
        return $this->db->findOne('camps', 'english name', $englishName);
    }

    public function deleteCamp($englishName)
    {
        $this->db->delete('camps', 'english name', $englishName);
    }
}

$personManager = new PersonManager($db);
$campManager = new CampManager($db);

?>
