<?php
require_once 'e:\projects\Loan system\includes\config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add columns to loans table
    $sql1 = "ALTER TABLE loans ADD COLUMN checkout_request_id VARCHAR(100) NULL AFTER status";
    $sql2 = "ALTER TABLE loans ADD COLUMN processing_fee_paid TINYINT(1) DEFAULT 0 AFTER checkout_request_id";
    
    $pdo->exec($sql1);
    echo "Added checkout_request_id column.\n";
    
    $pdo->exec($sql2);
    echo "Added processing_fee_paid column.\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
