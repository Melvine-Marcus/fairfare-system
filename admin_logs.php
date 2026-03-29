<?php
/**
 * FairFare System - Admin Activity Logs
 * 
 * Displays audit trail of admin activities with pagination
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

// Pagination setup
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 25;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM activity_logs WHERE user_id IS NOT NULL");
    $count_stmt->execute();
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);
    
    // Get paginated admin logs
    $stmt = $conn->prepare("SELECT user_id, username, action, details, ip_address, created_at FROM activity_logs WHERE user_id IS NOT NULL ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindParam(1, $per_page, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Admin logs error: " . $e->getMessage());
    $logs = [];
    $total_records = 0;
    $total_pages = 1;
    $page = 1;
}

?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-file-text"></i> Admin Activity Logs</h2>
            <p class="text-muted">Complete audit trail of administrative actions</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <?php if (count($logs) > 0): ?>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($logs as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                    <br><small class="text-muted">ID: <?php echo htmlspecialchars($row['user_id']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($row['action']); ?></span>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($row['details'] ?? '', 0, 60)); ?><?php echo strlen($row['details'] ?? '') > 60 ? '...' : ''; ?></small>
                                </td>
                                <td><small class="font-monospace"><?php echo htmlspecialchars($row['ip_address'] ?? 'N/A'); ?></small></td>
                                <td><small><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info m-3 mb-0">
                        <i class="bi bi-info-circle"></i> No admin activity logs available.
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <div class="text-center text-muted mb-3">
                    <small>Showing page <?php echo $page; ?> of <?php echo $total_pages; ?> (<?php echo $total_records; ?> total records)</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>