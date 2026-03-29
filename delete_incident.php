<?php
/**
 * FairFare System - Archive Incident
 *
 * Soft-deletes an incident record (marks as closed)
 * Preserves data for audit purposes
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
            $check_stmt = $conn->prepare("SELECT id, status FROM incidents WHERE id = ?");
            $check_stmt->execute([$id]);
            $incident = $check_stmt->fetch();
            
            if ($incident) {
                // Soft delete: mark status as closed instead of hard delete
                // This preserves data for audit and compliance purposes
                $stmt = $conn->prepare("UPDATE incidents SET status = 'closed', updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $success = "Incident #" . $id . " has been closed and archived.";
                    log_activity('INCIDENT_ARCHIVED', "Incident #" . $id . " archived (previously: " . $incident['status'] . ")");
                    $_SESSION['incident_message'] = $success;
                } else {
                    $error = "Failed to archive incident. Please try again.";
                }
            } else {
                $error = "Incident not found.";
            }
        } catch (PDOException $e) {
            error_log("Archive incident error: " . $e->getMessage());
            $error = "An error occurred while archiving the incident.";
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