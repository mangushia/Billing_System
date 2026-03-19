  
<?php
// General Functions

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getPackages($conn) {
    $sql = "SELECT * FROM packages WHERE status = 'active' ORDER BY price ASC";
    $result = $conn->query($sql);
    $packages = [];
    while($row = $result->fetch_assoc()) {
        $packages[] = $row;
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

function sendSMS($phone, $message) {
    // Implement SMS sending logic (e.g., Africa's Talking, Twilio)
    // This is a placeholder
    error_log("SMS to $phone: $message");
    return true;
}

function generateVoucherCode() {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = VOUCHER_PREFIX;
    for($i = 0; $i < VOUCHER_LENGTH; $i++) {
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
    $logFile = 'logs/errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $error" . PHP_EOL;
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