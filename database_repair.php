<?php
/**
 * FairFare System - Database Repair & Initialization Script
 * Drops existing tables and recreates them with proper schema
 */

require_once 'config.php';

echo "=== FairFare Database Management ===\n\n";

try {
    // Drop existing tables (in reverse dependency order)
    $tables_to_drop = ['admin_logs', 'activity_logs', 'fare_history', 'incidents', 'fares', 'users'];
    
    foreach ($tables_to_drop as $table) {
        $conn->exec("DROP TABLE IF EXISTS $table");
        echo "✓ Dropped table: $table\n";
    }
    
    echo "\n--- Creating tables ---\n";
    
    // 1. Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        is_active BOOLEAN DEFAULT 1,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ Created users table\n";
    
    // 2. Create fares table
    $conn->exec("CREATE TABLE IF NOT EXISTS fares (
        id INT PRIMARY KEY AUTO_INCREMENT,
        route VARCHAR(100) NOT NULL,
        fare DECIMAL(10, 2) NOT NULL,
        effective_date DATE NOT NULL,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Created fares table\n";
    
    // 3. Create incidents table
    $conn->exec("CREATE TABLE IF NOT EXISTS incidents (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        route VARCHAR(100) NOT NULL,
        incident_type VARCHAR(50),
        description LONGTEXT NOT NULL,
        status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
        resolved_by INT,
        resolved_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
    )");
    echo "✓ Created incidents table\n";
    
    // 4. Create fare_history table
    $conn->exec("CREATE TABLE IF NOT EXISTS fare_history (
        id INT PRIMARY KEY AUTO_INCREMENT,
        route VARCHAR(100) NOT NULL,
        old_fare DECIMAL(10, 2),
        new_fare DECIMAL(10, 2) NOT NULL,
        changed_by INT NOT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT
    )");
    echo "✓ Created fare_history table\n";
    
    // 5. Create activity_logs table
    $conn->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        username VARCHAR(50),
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user_created (user_id, created_at)
    )");
    echo "✓ Created activity_logs table\n";
    
    // 6. Create admin_logs table
    $conn->exec("CREATE TABLE IF NOT EXISTS admin_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        admin_id INT NOT NULL,
        admin_name VARCHAR(100),
        action VARCHAR(200) NOT NULL,
        details TEXT,
        log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE RESTRICT
    )");
    echo "✓ Created admin_logs table\n";
    
    echo "\n--- Creating Admin User ---\n";
    
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@fairfare.local']);
    
    if ($stmt->rowCount() == 0) {
        $admin_password = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, role, is_active) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute(['admin', 'admin@fairfare.local', $admin_password, 'admin']);
        echo "✓ Created admin user\n";
        echo "  Email: admin@fairfare.local\n";
        echo "  Password: Admin@123\n";
    } else {
        echo "ⓘ Admin user already exists\n";
    }
    
    echo "\n✓ DATABASE INITIALIZATION COMPLETE!\n";
    echo "\nYou can now:\n";
    echo "1. Login with admin@fairfare.local / Admin@123\n";
    echo "2. Access the system at: " . APP_URL . "\n";
    echo "3. Delete this script after verification\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
