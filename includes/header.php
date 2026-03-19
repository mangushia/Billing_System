<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants if not defined
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Ardthon Solutions WiFi Hotspot');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('SITE_NAME') ? SITE_NAME : 'WiFi Hotspot'; ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <a href="/index.php">
                        <img src="/assets/images/logo.png" alt="Ardthon Solutions" onerror="this.src='https://via.placeholder.com/150x50?text=Ardthon'">
                    </a>
                </div>
                <ul class="nav-menu">
                    <li><a href="/index.php">Home</a></li>
                    <li><a href="#packages">Packages</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if(isset($_SESSION) && isset($_SESSION['user_id'])): ?>
                        <li><a href="/admin/dashboard.php">Dashboard</a></li>
                        <li><a href="/logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>
    <main>