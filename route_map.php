<?php
/**
 * FairFare System - Route Map
 * 
 * Displays all public transport routes with fares
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

try {
    // Get all routes with fare and incident information
    $stmt = $conn->prepare("
        SELECT 
            f.id,
            f.route, 
            f.fare,
            f.effective_date,
            (SELECT COUNT(*) FROM incidents WHERE route = f.route AND status != 'closed') as open_incidents
        FROM fares f 
        WHERE f.effective_date <= CURDATE() 
        ORDER BY f.route ASC
    ");
    $stmt->execute();
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Route map error: " . $e->getMessage());
    $routes = [];
}

?>

<div class="container-fluid mt-4 mb-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-geo-alt"></i> Ongata Rongai Public Transport Routes</h2>
            <p class="text-muted">Map of all active transport routes with current fares</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if ($google_maps_key): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div id="map" style="height: 500px; border-radius: 8px;"></div>
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
                    <h5 class="card-title mb-0"><i class="bi bi-list-ul"></i> Active Routes</h5>
                </div>
                <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    <?php if (count($routes) > 0): ?>
                        <?php foreach($routes as $route): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong><?php echo htmlspecialchars($route['route']); ?></strong>
                                    <br>
                                    <small class="text-muted">Fare: <strong>KES <?php echo number_format((float)$route['fare'], 2); ?></strong></small>
                                    <?php if ($route['open_incidents'] > 0): ?>
                                    <br>
                                    <small class="text-danger">
                                        <i class="bi bi-exclamation-circle"></i> 
                                        <?php echo $route['open_incidents']; ?> active incident<?php echo $route['open_incidents'] !== 1 ? 's' : ''; ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-inbox"></i> No active routes configured.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Legend -->
            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">Legend</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <i class="bi bi-geo-fill text-info"></i> Standard Route
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-exclamation-circle text-danger"></i> Route with Active Incidents
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($google_maps_key): ?>
<script>
let map;
let markers = [];

function initMap() {
    const rongaiCenter = { lat: -1.395, lng: 36.761 };
    
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 13,
        center: rongaiCenter,
        mapTypeId: 'roadmap'
    });
    
    // Add center marker
    new google.maps.Marker({
        position: rongaiCenter,
        map: map,
        title: 'Ongata Rongai Center'
    });
    
    // Add route markers
    const routesData = <?php echo json_encode($routes); ?>;
    
    routesData.forEach(function(route, index) {
        // Create variations around center
        const lat = -1.395 + (Math.sin(index * 0.5) * 0.08);
        const lng = 36.761 + (Math.cos(index * 0.5) * 0.08);
        const position = { lat: lat, lng: lng };
        
        const markerColor = route.open_incidents > 0 ? 'FF0000' : '0099FF';
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: route.route,
            icon: 'http://maps.google.com/mapfiles/ms/icons/' + markerColor + '-dot.png'
        });
        
        // Create info window
        const infoContent = '<div style="max-width: 200px;">' +
            '<strong style="font-size: 14px;">' + route.route + '</strong><br>' +
            '<strong>Fare:</strong> KES ' + parseFloat(route.fare).toFixed(2) + '<br>' +
            '<strong>Effective:</strong> ' + route.effective_date + '<br>';
        
        if (route.open_incidents > 0) {
            infoContent += '<span style="color: #dc3545;">' +
                '<i class="bi bi-exclamation-circle"></i> ' + route.open_incidents + ' Active Incident(s)</span>';
        }
        
        infoContent += '</div>';
        
        const infoWindow = new google.maps.InfoWindow({
            content: infoContent
        });
        
        marker.addListener('click', function() {
            // Close all other info windows
            markers.forEach(function(m) {
                if (m.infoWindow) m.infoWindow.close();
            });
            infoWindow.open(map, marker);
            marker.infoWindow = infoWindow;
        });
        
        markers.push(marker);
        marker.infoWindow = infoWindow;
    });
}

// Initialize map on page load
window.addEventListener('DOMContentLoaded', initMap);
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo htmlspecialchars($google_maps_key); ?>&callback=initMap">
</script>
<?php endif; ?>

});

}

</script>

<script async defer
src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap">
</script>

</body>

</html>