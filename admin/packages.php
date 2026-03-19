  
<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Handle package creation/editing
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        if($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
            $name = sanitizeInput($_POST['name']);
            $price = sanitizeInput($_POST['price']);
            $speed = sanitizeInput($_POST['speed']);
            $duration = sanitizeInput($_POST['duration']);
            $duration_unit = sanitizeInput($_POST['duration_unit']);
            $data_limit = sanitizeInput($_POST['data_limit']);
            $description = sanitizeInput($_POST['description']);
            
            if($_POST['action'] == 'add') {
                $stmt = $conn->prepare("INSERT INTO packages (name, price, speed, duration, duration_unit, data_limit, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdiisis", $name, $price, $speed, $duration, $duration_unit, $data_limit, $description);
            } else {
                $id = sanitizeInput($_POST['id']);
                $stmt = $conn->prepare("UPDATE packages SET name=?, price=?, speed=?, duration=?, duration_unit=?, data_limit=?, description=? WHERE id=?");
                $stmt->bind_param("sdiisisi", $name, $price, $speed, $duration, $duration_unit, $data_limit, $description, $id);
            }
            
            if($stmt->execute()) {
                $success = "Package saved successfully";
            } else {
                $error = "Error saving package";
            }
        } elseif($_POST['action'] == 'delete') {
            $id = sanitizeInput($_POST['id']);
            $stmt = $conn->prepare("DELETE FROM packages WHERE id=?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                $success = "Package deleted successfully";
            } else {
                $error = "Error deleting package";
            }
        }
    }
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY price ASC");

include 'includes/admin-header.php';
?>

<div class="packages-management">
    <h1>Manage Packages</h1>
    
    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <button class="btn-primary" onclick="showAddPackageForm()">
        <i class="fas fa-plus"></i> Add New Package
    </button>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price (KSh)</th>
                <th>Speed (Mbps)</th>
                <th>Duration</th>
                <th>Data Limit</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($package = $packages->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($package['name']); ?></td>
                <td><?php echo number_format($package['price'], 2); ?></td>
                <td><?php echo $package['speed']; ?></td>
                <td><?php echo $package['duration'] . ' ' . $package['duration_unit']; ?></td>
                <td><?php echo $package['data_limit']; ?> GB</td>
                <td>
                    <span class="status-badge status-<?php echo $package['status']; ?>">
                        <?php echo $package['status']; ?>
                    </span>
                </td>
                <td>
                    <button onclick="editPackage(<?php echo htmlspecialchars(json_encode($package)); ?>)" 
                            class="btn-edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deletePackage(<?php echo $package['id']; ?>)" 
                            class="btn-delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Package Form Modal -->
<div id="packageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Add Package</h2>
        
        <form method="POST" id="packageForm">
            <input type="hidden" name="action" id="action" value="add">
            <input type="hidden" name="id" id="package_id">
            
            <div class="form-group">
                <label for="name">Package Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price (KSh)</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="speed">Speed (Mbps)</label>
                <input type="number" id="speed" name="speed" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="number" id="duration" name="duration" required>
                </div>
                
                <div class="form-group">
                    <label for="duration_unit">Unit</label>
                    <select id="duration_unit" name="duration_unit" required>
                        <option value="minutes">Minutes</option>
                        <option value="hours">Hours</option>
                        <option value="days">Days</option>
                        <option value="months">Months</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="data_limit">Data Limit (GB)</label>
                <input type="number" id="data_limit" name="data_limit" step="0.1" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            
            <button type="submit" class="btn-primary">Save Package</button>
        </form>
    </div>
</div>

<script>
function showAddPackageForm() {
    document.getElementById('modalTitle').textContent = 'Add Package';
    document.getElementById('action').value = 'add';
    document.getElementById('packageForm').reset();
    document.getElementById('packageModal').style.display = 'block';
}

function editPackage(package) {
    document.getElementById('modalTitle').textContent = 'Edit Package';
    document.getElementById('action').value = 'edit';
    document.getElementById('package_id').value = package.id;
    document.getElementById('name').value = package.name;
    document.getElementById('price').value = package.price;
    document.getElementById('speed').value = package.speed;
    document.getElementById('duration').value = package.duration;
    document.getElementById('duration_unit').value = package.duration_unit;
    document.getElementById('data_limit').value = package.data_limit;
    document.getElementById('description').value = package.description;
    document.getElementById('packageModal').style.display = 'block';
}

function deletePackage(id) {
    if(confirm('Are you sure you want to delete this package?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/admin-footer.php'; ?>