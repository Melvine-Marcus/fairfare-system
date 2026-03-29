<?php
/**
 * FairFare System - User Logout
 * 
 * Handles user session termination
 * 
 * @package FairFare
 * @version 1.0.0
 */

require_once "config.php";
require_once "includes/auth.php";

// Log the logout activity
if (is_logged_in()) {
    log_activity('LOGOUT', "User logged out");
}

// Destroy session
$_SESSION = [];
session_destroy();

// Redirect to login page with success message
header("Location: " . APP_URL . "/index.php?logout=1");
exit();
?>