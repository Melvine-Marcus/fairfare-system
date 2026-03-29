<?php
/**
 * FairFare System - Home Page
 * 
 * Displays the landing page with system overview and key features
 * 
 * @package FairFare
 * @version 1.0.0
 */

require_once "includes/header.php";
?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('assets/images/matatu-bg.jpg') center/cover no-repeat;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            background: rgba(0, 0, 0, 0.6);
            padding: 3rem 2rem;
            border-radius: 10px;
            max-width: 800px;
            animation: slideUp 0.8s ease-out;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease-out;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-buttons {
            animation: zoomIn 1s ease-out 0.4s both;
        }

        .hero-buttons .btn {
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .hero-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
            background: #f4f6f9;
        }

        .features h2 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 3rem;
            color: #222;
        }

        .feature-card {
            background: white;
            border: none;
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-card h3 {
            color: #0d6efd;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: #212529;
            color: #fff;
            padding: 2rem 0;
            text-align: center;
            border-top: 3px solid #0d6efd;
        }

        footer p {
            margin: 0;
            font-size: 0.95rem;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes zoomIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons .btn {
                display: block;
                margin: 0.5rem 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>FairFare – Transparent Fare & Incident Reporting</h1>
            <p>Empowering commuters in Ongata Rongai with real-time fare information and easy incident reporting for safer, accountable transport.</p>
            <div class="hero-buttons">
                <a href="<?php echo APP_URL; ?>/report_incident.php" class="btn btn-warning btn-lg">Report an Incident</a>
                <a href="<?php echo APP_URL; ?>/view_fares.php" class="btn btn-info btn-lg">View Fares</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card">
                        <h3>📊 Fare Transparency</h3>
                        <p>Access up-to-date fare information for Ongata Rongai public transport, ensuring fair pricing and informed travel decisions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <h3>📝 Easy Incident Reporting</h3>
                        <p>Quickly report incidents like overcharging, misconduct, or unsafe conditions directly from your device.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card">
                        <h3>🔒 Transport Accountability</h3>
                        <p>Enhance accountability by tracking reported incidents and fare changes, fostering a safer and more reliable transport system.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</main>

<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; 2026 FairFare System. All rights reserved.</p>
    </div>
</footer>

</body>
</html>