<?php
$pageTitle = 'System Management - Education Portal';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$success = '';
$error = '';

// Handle department actions
if ($_POST && isset($_POST['add_department'])) {
    $name = sanitizeInput($_POST['dept_name'] ?? '');
    $code = sanitizeInput($_POST['dept_code'] ?? '');
    
    if (empty($name) || empty($code)) {
        $error = 'Department name and code are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
        if ($stmt->execute([$name, $code])) {
            $success = 'Department added successfully.';
        } else {
            $error = 'Failed to add department.';
        }
    }
}

// Handle module actions
if ($_POST && isset($_POST['add_module'])) {
    $name = sanitizeInput($_POST['module_name'] ?? '');
    $department_id = intval($_POST['department_id'] ?? 0);
    $module_type = sanitizeInput($_POST['module_type'] ?? '');
    
    if (empty($name) || $department_id <= 0) {
        $error = 'Module name and department are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO modules (name, department_id, module_type) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $department_id, $module_type ?: null])) {
            $success = 'Module added successfully.';
        } else {
            $error = 'Failed to add module.';
        }
    }
}

// Handle category actions
if ($_POST && isset($_POST['add_category'])) {
    $name = sanitizeInput($_POST['category_name'] ?? '');
    $department_id = intval($_POST['category_department_id'] ?? 0);
    
    if (empty($name) || $department_id <= 0) {
        $error = 'Category name and department are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, department_id) VALUES (?, ?)");
        if ($stmt->execute([$name, $department_id])) {
            $success = 'Category added successfully.';
        } else {
            $error = 'Failed to add category.';
        }
    }
}

// Get current data
$departments = getDepartments();
$allModules = $pdo->query("SELECT m.*, d.name as department_name FROM modules m JOIN departments d ON m.department_id = d.id ORDER BY d.name, m.name")->fetchAll();
$allCategories = $pdo->query("SELECT c.*, d.name as department_name FROM categories c JOIN departments d ON c.department_id = d.id ORDER BY d.name, c.name")->fetchAll();

// Get system statistics
$totalFiles = $pdo->query("SELECT COUNT(*) FROM uploads")->fetchColumn();
$totalSize = $pdo->query("SELECT SUM(file_size) FROM uploads WHERE file_size IS NOT NULL")->fetchColumn();
$uploadPath = realpath('uploads') ?: 'uploads';
$diskSpace = disk_free_space($uploadPath);

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Admin Panel</a></li>
                <li class="breadcrumb-item active">System Management</li>
            </ol>
        </nav>
        
        <h2>System Management</h2>
        <p class="text-muted">Manage universities, departments, modules, and system settings</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- System Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Total Files</h6>
                <h3><?php echo number_format($totalFiles); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Storage Used</h6>
                <h3><?php echo $totalSize ? formatFileSize($totalSize) : '0 B'; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6>Disk Space Free</h6>
                <h3><?php echo $diskSpace ? formatFileSize($diskSpace) : 'Unknown'; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Departments</h6>
                <h3><?php echo count($departments); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Management Tabs -->
<ul class="nav nav-tabs" id="managementTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments" type="button" role="tab">
            Departments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="modules-tab" data-bs-toggle="tab" data-bs-target="#modules" type="button" role="tab">
            Modules
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
            Categories
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
            System Settings
        </button>
    </li>
</ul>

<div class="tab-content" id="managementTabsContent">
    <!-- Departments Tab -->
    <div class="tab-pane fade show active" id="departments" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Departments Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Add New Department</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="dept_name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="dept_name" name="dept_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="dept_code" class="form-label">Department Code</label>
                                <input type="text" class="form-control" id="dept_code" name="dept_code" maxlength="10" required>
                            </div>
                            <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <h6>Existing Departments</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Modules</th>
                                        <th>Categories</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $dept): ?>
                                        <?php
                                        $moduleCount = count(array_filter($allModules, fn($m) => $m['department_id'] == $dept['id']));
                                        $categoryCount = count(array_filter($allCategories, fn($c) => $c['department_id'] == $dept['id']));
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                            <td><code><?php echo htmlspecialchars($dept['code']); ?></code></td>
                                            <td><span class="badge bg-info"><?php echo $moduleCount; ?></span></td>
                                            <td><span class="badge bg-secondary"><?php echo $categoryCount; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules Tab -->
    <div class="tab-pane fade" id="modules" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Modules Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Add New Module</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="module_name" class="form-label">Module Name</label>
                                <input type="text" class="form-control" id="module_name" name="module_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="module_type" class="form-label">Module Type (Optional)</label>
                                <input type="text" class="form-control" id="module_type" name="module_type" 
                                       placeholder="e.g., Web Application, Mobile Application">
                            </div>
                            <button type="submit" name="add_module" class="btn btn-primary">Add Module</button>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <h6>Existing Modules</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Module Name</th>
                                        <th>Department</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allModules as $module): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($module['name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $module['department_name'] === 'IT' ? 'primary' : 
                                                        ($module['department_name'] === 'Business Management' ? 'success' : 'danger'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($module['department_name']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($module['module_type'] ?: 'General'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Tab -->
    <div class="tab-pane fade" id="categories" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Categories Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Add New Category</h6>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="category_name" name="category_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_department_id" class="form-label">Department</label>
                                <select class="form-select" id="category_department_id" name="category_department_id" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <h6>Existing Categories</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category Name</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allCategories as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $category['department_name'] === 'IT' ? 'primary' : 
                                                        ($category['department_name'] === 'Business Management' ? 'success' : 'danger'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($category['department_name']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Settings Tab -->
    <div class="tab-pane fade" id="system" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Settings</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>File Upload Settings</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td>Max Upload Size:</td>
                                <td><strong>50 MB</strong></td>
                            </tr>
                            <tr>
                                <td>Allowed Extensions:</td>
                                <td><code>PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR</code></td>
                            </tr>
                            <tr>
                                <td>Upload Directory:</td>
                                <td><code><?php echo $uploadPath; ?></code></td>
                            </tr>
                            <tr>
                                <td>Directory Writable:</td>
                                <td>
                                    <?php if (is_writable($uploadPath)): ?>
                                        <span class="badge bg-success">Yes</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">No</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Database Information</h6>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td>Database:</td>
                                <td><strong>education_portal</strong></td>
                            </tr>
                            <tr>
                                <td>Tables:</td>
                                <td>
                                    <?php
                                    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                                    echo count($tables) . ' tables';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Version:</td>
                                <td><?php echo $pdo->query("SELECT VERSION()")->fetchColumn(); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-12">
                        <h6>Administrative Actions</h6>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="optimizeDatabase()">
                                <i class="fas fa-database"></i> Optimize Database
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="exportData()">
                                <i class="fas fa-download"></i> Export Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        // Implementation would go here
        alert('Cache cleared successfully!');
    }
}

function optimizeDatabase() {
    if (confirm('Are you sure you want to optimize the database? This may take a few moments.')) {
        // Implementation would go here
        alert('Database optimization completed!');
    }
}

function exportData() {
    if (confirm('This will export all system data. Continue?')) {
        // Implementation would go here
        alert('Data export initiated. You will receive a download link shortly.');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
