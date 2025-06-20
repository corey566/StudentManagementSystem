<?php
$pageTitle = 'Profile - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$userUploads = getUploadsByUser($_SESSION['user_id']);

$success = '';
$error = '';

if ($_POST && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $student_id = sanitizeInput($_POST['student_id'] ?? '');
    $university = sanitizeInput($_POST['university'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required.';
    } else {
        // Check if email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, student_id = ?, university = ? WHERE id = ?");
            if ($stmt->execute([$full_name, $email, $student_id, $university, $_SESSION['user_id']])) {
                $success = 'Profile updated successfully!';
                $_SESSION['full_name'] = $full_name;
                $user = getCurrentUser(); // Refresh user data
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" 
                               value="<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="university" class="form-label">University</label>
                        <input type="text" class="form-control" id="university" name="university" 
                               value="<?php echo htmlspecialchars($user['university'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['department']); ?>" disabled>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Uploads</h5>
                <a href="upload.php" class="btn btn-sm btn-primary">New Upload</a>
            </div>
            <div class="card-body">
                <?php if (empty($userUploads)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-upload fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No uploads yet</h5>
                        <p class="text-muted">Start sharing your academic materials with the community.</p>
                        <a href="upload.php" class="btn btn-primary">Upload Your First Material</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Department</th>
                                    <th>Module</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Uploaded</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userUploads as $upload): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($upload['title']); ?></strong>
                                            <?php if ($upload['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($upload['description'], 0, 50)) . (strlen($upload['description']) > 50 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($upload['department']); ?></td>
                                        <td>
                                            <?php if ($upload['module_type']): ?>
                                                <?php echo htmlspecialchars($upload['module_type']); ?><br>
                                            <?php endif; ?>
                                            <?php if ($upload['module_name']): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($upload['module_name']); ?></small>
                                            <?php endif; ?>
                                        </td>
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
                                        <td><?php echo $upload['file_size'] ? formatFileSize($upload['file_size']) : 'N/A'; ?></td>
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

<?php require_once 'includes/footer.php'; ?>
