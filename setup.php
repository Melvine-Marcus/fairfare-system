<?php
/**
 * FairFare System - Database Setup
 * 
 * Creates all necessary database tables
 * Access this file once at: http://localhost/FairFareSystem/setup.php
 * Then delete this file or rename it to setup.php.bak
 * 
 * @package FairFare
 * @version 1.0.0
 */

require_once "config.php";

$setup_complete = false;
$error_message = "";
$success_message = "";

// Check if tables already exist
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        $setup_complete = true;
        $success_message = "Database already initialized with " . count($tables) . " table(s).";
    }
} catch (Exception $e) {
    $error_message = "Error checking tables: " . $e->getMessage();
}

// Create tables if they don't exist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    try {
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
        
        // 2. Create fares table
        $conn->exec("CREATE TABLE IF NOT EXISTS fares (
            id INT PRIMARY KEY AUTO_INCREMENT,
            route VARCHAR(100) NOT NULL,
            fare DECIMAL(10, 2) NOT NULL,
            effective_date DATE NOT NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )");
        
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
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (resolved_by) REFERENCES users(id)
        )");
        
        // 4. Create fare_history table
        $conn->exec("CREATE TABLE IF NOT EXISTS fare_history (
            id INT PRIMARY KEY AUTO_INCREMENT,
            route VARCHAR(100) NOT NULL,
            old_fare DECIMAL(10, 2),
            new_fare DECIMAL(10, 2) NOT NULL,
            changed_by INT NOT NULL,
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (changed_by) REFERENCES users(id)
        )");
        
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
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX (user_id, created_at)
        )");
        
        // 6. Create admin_logs table
        $conn->exec("CREATE TABLE IF NOT EXISTS admin_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            admin_id INT NOT NULL,
            admin_name VARCHAR(100),
            action VARCHAR(200) NOT NULL,
            details TEXT,
            log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id)
        )");
        
        $success_message = "✓ All database tables created successfully!";
        $setup_complete = true;
        
    } catch (Exception $e) {
        $error_message = "Error creating tables: " . $e->getMessage();
    }
}

// Create initial admin user if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    try {
        $admin_email = "admin@fairfare.local";
        $admin_password = password_hash("Admin@123", PASSWORD_BCRYPT);
        $admin_username = "admin";
        
        // Check if admin already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$admin_email]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$admin_username, $admin_email, $admin_password, 'admin']);
            $success_message .= "\n✓ Admin user created! Email: admin@fairfare.local | Password: Admin@123";
        } else {
            $error_message = "Admin user already exists.";
        }
        
    } catch (Exception $e) {
        $error_message = "Error creating admin user: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FairFare System - Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .setup-container {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            margin: 1rem;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 3px solid #667eea;
            padding-bottom: 1rem;
        }
        
        .setup-header h1 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .setup-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .btn-setup {
            width: 100%;
            padding: 0.8rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 0.75rem;
        }
        
        .btn-setup:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .status-icon {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .instructions {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        code {
            background: #e9ecef;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🔧 FairFare System Setup</h1>
            <p>Database Initialization Wizard</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> 
                <?php 
                    $messages = explode("\n", $success_message);
                    foreach ($messages as $msg) {
                        if (!empty(trim($msg))) {
                            echo htmlspecialchars($msg) . "<br>";
                        }
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!$setup_complete): ?>
            <form method="POST">
                <div class="instructions">
                    <strong>Step 1: Create Database Tables</strong>
                    <p>This will create all necessary tables in the <code>fairfare_system_db</code> database.</p>
                </div>
                
                <button type="submit" name="create_tables" class="btn btn-primary btn-setup">
                    <i class="bi bi-database"></i> Create Database Tables
                </button>
            </form>
        <?php else: ?>
            <form method="POST">
                <div class="instructions">
                    <strong>Step 2: Create Admin Account</strong>
                    <p>Creates an initial admin user to access the admin dashboard.</p>
                    <p><strong>Default Credentials:</strong></p>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                        <li>Email: <code>admin@fairfare.local</code></li>
                        <li>Password: <code>Admin@123</code></li>
                    </ul>
                    <p class="text-warning" style="margin-top: 0.5rem;">
                        <small>⚠️ Change this password after first login!</small>
                    </p>
                </div>
                
                <button type="submit" name="create_admin" class="btn btn-success btn-setup">
                    <i class="bi bi-person-plus"></i> Create Admin User
                </button>
            </form>
            
            <div class="instructions" style="border-left-color: #28a745; margin-top: 1.5rem;">
                <strong>✓ Setup Complete!</strong>
                <p>You can now:</p>
                <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                    <li><a href="index.php">Visit the home page</a></li>
                    <li><a href="register.php">Register a new user</a></li>
                    <li><a href="login.php">Login with admin account</a></li>
                </ul>
                <p class="text-muted" style="margin-top: 0.75rem;">
                    <small>For security, delete or rename this <code>setup.php</code> file after setup is complete.</small>
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
