<?php
$pageTitle = 'Dashboard - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$userUploads = getUploadsByUser($_SESSION['user_id'], 5);

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_uploads FROM uploads WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalUploads = $stmt->fetch()['total_uploads'];

$stmt = $pdo->prepare("SELECT COUNT(*) as approved_uploads FROM uploads WHERE user_id = ? AND approval_status = 'approved'");
$stmt->execute([$_SESSION['user_id']]);
$approvedUploads = $stmt->fetch()['approved_uploads'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_uploads FROM uploads WHERE user_id = ? AND approval_status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$pendingUploads = $stmt->fetch()['pending_uploads'];

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        <p class="text-muted">Department: <?php echo htmlspecialchars($user['department']); ?></p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Uploads</h5>
                        <h2><?php echo $totalUploads; ?></h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-upload fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Approved</h5>
                        <h2><?php echo $approvedUploads; ?></h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Pending</h5>
                        <h2><?php echo $pendingUploads; ?></h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Quick Upload</h5>
                        <a href="upload.php" class="btn btn-light btn-sm mt-2">Upload Now</a>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-plus-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Uploads</h5>
                <a href="profile.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($userUploads)): ?>
                    <p class="text-muted text-center py-3">No uploads yet. <a href="upload.php">Start uploading</a> your first material!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userUploads as $upload): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($upload['title']); ?></td>
                                        <td><?php echo htmlspecialchars($upload['department']); ?></td>
                                        <td><?php echo htmlspecialchars($upload['category']); ?></td>
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
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="upload.php" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Material
                    </a>
                    <a href="departments.php" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> Browse Materials
                    </a>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="fas fa-user"></i> View Profile
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php" class="btn btn-outline-danger">
                            <i class="fas fa-cog"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Department Info</h6>
            </div>
            <div class="card-body">
                <p><strong>Your Department:</strong><br><?php echo htmlspecialchars($user['department']); ?></p>
                <?php if ($user['university']): ?>
                    <p><strong>University:</strong><br><?php echo htmlspecialchars($user['university']); ?></p>
                <?php endif; ?>
                <?php if ($user['student_id']): ?>
                    <p><strong>Student ID:</strong><br><?php echo htmlspecialchars($user['student_id']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
