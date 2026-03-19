  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Handle bulk voucher generation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_bulk'])) {
    $packageId = sanitizeInput($_POST['package_id']);
    $quantity = (int)$_POST['quantity'];
    $generated = [];
    
    $conn->begin_transaction();
    
    try {
        for($i = 0; $i < $quantity; $i++) {
            $code = generateVoucherCode();
            $expiryDate = calculateExpiryDateFromPackage($packageId, $conn);
            
            $stmt = $conn->prepare("INSERT INTO vouchers (code, package_id, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $code, $packageId, $expiryDate);
            $stmt->execute();
            
            $generated[] = $code;
        }
        
        $conn->commit();
        $success = "Generated " . count($generated) . " vouchers successfully";
    } catch(Exception $e) {
        $conn->rollback();
        $error = "Error generating vouchers: " . $e->getMessage();
    }
}

// Get vouchers with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

$vouchers = $conn->query("SELECT v.*, p.name as package_name, p.price 
                          FROM vouchers v 
                          LEFT JOIN packages p ON v.package_id = p.id 
                          ORDER BY v.created_at DESC 
                          LIMIT $offset, " . ITEMS_PER_PAGE);

// Get packages for dropdown
$packages = $conn->query("SELECT * FROM packages WHERE status = 'active' ORDER BY price");

include 'includes/admin-header.php';
?>

<div class="vouchers-management">
    <h1>Manage Vouchers</h1>
    
    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="voucher-actions">
        <button class="btn-primary" onclick="showGenerateForm()">
            <i class="fas fa-plus-circle"></i> Generate Vouchers
        </button>
        
        <button class="btn-secondary" onclick="exportVouchers()">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Voucher Code</th>
                <th>Package</th>
                <th>Price</th>
                <th>Status</th>
                <th>Expires At</th>
                <th>Used At</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while($voucher = $vouchers->fetch_assoc()): ?>
            <tr>
                <td><strong><?php echo $voucher['code']; ?></strong></td>
                <td><?php echo $voucher['package_name']; ?></td>
                <td><?php echo CURRENCY; ?> <?php echo number_format($voucher['price'], 2); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $voucher['status']; ?>">
                        <?php echo $voucher['status']; ?>
                    </span>
                </td>
                <td><?php echo $voucher['expires_at'] ? date('d/m/Y H:i', strtotime($voucher['expires_at'])) : 'Never'; ?></td>
                <td><?php echo $voucher['used_at'] ? date('d/m/Y H:i', strtotime($voucher['used_at'])) : '-'; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($voucher['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Generate Vouchers Modal -->
<div id="generateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Generate Vouchers</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="package_id">Package</label>
                <select id="package_id" name="package_id" required>
                    <option value="">Select Package</option>
                    <?php 
                    $packages->data_seek(0);
                    while($package = $packages->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $package['id']; ?>">
                        <?php echo $package['name']; ?> - <?php echo CURRENCY; ?> <?php echo $package['price']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" max="100" value="10" required>
            </div>
            
            <button type="submit" name="generate_bulk" class="btn-primary">
                Generate Vouchers
            </button>
        </form>
    </div>
</div>

<script>
function showGenerateForm() {
    document.getElementById('generateModal').style.display = 'block';
}

function exportVouchers() {
    window.location.href = 'export.php?type=vouchers';
}

// Close modal when clicking on X
document.querySelectorAll('.modal .close').forEach(btn => {
    btn.onclick = function() {
        this.closest('.modal').style.display = 'none';
    }
});
</script>

<?php include 'includes/admin-footer.php'; ?>