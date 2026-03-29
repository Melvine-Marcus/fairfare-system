<?php
/**
 * FairFare System - Header/Navigation Template
 * 
 * Common header and navigation for all pages
 * 
 * @package FairFare
 * @version 1.0.0
 */

require_once dirname(__FILE__) . "/../config.php";
require_once dirname(__FILE__) . "/auth.php";

$csrf_token = generate_csrf_token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #fff !important;
        }

        .nav-link {
            margin-left: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #ffc107 !important;
            padding-left: 0.5rem;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.3);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }

        footer {
            background: #212529;
            color: #fff;
            padding: 2rem 0;
            text-align: center;
            border-top: 3px solid #0d6efd;
            margin-top: auto;
        }

        footer p {
            margin: 0;
            font-size: 0.95rem;
        }

        .alert-dismissible .btn-close {
            padding: 0.5rem;
        }

        /* Loading indicator */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
        }

        .loading.active {
            display: block;
        }

        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(0, 0, 0, 0.1);
                border-radius: 5px;
                margin-top: 0.5rem;
                padding: 0.5rem 0;
            }

            .nav-link {
                margin-left: 0;
                padding-left: 1rem !important;
            }

            .nav-link:hover {
                background: rgba(255, 255, 255, 0.1);
                border-radius: 3px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
                <i class="bi bi-shield-check"></i> FairFare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/report_incident.php">
                            <i class="bi bi-exclamation-triangle"></i> Report Incident
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/view_fares.php">
                            <i class="bi bi-receipt"></i> View Fares
                        </a>
                    </li>
                    
                    <?php if (is_admin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="adminMenu">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin_dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/view_incidents.php">View Incidents</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/update_fares.php">Update Fares</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/fare_history.php">Fare History</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin_logs.php">Activity Logs</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/export_incidents.php">Export Incidents</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(get_current_username() ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/logout.php">
                                <i class="bi bi-door-left"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/register.php">
                            <i class="bi bi-person-plus"></i> Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">