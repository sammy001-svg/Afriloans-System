<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // In production, log error instead of echoing
    die("Database Connection Failed: " . $e->getMessage());
}

/**
 * Helper to run simple select queries
 */
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Helper to get a single row
 */
function fetch($sql, $params = []) {
    return query($sql, $params)->fetch();
}

/**
 * Helper to get all rows
 */
function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}
?>
