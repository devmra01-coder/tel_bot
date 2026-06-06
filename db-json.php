<?php

/**
 * ========================================
 * کلاس مدیریت دیتابیس JSON
 * ========================================
 */

class JSONDatabase
{
    private $filePath;
    private $data = [];
    private static $instance = null;

    private function __construct($filePath = DB_FILE)
    {
        $this->filePath = $filePath;
        $this->load();
    }

    public static function getInstance($filePath = DB_FILE)
    {
        if (self::$instance === null) {
            self::$instance = new self($filePath);
        }
        return self::$instance;
    }

    /**
     * بارگذاری دیتابیس از فایل JSON
     */
    private function load()
    {
        if (!file_exists($this->filePath)) {
            $this->data = $this->getDefaultStructure();
            $this->save();
        } else {
            $content = file_get_contents($this->filePath);
            $this->data = json_decode($content, true) ?? $this->getDefaultStructure();
        }
    }

    /**
     * ساختار پیش‌فرض دیتابیس
     */
    private function getDefaultStructure()
    {
        return [
            'items' => [],
            'soldiers' => [],
            'people' => [],
            'buildings' => [],
            'camps' => [],
            'cities' => [],
            'admins' => [],
            'cityItems' => {},
            'cityBuildings' => {},
            'citySoldiers' => {},
            'cityPeople' => {},
            'cityCamps' => {}
        ];
    }

    /**
     * ذخیره دیتابیس به فایل JSON
     */
    public function save()
    {
        $json = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($this->filePath, $json);
    }

    /**
     * دریافت تمام رکوردهای یک جدول
     */
    public function getAll($table)
    {
        return $this->data[$table] ?? [];
    }

    /**
     * دریافت یک رکورد بر اساس شرط
     */
    public function findOne($table, $key, $value)
    {
        $items = $this->getAll($table);
        foreach ($items as $item) {
            if (isset($item[$key]) && $item[$key] == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * دریافت چند رکورد بر اساس شرط
     */
    public function find($table, $key, $value)
    {
        $items = $this->getAll($table);
        $results = [];
        foreach ($items as $item) {
            if (isset($item[$key]) && $item[$key] == $value) {
                $results[] = $item;
            }
        }
        return $results;
    }

    /**
     * اضافه کردن رکورد جدید
     */
    public function insert($table, $data)
    {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }
        $this->data[$table][] = $data;
        $this->save();
        return true;
    }

    /**
     * به‌روزرسانی رکورد
     */
    public function update($table, $key, $value, $updateData)
    {
        $items = $this->getAll($table);
        foreach ($items as &$item) {
            if (isset($item[$key]) && $item[$key] == $value) {
                $item = array_merge($item, $updateData);
                break;
            }
        }
        $this->data[$table] = $items;
        $this->save();
        return true;
    }

    /**
     * حذف رکورد
     */
    public function delete($table, $key, $value)
    {
        $items = $this->getAll($table);
        $this->data[$table] = array_filter($items, function($item) use ($key, $value) {
            return !(isset($item[$key]) && $item[$key] == $value);
        });
        $this->save();
        return true;
    }

    /**
     * حذف تمام رکوردهای یک جدول
     */
    public function truncate($table)
    {
        if (isset($this->data[$table])) {
            if (is_array($this->data[$table])) {
                $this->data[$table] = [];
            } else {
                $this->data[$table] = (object)[];
            }
        }
        $this->save();
        return true;
    }

    /**
     * دریافت یک مقدار از دیتابیس
     */
    public function get($table, $key = null)
    {
        if ($key === null) {
            return $this->data[$table] ?? null;
        }
        return $this->data[$table][$key] ?? null;
    }

    /**
     * تنظیم یک مقدار
     */
    public function set($table, $key, $value)
    {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }
        $this->data[$table][$key] = $value;
        $this->save();
    }

    /**
     * شمارش رکوردهای یک جدول
     */
    public function count($table)
    {
        $items = $this->getAll($table);
        return is_array($items) ? count($items) : 0;
    }
}

// شروع دیتابیس
$db = JSONDatabase::getInstance();

?>
