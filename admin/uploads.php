<?php
$pageTitle = 'Manage Uploads - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle search and filters
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';
$status = $_GET['status'] ?? '';
$orderBy = $_GET['order'] ?? 'newest';

$where = ["1=1"];
$params = [];

if ($search) {
    $where[] = "(u.title LIKE ? OR u.description LIKE ? OR us.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($department) {
    $where[] = "u.department = ?";
    $params[] = $department;
}

if ($status) {
    $where[] = "u.approval_status = ?";
    $params[] = $status;
}

$orderQuery = $orderBy === 'oldest' ? 'ORDER BY u.created_at ASC' : 'ORDER BY u.created_at DESC';

$query = "SELECT u.*, us.full_name as uploader_name, us.email as uploader_email 
          FROM uploads u 
          JOIN users us ON u.user_id = us.id 
          WHERE " . implode(' AND ', $where) . " $orderQuery";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$uploads = $stmt->fetchAll();

// Handle bulk actions
if ($_POST && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selectedIds = $_POST['selected_uploads'] ?? [];
    
    if (!empty($selectedIds) && in_array($action, ['approve', 'reject', 'pending'])) {
        $statusMap = [
            'approve' => 'approved',
            'reject' => 'rejected',
            'pending' => 'pending'
        ];
        
        $newStatus = $statusMap[$action];
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        
        $stmt = $pdo->prepare("UPDATE uploads SET approval_status = ?, approved_by = ? WHERE id IN ($placeholders)");
        $params = [$newStatus, $_SESSION['user_id']];
        $params = array_merge($params, $selectedIds);
        
        if ($stmt->execute($params)) {
            $message = "Successfully updated " . count($selectedIds) . " uploads to " . $newStatus . " status.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Admin Panel</a></li>
                <li class="breadcrumb-item active">Manage Uploads</li>
            </ol>
        </nav>
        
        <h2>Manage Uploads</h2>
        <p class="text-muted">Review and approve student material uploads</p>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Uploads</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Title, description, uploader..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" id="department" name="department">
                    <option value="">All Departments</option>
                    <option value="IT" <?php echo $department === 'IT' ? 'selected' : ''; ?>>Information Technology</option>
                    <option value="Business Management" <?php echo $department === 'Business Management' ? 'selected' : ''; ?>>Business Management</option>
                    <option value="Biomedical" <?php echo $department === 'Biomedical' ? 'selected' : ''; ?>>Biomedical</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="order" class="form-label">Order By</label>
                <select class="form-select" id="order" name="order">
                    <option value="newest" <?php echo $orderBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $orderBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="uploads.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Uploads List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Uploads (<?php echo count($uploads); ?>)</h5>
        <div class="btn-group">
            <a href="index.php" class="btn btn-sm btn-outline-secondary">Dashboard</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($uploads)): ?>
            <div class="text-center py-5">
                <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No uploads found</h5>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <form method="POST" id="bulkForm">
                <!-- Bulk Actions -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <select class="form-select" name="bulk_action" id="bulkAction">
                                <option value="">Bulk Actions</option>
                                <option value="approve">Approve Selected</option>
                                <option value="reject">Reject Selected</option>
                                <option value="pending">Mark as Pending</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary" onclick="return confirmBulkAction()">
                                Apply
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll()">
                            Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectNone()">
                            Select None
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                                </th>
                                <th>Upload Details</th>
                                <th>Uploader</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($uploads as $upload): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_uploads[]" value="<?php echo $upload['id']; ?>" class="upload-checkbox">
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($upload['title']); ?></strong>
                                            <?php if ($upload['description']): ?>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($upload['description'], 0, 60)) . (strlen($upload['description']) > 60 ? '...' : ''); ?>
                                                </small>
                                            <?php endif; ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-file"></i> <?php echo htmlspecialchars($upload['file_name']); ?>
                                                <?php if ($upload['file_size']): ?>
                                                    (<?php echo formatFileSize($upload['file_size']); ?>)
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($upload['uploader_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($upload['uploader_email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $upload['department'] === 'IT' ? 'primary' : 
                                                ($upload['department'] === 'Business Management' ? 'success' : 'danger'); 
                                        ?>">
                                            <?php echo htmlspecialchars($upload['department']); ?>
                                        </span>
                                        <?php if ($upload['module_type']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($upload['module_type']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($upload['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass[$upload['approval_status']]; ?>">
                                            <?php echo ucfirst($upload['approval_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($upload['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="project_details.php?id=<?php echo $upload['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if ($upload['approval_status'] === 'pending'): ?>
                                                <button type="button" class="btn btn-success btn-sm" onclick="quickApprove(<?php echo $upload['id']; ?>)">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" onclick="quickReject(<?php echo $upload['id']; ?>)">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAll() {
    const masterCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.upload-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });
}

function selectAll() {
    document.querySelectorAll('.upload-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function selectNone() {
    document.querySelectorAll('.upload-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function confirmBulkAction() {
    const action = document.getElementById('bulkAction').value;
    const checkedBoxes = document.querySelectorAll('.upload-checkbox:checked');
    
    if (!action) {
        alert('Please select an action.');
        return false;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Please select at least one upload.');
        return false;
    }
    
    return confirm(`Are you sure you want to ${action} ${checkedBoxes.length} selected upload(s)?`);
}

function quickApprove(uploadId) {
    if (confirm('Are you sure you want to approve this upload?')) {
        fetch('../api/admin_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                upload_id: uploadId,
                status: 'approved'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function quickReject(uploadId) {
    if (confirm('Are you sure you want to reject this upload?')) {
        fetch('../api/admin_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                upload_id: uploadId,
                status: 'rejected'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
