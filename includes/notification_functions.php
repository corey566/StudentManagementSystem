<?php
require_once __DIR__ . '/../config/database.php';

function createNotification($userId, $title, $message, $type = 'info') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function notifyAdminsOfNewUpload($uploadId, $title, $uploaderName) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE is_admin = 1");
    $stmt->execute();
    $admins = $stmt->fetchAll();
    
    foreach ($admins as $admin) {
        createNotification(
            $admin['id'],
            'New Upload Pending Approval',
            "User '{$uploaderName}' uploaded '{$title}' - ID: {$uploadId}",
            'pending'
        );
    }
}

function notifyUserOfApproval($userId, $title, $status) {
    $message = $status === 'approved' 
        ? "Your upload '{$title}' has been approved and is now available to other users."
        : "Your upload '{$title}' has been rejected. Please contact an administrator for details.";
    
    $type = $status === 'approved' ? 'success' : 'warning';
    
    createNotification($userId, 'Upload Status Update', $message, $type);
}

function getUnreadNotificationCount($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    return $result['count'];
}
?>