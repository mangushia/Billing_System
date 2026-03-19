<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";

// Test database connection
require_once 'config/database.php';
echo "Database connection successful!<br>";

// Check if tables exist
$result = $conn->query("SHOW TABLES");
echo "Tables in database:<br>";
while($row = $result->fetch_array()) {
    echo "- " . $row[0] . "<br>";
}

phpinfo();
?>