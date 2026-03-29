<?php
/**
 * FairFare System - Admin Dashboard
 * 
 * Main administrative dashboard with system overview
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

try {
    // Get incident statistics
    $incident_stats = $conn->prepare("SELECT status, COUNT(*) as count FROM incidents GROUP BY status");
    $incident_stats->execute();
    $incidents_by_status = $incident_stats->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $total_incidents = array_sum($incidents_by_status);
    
    // Get fare statistics
    $fare_stmt = $conn->prepare("SELECT COUNT(*) as total FROM fares");
    $fare_stmt->execute();
    $total_fares = $fare_stmt->fetch()['total'];
    
    // Get user count
    $users_stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
    $users_stmt->execute();
    $total_users = $users_stmt->fetch()['total'];
    
    // Get recent incidents
    $recent_stmt = $conn->prepare("SELECT id, name, route, incident_type, status, created_at FROM incidents ORDER BY created_at DESC LIMIT 5");
    $recent_stmt->execute();
    $recent_incidents = $recent_stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $incidents_by_status = [];
    $total_incidents = 0;
    $total_fares = 0;
    $total_users = 0;
    $recent_incidents = [];
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <p class="text-muted">Welcome back, <strong><?php echo htmlspecialchars(get_current_username()); ?></strong>!</p>
        </div>
        <div class="col-md-4 text-end">
            <p class="text-muted">Last activity: <strong><?php echo date('M d, Y H:i'); ?></strong></p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <p class="text-muted mb-1">Total Incidents</p>
                            <h3 class="mb-0"><?php echo $total_incidents; ?></h3>
                        </div>
                        <div class="text-danger" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <p class="text-muted mb-1">Open Incidents</p>
                            <h3 class="mb-0 text-danger"><?php echo $incidents_by_status['open'] ?? 0; ?></h3>
                        </div>
                        <div class="text-warning" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <p class="text-muted mb-1">Total Fare Entries</p>
                            <h3 class="mb-0 text-info"><?php echo $total_fares; ?></h3>
                        </div>
                        <div class="text-info" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <p class="text-muted mb-1">Active Users</p>
                            <h3 class="mb-0 text-success"><?php echo $total_users; ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 2.5rem; opacity: 0.2;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="view_incidents.php" class="btn btn-outline-danger w-100">
                                <i class="bi bi-exclamation-triangle"></i> View All Incidents
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="view_incidents.php?status=open" class="btn btn-outline-warning w-100">
                                <i class="bi bi-clock"></i> Open Incidents
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="update_fares.php" class="btn btn-outline-info w-100">
                                <i class="bi bi-plus-circle"></i> Add New Fare
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="fare_history.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-clock-history"></i> Fare History
                            </a>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3 mb-2">
                            <a href="export_incidents.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-download"></i> Export Incidents
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="admin_logs.php" class="btn btn-outline-dark w-100">
                                <i class="bi bi-file-text"></i> Activity Logs
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="export_documentation.php" class="btn btn-outline-primary w-100" target="_blank">
                                <i class="bi bi-book"></i> Download Docs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Incidents -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-clock"></i> Recent Incidents (Last 5)</h5>
                </div>
                <div class="table-responsive">
                    <?php if (count($recent_incidents) > 0): ?>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Route</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($recent_incidents as $incident): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($incident['id']); ?></td>
                                <td><?php echo htmlspecialchars($incident['name']); ?></td>
                                <td><?php echo htmlspecialchars($incident['route']); ?></td>
                                <td><?php echo htmlspecialchars($incident['incident_type'] ?? 'General'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $incident['status'] === 'open' ? 'danger' : ($incident['status'] === 'in_progress' ? 'warning' : 'success'); ?>">
                                        <?php echo ucfirst($incident['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($incident['created_at'])); ?></td>
                                <td>
                                    <a href="view_incidents.php" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info m-3 mb-0">
                        <i class="bi bi-info-circle"></i> No incidents reported yet.
                    </div>
                    <?php endif; ?>
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