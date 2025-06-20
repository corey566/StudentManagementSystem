<?php
$pageTitle = 'Admin Dashboard - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

// Get statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = FALSE")->fetchColumn();
$totalUploads = $pdo->query("SELECT COUNT(*) FROM uploads")->fetchColumn();
$pendingUploads = $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'pending'")->fetchColumn();
$approvedUploads = $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'approved'")->fetchColumn();
$rejectedUploads = $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'rejected'")->fetchColumn();
$totalDownloads = $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn();

// Recent activity
$recentUploads = $pdo->query("SELECT u.*, us.full_name as uploader_name FROM uploads u JOIN users us ON u.user_id = us.id ORDER BY u.created_at DESC LIMIT 5")->fetchAll();
$recentUsers = $pdo->query("SELECT * FROM users WHERE is_admin = FALSE ORDER BY created_at DESC LIMIT 5")->fetchAll();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Admin Panel</li>
            </ol>
        </nav>
        
        <h2>Admin Dashboard</h2>
        <p class="text-muted">System overview and management</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Users</h6>
                        <h3><?php echo $totalUsers; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Uploads</h6>
                        <h3><?php echo $totalUploads; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-upload fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Pending</h6>
                        <h3><?php echo $pendingUploads; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Approved</h6>
                        <h3><?php echo $approvedUploads; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Rejected</h6>
                        <h3><?php echo $rejectedUploads; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-2 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Downloads</h6>
                        <h3><?php echo $totalDownloads; ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-download fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Menu -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Admin Menu</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="students.php" class="btn btn-outline-primary w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-user-graduate fa-2x mb-2"></i>
                            <span>Student Management</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="uploads.php" class="btn btn-outline-success w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-file-upload fa-2x mb-2"></i>
                            <span>Manage Uploads</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="manage.php" class="btn btn-outline-info w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-cog fa-2x mb-2"></i>
                            <span>System Management</span>
                        </a>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <a href="../logout.php" class="btn btn-outline-danger w-100 h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-sign-out-alt fa-2x mb-2"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Uploads</h5>
                <a href="uploads.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUploads)): ?>
                    <p class="text-muted text-center">No uploads yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Uploader</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUploads as $upload): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(substr($upload['title'], 0, 30)) . (strlen($upload['title']) > 30 ? '...' : ''); ?></td>
                                        <td><?php echo htmlspecialchars($upload['uploader_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $upload['approval_status'] === 'pending' ? 'warning' : 
                                                    ($upload['approval_status'] === 'approved' ? 'success' : 'danger'); 
                                            ?>">
                                                <?php echo ucfirst($upload['approval_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j', strtotime($upload['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Users</h5>
                <a href="students.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentUsers)): ?>
                    <p class="text-muted text-center">No users registered yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['department']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('M j', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
