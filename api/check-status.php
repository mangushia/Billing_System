  
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(isset($_POST['checkoutRequestID'])) {
    $checkoutRequestID = $_POST['checkoutRequestID'];
    
    // Query M-PESA status
    require_once '../includes/mpesa-functions.php';
    $status = queryStatus($checkoutRequestID);
    
    echo json_encode($status);
} else {
    echo json_encode(['error' => 'Missing checkoutRequestID']);
}
?>