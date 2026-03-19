  
<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['user'])) {
    // Log the session end
    $stmt = $conn->prepare("UPDATE sessions SET end_time = NOW(), status = 'closed' WHERE session_id = ?");
    $stmt->bind_param("s", session_id());
    $stmt->execute();
}

session_destroy();

// Redirect to MikroTik logout
header("Location: http://{$_SERVER['SERVER_ADDR']}/logout?logout=1");
exit();
?>