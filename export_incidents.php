<?php
/**
 * FairFare System - Export Incidents as CSV
 * 
 * Exports incident data with proper escaping and limits
 * 
 * @package FairFare
 * @version 1.0.0
 */

// Load config and auth ONLY (no HTML output - this is a download handler)
require_once "config.php";
require_once "includes/auth.php";

// Check admin BEFORE any output
require_admin();

// Limit export to prevent memory issues with large datasets
$limit = 10000;
$filename = 'incidents_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 (helps with Excel compatibility)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Write header
    fputcsv($output, ['ID', 'Reporter Name', 'Email', 'Phone', 'Route', 'Type', 'Description', 'Status', 'Resolved By', 'Resolved At', 'Created At']);
    
    // Get incidents with limit
    $stmt = $conn->prepare("SELECT id, name, email, phone, route, incident_type, description, status, resolved_by, resolved_at, created_at FROM incidents ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    
    $count = 0;
    while ($incident = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ensure all fields are properly escaped and formatted
        fputcsv($output, [
            $incident['id'],
            $incident['name'],
            $incident['email'],
            $incident['phone'] ?? '',
            $incident['route'],
            $incident['incident_type'] ?? '',
            $incident['description'],
            $incident['status'],
            $incident['resolved_by'] ?? '',
            $incident['resolved_at'] ?? '',
            $incident['created_at']
        ]);
        $count++;
    }
    
    fclose($output);
    
    // Log the export
    log_activity('EXPORT_INCIDENTS', "Exported $count incidents to CSV");
    exit();
    
} catch (Exception $e) {
    error_log("Export incidents error: " . $e->getMessage());
    die("Error exporting incidents: " . $e->getMessage());
}