<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/notification_functions.php';

requireLogin();

$user = getCurrentUser();
if (!$user['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$action = $_POST['action'] ?? '';
$uploadIds = $_POST['upload_ids'] ?? [];

if (empty($uploadIds) || !is_array($uploadIds)) {
    echo json_encode(['success' => false, 'message' => 'No items selected']);
    exit;
}

// Sanitize upload IDs
$uploadIds = array_map('intval', $uploadIds);
$uploadIds = array_filter($uploadIds);

if (empty($uploadIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid selection']);
    exit;
}

try {
    $placeholders = str_repeat('?,', count($uploadIds) - 1) . '?';
    
    switch ($action) {
        case 'approve':
            $stmt = $pdo->prepare("UPDATE uploads SET approval_status = 'approved', approved_by = ? WHERE id IN ($placeholders)");
            $params = array_merge([$user['id']], $uploadIds);
            $stmt->execute($params);
            
            // Notify users of approval
            $stmt = $pdo->prepare("SELECT user_id, title FROM uploads WHERE id IN ($placeholders)");
            $stmt->execute($uploadIds);
            $uploads = $stmt->fetchAll();
            
            foreach ($uploads as $upload) {
                notifyUserOfApproval($upload['user_id'], $upload['title'], 'approved');
            }
            
            echo json_encode(['success' => true, 'message' => count($uploadIds) . ' materials approved']);
            break;
            
        case 'reject':
            $stmt = $pdo->prepare("UPDATE uploads SET approval_status = 'rejected', approved_by = ? WHERE id IN ($placeholders)");
            $params = array_merge([$user['id']], $uploadIds);
            $stmt->execute($params);
            
            // Notify users of rejection
            $stmt = $pdo->prepare("SELECT user_id, title FROM uploads WHERE id IN ($placeholders)");
            $stmt->execute($uploadIds);
            $uploads = $stmt->fetchAll();
            
            foreach ($uploads as $upload) {
                notifyUserOfApproval($upload['user_id'], $upload['title'], 'rejected');
            }
            
            echo json_encode(['success' => true, 'message' => count($uploadIds) . ' materials rejected']);
            break;
            
        case 'delete':
            // Get file paths before deletion
            $stmt = $pdo->prepare("SELECT file_path FROM uploads WHERE id IN ($placeholders)");
            $stmt->execute($uploadIds);
            $filePaths = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM uploads WHERE id IN ($placeholders)");
            $stmt->execute($uploadIds);
            
            // Delete physical files
            foreach ($filePaths as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            echo json_encode(['success' => true, 'message' => count($uploadIds) . ' materials deleted']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Operation failed: ' . $e->getMessage()]);
}
?>