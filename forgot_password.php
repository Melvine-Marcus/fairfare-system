<?php
/**
 * FairFare System - Password Reset Request
 * 
 * Handles password reset requests with email verification
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        
        // Validate email
        if (empty($email)) {
            $error = "Email address is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                // Check if user exists
                $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND is_active = 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Generate reset token (valid for 1 hour)
                    $reset_token = bin2hex(random_bytes(32));
                    $token_hash = hash('sha256', $reset_token);
                    $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
                    
                    // Store token in session for this demo
                    // In production, store in database: password_reset_tokens table
                    $_SESSION['reset_token_' . $user['id']] = [
                        'token_hash' => $token_hash,
                        'expires_at' => $expires_at
                    ];
                    
                    $success = "If this email exists in our system, you will receive password reset instructions. ";
                    $success .= "Please check your email (and spam folder) for further instructions.";
                    
                    // Log the activity
                    log_activity('PASSWORD_RESET_REQUESTED', "Password reset requested for email: " . $email);
                    
                    // In production, send email here with reset link
                    error_log("Password reset requested for user ID: " . $user['id']);
                } else {
                    // Don't reveal if email exists (security best practice)
                    $success = "If this email exists in our system, you will receive password reset instructions. ";
                    $success .= "Please check your email (and spam folder) for further instructions.";
                }
            } catch (PDOException $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
            }
        }
    }
}

$_csrf_token = generate_csrf_token();
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-info text-white">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-key"></i> Reset Password
                    </h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Enter your email address and we'll send you instructions to reset your password.
                    </p>

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

                    <form method="POST" name="forgotPasswordForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                   placeholder="Enter your email" required autocomplete="email">
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-info btn-lg" type="submit">
                                <i class="bi bi-send"></i> Send Reset Link
                            </button>
                        </div>

                        <div class="text-center">
                            <small class="text-muted">
                                Remember your password? <a href="login.php" class="text-primary fw-bold">Login here</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
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