  
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
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
        <div class="package-grid">
            <?php
            $packages = getPackages($conn);
            foreach($packages as $package):
            ?>
            <div class="package-card">
                <div class="package-header">
                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                    <div class="price"><?php echo CURRENCY; ?> <?php echo number_format($package['price'], 2); ?></div>
                </div>
                <div class="package-body">
                    <ul class="features">
                        <li><i class="fas fa-tachometer-alt"></i> Speed: <?php echo $package['speed']; ?> Mbps</li>
                        <li><i class="fas fa-clock"></i> Duration: <?php echo $package['duration']; ?> <?php echo $package['duration_unit']; ?></li>
                        <li><i class="fas fa-database"></i> Data: <?php echo $package['data_limit']; ?> GB</li>
                    </ul>
                    <button onclick="selectPackage(<?php echo $package['id']; ?>, <?php echo $package['price']; ?>)" 
                            class="btn-select">
                        Select Package
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Payment Details</h2>
        <form id="mpesa-form" onsubmit="processPayment(event)">
            <input type="hidden" id="package_id" name="package_id">
            <div class="form-group">
                <label for="phone">M-PESA Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g., 0712345678" 
                       pattern="[0-9]{10}" required>
                <small>Enter Safaricom number starting with 07</small>
            </div>
            <div class="form-group">
                <label>Amount to Pay:</label>
                <div class="amount-display"><?php echo CURRENCY; ?> <span id="amount"></span></div>
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

<?php require_once 'includes/footer.php'; ?>