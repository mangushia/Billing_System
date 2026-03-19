  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Get total count
$totalResult = $conn->query("SELECT COUNT(*) as count FROM transactions");
$totalRows = $totalResult->fetch_assoc()['count'];
$totalPages = ceil($totalRows / ITEMS_PER_PAGE);

// Get transactions
$transactions = $conn->query("SELECT t.*, p.name as package_name 
                              FROM transactions t 
                              LEFT JOIN packages p ON t.package_id = p.id 
                              ORDER BY t.created_at DESC 
                              LIMIT $offset, " . ITEMS_PER_PAGE);

include 'includes/admin-header.php';
?>

<div class="transactions">
    <h1>Transactions</h1>
    
    <div class="filters">
        <input type="text" id="search" placeholder="Search by phone or M-PESA code">
        <select id="status-filter">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
        </select>
        <input type="date" id="date-filter">
    </div>
    
    <table class="data-table" id="transactions-table">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Package</th>
                <th>M-PESA Code</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($transaction = $transactions->fetch_assoc()): ?>
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
                <td>
                    <button onclick="viewTransaction(<?php echo $transaction['id']; ?>)" 
                            class="btn-view">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" 
               class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Simple client-side filtering
document.getElementById('search').addEventListener('keyup', filterTable);
document.getElementById('status-filter').addEventListener('change', filterTable);
document.getElementById('date-filter').addEventListener('change', filterTable);

function filterTable() {
    var search = document.getElementById('search').value.toLowerCase();
    var status = document.getElementById('status-filter').value.toLowerCase();
    var date = document.getElementById('date-filter').value;
    
    var rows = document.querySelectorAll('#transactions-table tbody tr');
    
    rows.forEach(function(row) {
        var phone = row.cells[1].textContent.toLowerCase();
        var mpesaCode = row.cells[4].textContent.toLowerCase();
        var rowStatus = row.cells[5].textContent.toLowerCase().trim();
        var rowDate = row.cells[6].textContent;
        
        var show = true;
        
        if(search && !phone.includes(search) && !mpesaCode.includes(search)) {
            show = false;
        }
        
        if(status && rowStatus !== status) {
            show = false;
        }
        
        if(date && !rowDate.includes(date)) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}
</script>

<?php include 'includes/admin-footer.php'; ?>