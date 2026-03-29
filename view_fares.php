<?php
/**
 * FairFare System - View Fares Page
 * 
 * Displays current fare information for transport routes
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

$search_route = sanitize_input($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'effective_date';
$sort_order = $_GET['order'] ?? 'DESC';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

// Validate sort parameters to prevent injection
$valid_sorts = ['effective_date', 'route', 'fare'];
$valid_orders = ['ASC', 'DESC'];
if (!in_array($sort_by, $valid_sorts)) $sort_by = 'effective_date';
if (!in_array($sort_order, $valid_orders)) $sort_order = 'DESC';

// Build count query
$count_query = "SELECT COUNT(*) as total FROM fares WHERE 1=1 ";
$params = [];

if (!empty($search_route)) {
    $count_query .= " AND route LIKE ?";
    $params[] = "%" . $search_route . "%";
}

// Get total count
try {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_fares = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_fares / $per_page);
} catch (PDOException $e) {
    error_log("Count fares error: " . $e->getMessage());
    $total_fares = 0;
    $total_pages = 0;
}

// Build main query with pagination
$query = "SELECT id, route, fare, effective_date, updated_at FROM fares WHERE 1=1 ";
if (!empty($search_route)) {
    $query .= " AND route LIKE ?";
}
$query .= " ORDER BY " . $sort_by . " " . $sort_order . " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $fares = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("View fares error: " . $e->getMessage());
    $fares = [];
    $total_fares = 0;
    $total_pages = 0;
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-receipt"></i> Current Fares - Ongata Rongai Routes</h2>
        </div>
        <div class="col-md-4 text-end">
            <?php if (is_admin()): ?>
            <a href="update_fares.php" class="btn btn-primary">
                <i class="bi bi-pencil-square"></i> Update Fare
            </a>
            <a href="fare_history.php" class="btn btn-info">
                <i class="bi bi-clock-history"></i> History
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-9">
                    <input type="text" class="form-control" name="search" placeholder="Search by route name..." 
                           value="<?php echo htmlspecialchars($search_route); ?>">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Fares Table -->
    <div class="card">
        <div class="table-responsive">
            <?php if (count($fares) > 0): ?>
            <table class="table table-hover mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th><i class="bi bi-map"></i> Route</th>
                        <th class="text-end"><i class="bi bi-cash-coin"></i> Fare (KES)</th>
                        <th><i class="bi bi-calendar-check"></i> Effective Date</th>
                        <th><i class="bi bi-pencil"></i> Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fares as $fare): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($fare['route']); ?></strong>
                        </td>
                        <td class="text-end">
                            <span class="badge bg-success" style="font-size: 1rem;">
                                KES <?php echo number_format($fare['fare'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($fare['effective_date'])); ?></td>
                        <td>
                            <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($fare['updated_at'])); ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info m-3" role="alert">
                <i class="bi bi-info-circle"></i> No fares available at the moment.
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
                <a class="page-link" href="?search=<?php echo urlencode($search_route); ?>&page=1">First</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?search=<?php echo urlencode($search_route); ?>&page=<?php echo $page - 1; ?>">Previous</a>
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
                <a class="page-link" href="?search=<?php echo urlencode($search_route); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?search=<?php echo urlencode($search_route); ?>&page=<?php echo $page + 1; ?>">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?search=<?php echo urlencode($search_route); ?>&page=<?php echo $total_pages; ?>">Last</a>
            </li>
            <?php else: ?>
            <li class="page-item disabled"><span class="page-link">Next</span></li>
            <li class="page-item disabled"><span class="page-link">Last</span></li>
            <?php endif; ?>
        </ul>
        <div class="text-center text-muted">
            <small>Page <?php echo $page; ?> of <?php echo $total_pages; ?> | Total: <?php echo $total_fares; ?> fares</small>
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