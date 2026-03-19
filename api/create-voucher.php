  
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $packageId = $_POST['package_id'] ?? null;
    
    if($packageId) {
        $voucherCode = generateVoucherCode();
        $expiryDate = calculateExpiryDateFromPackage($packageId, $conn);
        
        $stmt = $conn->prepare("INSERT INTO vouchers (code, package_id, status, expires_at) VALUES (?, ?, 'active', ?)");
        $stmt->bind_param("sis", $voucherCode, $packageId, $expiryDate);
        
        if($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'voucher' => $voucherCode,
                'expires_at' => $expiryDate
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create voucher']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing package ID']);
    }
}
?>