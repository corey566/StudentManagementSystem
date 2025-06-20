<?php
$pageTitle = 'Project Details - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: uploads.php');
    exit();
}

$uploadId = intval($_GET['id']);

// Get upload details with user information
$stmt = $pdo->prepare("SELECT u.*, us.full_name as uploader_name, us.email as uploader_email, us.student_id, us.university FROM uploads u JOIN users us ON u.user_id = us.id WHERE u.id = ?");
$stmt->execute([$uploadId]);
$upload = $stmt->fetch();

if (!$upload) {
    header('Location: uploads.php');
    exit();
}

$success = '';
$error = '';

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    $newStatus = $_POST['approval_status'] ?? '';
    
    if (in_array($newStatus, ['pending', 'approved', 'rejected'])) {
        $stmt = $pdo->prepare("UPDATE uploads SET approval_status = ?, approved_by = ? WHERE id = ?");
        if ($stmt->execute([$newStatus, $_SESSION['user_id'], $uploadId])) {
            $success = "Upload status updated to " . ucfirst($newStatus);
            $upload['approval_status'] = $newStatus;
        } else {
            $error = "Failed to update status.";
        }
    } else {
        $error = "Invalid status selected.";
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
                <li class="breadcrumb-item"><a href="uploads.php">Uploads</a></li>
                <li class="breadcrumb-item active">Project Details</li>
            </ol>
        </nav>
        
        <h2>Project Details</h2>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Project Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="200"><strong>Project Title:</strong></td>
                        <td><?php echo htmlspecialchars($upload['title']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>University:</strong></td>
                        <td><?php echo htmlspecialchars($upload['university'] ?? 'Not specified'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $upload['department'] === 'IT' ? 'primary' : 
                                    ($upload['department'] === 'Business Management' ? 'success' : 'danger'); 
                            ?>">
                                <?php echo htmlspecialchars($upload['department']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php if ($upload['module_type']): ?>
                    <tr>
                        <td><strong>Module Type:</strong></td>
                        <td><?php echo htmlspecialchars($upload['module_type']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($upload['module_name']): ?>
                    <tr>
                        <td><strong>Module Name:</strong></td>
                        <td><?php echo htmlspecialchars($upload['module_name']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td><strong>Student Material:</strong></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($upload['category']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Updated On:</strong></td>
                        <td><?php echo date('F j, Y \a\t g:i A', strtotime($upload['updated_at'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong>File Name:</strong></td>
                        <td><?php echo htmlspecialchars($upload['file_name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>File Size:</strong></td>
                        <td><?php echo $upload['file_size'] ? formatFileSize($upload['file_size']) : 'Unknown'; ?></td>
                    </tr>
                </table>
                
                <?php if ($upload['description']): ?>
                <div class="mt-4">
                    <h6>Description:</h6>
                    <div class="border p-3 rounded bg-light">
                        <?php echo nl2br(htmlspecialchars($upload['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h6>File Actions:</h6>
                    <div class="btn-group">
                        <?php if (file_exists($upload['file_path'])): ?>
                            <a href="../download.php?id=<?php echo $upload['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        <?php else: ?>
                            <button class="btn btn-outline-danger" disabled>
                                <i class="fas fa-exclamation-triangle"></i> File Not Found
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Uploader Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Uploader Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-3x text-muted"></i>
                    <h6 class="mt-2"><?php echo htmlspecialchars($upload['uploader_name']); ?></h6>
                </div>
                
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($upload['uploader_email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Student ID:</strong></td>
                        <td><?php echo htmlspecialchars($upload['student_id'] ?? 'Not provided'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td><?php echo htmlspecialchars($upload['department']); ?></td>
                    </tr>
                </table>
                
                <div class="text-center">
                    <a href="student_details.php?id=<?php echo $upload['user_id']; ?>" class="btn btn-sm btn-outline-primary">
                        View Full Profile
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Approval Status -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Approval Status</h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="approval_status" class="form-label">Status</label>
                        <select class="form-select" id="approval_status" name="approval_status">
                            <option value="pending" <?php echo $upload['approval_status'] === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                            <option value="approved" <?php echo $upload['approval_status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $upload['approval_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Current Status: 
                        <span class="badge bg-<?php 
                            echo $upload['approval_status'] === 'pending' ? 'warning' : 
                                ($upload['approval_status'] === 'approved' ? 'success' : 'danger'); 
                        ?>">
                            <?php echo ucfirst($upload['approval_status']); ?>
                        </span>
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="uploads.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Uploads
                    </a>
                    <a href="student_details.php?id=<?php echo $upload['user_id']; ?>" class="btn btn-outline-info">
                        <i class="fas fa-user"></i> Student Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
