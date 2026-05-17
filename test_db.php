<?php
try {
    new PDO('mysql:host=localhost;dbname=loan_system;charset=utf8mb4', 'root', '');
    echo "Connected via localhost\n";
} catch (Exception $e) {
    echo "localhost failed: " . $e->getMessage() . "\n";
}

try {
    new PDO('mysql:host=127.0.0.1;dbname=loan_system;charset=utf8mb4', 'root', '');
    echo "Connected via 127.0.0.1\n";
} catch (Exception $e) {
    echo "127.0.0.1 failed: " . $e->getMessage() . "\n";
}
