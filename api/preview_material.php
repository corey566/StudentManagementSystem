<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
    exit;
}

$materialId = intval($_GET['id']);

// Get material details (only approved materials can be previewed by non-admins)
$stmt = $pdo->prepare("SELECT u.*, us.full_name as uploader_name FROM uploads u JOIN users us ON u.user_id = us.id WHERE u.id = ? AND (u.approval_status = 'approved' OR ? = 1)");
$stmt->execute([$materialId, isAdmin() ? 1 : 0]);
$material = $stmt->fetch();

if (!$material) {
    echo json_encode(['success' => false, 'message' => 'Material not found or not accessible']);
    exit;
}

// Generate preview HTML
$previewHtml = '
<div class="material-preview">
    <div class="row">
        <div class="col-md-8">
            <h5>' . htmlspecialchars($material['title']) . '</h5>
            <div class="mb-3">
                <span class="badge bg-' . ($material['department'] === 'IT' ? 'primary' : ($material['department'] === 'Business Management' ? 'success' : 'danger')) . '">
                    ' . htmlspecialchars($material['department']) . '
                </span>
                <span class="badge bg-secondary ms-1">
                    ' . htmlspecialchars($material['category']) . '
                </span>
            </div>
            
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="120"><strong>Uploader:</strong></td>
                    <td>' . htmlspecialchars($material['uploader_name']) . '</td>
                </tr>';

if ($material['module_type']) {
    $previewHtml .= '
                <tr>
                    <td><strong>Module Type:</strong></td>
                    <td>' . htmlspecialchars($material['module_type']) . '</td>
                </tr>';
}

if ($material['module_name']) {
    $previewHtml .= '
                <tr>
                    <td><strong>Module:</strong></td>
                    <td>' . htmlspecialchars($material['module_name']) . '</td>
                </tr>';
}

$previewHtml .= '
                <tr>
                    <td><strong>File:</strong></td>
                    <td>' . htmlspecialchars($material['file_name']) . '</td>
                </tr>';

if ($material['file_size']) {
    $previewHtml .= '
                <tr>
                    <td><strong>Size:</strong></td>
                    <td>' . formatFileSize($material['file_size']) . '</td>
                </tr>';
}

$previewHtml .= '
                <tr>
                    <td><strong>Uploaded:</strong></td>
                    <td>' . date('F j, Y \a\t g:i A', strtotime($material['created_at'])) . '</td>
                </tr>
            </table>';

if ($material['description']) {
    $previewHtml .= '
            <div class="mt-3">
                <h6>Description:</h6>
                <div class="border p-3 rounded bg-light">
                    ' . nl2br(htmlspecialchars($material['description'])) . '
                </div>
            </div>';
}

$previewHtml .= '
        </div>
        <div class="col-md-4">
            <div class="text-center">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <p class="text-muted">File preview not available</p>';

if (file_exists($material['file_path'])) {
    $previewHtml .= '
                <div class="d-grid">
                    <button type="button" class="btn btn-primary" onclick="window.location.href=\'../download.php?id=' . $material['id'] . '\'">
                        <i class="fas fa-download"></i> Download File
                    </button>
                </div>';
} else {
    $previewHtml .= '
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> File not found on server
                </div>';
}

$previewHtml .= '
            </div>
        </div>
    </div>
</div>';

echo json_encode([
    'success' => true,
    'html' => $previewHtml,
    'material' => [
        'id' => $material['id'],
        'title' => $material['title'],
        'filename' => $material['file_name']
    ]
]);
?>
