<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get filter parameters
$department = $_GET['department'] ?? '';
$module_type = $_GET['module_type'] ?? '';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$order = $_GET['order'] ?? 'newest';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build the query
$where = ["u.approval_status = 'approved'"];
$params = [];

if ($department) {
    $where[] = "u.department = ?";
    $params[] = $department;
}

if ($module_type) {
    $where[] = "u.module_type = ?";
    $params[] = $module_type;
}

if ($category) {
    $where[] = "u.category = ?";
    $params[] = $category;
}

if ($search) {
    $where[] = "(u.title LIKE ? OR u.description LIKE ? OR us.full_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$orderBy = $order === 'oldest' ? 'ORDER BY u.created_at ASC' : 'ORDER BY u.created_at DESC';

// Get total count
$countQuery = "SELECT COUNT(*) FROM uploads u JOIN users us ON u.user_id = us.id WHERE " . implode(' AND ', $where);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalCount = $countStmt->fetchColumn();

// Get materials
$query = "SELECT u.*, us.full_name as uploader_name 
          FROM uploads u 
          JOIN users us ON u.user_id = us.id 
          WHERE " . implode(' AND ', $where) . " 
          $orderBy 
          LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$materials = $stmt->fetchAll();

// Format materials for response
$formattedMaterials = array_map(function($material) {
    return [
        'id' => $material['id'],
        'title' => $material['title'],
        'description' => $material['description'],
        'department' => $material['department'],
        'module_type' => $material['module_type'],
        'module_name' => $material['module_name'],
        'category' => $material['category'],
        'file_name' => $material['file_name'],
        'file_size' => $material['file_size'],
        'uploader_name' => $material['uploader_name'],
        'created_at' => $material['created_at'],
        'updated_at' => $material['updated_at'],
        'formatted_size' => $material['file_size'] ? formatFileSize($material['file_size']) : null,
        'formatted_date' => date('M j, Y', strtotime($material['created_at'])),
        'department_color' => $material['department'] === 'IT' ? 'primary' : 
                             ($material['department'] === 'Business Management' ? 'success' : 'danger')
    ];
}, $materials);

echo json_encode([
    'success' => true,
    'materials' => $formattedMaterials,
    'pagination' => [
        'total' => $totalCount,
        'limit' => $limit,
        'offset' => $offset,
        'has_more' => ($offset + $limit) < $totalCount
    ],
    'filters' => [
        'department' => $department,
        'module_type' => $module_type,
        'category' => $category,
        'search' => $search,
        'order' => $order
    ]
]);
?>
