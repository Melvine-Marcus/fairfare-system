<?php
/**
 * FairFare System - View Incidents Page
 * 
 * Displays all reported incidents for admin review
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

$filter_status = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'created_at';
$sort_order = $_GET['order'] ?? 'DESC';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Validate sorting parameters
$valid_sorts = ['created_at', 'id', 'route', 'status'];
$valid_orders = ['ASC', 'DESC'];

if (!in_array($sort_by, $valid_sorts)) $sort_by = 'created_at';
if (!in_array($sort_order, $valid_orders)) $sort_order = 'DESC';

// Build query based on filter
$query = "SELECT id, user_id, name, email, phone, route, incident_type, description, status, created_at FROM incidents";
$count_query = "SELECT COUNT(*) as total FROM incidents";
$params = [];

if ($filter_status !== 'all') {
    $query .= " WHERE status = ?";
    $count_query .= " WHERE status = ?";
    $params[] = $filter_status;
}

// Get total count for pagination
try {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_incidents = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_incidents / $per_page);
} catch (PDOException $e) {
    error_log("Count incidents error: " . $e->getMessage());
    $total_incidents = 0;
    $total_pages = 0;
}

$query .= " ORDER BY " . $sort_by . " " . $sort_order . " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $incidents = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("View incidents error: " . $e->getMessage());
    $incidents = [];
    $total_incidents = 0;
    $total_pages = 0;
}

// Count incidents by status
$count_query = "SELECT status, COUNT(*) as count FROM incidents GROUP BY status";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute();
$status_counts = $count_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$status_badges = [
    'open' => 'bg-danger',
    'in_progress' => 'bg-warning',
    'resolved' => 'bg-success',
    'closed' => 'bg-secondary'
];
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-exclamation-triangle"></i> Reported Incidents</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="export_incidents.php" class="btn btn-secondary">
                <i class="bi bi-download"></i> Export as CSV
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Incidents</h5>
                    <p class="display-6 text-primary"><?php echo $total_incidents; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Open</h5>
                    <p class="display-6 text-danger"><?php echo $status_counts['open'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>In Progress</h5>
                    <p class="display-6 text-warning"><?php echo $status_counts['in_progress'] ?? 0; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Resolved</h5>
                    <p class="display-6 text-success"><?php echo $status_counts['resolved'] ?? 0; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Sort -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Filter by Status</label>
                    <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Incidents</option>
                        <option value="open" <?php echo $filter_status === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $filter_status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $filter_status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Incidents Table -->
    <div class="card">
        <div class="table-responsive">
            <?php if ($total_incidents > 0): ?>
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th><i class="bi bi-hash"></i> ID</th>
                        <th><i class="bi bi-person"></i> Name</th>
                        <th><i class="bi bi-envelope"></i> Email</th>
                        <th><i class="bi bi-map"></i> Route</th>
                        <th><i class="bi bi-flag"></i> Type</th>
                        <th><i class="bi bi-file-text"></i> Description</th>
                        <th><i class="bi bi-tag"></i> Status</th>
                        <th><i class="bi bi-calendar"></i> Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($incidents as $incident): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($incident['id']); ?></td>
                        <td><?php echo htmlspecialchars($incident['name']); ?></td>
                        <td><?php echo htmlspecialchars($incident['email']); ?></td>
                        <td><?php echo htmlspecialchars($incident['route']); ?></td>
                        <td><?php echo htmlspecialchars($incident['incident_type'] ?? 'General'); ?></td>
                        <td>
                            <small><?php echo htmlspecialchars(substr($incident['description'], 0, 50)); ?>...</small>
                        </td>
                        <td>
                            <span class="badge <?php echo $status_badges[$incident['status']] ?? 'bg-secondary'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $incident['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($incident['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <?php if ($incident['status'] !== 'resolved' && $incident['status'] !== 'closed'): ?>
                                <a href="resolve_incident.php?id=<?php echo $incident['id']; ?>&csrf_token=<?php echo urlencode(generate_csrf_token()); ?>" 
                                   class="btn btn-sm btn-success" title="Mark as Resolved">
                                    <i class="bi bi-check-circle"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($incident['status'] !== 'closed'): ?>
                                <a href="delete_incident.php?id=<?php echo $incident['id']; ?>&csrf_token=<?php echo urlencode(generate_csrf_token()); ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Archive this incident? This action cannot be undone.')" title="Archive">
                                    <i class="bi bi-archive"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info m-3" role="alert">
                <i class="bi bi-info-circle"></i> No incidents reported yet.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=1">First</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php else: ?>
            <li class="page-item disabled"><span class="page-link">First</span></li>
            <li class="page-item disabled"><span class="page-link">Previous</span></li>
            <?php endif; ?>

            <?php
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $page + 1; ?>">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?status=<?php echo $filter_status; ?>&page=<?php echo $total_pages; ?>">Last</a>
            </li>
            <?php else: ?>
            <li class="page-item disabled"><span class="page-link">Next</span></li>
            <li class="page-item disabled"><span class="page-link">Last</span></li>
            <?php endif; ?>
        </ul>
        <div class="text-center text-muted">
            <small>Page <?php echo $page; ?> of <?php echo $total_pages; ?> | Total: <?php echo $total_incidents; ?> incidents</small>
        </div>
    </nav>
    <?php endif; ?>

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