<?php
/**
 * FairFare System - Fare Trend Analysis
 * 
 * Provides fare trend analysis and demand prediction
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
    // Get fare statistics by route
    $stmt = $conn->prepare("
        SELECT 
            f.route,
            f.fare as current_fare,
            COUNT(i.id) as incident_count,
            AVG(f.fare) as avg_fare,
            MAX(fh.new_fare) as latest_recorded_fare,
            COUNT(DISTINCT f.route) as occurrences
        FROM fares f 
        LEFT JOIN incidents i ON f.route = i.route 
        LEFT JOIN fare_history fh ON f.route = fh.route 
        GROUP BY f.route 
        ORDER BY incident_count DESC, f.route ASC
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fare prediction error: " . $e->getMessage());
    $data = [];
}

?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-graph-up"></i> Fare Trend Analysis</h2>
            <p class="text-muted">Historical fare trends and route demand indicators</p>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <?php 
        if (count($data) > 0) {
            $total_avg = array_sum(array_column($data, 'current_fare')) / count($data);
            $high_demand = count(array_filter($data, function($r) { return $r['incident_count'] > 5; }));
        ?>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Average Fare</p>
                    <h3 class="mb-0">KES <?php echo number_format($total_avg, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Routes</p>
                    <h3 class="mb-0"><?php echo count($data); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">High Demand Routes</p>
                    <h3 class="mb-0 text-danger"><?php echo $high_demand; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Active Incidents</p>
                    <h3 class="mb-0 text-warning"><?php echo array_sum(array_column($data, 'incident_count')); ?></h3>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Trends Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-table"></i> Route Fare Trends</h5>
                </div>
                <div class="table-responsive">
                    <?php if (count($data) > 0): ?>
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Route</th>
                                <th>Current Fare</th>
                                <th>Average Fare</th>
                                <th>Incidents</th>
                                <th>Trend Assessment</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($data as $row): ?>
                            <?php 
                            // Determine trend based on fare and incidents
                            $fare_threshold = 100;
                            $incident_threshold = 5;
                            
                            if ($row['incident_count'] > $incident_threshold && $row['current_fare'] > $fare_threshold) {
                                $trend_class = 'bg-danger';
                                $trend_text = 'High Demand & Concerns';
                            } elseif ($row['incident_count'] > $incident_threshold) {
                                $trend_class = 'bg-warning';
                                $trend_text = 'Monitor Closely';
                            } elseif ($row['current_fare'] > ($row['avg_fare'] + 20)) {
                                $trend_class = 'bg-info';
                                $trend_text = 'Price Increase';
                            } else {
                                $trend_class = 'bg-success';
                                $trend_text = 'Stable Route';
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['route']); ?></strong></td>
                                <td>KES <?php echo number_format((float)$row['current_fare'], 2); ?></td>
                                <td>KES <?php echo number_format((float)$row['avg_fare'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['incident_count'] > 0 ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $row['incident_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $trend_class; ?>">
                                        <?php echo $trend_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info m-3 mb-0">
                        <i class="bi bi-info-circle"></i> No fare data available for analysis.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Notes -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-lightbulb"></i> Analysis Guide</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>High Demand & Concerns:</strong> Route with above-average fares and multiple incidents. Requires immediate investigation.</li>
                        <li><strong>Monitor Closely:</strong> Route with reported incidents but stable fares. May need operational review.</li>
                        <li><strong>Price Increase:</strong> Current fare significantly higher than historical average. Pattern to track.</li>
                        <li><strong>Stable Route:</strong> Consistent performance with minimal incidents and stable pricing.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>