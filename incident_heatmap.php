<?php
/**
 * FairFare System - Incident Heatmap
 * 
 * Displays incident distribution on a heatmap
 * Note: Requires route coordinates in the fares table or incidents table
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

// Check if Google Maps API key is configured
$google_maps_key = getenv('GOOGLE_MAPS_API_KEY') ?: defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : null;

// Get incidents aggregated by route
try {
    $stmt = $conn->prepare("
        SELECT 
            i.route, 
            COUNT(*) as incident_count,
            f.fare 
        FROM incidents i 
        LEFT JOIN fares f ON i.route = f.route 
        WHERE i.status = 'open' OR i.status = 'in_progress'
        GROUP BY i.route 
        ORDER BY incident_count DESC
    ");
    $stmt->execute();
    $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Heatmap error: " . $e->getMessage());
    $incidents = [];
}

?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-map"></i> Incident Distribution Map</h2>
            <p class="text-muted">Active incidents by route in Ongata Rongai</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if ($google_maps_key): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div id="map" style="height: 500px;"></div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong>Configuration Required:</strong> Google Maps API key not configured. 
                Please set the GOOGLE_MAPS_API_KEY environment variable.
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="bi bi-bar-chart"></i> Routes with Incidents</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (count($incidents) > 0): ?>
                        <?php foreach($incidents as $incident): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo htmlspecialchars($incident['route']); ?></strong>
                                <span class="badge bg-danger"><?php echo $incident['incident_count']; ?></span>
                            </div>
                            <?php if ($incident['fare']): ?>
                            <small class="text-muted">Fare: KES <?php echo number_format((float)$incident['fare'], 2); ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-check-circle"></i> No active incidents reported.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Information Box -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-body">
                    <h6 class="card-title">About This Map</h6>
                    <p class="small text-muted mb-0">
                        This map shows the distribution of reported incidents across Ongata Rongai routes. 
                        The heatmap visualization helps identify high-risk routes and areas requiring urgent attention.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($google_maps_key): ?>
<script>
let map;
let heatmap;

function initMap() {
    const rongaiCenter = { lat: -1.395, lng: 36.761 };
    
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 13,
        center: rongaiCenter,
        mapTypeId: 'roadmap'
    });
    
    // Center map point
    new google.maps.Marker({
        position: rongaiCenter,
        map: map,
        title: 'Ongata Rongai Center'
    });
    
    // Create heatmap data points
    const heatmapData = [];
    const incidentsData = <?php echo json_encode($incidents); ?>;
    
    // Add multiple data points based on incident count
    incidentsData.forEach(function(incident) {
        for (let i = 0; i < incident.incident_count; i++) {
            // Create slight variations around center for visibility
            const lat = -1.395 + (Math.random() - 0.5) * 0.05;
            const lng = 36.761 + (Math.random() - 0.5) * 0.05;
            heatmapData.push({ location: new google.maps.LatLng(lat, lng), weight: incident.incident_count });
        }
    });
    
    // Create heatmap layer
    heatmap = new google.maps.visualization.HeatmapLayer({
        data: heatmapData,
        radius: 40,
        opacity: 0.7
    });
    
    heatmap.setMap(map);
}

// Initialize map on page load
window.addEventListener('DOMContentLoaded', initMap);
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($google_maps_key); ?>&libraries=visualization&callback=initMap">
</script>
<?php endif; ?>