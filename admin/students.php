<?php
$pageTitle = 'Student Management - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle search and filters
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';
$university = $_GET['university'] ?? '';
$orderBy = $_GET['order'] ?? 'newest';

$where = ["is_admin = FALSE"];
$params = [];

if ($search) {
    $where[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ? OR student_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($department) {
    $where[] = "department = ?";
    $params[] = $department;
}

if ($university) {
    $where[] = "university LIKE ?";
    $params[] = "%$university%";
}

$orderQuery = $orderBy === 'oldest' ? 'ORDER BY created_at ASC' : 'ORDER BY created_at DESC';

$query = "SELECT * FROM users WHERE " . implode(' AND ', $where) . " $orderQuery";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get unique universities for filter
$universitiesStmt = $pdo->query("SELECT DISTINCT university FROM users WHERE university IS NOT NULL AND university != '' ORDER BY university");
$universities = $universitiesStmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Admin Panel</a></li>
                <li class="breadcrumb-item active">Student Management</li>
            </ol>
        </nav>
        
        <h2>Student Management</h2>
        <p class="text-muted">Manage student profiles and view their activities</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Students</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Name, email, username, student ID..." value="<?php echo htmlspecialchars($search); ?>">
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
            
            <div class="col-md-3">
                <label for="university" class="form-label">University</label>
                <select class="form-select" id="university" name="university">
                    <option value="">All Universities</option>
                    <?php foreach ($universities as $uni): ?>
                        <?php if ($uni['university']): ?>
                            <option value="<?php echo htmlspecialchars($uni['university']); ?>" 
                                    <?php echo $university === $uni['university'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($uni['university']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="order" class="form-label">Order By</label>
                <select class="form-select" id="order" name="order">
                    <option value="newest" <?php echo $orderBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $orderBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Students List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Students (<?php echo count($students); ?>)</h5>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
    </div>
    <div class="card-body">
        <?php if (empty($students)): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No students found</h5>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>Department</th>
                            <th>University</th>
                            <th>Joined</th>
                            <th>Uploads</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                            // Get upload count for this student
                            $uploadStmt = $pdo->prepare("SELECT COUNT(*) FROM uploads WHERE user_id = ?");
                            $uploadStmt->execute([$student['id']]);
                            $uploadCount = $uploadStmt->fetchColumn();
                            ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            @<?php echo htmlspecialchars($student['username']); ?>
                                            <?php if ($student['student_id']): ?>
                                                | ID: <?php echo htmlspecialchars($student['student_id']); ?>
                                            <?php endif; ?>
                                        </small>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $student['department'] === 'IT' ? 'primary' : 
                                            ($student['department'] === 'Business Management' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo htmlspecialchars($student['department']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($student['university'] ?? 'Not specified'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $uploadCount; ?> uploads</span>
                                </td>
                                <td>
                                    <a href="student_details.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View Details
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

<?php require_once '../includes/footer.php'; ?>
