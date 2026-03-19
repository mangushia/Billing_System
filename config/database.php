<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'msingico_wifi_billing');
define('DB_PASS', 'wifi_billing'); // Make sure this is the correct password
define('DB_NAME', 'msingico_wifi_billing');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $conn->error);
} else {
    // printf("Current character set: %s\n", $conn->character_set_name());
}

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Test query to verify connection
$test_query = $conn->query("SELECT 1");
if ($test_query) {
    // echo "Database connection is working properly";
} else {
    die("Database query failed: " . $conn->error);
}
?>