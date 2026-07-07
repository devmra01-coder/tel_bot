<?php
// ===================== تنظیمات =====================
$serverName = "acela.proxy.rlwy.net";
$userName = "root";
$password = "IRHMjKYYiWvjxEOnoStdxKHHGLdGFkzr";
$dbName = "railway";
$port = 14319;
        
$conn = mysqli_connect($serverName, $userName, $password, $dbName, $port);


if ($conn->connect_error) {
    die("خطا در اتصال: " . $conn->connect_error);
}

// ===================== دریافت همه جداول =====================
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// ===================== شروع دانلود ZIP =====================
$zipname = 'full_database_export_' . date('Y-m-d_H-i-s') . '.zip';
$zip = new ZipArchive();
$zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// برای هر جدول یک فایل CSV جدا بساز
foreach ($tables as $table) {
    // دریافت اسم همه ستون‌ها
    $columns = [];
    $col_result = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($col = $col_result->fetch_assoc()) {
        $columns[] = $col['Field'];
    }

    // ایجاد فایل CSV موقت در حافظه
    $csv_content = '';
    $output = fopen('php://memory', 'w');
    
    // هدر ستون‌ها
    fputcsv($output, $columns);
    
    // داده‌ها
    $sql = "SELECT * FROM `$table`";
    $data_result = $conn->query($sql);
    
    while ($row = $data_result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv_content = stream_get_contents($output);
    fclose($output);
    
    // اضافه کردن به زیپ
    $zip->addFromString($table . '.csv', $csv_content);
}

$zip->close();

// ===================== ارسال فایل زیپ برای دانلود =====================
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipname . '"');
header('Content-Length: ' . filesize($zipname));
readfile($zipname);

// پاک کردن فایل موقت
unlink($zipname);
$conn->close();
?>
