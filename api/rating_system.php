<?php
header('Content-Type: application/json');
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$action = $_POST['action'] ?? '';
$uploadId = (int)($_POST['upload_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);

if (!$uploadId) {
    echo json_encode(['success' => false, 'message' => 'Invalid upload ID']);
    exit;
}

try {
    if ($action === 'rate') {
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
            exit;
        }
        
        // Check if user already rated this material
        $stmt = $pdo->prepare("SELECT id FROM ratings WHERE upload_id = ? AND user_id = ?");
        $stmt->execute([$uploadId, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            // Update existing rating
            $stmt = $pdo->prepare("UPDATE ratings SET rating = ?, updated_at = CURRENT_TIMESTAMP WHERE upload_id = ? AND user_id = ?");
            $stmt->execute([$rating, $uploadId, $_SESSION['user_id']]);
        } else {
            // Insert new rating
            $stmt = $pdo->prepare("INSERT INTO ratings (upload_id, user_id, rating) VALUES (?, ?, ?)");
            $stmt->execute([$uploadId, $_SESSION['user_id'], $rating]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Rating saved']);
        
    } elseif ($action === 'get_average') {
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM ratings WHERE upload_id = ?");
        $stmt->execute([$uploadId]);
        $result = $stmt->fetch();
        
        echo json_encode([
            'success' => true,
            'average_rating' => round($result['avg_rating'], 1),
            'total_ratings' => $result['total_ratings']
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Operation failed']);
}
?>