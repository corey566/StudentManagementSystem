<?php
// Database configuration
$dbPath = __DIR__ . '/../database/education_portal.db';
$dbDir = dirname($dbPath);

// Create database directory if it doesn't exist
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable foreign key constraints for SQLite
    $pdo->exec("PRAGMA foreign_keys = ON");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
