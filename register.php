<?php
/**
 * FairFare System - User Registration Page
 * 
 * Handles new user registration
 * 
 * @package FairFare
 * @version 1.0.0
 */

// Load config FIRST before any includes that output HTML
require_once "config.php";
require_once "includes/auth.php";

// Check redirects BEFORE including header (which outputs HTML)
if (is_logged_in()) {
    header("Location: " . APP_URL . "/index.php");
    exit();
}

// NOW include header which outputs HTML
require_once "includes/header.php";

$error = "";
$success = "";

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = sanitize_input($_POST['username'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        // Validate inputs
        if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
            $error = "All fields are required.";
        } elseif (strlen($username) < 3) {
            $error = "Username must be at least 3 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $password_confirm) {
            $error = "Passwords do not match.";
        } else {
            try {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $error = "This email address is already registered.";
                } else {
                    // Check if username already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->rowCount() > 0) {
                        $error = "This username is already taken.";
                    } else {
                        // Hash password and create account
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                        $role = ROLE_USER; // Default role for new users

                        $stmt = $conn->prepare("
                            INSERT INTO users (username, email, password, role, is_active, created_at) 
                            VALUES (?, ?, ?, ?, 1, NOW())
                        ");
                        
                        if ($stmt->execute([$username, $email, $hashed_password, $role])) {
                            $success = "Registration successful! You can now <a href='login.php' class='alert-link'>login</a>.";
                            
                            // Log the activity
                            log_activity('REGISTRATION', "New user registered: " . $username);
                            
                            // Clear form
                            $_POST = [];
                        } else {
                            $error = "Registration failed. Please try again.";
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}

$_csrf_token = generate_csrf_token();
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-person-plus"></i> Create New Account
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" name="registrationForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                   placeholder="Choose a username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            <small class="form-text text-muted">3-50 characters, letters, numbers and underscores only.</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required autocomplete="email">
                            <small class="form-text text-muted">We'll never share your email.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                   placeholder="Create a strong password" required autocomplete="new-password">
                            <small class="form-text text-muted">At least 8 characters with uppercase, lowercase, and numbers.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-lg" id="password_confirm" 
                                   name="password_confirm" placeholder="Confirm your password" required>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-success btn-lg" type="submit">
                                <i class="bi bi-check-circle"></i> Create Account
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer (add this at end of main)
?>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>