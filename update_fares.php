<?php
/**
 * FairFare System - Update Fares Page
 * 
 * Allows admins to add or update fare information
 * 
 * @package FairFare
 * @version 1.0.0
 */

// Load config and auth FIRST before any includes that output HTML
require_once "config.php";
require_once "includes/auth.php";

// Check admin BEFORE including header (which outputs HTML)
require_admin();

// NOW include header which outputs HTML
require_once "includes/header.php";

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $route = sanitize_input($_POST['route'] ?? '');
        $fare = floatval($_POST['fare'] ?? 0);
        $effective_date = sanitize_input($_POST['effective_date'] ?? '');

        // Validate inputs
        if (empty($route)) {
            $error = "Route name is required.";
        } elseif ($fare <= 0) {
            $error = "Fare must be a positive number.";
        } elseif (empty($effective_date)) {
            $error = "Effective date is required.";
        } elseif (strtotime($effective_date) === false) {
            $error = "Please enter a valid date.";
        } else {
            try {
                // Check if route already exists
                $check_stmt = $conn->prepare("SELECT id, fare FROM fares WHERE route = ? AND effective_date = ? ORDER BY created_at DESC LIMIT 1");
                $check_stmt->execute([$route, $effective_date]);
                $existing_fare = $check_stmt->fetch();

                if ($existing_fare) {
                    $error = "A fare for this route on this date already exists.";
                } else {
                    // Insert new fare with admin ID
                    $admin_id = get_current_user_id();
                    $stmt = $conn->prepare("INSERT INTO fares (route, fare, effective_date, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                    
                    if ($stmt->execute([$route, $fare, $effective_date, $admin_id])) {
                        $success = "Fare has been updated successfully (KES " . number_format($fare, 2) . ").";
                        
                        // Log the activity
                        log_activity('FARE_UPDATED', "New fare added for route: " . $route . " (KES " . $fare . ")");
                        
                        // Clear form
                        $_POST = [];
                    } else {
                        $error = "Failed to update fare. Please try again.";
                    }
                }
            } catch (PDOException $e) {
                error_log("Update fare error: " . $e->getMessage());
                $error = "An error occurred while updating the fare. Please try again.";
            }
        }
    }
}

// Get list of routes for suggestion
try {
    $routes_stmt = $conn->prepare("SELECT DISTINCT route FROM fares ORDER BY route ASC");
    $routes_stmt->execute();
    $routes_list = $routes_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Get routes error: " . $e->getMessage());
    $routes_list = [];
}

$_csrf_token = generate_csrf_token();
?>

<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-pencil-square"></i> Update Fare Information
                    </h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Add a new fare or update an existing one for a specific route and date.
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

                    <form method="POST" name="updateFareForm" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_csrf_token); ?>">

                        <div class="mb-3">
                            <label for="route" class="form-label">Route Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="route" name="route" 
                                   placeholder="e.g., Ongata Rongai - CBD" 
                                   value="<?php echo htmlspecialchars($_POST['route'] ?? ''); ?>" 
                                   list="routeList" required>
                            <datalist id="routeList">
                                <?php foreach ($routes_list as $route): ?>
                                <option value="<?php echo htmlspecialchars($route); ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <small class="form-text text-muted">Enter the transport route (e.g., starting point - destination)</small>
                        </div>

                        <div class="mb-3">
                            <label for="fare" class="form-label">Fare Amount (KES) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">KES</span>
                                <input type="number" class="form-control" id="fare" name="fare" 
                                       placeholder="0.00" step="0.01" min="0.01" value="<?php echo htmlspecialchars($_POST['fare'] ?? ''); ?>" required>
                            </div>
                            <small class="form-text text-muted">Enter the fare amount in Kenyan Shillings</small>
                        </div>

                        <div class="mb-3">
                            <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="effective_date" name="effective_date" 
                                   value="<?php echo htmlspecialchars($_POST['effective_date'] ?? ''); ?>" required>
                            <small class="form-text text-muted">Date when this fare becomes effective</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-warning btn-lg" type="submit">
                                <i class="bi bi-check-circle"></i> Update Fare
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <a href="view_fares.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Fares
                    </a>
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