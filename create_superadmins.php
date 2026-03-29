<?php
/**
 * Create Default Superadmin Users
 * Creates MarcusM and NdukuM as superadmins
 */

require_once "config.php";

echo "=== Creating Superadmin Users ===\n\n";

$admins = [
    [
        'username' => 'MarcusM',
        'email' => 'makokhanmelvin04@gmail.com',
        'phone' => '0795207374',
        'password' => 'M@eng2026',
        'role' => 'admin'
    ],
    [
        'username' => 'NdukuM',
        'email' => 'ndukumuambi@gmail.com',
        'phone' => '0737428245',
        'password' => 'N@eng2026',
        'role' => 'admin'
    ]
];

try {
    foreach ($admins as $admin) {
        // Check if user already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$admin['email']]);
        
        if ($check->rowCount() > 0) {
            echo "⚠ User {$admin['username']} ({$admin['email']}) already exists - SKIPPED\n";
            continue;
        }
        
        // Hash password
        $hashedPassword = password_hash($admin['password'], PASSWORD_BCRYPT);
        
        // Insert user
        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password, phone, role, is_active) 
             VALUES (?, ?, ?, ?, ?, 1)"
        );
        
        $result = $stmt->execute([
            $admin['username'],
            $admin['email'],
            $hashedPassword,
            $admin['phone'],
            $admin['role']
        ]);
        
        if ($result) {
            $userId = $conn->lastInsertId();
            echo "✓ Superadmin Created:\n";
            echo "  - Username: {$admin['username']}\n";
            echo "  - Email: {$admin['email']}\n";
            echo "  - Phone: {$admin['phone']}\n";
            echo "  - Role: {$admin['role']}\n";
            echo "  - ID: {$userId}\n";
            
            // Log the action
            $logStmt = $conn->prepare(
                "INSERT INTO activity_logs (user_id, action, description) 
                 VALUES (?, ?, ?)"
            );
            $logStmt->execute([
                $userId,
                'SUPERADMIN_CREATED',
                "Superadmin account created for {$admin['username']}"
            ]);
            
            echo "\n";
        } else {
            echo "✗ Failed to create {$admin['username']}\n";
            echo "  Error: " . print_r($stmt->errorInfo(), true) . "\n\n";
        }
    }
    
    echo "\n=== Superadmin Creation Complete ===\n";
    echo "\nYou can now login with:\n";
    echo "  - Email: makokhanmelvin04@gmail.com, Password: M@eng2026\n";
    echo "  - Email: ndukumuambi@gmail.com, Password: N@eng2026\n";
    
} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
}

?>
