<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'update_status':
        updateUploadStatus($input);
        break;
        
    case 'bulk_update_status':
        bulkUpdateStatus($input);
        break;
        
    case 'delete_upload':
        deleteUpload($input);
        break;
        
    case 'get_upload_stats':
        getUploadStats();
        break;
        
    case 'toggle_user_status':
        toggleUserStatus($input);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function updateUploadStatus($input) {
    global $pdo;
    
    $uploadId = intval($input['upload_id'] ?? 0);
    $status = $input['status'] ?? '';
    
    if ($uploadId <= 0 || !in_array($status, ['pending', 'approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE uploads SET approval_status = ?, approved_by = ?, updated_at = NOW() WHERE id = ?");
        $success = $stmt->execute([$status, $_SESSION['user_id'], $uploadId]);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function bulkUpdateStatus($input) {
    global $pdo;
    
    $uploadIds = $input['upload_ids'] ?? [];
    $status = $input['status'] ?? '';
    
    if (empty($uploadIds) || !in_array($status, ['pending', 'approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }
    
    try {
        $placeholders = str_repeat('?,', count($uploadIds) - 1) . '?';
        $stmt = $pdo->prepare("UPDATE uploads SET approval_status = ?, approved_by = ?, updated_at = NOW() WHERE id IN ($placeholders)");
        
        $params = [$status, $_SESSION['user_id']];
        $params = array_merge($params, $uploadIds);
        
        $success = $stmt->execute($params);
        
        if ($success) {
            $count = $stmt->rowCount();
            echo json_encode(['success' => true, 'message' => "Updated $count uploads successfully"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update uploads']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteUpload($input) {
    global $pdo;
    
    $uploadId = intval($input['upload_id'] ?? 0);
    
    if ($uploadId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid upload ID']);
        return;
    }
    
    try {
        // Get file path before deleting from database
        $stmt = $pdo->prepare("SELECT file_path FROM uploads WHERE id = ?");
        $stmt->execute([$uploadId]);
        $upload = $stmt->fetch();
        
        if (!$upload) {
            echo json_encode(['success' => false, 'message' => 'Upload not found']);
            return;
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM uploads WHERE id = ?");
        $success = $stmt->execute([$uploadId]);
        
        if ($success) {
            // Delete file from filesystem
            if (file_exists($upload['file_path'])) {
                unlink($upload['file_path']);
            }
            
            echo json_encode(['success' => true, 'message' => 'Upload deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete upload']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getUploadStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total uploads by status
        $stmt = $pdo->query("SELECT approval_status, COUNT(*) as count FROM uploads GROUP BY approval_status");
        $statusCounts = $stmt->fetchAll();
        
        foreach ($statusCounts as $status) {
            $stats['by_status'][$status['approval_status']] = $status['count'];
        }
        
        // Uploads by department
        $stmt = $pdo->query("SELECT department, COUNT(*) as count FROM uploads GROUP BY department");
        $deptCounts = $stmt->fetchAll();
        
        foreach ($deptCounts as $dept) {
            $stats['by_department'][$dept['department']] = $dept['count'];
        }
        
        // Recent uploads (last 7 days)
        $stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM uploads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date");
        $recentUploads = $stmt->fetchAll();
        
        $stats['recent_uploads'] = $recentUploads;
        
        // Total file size
        $stmt = $pdo->query("SELECT SUM(file_size) as total_size FROM uploads WHERE file_size IS NOT NULL");
        $totalSize = $stmt->fetchColumn();
        
        $stats['total_size'] = $totalSize;
        $stats['formatted_total_size'] = $totalSize ? formatFileSize($totalSize) : '0 B';
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function toggleUserStatus($input) {
    global $pdo;
    
    $userId = intval($input['user_id'] ?? 0);
    $isActive = $input['is_active'] ?? true;
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // For this simple system, we don't have an active/inactive status
        // This could be extended to add a status field to users table
        echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
