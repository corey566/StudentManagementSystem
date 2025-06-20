<?php
$pageTitle = 'Upload Material - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$user = getCurrentUser();
$success = '';
$error = '';

if ($_POST && isset($_POST['upload_material'])) {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $department = $_POST['department'] ?? '';
    $module_type = $_POST['module_type'] ?? '';
    $module_name = sanitizeInput($_POST['module_name'] ?? '');
    $category = $_POST['category'] ?? '';
    
    // Validate required fields
    if (empty($title) || empty($department) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a file to upload.';
    } else {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];
        
        // Validate file size (max 50MB)
        if ($fileSize > 50 * 1024 * 1024) {
            $error = 'File size must be less than 50MB.';
        } else {
            // Generate unique filename
            $newFileName = generateFileName($fileName, $_SESSION['user_id']);
            $uploadPath = 'uploads/' . $newFileName;
            
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO uploads (user_id, title, description, department, module_type, module_name, category, file_name, file_path, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $title, $description, $department, $module_type, $module_name, $category, $fileName, $uploadPath, $fileSize])) {
                    $success = 'Material uploaded successfully! It will be reviewed by administrators before being published.';
                    // Reset form
                    $_POST = [];
                } else {
                    $error = 'Failed to save upload information.';
                    unlink($uploadPath); // Delete the uploaded file
                }
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Upload Academic Material</h4>
                <small class="text-muted">Share your knowledge with the academic community</small>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department *</label>
                            <select class="form-select" id="department" name="department" onchange="updateDepartmentOptions()" required>
                                <option value="">Select Department</option>
                                <option value="IT" <?php echo ($_POST['department'] ?? '') === 'IT' ? 'selected' : ''; ?>>Information Technology</option>
                                <option value="Business Management" <?php echo ($_POST['department'] ?? '') === 'Business Management' ? 'selected' : ''; ?>>Business Management</option>
                                <option value="Biomedical" <?php echo ($_POST['department'] ?? '') === 'Biomedical' ? 'selected' : ''; ?>>Biomedical</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="itModuleRow" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="module_type" class="form-label">Module Type</label>
                            <select class="form-select" id="module_type" name="module_type" onchange="updateITCategories()">
                                <option value="">Select Module Type</option>
                                <option value="Web Application" <?php echo ($_POST['module_type'] ?? '') === 'Web Application' ? 'selected' : ''; ?>>Web Application</option>
                                <option value="Mobile Application" <?php echo ($_POST['module_type'] ?? '') === 'Mobile Application' ? 'selected' : ''; ?>>Mobile Application</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="nonITModuleRow" style="display: none;">
                        <div class="col-md-6 mb-3">
                            <label for="module_name" class="form-label">Module</label>
                            <select class="form-select" id="module_name" name="module_name">
                                <option value="">Select Module</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Provide a detailed description of the material..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">File *</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <small class="text-muted">Supported formats: PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR. Maximum size: 50MB</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" name="upload_material" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Material
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Upload Guidelines</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><i class="fas fa-check text-success"></i> Ensure your content is original or properly cited</li>
                    <li><i class="fas fa-check text-success"></i> Use descriptive titles and detailed descriptions</li>
                    <li><i class="fas fa-check text-success"></i> Select the correct department and category</li>
                    <li><i class="fas fa-check text-success"></i> Files will be reviewed before publication</li>
                    <li><i class="fas fa-check text-success"></i> Respect copyright and academic integrity policies</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function updateDepartmentOptions() {
    const department = document.getElementById('department').value;
    const itModuleRow = document.getElementById('itModuleRow');
    const nonITModuleRow = document.getElementById('nonITModuleRow');
    const moduleNameSelect = document.getElementById('module_name');
    const categorySelect = document.getElementById('category');
    
    // Reset
    itModuleRow.style.display = 'none';
    nonITModuleRow.style.display = 'none';
    moduleNameSelect.innerHTML = '<option value="">Select Module</option>';
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    if (department === 'IT') {
        itModuleRow.style.display = 'block';
        updateITCategories();
    } else if (department === 'Business Management') {
        nonITModuleRow.style.display = 'block';
        moduleNameSelect.innerHTML += '<option value="Marketing Management">Marketing Management</option>';
        moduleNameSelect.innerHTML += '<option value="Business Law">Business Law</option>';
        moduleNameSelect.innerHTML += '<option value="Financial Accounting">Financial Accounting</option>';
        
        categorySelect.innerHTML += '<option value="Lecture Notes">Lecture Notes</option>';
        categorySelect.innerHTML += '<option value="Assignment">Assignment</option>';
        categorySelect.innerHTML += '<option value="Research Paper">Research Paper</option>';
        categorySelect.innerHTML += '<option value="Past Paper">Past Paper</option>';
    } else if (department === 'Biomedical') {
        nonITModuleRow.style.display = 'block';
        moduleNameSelect.innerHTML += '<option value="Human Anatomy">Human Anatomy</option>';
        moduleNameSelect.innerHTML += '<option value="Biomedical Instrumentation">Biomedical Instrumentation</option>';
        moduleNameSelect.innerHTML += '<option value="Pharmacology">Pharmacology</option>';
        
        categorySelect.innerHTML += '<option value="Lecture Notes">Lecture Notes</option>';
        categorySelect.innerHTML += '<option value="Assignment">Assignment</option>';
        categorySelect.innerHTML += '<option value="Research Paper">Research Paper</option>';
        categorySelect.innerHTML += '<option value="Past Paper">Past Paper</option>';
        categorySelect.innerHTML += '<option value="Lab Report">Lab Report</option>';
    }
}

function updateITCategories() {
    const moduleType = document.getElementById('module_type').value;
    const categorySelect = document.getElementById('category');
    
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    if (moduleType === 'Web Application') {
        categorySelect.innerHTML += '<option value="Backend">Backend</option>';
        categorySelect.innerHTML += '<option value="Frontend">Frontend</option>';
        categorySelect.innerHTML += '<option value="Full Stack">Full Stack</option>';
        categorySelect.innerHTML += '<option value="Styling (CSS)">Styling (CSS)</option>';
    } else if (moduleType === 'Mobile Application') {
        categorySelect.innerHTML += '<option value="Backend">Backend</option>';
        categorySelect.innerHTML += '<option value="Native">Native</option>';
        categorySelect.innerHTML += '<option value="Full Stack">Full Stack</option>';
        categorySelect.innerHTML += '<option value="Cross Platform">Cross Platform</option>';
    }
}

// Restore form state if there was an error
document.addEventListener('DOMContentLoaded', function() {
    updateDepartmentOptions();
    
    // Restore selected values
    const savedModuleType = '<?php echo $_POST['module_type'] ?? ''; ?>';
    const savedModuleName = '<?php echo $_POST['module_name'] ?? ''; ?>';
    const savedCategory = '<?php echo $_POST['category'] ?? ''; ?>';
    
    if (savedModuleType) {
        document.getElementById('module_type').value = savedModuleType;
        updateITCategories();
    }
    
    if (savedModuleName) {
        document.getElementById('module_name').value = savedModuleName;
    }
    
    if (savedCategory) {
        document.getElementById('category').value = savedCategory;
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
