<?php
$pageTitle = 'Advanced Search - Education Portal';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$departments = getDepartments();
$user = getCurrentUser();

// Handle search
$searchResults = [];
$totalResults = 0;
$searchPerformed = false;

if ($_GET) {
    $searchPerformed = true;
    $query = sanitizeInput($_GET['q'] ?? '');
    $department = sanitizeInput($_GET['department'] ?? '');
    $category = sanitizeInput($_GET['category'] ?? '');
    $moduleType = sanitizeInput($_GET['module_type'] ?? '');
    $dateFrom = sanitizeInput($_GET['date_from'] ?? '');
    $dateTo = sanitizeInput($_GET['date_to'] ?? '');
    $minSize = (int)($_GET['min_size'] ?? 0);
    $maxSize = (int)($_GET['max_size'] ?? 0);
    
    $sql = "SELECT u.*, users.full_name as uploader_name, 
            COALESCE(AVG(r.rating), 0) as avg_rating,
            COUNT(r.id) as rating_count,
            COUNT(d.id) as download_count
            FROM uploads u 
            JOIN users ON u.user_id = users.id 
            LEFT JOIN ratings r ON u.id = r.upload_id
            LEFT JOIN downloads d ON u.id = d.upload_id
            WHERE u.approval_status = 'approved'";
    
    $params = [];
    
    if ($query) {
        $sql .= " AND (u.title LIKE ? OR u.description LIKE ? OR u.module_name LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    
    if ($department) {
        $sql .= " AND u.department = ?";
        $params[] = $department;
    }
    
    if ($category) {
        $sql .= " AND u.category = ?";
        $params[] = $category;
    }
    
    if ($moduleType) {
        $sql .= " AND u.module_type = ?";
        $params[] = $moduleType;
    }
    
    if ($dateFrom) {
        $sql .= " AND u.created_at >= ?";
        $params[] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo) {
        $sql .= " AND u.created_at <= ?";
        $params[] = $dateTo . ' 23:59:59';
    }
    
    if ($minSize > 0) {
        $sql .= " AND u.file_size >= ?";
        $params[] = $minSize * 1024; // Convert KB to bytes
    }
    
    if ($maxSize > 0) {
        $sql .= " AND u.file_size <= ?";
        $params[] = $maxSize * 1024; // Convert KB to bytes
    }
    
    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT 50";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll();
        $totalResults = count($searchResults);
    } catch (Exception $e) {
        $error = "Search failed. Please try again.";
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-search-plus"></i> Advanced Search</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Advanced Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-filter"></i> Search Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="advancedSearchForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="q" class="form-label">Search Keywords</label>
                                <input type="text" class="form-control" id="q" name="q" 
                                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                                       placeholder="Title, description, or module name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department">
                                    <option value="">All Departments</option>
                                    <option value="IT" <?= ($_GET['department'] ?? '') === 'IT' ? 'selected' : '' ?>>IT</option>
                                    <option value="Business Management" <?= ($_GET['department'] ?? '') === 'Business Management' ? 'selected' : '' ?>>Business Management</option>
                                    <option value="Biomedical" <?= ($_GET['department'] ?? '') === 'Biomedical' ? 'selected' : '' ?>>Biomedical</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="module_type" class="form-label">Module Type</label>
                                <select class="form-select" id="module_type" name="module_type">
                                    <option value="">All Module Types</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="min_size" class="form-label">Min File Size (KB)</label>
                                <input type="number" class="form-control" id="min_size" name="min_size" 
                                       value="<?= htmlspecialchars($_GET['min_size'] ?? '') ?>"
                                       placeholder="Minimum size in KB">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_size" class="form-label">Max File Size (KB)</label>
                                <input type="number" class="form-control" id="max_size" name="max_size" 
                                       value="<?= htmlspecialchars($_GET['max_size'] ?? '') ?>"
                                       placeholder="Maximum size in KB">
                            </div>
                            <div class="col-md-4 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="advanced_search.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <?php if ($searchPerformed): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-list"></i> Search Results</h5>
                    <span class="badge bg-primary"><?= $totalResults ?> results found</span>
                </div>
                <div class="card-body">
                    <?php if (empty($searchResults)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No materials found matching your search criteria.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($searchResults as $material): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($material['title']) ?></h6>
                                        <p class="card-text text-muted small">
                                            <?= htmlspecialchars(substr($material['description'], 0, 100)) ?>...
                                        </p>
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?= htmlspecialchars($material['department']) ?></span>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($material['category']) ?></span>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($material['uploader_name']) ?><br>
                                            <i class="fas fa-file-alt"></i> <?= formatFileSize($material['file_size']) ?><br>
                                            <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                        </div>
                                        <?php if ($material['rating_count'] > 0): ?>
                                        <div class="mb-2">
                                            <span class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?= $i <= round($material['avg_rating']) ? '' : '-o' ?>"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <small class="text-muted">(<?= $material['rating_count'] ?> ratings)</small>
                                        </div>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between">
                                            <button class="btn btn-sm btn-outline-primary" onclick="previewMaterial(<?= $material['id'] ?>)">
                                                <i class="fas fa-eye"></i> Preview
                                            </button>
                                            <a href="download.php?id=<?= $material['id'] ?>" class="btn btn-sm btn-success">
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
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Update categories and module types based on department selection
document.getElementById('department').addEventListener('change', function() {
    const dept = this.value;
    const categorySelect = document.getElementById('category');
    const moduleSelect = document.getElementById('module_type');
    
    // Clear current options
    categorySelect.innerHTML = '<option value="">All Categories</option>';
    moduleSelect.innerHTML = '<option value="">All Module Types</option>';
    
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
        categorySelect.innerHTML += '<option value="Assignments">Assignments</option>';
        categorySelect.innerHTML += '<option value="Research Papers">Research Papers</option>';
    } else if (dept === 'Biomedical') {
        moduleSelect.innerHTML += '<option value="Anatomy and Physiology">Anatomy and Physiology</option>';
        moduleSelect.innerHTML += '<option value="Medical Technology">Medical Technology</option>';
        categorySelect.innerHTML += '<option value="Lecture Notes">Lecture Notes</option>';
        categorySelect.innerHTML += '<option value="Assignments">Assignments</option>';
        categorySelect.innerHTML += '<option value="Research Papers">Research Papers</option>';
    }
    
    // Restore selected values if they exist
    const urlParams = new URLSearchParams(window.location.search);
    const selectedCategory = urlParams.get('category');
    const selectedModule = urlParams.get('module_type');
    
    if (selectedCategory) {
        categorySelect.value = selectedCategory;
    }
    if (selectedModule) {
        moduleSelect.value = selectedModule;
    }
});

// Initialize on page load
document.getElementById('department').dispatchEvent(new Event('change'));

function previewMaterial(id) {
    fetch(`api/preview_material.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create modal for preview
                const modal = document.createElement('div');
                modal.className = 'modal fade';
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Material Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${data.html}
                            </div>
                            <div class="modal-footer">
                                <a href="download.php?id=${id}" class="btn btn-primary">Download</a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
                new bootstrap.Modal(modal).show();
                
                modal.addEventListener('hidden.bs.modal', function() {
                    document.body.removeChild(modal);
                });
            } else {
                alert('Failed to load preview: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load preview');
        });
}
</script>

<?php include 'includes/footer.php'; ?>