<?php
require_once 'config/database.php';

function getDepartments() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY name");
    return $stmt->fetchAll();
}

function getModulesByDepartment($departmentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE department_id = ? ORDER BY name");
    $stmt->execute([$departmentId]);
    return $stmt->fetchAll();
}

function getCategoriesByDepartment($departmentId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE department_id = ? ORDER BY name");
    $stmt->execute([$departmentId]);
    return $stmt->fetchAll();
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function generateFileName($originalName, $userId) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return $userId . '_' . time() . '_' . uniqid() . '.' . $extension;
}

function getUploadsByUser($userId, $limit = null) {
    global $pdo;
    
    $query = "SELECT * FROM uploads WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getApprovedUploads($filters = []) {
    global $pdo;
    
    $where = ["approval_status = 'approved'"];
    $params = [];
    
    if (!empty($filters['department'])) {
        $where[] = "department = ?";
        $params[] = $filters['department'];
    }
    
    if (!empty($filters['module_type'])) {
        $where[] = "module_type = ?";
        $params[] = $filters['module_type'];
    }
    
    if (!empty($filters['category'])) {
        $where[] = "category = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(title LIKE ? OR description LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    $orderBy = "ORDER BY created_at DESC";
    if (!empty($filters['order']) && $filters['order'] === 'oldest') {
        $orderBy = "ORDER BY created_at ASC";
    }
    
    $query = "SELECT u.*, us.full_name as uploader_name 
              FROM uploads u 
              JOIN users us ON u.user_id = us.id 
              WHERE " . implode(' AND ', $where) . " " . $orderBy;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
