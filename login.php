<?php
/**
 * FairFare System - User Login Page
 * 
 * Handles user authentication
 * 
 * @package FairFare
 * @version 1.0.0
 */

// Load config and auth FIRST before any includes that output HTML
require_once "config.php";
require_once "includes/auth.php";

$error = "";
$success = "";

// Check redirects BEFORE including header (which outputs HTML)
if (is_logged_in()) {
    header("Location: " . APP_URL . "/" . (is_admin() ? "admin_dashboard.php" : "index.php"));
    exit();
}

// Process login form BEFORE including header.php (which outputs HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Check rate limiting
        if (is_rate_limited($email)) {
            $error = "Too many failed login attempts. Please try again in 15 minutes.";
        } elseif (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ? AND is_active = 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();

                    // Log the activity
                    log_activity('LOGIN', "User logged in successfully");

                    // Clear failed attempts
                    $key = 'login_attempts_' . md5($email);
                    unset($_SESSION[$key]);

                    // Redirect based on role - THIS HAPPENS BEFORE ANY HTML OUTPUT
                    $redirect = $_GET['redirect'] ?? (is_admin() ? 'admin_dashboard.php' : 'index.php');
                    header("Location: " . APP_URL . "/" . basename($redirect));
                    exit();
                } else {
                    $error = "Incorrect email or password.";
                    record_failed_login($email);
                    // Log failed attempt
                    log_activity('LOGIN_FAILED', "Failed login attempt for email: " . $email);
                    sleep(2); // Stronger brute force prevention
                }
            } catch (PDOException $e) {
                error_log("Login error: " . $e->getMessage());
                $error = "An error occurred during login. Please try again.";
            }
        }
    }
}

// NOW include header which outputs HTML (only if we didn't redirect)
require_once "includes/header.php";

$_csrf_token = generate_csrf_token();
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-box-arrow-in-right"></i> User Login
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
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" name="loginForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   placeholder="Enter your email" required autocomplete="email">
                            <small class="form-text text-muted">We'll never share your email.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                   placeholder="Enter your password" required autocomplete="current-password">
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-primary btn-lg" type="submit">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </div>

                        <div class="text-center mb-3">
                            <small class="text-muted">
                                <a href="forgot_password.php" class="text-primary">Forgot password?</a> | 
                                Don't have an account? <a href="register.php" class="text-primary fw-bold">Register here</a>
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