  
<?php
// Safaricom Daraja API Configuration

// Environment
define('MPESA_ENV', 'sandbox'); // Change to 'production' for live

// API Credentials
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY_HERE');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET_HERE');
define('MPESA_PASSKEY', 'YOUR_PASSKEY_HERE');
define('MPESA_SHORTCODE', '174379'); // Your paybill/till number

// URLs
define('MPESA_CALLBACK_URL', 'https://yourdomain.com/api/mpesa-callback.php');
define('MPESA_CONFIRMATION_URL', 'https://yourdomain.com/billing/mpesa/confirm.php');
define('MPESA_VALIDATION_URL', 'https://yourdomain.com/billing/mpesa/validation.php');

// Business details
define('BUSINESS_NAME', 'Ardthon Solutions');
define('BUSINESS_EMAIL', 'billing@ardthon.com');
define('BUSINESS_PHONE', '+254700000000');

// API Endpoints
if (MPESA_ENV == 'sandbox') {
    define('MPESA_AUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    define('MPESA_QUERY_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query');
    define('MPESA_REGISTER_URL', 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl');
} else {
    define('MPESA_AUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STK_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    define('MPESA_QUERY_URL', 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query');
    define('MPESA_REGISTER_URL', 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl');
}
?>