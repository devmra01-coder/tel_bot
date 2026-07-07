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

$conn->query("SET NAMES utf8mb4");

// ===================== ساخت فایل SQL =====================
$filename = 'full_backup_' . date('Y-m-d_H-i-s') . '.sql';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// شروع فایل SQL
echo "-- Backup Database: $db\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    // CREATE TABLE
    $create = $conn->query("SHOW CREATE TABLE `$table`");
    $create_row = $create->fetch_assoc();
    echo "\n-- Table structure for table `$table`\n";
    echo $create_row['Create Table'] . ";\n\n";

    // INSERT DATA
    $data = $conn->query("SELECT * FROM `$table`");
    if ($data && $data->num_rows > 0) {
        echo "-- Dumping data for table `$table`\n";
        
        $columns = [];
        $fields = $data->fetch_fields();
        foreach ($fields as $field) {
            $columns[] = '`' . $field->name . '`';
        }
        
        $values = [];
        while ($row = $data->fetch_row()) {
            $escaped = array_map(function($v) use ($conn) {
                if ($v === null) return 'NULL';
                return "'" . $conn->real_escape_string($v) . "'";
            }, $row);
            
            $values[] = "(" . implode(", ", $escaped) . ")";
        }
        
        echo "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES\n";
        echo implode(",\n", $values) . ";\n\n";
    }
}

echo "SET FOREIGN_KEY_CHECKS=1;\n";
echo "-- Backup completed successfully.\n";

$conn->close();
?>
