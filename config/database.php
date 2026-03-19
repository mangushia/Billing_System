<?php
// Database configuration - UPDATED with your credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'msingico_wifi_billing');  // Your cPanel username
define('DB_PASS', 'wifi_billing'); // Your database password
define('DB_NAME', 'msingico_wifi_billing'); // Your database name

// Create connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// Set timezone
date_default_timezone_set('Africa/Nairobi');
?>