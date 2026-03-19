  
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(isset($_POST['voucher'])) {
    $voucher = sanitizeInput($_POST['voucher']);
    
    // Check if voucher is valid
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
        
        // Get user IP and MAC
        $ip = getClientIP();
        $mac = $_POST['mac'] ?? '';
        
        // Create session
        $sessionId = session_id();
        $stmt = $conn->prepare("INSERT INTO sessions (username, mac_address, ip_address, session_id) 
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $voucher, $mac, $ip, $sessionId);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Voucher validated successfully',
            'data' => [
                'speed' => $voucherData['speed'],
                'expires_at' => $voucherData['expires_at']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired voucher']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing voucher code']);
}
?>