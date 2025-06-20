<?php
$pageTitle = 'Departments - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$selectedDept = $_GET['dept'] ?? '';
$selectedModuleType = $_GET['module_type'] ?? '';
$selectedCategory = $_GET['category'] ?? '';
$searchTerm = $_GET['search'] ?? '';
$orderBy = $_GET['order'] ?? 'newest';

$filters = [];
if ($selectedDept) $filters['department'] = $selectedDept;
if ($selectedModuleType) $filters['module_type'] = $selectedModuleType;
if ($selectedCategory) $filters['category'] = $selectedCategory;
if ($searchTerm) $filters['search'] = $searchTerm;
if ($orderBy) $filters['order'] = $orderBy;

$materials = getApprovedUploads($filters);

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2>Browse Academic Materials</h2>
        <p class="text-muted">Discover and download approved academic resources from all departments</p>
    </div>
</div>

<!-- Department Cards -->
<?php if (!$selectedDept): ?>
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-primary">
            <div class="card-body text-center">
                <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Information Technology</h5>
                <p class="card-text">Web and Mobile Development resources</p>
                <a href="?dept=IT" class="btn btn-primary">Browse IT Materials</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-success">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                <h5 class="card-title">Business Management</h5>
                <p class="card-text">Marketing, Law, and Accounting materials</p>
                <a href="?dept=Business Management" class="btn btn-success">Browse Business Materials</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card h-100 border-danger">
            <div class="card-body text-center">
                <i class="fas fa-heartbeat fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Biomedical</h5>
                <p class="card-text">Anatomy, Instrumentation, and Pharmacology</p>
                <a href="?dept=Biomedical" class="btn btn-danger">Browse Biomedical Materials</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter"></i> Filter Materials
            <?php if ($selectedDept): ?>
                - <?php echo htmlspecialchars($selectedDept); ?> Department
            <?php endif; ?>
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="dept" class="form-label">Department</label>
                <select class="form-select" id="dept" name="dept" onchange="updateModuleOptions()">
                    <option value="">All Departments</option>
                    <option value="IT" <?php echo $selectedDept === 'IT' ? 'selected' : ''; ?>>Information Technology</option>
                    <option value="Business Management" <?php echo $selectedDept === 'Business Management' ? 'selected' : ''; ?>>Business Management</option>
                    <option value="Biomedical" <?php echo $selectedDept === 'Biomedical' ? 'selected' : ''; ?>>Biomedical</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="module_type" class="form-label">Module Type</label>
                <select class="form-select" id="module_type" name="module_type">
                    <option value="">All Types</option>
                    <?php if ($selectedDept === 'IT' || !$selectedDept): ?>
                        <option value="Web Application" <?php echo $selectedModuleType === 'Web Application' ? 'selected' : ''; ?>>Web Application</option>
                        <option value="Mobile Application" <?php echo $selectedModuleType === 'Mobile Application' ? 'selected' : ''; ?>>Mobile Application</option>
                    <?php endif; ?>
                    <?php if ($selectedDept === 'Business Management' || !$selectedDept): ?>
                        <option value="Marketing Management" <?php echo $selectedModuleType === 'Marketing Management' ? 'selected' : ''; ?>>Marketing Management</option>
                        <option value="Business Law" <?php echo $selectedModuleType === 'Business Law' ? 'selected' : ''; ?>>Business Law</option>
                        <option value="Financial Accounting" <?php echo $selectedModuleType === 'Financial Accounting' ? 'selected' : ''; ?>>Financial Accounting</option>
                    <?php endif; ?>
                    <?php if ($selectedDept === 'Biomedical' || !$selectedDept): ?>
                        <option value="Human Anatomy" <?php echo $selectedModuleType === 'Human Anatomy' ? 'selected' : ''; ?>>Human Anatomy</option>
                        <option value="Biomedical Instrumentation" <?php echo $selectedModuleType === 'Biomedical Instrumentation' ? 'selected' : ''; ?>>Biomedical Instrumentation</option>
                        <option value="Pharmacology" <?php echo $selectedModuleType === 'Pharmacology' ? 'selected' : ''; ?>>Pharmacology</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php if ($selectedDept === 'IT' || !$selectedDept): ?>
                        <option value="Backend" <?php echo $selectedCategory === 'Backend' ? 'selected' : ''; ?>>Backend</option>
                        <option value="Frontend" <?php echo $selectedCategory === 'Frontend' ? 'selected' : ''; ?>>Frontend</option>
                        <option value="Full Stack" <?php echo $selectedCategory === 'Full Stack' ? 'selected' : ''; ?>>Full Stack</option>
                        <option value="Styling (CSS)" <?php echo $selectedCategory === 'Styling (CSS)' ? 'selected' : ''; ?>>Styling (CSS)</option>
                        <option value="Native" <?php echo $selectedCategory === 'Native' ? 'selected' : ''; ?>>Native</option>
                        <option value="Cross Platform" <?php echo $selectedCategory === 'Cross Platform' ? 'selected' : ''; ?>>Cross Platform</option>
                    <?php endif; ?>
                    <?php if ($selectedDept !== 'IT'): ?>
                        <option value="Lecture Notes" <?php echo $selectedCategory === 'Lecture Notes' ? 'selected' : ''; ?>>Lecture Notes</option>
                        <option value="Assignment" <?php echo $selectedCategory === 'Assignment' ? 'selected' : ''; ?>>Assignment</option>
                        <option value="Research Paper" <?php echo $selectedCategory === 'Research Paper' ? 'selected' : ''; ?>>Research Paper</option>
                        <option value="Past Paper" <?php echo $selectedCategory === 'Past Paper' ? 'selected' : ''; ?>>Past Paper</option>
                        <?php if ($selectedDept === 'Biomedical' || !$selectedDept): ?>
                            <option value="Lab Report" <?php echo $selectedCategory === 'Lab Report' ? 'selected' : ''; ?>>Lab Report</option>
                        <?php endif; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Search titles and descriptions..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="order" class="form-label">Order By</label>
                <select class="form-select" id="order" name="order">
                    <option value="newest" <?php echo $orderBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $orderBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="departments.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Materials List -->
<div class="row">
    <div class="col-12">
        <?php if (empty($materials)): ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No materials found</h5>
                <p class="text-muted">Try adjusting your filters or search terms.</p>
                <?php if (!$selectedDept): ?>
                    <a href="departments.php?dept=IT" class="btn btn-primary me-2">Browse IT</a>
                    <a href="departments.php?dept=Business Management" class="btn btn-success me-2">Browse Business</a>
                    <a href="departments.php?dept=Biomedical" class="btn btn-danger">Browse Biomedical</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($materials as $material): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($material['department']); ?>
                                    <?php if ($material['module_type']): ?>
                                        - <?php echo htmlspecialchars($material['module_type']); ?>
                                    <?php endif; ?>
                                </small>
                                <span class="badge bg-<?php 
                                    echo $material['department'] === 'IT' ? 'primary' : 
                                        ($material['department'] === 'Business Management' ? 'success' : 'danger'); 
                                ?>">
                                    <?php echo htmlspecialchars($material['category']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($material['title']); ?></h6>
                                <?php if ($material['description']): ?>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($material['description'], 0, 100)) . (strlen($material['description']) > 100 ? '...' : ''); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <small class="text-muted d-block">
                                        By: <?php echo htmlspecialchars($material['uploader_name']); ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        Uploaded: <?php echo date('M j, Y', strtotime($material['created_at'])); ?>
                                    </small>
                                    <?php if ($material['file_size']): ?>
                                        <small class="text-muted d-block">
                                            Size: <?php echo formatFileSize($material['file_size']); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary" onclick="previewMaterial(<?php echo $material['id']; ?>)">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                    <a href="download.php?id=<?php echo $material['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Material Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadFromPreview">Download</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewMaterial(id) {
    fetch(`api/preview_material.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('previewContent').innerHTML = data.html;
                document.getElementById('downloadFromPreview').onclick = () => {
                    window.location.href = `download.php?id=${id}`;
                };
                new bootstrap.Modal(document.getElementById('previewModal')).show();
            } else {
                alert('Failed to load preview: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load preview');
        });
}

function updateModuleOptions() {
    const dept = document.getElementById('dept').value;
    const moduleSelect = document.getElementById('module_type');
    const categorySelect = document.getElementById('category');
    
    // Clear current options
    moduleSelect.innerHTML = '<option value="">All Types</option>';
    categorySelect.innerHTML = '<option value="">All Categories</option>';
    
    if (dept === 'IT') {
        moduleSelect.innerHTML += '<option value="Web Application">Web Application</option>';
        moduleSelect.innerHTML += '<option value="Mobile Application">Mobile Application</option>';
        
        categorySelect.innerHTML += '<option value="Backend">Backend</option>';
        categorySelect.innerHTML += '<option value="Frontend">Frontend</option>';
        categorySelect.innerHTML += '<option value="Full Stack">Full Stack</option>';
        categorySelect.innerHTML += '<option value="Styling (CSS)">Styling (CSS)</option>';
        categorySelect.innerHTML += '<option value="Native">Native</option>';
        categorySelect.innerHTML += '<option value="Cross Platform">Cross Platform</option>';
    } else if (dept === 'Business Management') {
        moduleSelect.innerHTML += '<option value="Marketing Management">Marketing Management</option>';
        moduleSelect.innerHTML += '<option value="Business Law">Business Law</option>';
        moduleSelect.innerHTML += '<option value="Financial Accounting">Financial Accounting</option>';
        
        categorySelect.innerHTML += '<option value="Lecture Notes">Lecture Notes</option>';
        categorySelect.innerHTML += '<option value="Assignment">Assignment</option>';
        categorySelect.innerHTML += '<option value="Research Paper">Research Paper</option>';
        categorySelect.innerHTML += '<option value="Past Paper">Past Paper</option>';
    } else if (dept === 'Biomedical') {
        moduleSelect.innerHTML += '<option value="Human Anatomy">Human Anatomy</option>';
        moduleSelect.innerHTML += '<option value="Biomedical Instrumentation">Biomedical Instrumentation</option>';
        moduleSelect.innerHTML += '<option value="Pharmacology">Pharmacology</option>';
        
        categorySelect.innerHTML += '<option value="Lecture Notes">Lecture Notes</option>';
        categorySelect.innerHTML += '<option value="Assignment">Assignment</option>';
        categorySelect.innerHTML += '<option value="Research Paper">Research Paper</option>';
        categorySelect.innerHTML += '<option value="Past Paper">Past Paper</option>';
        categorySelect.innerHTML += '<option value="Lab Report">Lab Report</option>';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
