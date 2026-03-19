  
<?php
// Database-specific functions

function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getTransactionById($conn, $transactionId) {
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
    $stmt->bind_param("i", $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getVoucherByCode($conn, $code) {
    $stmt = $conn->prepare("SELECT v.*, p.* FROM vouchers v 
                            JOIN packages p ON v.package_id = p.id 
                            WHERE v.code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function updateVoucherStatus($conn, $code, $status) {
    $stmt = $conn->prepare("UPDATE vouchers SET status = ? WHERE code = ?");
    $stmt->bind_param("ss", $status, $code);
    return $stmt->execute();
}

function getDailyRevenue($conn) {
    $sql = "SELECT SUM(amount) as total FROM transactions 
            WHERE DATE(created_at) = CURDATE() AND status = 'completed'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getMonthlyRevenue($conn) {
    $sql = "SELECT SUM(amount) as total FROM transactions 
            WHERE MONTH(created_at) = MONTH(CURDATE()) 
            AND YEAR(created_at) = YEAR(CURDATE())
            AND status = 'completed'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getActiveVouchers($conn) {
    $sql = "SELECT COUNT(*) as count FROM vouchers 
            WHERE status = 'active' AND expires_at > NOW()";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

function getTotalUsers($conn) {
    $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}
?>