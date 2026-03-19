<?php
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files with absolute paths
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if functions file loaded correctly
if (!function_exists('getPackages')) {
    die('Functions file not loaded properly');
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1>Welcome to Ardthon Solutions WiFi</h1>
        <p>High-speed internet for everyone</p>
    </div>
</div>

<div class="packages-section">
    <div class="container">
        <h2>Our Internet Packages</h2>
        <?php
        // Get packages with error checking
        $packages = getPackages($conn);
        if (empty($packages)) {
            echo '<p class="alert alert-warning">No packages available at the moment.</p>';
        } else {
        ?>
        <div class="package-grid">
            <?php foreach($packages as $package): ?>
            <div class="package-card">
                <div class="package-header">
                    <h3><?php echo htmlspecialchars($package['name'] ?? 'Package'); ?></h3>
                    <div class="price">KSh <?php echo number_format($package['price'] ?? 0, 2); ?></div>
                </div>
                <div class="package-body">
                    <ul class="features">
                        <li><i class="fas fa-tachometer-alt"></i> Speed: <?php echo $package['speed'] ?? 0; ?> Mbps</li>
                        <li><i class="fas fa-clock"></i> Duration: <?php echo $package['duration'] ?? 0; ?> <?php echo $package['duration_unit'] ?? 'hours'; ?></li>
                        <li><i class="fas fa-database"></i> Data: <?php echo $package['data_limit'] ?? 0; ?> GB</li>
                    </ul>
                    <button onclick="selectPackage(<?php echo $package['id'] ?? 0; ?>, <?php echo $package['price'] ?? 0; ?>)" 
                            class="btn-select">
                        Select Package
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php } ?>
    </div>
</div>

<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Payment Details</h2>
        <form id="mpesa-form" onsubmit="return processPayment(event)">
            <input type="hidden" id="package_id" name="package_id">
            <div class="form-group">
                <label for="phone">M-PESA Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g., 0712345678" 
                       pattern="[0-9]{10}" required>
                <small>Enter Safaricom number starting with 07</small>
            </div>
            <div class="form-group">
                <label>Amount to Pay:</label>
                <div class="amount-display">KSh <span id="amount"></span></div>
            </div>
            <div class="form-group">
                <label for="email">Email (Optional)</label>
                <input type="email" id="email" name="email" placeholder="receipt@email.com">
            </div>
            <button type="submit" class="btn-pay">
                <i class="fas fa-mobile-alt"></i> Pay with M-PESA
            </button>
        </form>
        <div id="payment-status"></div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>