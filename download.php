<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid download request.');
}

$uploadId = intval($_GET['id']);

// Get upload details and verify it's approved
$stmt = $pdo->prepare("SELECT * FROM uploads WHERE id = ? AND approval_status = 'approved'");
$stmt->execute([$uploadId]);
$upload = $stmt->fetch();

if (!$upload) {
    die('File not found or not approved.');
}

// Check if file exists
$filePath = $upload['file_path'];
if (!file_exists($filePath)) {
    die('File not found on server.');
}

// Log the download
$stmt = $pdo->prepare("INSERT INTO downloads (upload_id, user_id) VALUES (?, ?)");
$stmt->execute([$uploadId, $_SESSION['user_id']]);

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $upload['file_name'] . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($filePath);
exit();
?>
