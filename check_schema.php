<?php
require_once 'config.php';

$tables = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== DATABASE SCHEMA CHECK ===\n\n";
echo "Tables found: " . implode(', ', $tables) . "\n\n";

if (in_array('users', $tables)) {
    $desc = $conn->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
    echo "Users table columns:\n";
    foreach ($desc as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
}

if (in_array('fares', $tables)) {
    $desc = $conn->query('DESCRIBE fares')->fetchAll(PDO::FETCH_ASSOC);
    echo "Fares table columns:\n";
    foreach ($desc as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
}

if (in_array('incidents', $tables)) {
    $desc = $conn->query('DESCRIBE incidents')->fetchAll(PDO::FETCH_ASSOC);
    echo "Incidents table columns:\n";
    foreach ($desc as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    echo "\n";
}
?>
