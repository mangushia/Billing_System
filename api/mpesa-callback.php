  
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/mpesa-functions.php';

// Get callback data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData, true);

// Log the callback
logMpesaCallback($callbackData);

// Process the callback
if(isset($data['Body']['stkCallback'])) {
    $callback = $data['Body']['stkCallback'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    
    if($resultCode == 0) {
        // Payment successful
        $metadata = $callback['CallbackMetadata']['Item'];
        
        $amount = 0;
        $mpesaCode = '';
        $phone = '';
        
        foreach($metadata as $item) {
            switch($item['Name']) {
                case 'Amount':
                    $amount = $item['Value'];
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaCode = $item['Value'];
                    break;
                case 'PhoneNumber':
                    $phone = $item['Value'];
                    break;
            }
        }
        
        // Extract package ID from AccountReference (stored in session or passed)
        session_start();
        $packageId = $_SESSION['pending_package'] ?? null;
        
        if($packageId) {
            // Process successful payment
            $result = processSuccessfulPayment([
                'amount' => $amount,
                'mpesaCode' => $mpesaCode,
                'phone' => $phone,
                'packageId' => $packageId
            ]);
            
            // Clear session
            unset($_SESSION['pending_package']);
            
            // Return success response
            http_response_code(200);
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        } else {
            error_log('No pending package found for callback');
            http_response_code(400);
            echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid request']);
        }
    } else {
        // Payment failed
        error_log("Payment failed: $resultDesc");
        http_response_code(200);
        echo json_encode(['ResultCode' => $resultCode, 'ResultDesc' => $resultDesc]);
    }
} else {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data']);
}
?>