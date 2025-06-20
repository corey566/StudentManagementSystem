<?php
$pageTitle = 'Student Details - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: students.php');
    exit();
}

$studentId = intval($_GET['id']);

// Get student details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_admin = FALSE");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: students.php');
    exit();
}

// Get student uploads
$uploadsStmt = $pdo->prepare("SELECT * FROM uploads WHERE user_id = ? ORDER BY created_at DESC");
$uploadsStmt->execute([$studentId]);
$uploads = $uploadsStmt->fetchAll();

// Get statistics
$totalUploads = count($uploads);
$approvedUploads = count(array_filter($uploads, fn($u) => $u['approval_status'] === 'approved'));
$pendingUploads = count(array_filter($uploads, fn($u) => $u['approval_status'] === 'pending'));
$rejectedUploads = count(array_filter($uploads, fn($u) => $u['approval_status'] === 'rejected'));

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Admin Panel</a></li>
                <li class="breadcrumb-item"><a href="students.php">Students</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($student['full_name']); ?></li>
            </ol>
        </nav>
        
        <h2>Student Details</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                    <h5 class="mt-2"><?php echo htmlspecialchars($student['full_name']); ?></h5>
                    <p class="text-muted">@<?php echo htmlspecialchars($student['username']); ?></p>
                </div>
                
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Student ID:</strong></td>
                        <td><?php echo htmlspecialchars($student['student_id'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $student['department'] === 'IT' ? 'primary' : 
                                    ($student['department'] === 'Business Management' ? 'success' : 'danger'); 
                            ?>">
                                <?php echo htmlspecialchars($student['department']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>University:</strong></td>
                        <td><?php echo htmlspecialchars($student['university'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Joined:</strong></td>
                        <td><?php echo date('F j, Y', strtotime($student['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Upload Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Upload Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-2">
                        <h4 class="text-primary"><?php echo $totalUploads; ?></h4>
                        <small>Total</small>
                    </div>
                    <div class="col-6 mb-2">
                        <h4 class="text-success"><?php echo $approvedUploads; ?></h4>
                        <small>Approved</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning"><?php echo $pendingUploads; ?></h4>
                        <small>Pending</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger"><?php echo $rejectedUploads; ?></h4>
                        <small>Rejected</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Uploaded Projects</h5>
                <a href="students.php" class="btn btn-sm btn-outline-secondary">Back to Students</a>
            </div>
            <div class="card-body">
                <?php if (empty($uploads)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No uploads yet</h5>
                        <p class="text-muted">This student hasn't uploaded any materials yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Project Title</th>
                                    <th>Module</th>
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
                                            <strong><?php echo htmlspecialchars($upload['title']); ?></strong>
                                            <?php if ($upload['description']): ?>
                                                <br><small class="text-muted">
                                                    <?php echo htmlspecialchars(substr($upload['description'], 0, 50)) . (strlen($upload['description']) > 50 ? '...' : ''); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($upload['module_type'] ?? $upload['module_name'] ?? 'N/A'); ?>
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
                                            <a href="project_details.php?id=<?php echo $upload['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
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
