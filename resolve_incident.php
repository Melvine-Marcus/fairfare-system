<?php
/**
 * FairFare System - Resolve Incident
 *
 * Marks an incident as resolved with admin tracking
 *
 * @package FairFare
 * @version 1.0.0
 */

// Load config and auth ONLY (no HTML output - this is an action handler)
require_once "config.php";
require_once "includes/auth.php";

// Check admin BEFORE any output
require_admin();

$error = "";
$success = "";

if (isset($_GET['id']) && isset($_GET['csrf_token'])) {
    $id = intval($_GET['id']);
    $csrf_token = $_GET['csrf_token'];
    
    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = "Invalid security token. Please try again.";
    } else {
        try {
            // Verify incident exists
            $check_stmt = $conn->prepare("SELECT id FROM incidents WHERE id = ?");
            $check_stmt->execute([$id]);
            $incident = $check_stmt->fetch();
            
            if ($incident) {
                // Update status to resolved with admin ID
                $admin_id = get_current_user_id();
                $stmt = $conn->prepare("UPDATE incidents SET status = 'resolved', resolved_by = ?, resolved_at = NOW(), updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$admin_id, $id])) {
                    $success = "Incident #" . $id . " has been marked as resolved.";
                    log_activity('INCIDENT_RESOLVED', "Incident #" . $id . " marked as resolved");
                    $_SESSION['incident_message'] = $success;
                } else {
                    $error = "Failed to resolve incident. Please try again.";
                }
            } else {
                $error = "Incident not found.";
            }
        } catch (PDOException $e) {
            error_log("Resolve incident error: " . $e->getMessage());
            $error = "An error occurred while resolving the incident.";
        }
    }
} else {
    $error = "Invalid request parameters.";
}

if (!empty($error)) {
    $_SESSION['incident_error'] = $error;
}

header("Location: view_incidents.php?status=all");
exit();