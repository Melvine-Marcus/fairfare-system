<?php
/**
 * FairFare System - Report Incident Page
 * 
 * Allows users to report transport incidents
 * 
 * @package FairFare
 * @version 1.0.0
 */

// Load config and auth FIRST before any includes that output HTML
require_once "config.php";
require_once "includes/auth.php";

// Check login BEFORE including header (which outputs HTML)
require_login();

// NOW include header which outputs HTML
require_once "includes/header.php";

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $route = sanitize_input($_POST['route'] ?? '');
        $incident_type = sanitize_input($_POST['incident_type'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');

        // Validate inputs
        if (empty($name) || empty($email) || empty($route) || empty($incident_type) || empty($description)) {
            $error = "All required fields must be filled.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (!empty($phone) && !preg_match('/^(\+254|0)[0-9]{9,10}$/', str_replace([' ', '-'], '', $phone))) {
            $error = "Please enter a valid Kenyan phone number (e.g., +254712345678 or 0712345678).";
        } elseif (strlen($description) < 10) {
            $error = "Description must be at least 10 characters long.";
        } else {
            try {
                $user_id = get_current_user_id();
                $created_at = date('Y-m-d H:i:s');
                $status = 'open';

                $stmt = $conn->prepare("
                    INSERT INTO incidents (user_id, name, email, phone, route, incident_type, description, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$user_id, $name, $email, $phone, $route, $incident_type, $description, $status, $created_at])) {
                    $success = "Thank you! Your incident has been reported successfully. Our admin team will review it shortly.";
                    
                    // Log the activity
                    log_activity('INCIDENT_REPORTED', "Incident reported on route: " . $route);
                    
                    // Clear form
                    $_POST = [];
                } else {
                    $error = "Failed to report incident. Please try again.";
                }
            } catch (PDOException $e) {
                error_log("Incident reporting error: " . $e->getMessage());
                $error = "An error occurred while reporting the incident. Please try again.";
            }
        }
    }
}

$_csrf_token = generate_csrf_token();
$user_email = $_SESSION['email'] ?? '';
?>

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Report an Incident
                    </h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Help us improve transport safety by reporting incidents such as overcharging, misconduct, or unsafe conditions.
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

                    <form method="POST" name="incidentForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars(get_current_username() ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number (Optional)</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Your contact number" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="route" class="form-label">Route <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="route" name="route" 
                                       placeholder="e.g., Ongata Rongai - CBD" value="<?php echo htmlspecialchars($_POST['route'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="incident_type" class="form-label">Incident Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="incident_type" name="incident_type" required>
                                <option value="">-- Select Incident Type --</option>
                                <option value="Overcharging" <?php echo ($_POST['incident_type'] ?? '') === 'Overcharging' ? 'selected' : ''; ?>>Overcharging</option>
                                <option value="Misconduct" <?php echo ($_POST['incident_type'] ?? '') === 'Misconduct' ? 'selected' : ''; ?>>Misconduct</option>
                                <option value="Unsafe Condition" <?php echo ($_POST['incident_type'] ?? '') === 'Unsafe Condition' ? 'selected' : ''; ?>>Unsafe Condition</option>
                                <option value="Poor Service" <?php echo ($_POST['incident_type'] ?? '') === 'Poor Service' ? 'selected' : ''; ?>>Poor Service</option>
                                <option value="Other" <?php echo ($_POST['incident_type'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                      placeholder="Please provide detailed information about the incident (minimum 10 characters)" 
                                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">Minimum 10 characters</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-warning btn-lg" type="submit">
                                <i class="bi bi-send"></i> Submit Incident Report
                            </button>
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