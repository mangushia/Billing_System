  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/mpesa-functions.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = sanitizeInput($_POST['phone']);
    $packageId = sanitizeInput($_POST['package_id']);
    
    // Validate phone number
    $phone = formatPhoneNumber($phone);
    
    // Get package details
    $stmt = $conn->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    
    if(!$package) {
        echo json_encode(['success' => false, 'message' => 'Invalid package']);
        exit();
    }
    
    // Store package ID in session for callback
    $_SESSION['pending_package'] = $packageId;
    
    // Initiate STK Push
    $response = stkPush(
        $phone,
        $package['price'],
        'WiFi Package',
        'Ardthon WiFi - ' . $package['name']
    );
    
    if(isset($response['CheckoutRequestID'])) {
        echo json_encode([
            'success' => true,
            'checkoutRequestID' => $response['CheckoutRequestID'],
            'message' => 'STK Push sent. Please check your phone and enter PIN.'
        ]);
    } else {
        $errorMsg = $response['error'] ?? 'Failed to initiate payment';
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    }
}
?>