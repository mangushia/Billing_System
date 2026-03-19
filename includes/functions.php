<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// General Functions

function sanitizeInput($data) {
    if ($data === null) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getPackages($conn) {
    $packages = [];
    try {
        $sql = "SELECT * FROM packages WHERE status = 'active' ORDER BY price ASC";
        $result = $conn->query($sql);
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $packages[] = $row;
            }
        } else {
            error_log("Error in getPackages: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Exception in getPackages: " . $e->getMessage());
    }
    return $packages;
}

function generateTransactionId() {
    return 'TXN' . time() . rand(1000, 9999);
}

function formatPhoneNumber($phone) {
    // Remove any non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if number starts with 0
    if(substr($phone, 0, 1) == '0') {
        $phone = '254' . substr($phone, 1);
    }
    // Check if number starts with 7
    elseif(substr($phone, 0, 1) == '7') {
        $phone = '254' . $phone;
    }
    
    return $phone;
}

function generateVoucherCode() {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for($i = 0; $i < 8; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function displayMessage($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}

function logError($error) {
    $logFile = __DIR__ . '/../logs/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $error" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function calculateExpiryDate($duration, $unit) {
    $date = new DateTime();
    switch($unit) {
        case 'minutes':
            $date->modify("+$duration minutes");
            break;
        case 'hours':
            $date->modify("+$duration hours");
            break;
        case 'days':
            $date->modify("+$duration days");
            break;
        case 'months':
            $date->modify("+$duration months");
            break;
    }
    return $date->format('Y-m-d H:i:s');
}
?>