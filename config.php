<?php

/**
 * ========================================
 * تنظیمات و کانفیگ بات تلگرام
 * ========================================
 */

// ۱. تنظیمات اساسی بات
define('API_KEY', '8973031455:AAFLRPS2L9PBFkZVGNw_zrw09OtxN7Pysxs');
define('ADMIN_IDS', [7761540434, 7015879742, 6707399737, 1317073026, 6788568011, 2022010806, 5958639761, 6389723091, 7165556662]);
define('BOT_PREFIX', 'TASMD');
define('ADMIN_GAP_ID', '-7761540434');
define('DB_FILE', __DIR__ . '/database.json');
define('LOG_FILE', __DIR__ . '/bot.log');

// ۲. تنظیمات زمان و توسعه
define('DEBUG_MODE', false);
error_reporting(E_ALL);
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

// ۳. کلاس مدیریت کانفیگ
class Config
{
    private static $instance = null;
    private $data = [];

    private function __construct()
    {
        // کانفیگ های اضافی می‌توانند در اینجا اضافه شوند
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getAdminIds()
    {
        return ADMIN_IDS;
    }

    public function isAdmin($userId)
    {
        return in_array($userId, ADMIN_IDS);
    }
}

// لاگ کردن درخواست‌های ورودی
file_put_contents(LOG_FILE, file_get_contents('php://input') . "\n---\n", FILE_APPEND);

?>
