<?php
/**
 * FairFare System - Documentation Export to Word
 * 
 * Generates a downloadable Word document with complete project documentation
 * 
 * @package FairFare
 * @version 1.0.0
 */

require_once "config.php";
require_once "includes/auth.php";

// Only admins can export documentation
if (!is_admin()) {
    header("Location: index.php");
    exit();
}

// Generate comprehensive HTML that can be saved as Word
function generateWordDocument() {
    $title = "FairFare System - Complete Project Documentation";
    $date = date('F d, Y');
    $version = "1.0.0";
    
    $content = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FairFare System Documentation</title>
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            line-height: 1.6;
            margin: 1in;
            color: #333;
        }
        h1 {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
            font-size: 24pt;
        }
        h2 {
            color: #0d6efd;
            border-left: 5px solid #0d6efd;
            padding-left: 10px;
            font-size: 18pt;
            margin-top: 20px;
        }
        h3 {
            color: #005a87;
            font-size: 14pt;
            margin-top: 15px;
        }
        .title-page {
            text-align: center;
            page-break-after: always;
            padding: 100px 0;
        }
        .title-page h1 {
            border: none;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        th {
            background-color: #0d6efd;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background-color: #f4f4f4;
            padding: 15px;
            border-left: 4px solid #0d6efd;
            overflow-x: auto;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin: 20px 0;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        ul, ol {
            margin: 10px 0;
            padding-left: 30px;
        }
        li {
            margin: 5px 0;
        }
        .section-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<!-- Title Page -->
<div class="title-page">
    <h1>FairFare System</h1>
    <h2 style="border: none; color: #666;">Complete Project Documentation</h2>
    <p style="margin-top: 30px;">
        <strong>Version:</strong> 1.0.0<br>
        <strong>Date:</strong> March 19, 2026<br>
        <strong>Author:</strong> Development Team
    </p>
</div>

<!-- Table of Contents -->
<div class="section-break">
    <h2>Table of Contents</h2>
    <ol>
        <li>Project Overview</li>
        <li>System Architecture</li>
        <li>Database Schema</li>
        <li>Installation Guide</li>
        <li>User Manual</li>
        <li>Admin Manual</li>
        <li>Security Features</li>
        <li>Troubleshooting Guide</li>
        <li>Maintenance & Support</li>
    </ol>
</div>

<!-- Project Overview -->
<div class="section-break">
    <h2>1. Project Overview</h2>
    
    <h3>What is FairFare System?</h3>
    <p>FairFare is a comprehensive web-based platform designed for the Ongata Rongai transport sector. It provides real-time fare transparency, easy incident reporting, and accountability tracking.</p>
    
    <h3>Key Features</h3>
    <ul>
        <li><strong>Fare Transparency:</strong> Real-time access to current fare information for all transport routes</li>
        <li><strong>Incident Reporting:</strong> Easy-to-use reporting system for transport-related incidents</li>
        <li><strong>Accountability Tracking:</strong> Centralized system for monitoring and resolving issues</li>
        <li><strong>Admin Management:</strong> Comprehensive tools for administrators</li>
    </ul>
    
    <h3>Technologies Used</h3>
    <ul>
        <li><strong>Backend:</strong> PHP 7.4+</li>
        <li><strong>Database:</strong> MySQL/MariaDB</li>
        <li><strong>Frontend:</strong> HTML5, CSS3, Bootstrap 5.3.2</li>
        <li><strong>Libraries:</strong> Bootstrap Icons, JavaScript ES6+</li>
    </ul>
</div>

<!-- System Architecture -->
<div class="section-break">
    <h2>2. System Architecture</h2>
    
    <h3>Application File Structure</h3>
    <pre>FairFareSystem/
├── index.php                 # Landing page
├── login.php                 # User authentication
├── register.php              # New user registration  
├── logout.php                # Session termination
├── config.php                # Database configuration
├── .htaccess                 # URL rewriting rules
│
├── includes/
│   ├── header.php            # Navigation & layout
│   ├── auth.php              # Authentication functions
│   └── config.php            # System configuration
│
├── Incident Management/
│   ├── report_incident.php
│   ├── view_incidents.php
│   ├── resolve_incident.php
│   ├── delete_incident.php
│   └── export_incidents.php
│
├── Fare Management/
│   ├── view_fares.php
│   ├── update_fares.php
│   ├── fare_history.php
│   ├── fare_prediction.php
│   └── route_map.php
│
├── Admin Functions/
│   ├── admin_dashboard.php
│   ├── admin_logs.php
│   ├── incident_heatmap.php
│   └── send_sms.php
│
└── assets/
    └── images/</pre>
    
    <h3>Data Flow</h3>
    <ol>
        <li>User Request arrives</li>
        <li>Header.php included (navigation)</li>
        <li>Config.php loaded (database connection)</li>
        <li>Auth.php checks permissions</li>
        <li>Process request (CRUD operations)</li>
        <li>Generate response (HTML output)</li>
        <li>Footer section included</li>
        <li>Browser renders output</li>
    </ol>
</div>

<!-- Database Schema -->
<div class="section-break">
    <h2>3. Database Schema</h2>
    
    <h3>Users Table</h3>
    <p>Stores user account information</p>
    <pre>CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT 1,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);</pre>
    
    <h3>Fares Table</h3>
    <p>Stores fare information for routes</p>
    <pre>CREATE TABLE fares (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route VARCHAR(100) NOT NULL,
    fare DECIMAL(10, 2) NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);</pre>
    
    <h3>Incidents Table</h3>
    <p>Stores reported incidents</p>
    <pre>CREATE TABLE incidents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    route VARCHAR(100) NOT NULL,
    incident_type VARCHAR(50),
    description LONGTEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);</pre>
    
    <h3>Activity Logs Table</h3>
    <p>Logs all user activities</p>
    <pre>CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);</pre>
</div>

<!-- Installation Guide -->
<div class="section-break">
    <h2>4. Installation Guide</h2>
    
    <h3>System Requirements</h3>
    <ul>
        <li>Apache 2.4+</li>
        <li>PHP 7.4 or higher</li>
        <li>MySQL 5.7+ or MariaDB 10.2+</li>
        <li>Apache with mod_rewrite enabled</li>
    </ul>
    
    <h3>Step-by-Step Installation</h3>
    
    <h4>Step 1: Create Database</h4>
    <pre>CREATE DATABASE fairfare_system_db;
USE fairfare_system_db;
-- Execute all table creation scripts</pre>
    
    <h4>Step 2: Upload Files</h4>
    <p>Place all files in web root (typically C:\xampp\htdocs\FairFareSystem\ for XAMPP)</p>
    
    <h4>Step 3: Configure Database</h4>
    <p>Edit config.php and update database credentials</p>
    
    <h4>Step 4: Create Logs Directory</h4>
    <pre>mkdir logs
chmod 755 logs</pre>
    
    <h4>Step 5: Set Permissions</h4>
    <pre>chmod 755 /path/to/FairFareSystem
chmod 644 /path/to/FairFareSystem/*.php</pre>
    
    <h4>Step 6: Verify Installation</h4>
    <ol>
        <li>Navigate to http://localhost/FairFareSystem/</li>
        <li>Register a new account</li>
        <li>Login and verify functionality</li>
    </ol>
</div>

<!-- User Manual -->
<div class="section-break">
    <h2>5. User Manual</h2>
    
    <h3>Registration</h3>
    <ol>
        <li>Click "Register" link on navigation bar</li>
        <li>Fill in the registration form:
            <ul>
                <li>Username: 3-50 characters</li>
                <li>Email: Valid email address</li>
                <li>Password: Minimum 8 characters</li>
                <li>Confirm Password: Must match password</li>
            </ul>
        </li>
        <li>Click "Create Account"</li>
        <li>Login with your credentials</li>
    </ol>
    
    <h3>Viewing Fares</h3>
    <ol>
        <li>Click "View Fares" in navigation</li>
        <li>Browse list of all routes and current fares</li>
        <li>Use search to find specific routes</li>
        <li>View fare effective date and last update</li>
    </ol>
    
    <h3>Reporting an Incident</h3>
    <ol>
        <li>Click "Report Incident" (requires login)</li>
        <li>Fill in the incident form:
            <ul>
                <li>Route: Name of the route where incident occurred</li>
                <li>Incident Type: Select appropriate type</li>
                <li>Description: Detailed description (minimum 10 characters)</li>
                <li>Phone: Optional contact number</li>
            </ul>
        </li>
        <li>Click "Submit Incident Report"</li>
        <li>Receive confirmation message</li>
    </ol>
</div>

<!-- Admin Manual -->
<div class="section-break">
    <h2>6. Admin Manual</h2>
    
    <h3>Admin Dashboard</h3>
    <p>Displays key system statistics and recent activities:</p>
    <ul>
        <li>Total incidents count</li>
        <li>Open incidents count</li>
        <li>Total fare entries</li>
        <li>Active users count</li>
        <li>Recent incidents (last 5)</li>
    </ul>
    
    <h3>Managing Incidents</h3>
    <ul>
        <li><strong>View:</strong> Navigate to View Incidents page</li>
        <li><strong>Filter:</strong> Filter by status (Open, In Progress, Resolved)</li>
        <li><strong>Resolve:</strong> Mark incident as resolved</li>
        <li><strong>Delete:</strong> Remove incident from system</li>
        <li><strong>Export:</strong> Download incidents as CSV</li>
    </ul>
    
    <h3>Managing Fares</h3>
    <ul>
        <li><strong>Add:</strong> Go to Update Fares, enter route, amount, effective date</li>
        <li><strong>View:</strong> Navigate to View Fares page</li>
        <li><strong>History:</strong> Check Fare History page for all changes</li>
        <li><strong>Track:</strong> See who made changes and when</li>
    </ul>
    
    <h3>Activity Logs</h3>
    <p>Monitor all system activities:</p>
    <ul>
        <li>Admin actions</li>
        <li>User login/logout</li>
        <li>Incident reports</li>
        <li>Fare updates</li>
    </ul>
</div>

<!-- Security Features -->
<div class="section-break">
    <h2>7. Security Features</h2>
    
    <h3>Authentication Security</h3>
    <ul>
        <li>Bcrypt password hashing (PASSWORD_BCRYPT)</li>
        <li>Minimum 8 character passwords</li>
        <li>Secure session management</li>
        <li>HTTPOnly and Secure cookies</li>
        <li>Session auto-expiration (30 minutes)</li>
    </ul>
    
    <h3>Data Protection</h3>
    <ul>
        <li>All queries use prepared statements</li>
        <li>SQL injection prevention</li>
        <li>XSS attack prevention</li>
        <li>Input sanitization and validation</li>
        <li>CSRF token protection</li>
    </ul>
    
    <h3>Security Headers</h3>
    <pre>X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin</pre>
</div>

<!-- Troubleshooting -->
<div class="section-break">
    <h2>8. Troubleshooting Guide</h2>
    
    <h3>Database Connection Failed</h3>
    <p><strong>Problem:</strong> Error message about database connection</p>
    <p><strong>Solution:</strong></p>
    <ul>
        <li>Verify MySQL service is running</li>
        <li>Check database name in config.php</li>
        <li>Verify username and password</li>
        <li>Ensure database is created</li>
    </ul>
    
    <h3>Login Not Working</h3>
    <p><strong>Problem:</strong> Cannot login despite correct credentials</p>
    <p><strong>Solution:</strong></p>
    <ul>
        <li>Clear browser cache and cookies</li>
        <li>Verify user account exists in database</li>
        <li>Check username/email spelling</li>
        <li>Ensure account is not disabled</li>
    </ul>
    
    <h3>Permission Denied</h3>
    <p><strong>Problem:</strong> 403 Forbidden errors</p>
    <p><strong>Solution:</strong></p>
    <ul>
        <li>Set correct file permissions (644 for files, 755 for directories)</li>
        <li>Verify mod_rewrite is enabled</li>
        <li>Check .htaccess file exists</li>
    </ul>
    
    <h3>Forms Not Submitting</h3>
    <p><strong>Problem:</strong> Form submits but no action occurs</p>
    <p><strong>Solution:</strong></p>
    <ul>
        <li>Check CSRF token is valid</li>
        <li>Verify form field names match code</li>
        <li>Check server error logs</li>
        <li>Look for JavaScript errors in browser console</li>
    </ul>
</div>

<!-- Maintenance & Support -->
<div class="section-break">
    <h2>9. Maintenance & Support</h2>
    
    <h3>Regular Maintenance Tasks</h3>
    
    <h4>Daily</h4>
    <ul>
        <li>Monitor system logs</li>
        <li>Check for error messages</li>
        <li>Verify system uptime</li>
    </ul>
    
    <h4>Weekly</h4>
    <ul>
        <li>Review new incidents</li>
        <li>Check database size</li>
        <li>Review user activities</li>
    </ul>
    
    <h4>Monthly</h4>
    <ul>
        <li>Database backup</li>
        <li>Performance review</li>
        <li>Security audit</li>
    </ul>
    
    <h4>Quarterly</h4>
    <ul>
        <li>System updates</li>
        <li>Security patches</li>
        <li>Feature planning</li>
    </ul>
    
    <h3>Version Information</h3>
    <p><strong>Current Version:</strong> 1.0.0</p>
    <p><strong>Release Date:</strong> March 2026</p>
    <p><strong>Last Updated:</strong> March 19, 2026</p>
</div>

<!-- End -->
<div style="page-break-before: always;">
    <h2>Document Information</h2>
    <p style="margin-top: 30px;">
        <strong>Title:</strong> FairFare System - Complete Project Documentation<br>
        <strong>Version:</strong> 1.0.0<br>
        <strong>Generated:</strong> March 19, 2026<br>
        <strong>Author:</strong> Development Team<br>
        <strong>Copyright:</strong> 2026 - All Rights Reserved
    </p>
    <p style="margin-top: 50px; color: #999; font-size: 12px;">
        This documentation is comprehensive and should serve as the complete reference guide for the FairFare System. For additional support or clarification, please contact the development team.
    </p>
</div>

</body>
</html>
HTML;
    
    return $content;
}

// Set headers for Word document download
header('Content-Type: application/msword');
header('Content-Disposition: attachment; filename="FairFare_System_Documentation_' . date('Y-m-d') . '.doc"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output the document
echo generateWordDocument();
exit();
?>