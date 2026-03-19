  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/db-functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Get statistics
$totalRevenue = getDailyRevenue($conn);
$monthlyRevenue = getMonthlyRevenue($conn);
$activeVouchers = getActiveVouchers($conn);
$totalUsers = getTotalUsers($conn);

// Get recent transactions
$recentTransactions = $conn->query("SELECT t.*, p.name as package_name 
                                    FROM transactions t 
                                    LEFT JOIN packages p ON t.package_id = p.id 
                                    ORDER BY t.created_at DESC LIMIT 10");

include 'includes/admin-header.php';
?>

<div class="dashboard">
    <h1>Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill"></i>
            </div>
            <div class="stat-details">
                <h3>Today's Revenue</h3>
                <p class="stat-value"><?php echo CURRENCY; ?> <?php echo number_format($totalRevenue, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-details">
                <h3>Monthly Revenue</h3>
                <p class="stat-value"><?php echo CURRENCY; ?> <?php echo number_format($monthlyRevenue, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-details">
                <h3>Active Vouchers</h3>
                <p class="stat-value"><?php echo $activeVouchers; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-details">
                <h3>Total Users</h3>
                <p class="stat-value"><?php echo $totalUsers; ?></p>
            </div>
        </div>
    </div>
    
    <div class="recent-transactions">
        <h2>Recent Transactions</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Phone</th>
                    <th>Amount</th>
                    <th>Package</th>
                    <th>M-PESA Code</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($transaction = $recentTransactions->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $transaction['transaction_id']; ?></td>
                    <td><?php echo $transaction['phone']; ?></td>
                    <td><?php echo CURRENCY; ?> <?php echo number_format($transaction['amount'], 2); ?></td>
                    <td><?php echo $transaction['package_name']; ?></td>
                    <td><?php echo $transaction['mpesa_code']; ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $transaction['status']; ?>">
                            <?php echo $transaction['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>