<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$query = sanitizeInput($_GET['q'] ?? '');
$department = sanitizeInput($_GET['dept'] ?? '');
$category = sanitizeInput($_GET['cat'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Search query too short']);
    exit;
}

try {
    $sql = "SELECT u.*, users.full_name as uploader_name 
            FROM uploads u 
            JOIN users ON u.user_id = users.id 
            WHERE u.approval_status = 'approved' 
            AND (u.title LIKE ? OR u.description LIKE ? OR u.module_name LIKE ?)";
    
    $params = ["%$query%", "%$query%", "%$query%"];
    
    if ($department) {
        $sql .= " AND u.department = ?";
        $params[] = $department;
    }
    
    if ($category) {
        $sql .= " AND u.category = ?";
        $params[] = $category;
    }
    
    $sql .= " ORDER BY u.created_at DESC LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materials = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'materials' => $materials,
        'count' => count($materials)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Search failed']);
}
?>