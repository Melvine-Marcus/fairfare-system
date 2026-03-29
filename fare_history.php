<?php
/**
 * FairFare System - Fare Change History
 * 
 * Displays audit trail of all fare changes with pagination
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
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM fare_history");
    $count_stmt->execute();
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $per_page);
    
    // Get paginated history
    $stmt = $conn->prepare("SELECT * FROM fare_history ORDER BY changed_at DESC LIMIT ? OFFSET ?");
    $stmt->bindParam(1, $per_page, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Fare history error: " . $e->getMessage());
    $history = [];
    $total_records = 0;
    $total_pages = 1;
    $page = 1;
}

?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-clock-history"></i> Fare Change History</h2>
            <p class="text-muted">Complete audit trail of all fare modifications</p>
        </div>
    </div>

    <!-- History Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <?php if (count($history) > 0): ?>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Route</th>
                                <th>Old Fare</th>
                                <th>New Fare</th>
                                <th>Change</th>
                                <th>Changed By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($history as $row): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['route']); ?></strong></td>
                                <td><?php echo 'KES ' . number_format((float)$row['old_fare'], 2); ?></td>
                                <td><?php echo 'KES ' . number_format((float)$row['new_fare'], 2); ?></td>
                                <td>
                                    <?php 
                                    $change = (float)$row['new_fare'] - (float)$row['old_fare'];
                                    $badge_class = $change > 0 ? 'bg-danger' : ($change < 0 ? 'bg-success' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ($change > 0 ? '+' : '') . 'KES ' . number_format($change, 2); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['changed_by'] ?? 'System'); ?></td>
                                <td><small><?php echo date('M d, Y H:i', strtotime($row['changed_at'])); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info m-3 mb-0">
                        <i class="bi bi-info-circle"></i> No fare change history available.
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