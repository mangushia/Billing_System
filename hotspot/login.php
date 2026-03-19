  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get parameters from MikroTik
$mac = $_GET['mac'] ?? '';
$ip = $_GET['ip'] ?? '';
$username = $_GET['username'] ?? '';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $voucher = sanitizeInput($_POST['voucher']);
    
    // Check voucher
    $stmt = $conn->prepare("SELECT v.*, p.* FROM vouchers v 
                            JOIN packages p ON v.package_id = p.id 
                            WHERE v.code = ? AND v.status = 'active' 
                            AND (v.expires_at IS NULL OR v.expires_at > NOW())");
    $stmt->bind_param("s", $voucher);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $voucherData = $result->fetch_assoc();
        
        // Update voucher status
        $update = $conn->prepare("UPDATE vouchers SET status = 'used', used_at = NOW() WHERE code = ?");
        $update->bind_param("s", $voucher);
        $update->execute();
        
        // Redirect to MikroTik with login parameters
        $loginUrl = "http://{$_SERVER['SERVER_ADDR']}/login?" . http_build_query([
            'username' => $voucher,
            'password' => $voucher,
            'dst' => 'http://www.google.com',
            'popup' => 'true'
        ]);
        
        header("Location: $loginUrl");
        exit();
    } else {
        $error = "Invalid or expired voucher code";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ardthon Solutions - Hotspot Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="hotspot-page">
    <div class="hotspot-container">
        <div class="hotspot-box">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="Ardthon Solutions">
            </div>
            <h2>WiFi Hotspot Login</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="hotspot-form">
                <div class="form-group">
                    <label for="voucher">Voucher Code</label>
                    <input type="text" id="voucher" name="voucher" 
                           placeholder="Enter your 8-digit voucher" 
                           pattern="[A-Z0-9]{8,12}" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-wifi"></i> Connect
                </button>
            </form>
            
            <div class="hotspot-footer">
                <p>No voucher? <a href="../">Purchase now</a></p>
                <p class="small">Device: <?php echo htmlspecialchars($mac); ?></p>
            </div>
        </div>
    </div>
</body>
</html>