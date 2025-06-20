<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser();
if (!$user['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$format = $_GET['format'] ?? 'json';
$type = $_GET['type'] ?? 'uploads';

try {
    switch ($type) {
        case 'uploads':
            $stmt = $pdo->query("
                SELECT u.*, users.full_name as uploader_name, users.email as uploader_email
                FROM uploads u 
                JOIN users ON u.user_id = users.id 
                ORDER BY u.created_at DESC
            ");
            $data = $stmt->fetchAll();
            break;
            
        case 'users':
            $stmt = $pdo->query("
                SELECT id, username, email, full_name, student_id, university, department, 
                       is_admin, created_at, updated_at
                FROM users 
                ORDER BY created_at DESC
            ");
            $data = $stmt->fetchAll();
            break;
            
        case 'statistics':
            $data = [
                'summary' => [
                    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'total_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads")->fetchColumn(),
                    'approved_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'approved'")->fetchColumn(),
                    'pending_uploads' => $pdo->query("SELECT COUNT(*) FROM uploads WHERE approval_status = 'pending'")->fetchColumn(),
                    'total_downloads' => $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn()
                ],
                'by_department' => $pdo->query("
                    SELECT department, COUNT(*) as count 
                    FROM uploads 
                    WHERE approval_status = 'approved' 
                    GROUP BY department
                ")->fetchAll(),
                'by_category' => $pdo->query("
                    SELECT category, COUNT(*) as count 
                    FROM uploads 
                    WHERE approval_status = 'approved' 
                    GROUP BY category
                ")->fetchAll()
            ];
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid export type']);
            exit;
    }
    
    if ($format === 'csv' && $type !== 'statistics') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    } else {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()]);
}
?>