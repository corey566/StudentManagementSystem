<?php
// Database setup script for SQLite
require_once 'database.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/init_sqlite.sql');
    
    // Split the SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "Database initialized successfully!\n";
    
    // Verify that tables were created
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
    echo "Created tables: " . implode(', ', array_column($tables, 'name')) . "\n";
    
    // Check if admin user was created
    $adminUser = $pdo->query("SELECT username FROM users WHERE is_admin = 1")->fetch();
    if ($adminUser) {
        echo "Admin user created: " . $adminUser['username'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
?>