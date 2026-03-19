  
<?php
require_once 'config/daraja.php';

function getAccessToken() {
    $url = MPESA_AUTH_URL;
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . $credentials,
        'Content-Type: application/json'
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    
    if(curl_error($curl)) {
        error_log('Curl error: ' . curl_error($curl));
        return false;
    }
    
    curl_close($curl);
    
    $result = json_decode($response);
    
    if(isset($result->access_token)) {
        return $result->access_token;
    }
    
    error_log('Failed to get access token: ' . $response);
    return false;
}

function stkPush($phone, $amount, $accountReference, $transactionDesc) {
    $accessToken = getAccessToken();
    
    if(!$accessToken) {
        return ['error' => 'Failed to get access token'];
    }
    
    $url = MPESA_STK_URL;
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $curl_post_data = array(
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => round($amount),
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => MPESA_CALLBACK_URL,
        'AccountReference' => $accountReference,
        'TransactionDesc' => $transactionDesc
    );
    
    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    
    $response = curl_exec($curl);
    
    if(curl_error($curl)) {
        error_log('STK Push curl error: ' . curl_error($curl));
        return ['error' => 'Curl error: ' . curl_error($curl)];
    }
    
    curl_close($curl);
    
    return json_decode($response, true);
}

function queryStatus($checkoutRequestID) {
    $accessToken = getAccessToken();
    
    if(!$accessToken) {
        return false;
    }
    
    $url = MPESA_QUERY_URL;
    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $curl_post_data = array(
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'CheckoutRequestID' => $checkoutRequestID
    );
    
    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    return json_decode($response, true);
}

function logMpesaCallback($data) {
    $logFile = 'logs/mpesa-callbacks.log';
    $timestamp = date('Y-m-d H:i:s');
    $logData = "[$timestamp] " . json_encode($data) . PHP_EOL;
    file_put_contents($logFile, $logData, FILE_APPEND);
}

function processSuccessfulPayment($data) {
    global $conn;
    
    $amount = $data['amount'];
    $mpesaCode = $data['mpesaCode'];
    $phone = $data['phone'];
    $packageId = $data['packageId'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate voucher
        $voucherCode = generateVoucherCode();
        $expiryDate = calculateExpiryDateFromPackage($packageId, $conn);
        
        // Insert voucher
        $stmt = $conn->prepare("INSERT INTO vouchers (code, package_id, status, expires_at, created_at) VALUES (?, ?, 'active', ?, NOW())");
        $stmt->bind_param("sis", $voucherCode, $packageId, $expiryDate);
        $stmt->execute();
        
        // Insert transaction
        $transactionId = generateTransactionId();
        $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, phone, amount, mpesa_code, package_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'completed', NOW())");
        $stmt->bind_param("ssdsi", $transactionId, $phone, $amount, $mpesaCode, $packageId);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Send SMS with voucher
        $message = "Thank you for purchasing Ardthon WiFi. Your voucher code is: $voucherCode. Valid until: $expiryDate";
        sendSMS($phone, $message);
        
        return [
            'success' => true,
            'voucher' => $voucherCode,
            'message' => 'Payment successful. Check your phone for voucher code.'
        ];
        
    } catch(Exception $e) {
        $conn->rollback();
        error_log('Payment processing error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to process payment. Please contact support.'
        ];
    }
}

function calculateExpiryDateFromPackage($packageId, $conn) {
    $stmt = $conn->prepare("SELECT duration, duration_unit FROM packages WHERE id = ?");
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    
    return calculateExpiryDate($package['duration'], $package['duration_unit']);
}
?>