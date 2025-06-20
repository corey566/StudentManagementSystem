<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate form data
$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$department = $_POST['department'] ?? '';
$module_type = $_POST['module_type'] ?? '';
$module_name = sanitizeInput($_POST['module_name'] ?? '');
$category = $_POST['category'] ?? '';

// Validation
$errors = [];

if (empty($title)) {
    $errors[] = 'Title is required';
}

if (empty($department)) {
    $errors[] = 'Department is required';
}

if (empty($category)) {
    $errors[] = 'Category is required';
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Please select a valid file to upload';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file extension
$allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)
    ]);
    exit;
}

// Validate file size (50MB max)
$maxSize = 50 * 1024 * 1024; // 50MB in bytes
if ($fileSize > $maxSize) {
    echo json_encode([
        'success' => false, 
        'message' => 'File size must be less than 50MB'
    ]);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create upload directory'
        ]);
        exit;
    }
}

// Generate unique filename
$newFileName = generateFileName($fileName, $_SESSION['user_id']);
$uploadPath = $uploadDir . '/' . $newFileName;

// Move uploaded file
if (!move_uploaded_file($fileTmpName, $uploadPath)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to upload file'
    ]);
    exit;
}

// Save to database
try {
    $stmt = $pdo->prepare("INSERT INTO uploads (user_id, title, description, department, module_type, module_name, category, file_name, file_path, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $success = $stmt->execute([
        $_SESSION['user_id'], 
        $title, 
        $description, 
        $department, 
        $module_type ?: null, 
        $module_name ?: null, 
        $category, 
        $fileName, 
        $uploadPath, 
        $fileSize
    ]);
    
    if ($success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Material uploaded successfully! It will be reviewed by administrators before being published.',
            'upload_id' => $pdo->lastInsertId()
        ]);
    } else {
        // Delete the uploaded file if database insert fails
        unlink($uploadPath);
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to save upload information'
        ]);
    }
} catch (Exception $e) {
    // Delete the uploaded file if there's an error
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
