<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forces Academy LMS - Welcome</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }

        /* Hero Header */
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0a4db8 100%);
            color: white;
            padding: 100px 0 120px;
            clip-path: polygon(0 0, 100% 0, 100% 92%, 0 100%);
        }

        /* Portal Choice Cards */
        .portal-card {
            border: none;
            border-radius: 16px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            background: #ffffff;
            overflow: hidden;
        }

        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.2);
        }

        .portal-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 20px;
        }

        /* Feature Icons Box */
        .feature-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #eef2f5;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-box:hover {
            border-color: #0d6efd;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: #e7f1ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        /* Stats Badge */
        .badge-pill-custom {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px;
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(10, 77, 184, 0.95);
                backdrop-filter: blur(10px);
                padding: 20px;
                border-radius: 12px;
                margin-top: 10px;
            }

            .hero-section {
                padding: 90px 0 100px;
                clip-path: polygon(0 0, 100% 0, 100% 96%, 0 100%);
            }

            .hero-section h1 {
                font-size: 2.2rem;
            }

            .gateway-container {
                margin-top: -40px !important;
            }
        }

        @media (max-width: 575.98px) {
            .hero-section {
                padding: 80px 0 80px;
                clip-path: polygon(0 0, 100% 0, 100% 98%, 0 100%);
            }

            .hero-section h1 {
                font-size: 1.75rem;
            }

            .hero-section p {
                font-size: 0.95rem;
            }

            .gateway-container {
                margin-top: -30px !important;
            }

            .portal-card {
                padding: 1.5rem !important;
            }

            .portal-icon-wrapper {
                width: 65px;
                height: 65px;
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-transparent position-absolute w-100 z-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="#"><i class="bi bi-mortarboard-fill me-2"></i>Forces Academy LMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item">
                    <a class="btn btn-outline-light rounded-pill px-4 w-100 text-start text-lg-center" href="login.php"><i class="bi bi-person-circle me-1"></i> Student Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning rounded-pill px-4 fw-semibold text-dark w-100 text-start text-lg-center" href="admin/login.php"><i class="bi bi-shield-lock me-1"></i> Admin Portal</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center position-relative">
    <div class="container pt-4">
        <span class="badge badge-pill-custom text-white mb-3 d-inline-block"><i class="bi bi-stars me-1"></i> Smart Learning Management System</span>
        <h1 class="fw-bold mb-3">Forces Academy Student & Admin Portal</h1>
        <p class="lead max-w-700 mx-auto opacity-75 mb-4 px-2">An all-in-one digital campus platform designed to streamline course management, daily schedules, grades, and administrative workflows effortlessly.</p>
    </div>
</section>

<!-- Dual Access Gateway Section -->
<section class="container gateway-container" style="margin-top: -60px; position: relative; z-index: 5;">
    <div class="row g-4 justify-content-center">
        
        <!-- Student Portal Box -->
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card portal-card p-4 text-center h-100">
                <div class="portal-icon-wrapper bg-primary-subtle text-primary">
                    <i class="bi bi-person-video3"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Student Portal</h3>
                <p class="text-muted fs-6 mb-4">Access registered courses, download lecture materials, track timetables, check results, and view fee status.</p>
                <div class="d-grid gap-2 mt-auto">
                    <a href="login.php" class="btn btn-primary btn-lg fw-semibold rounded-pill"><i class="bi bi-box-arrow-in-right me-2"></i>Student Login</a>
                </div>
            </div>
        </div>

        <!-- Admin Portal Box -->
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card portal-card p-4 text-center h-100">
                <div class="portal-icon-wrapper bg-dark text-warning">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Admin Portal</h3>
                <p class="text-muted fs-6 mb-4">Manage student profiles, publish timetables, post notices, record fees, and oversee academic records.</p>
                <div class="d-grid gap-2 mt-auto">
                    <a href="admin/login.php" class="btn btn-dark btn-lg fw-semibold rounded-pill"><i class="bi bi-shield-lock-fill me-2"></i>Admin Login Access</a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Detailed Features Breakdown -->
<section class="container py-5 my-2 my-md-4">
    <div class="text-center mb-5">
        <h6 class="text-primary fw-bold text-uppercase tracking-wider">System Modules</h6>
        <h2 class="fw-bold text-dark">What You Can Access Inside</h2>
        <p class="text-muted">Explore the key capabilities integrated into the Forces Academy LMS ecosystem.</p>
    </div>

    <div class="row g-4">
        <!-- Feature 1 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-journal-text"></i></div>
                <h5 class="fw-bold">My Courses & Material</h5>
                <p class="text-muted small mb-0">View all enrolled subjects, course outlines, downloadable slides, and assignment submission deadlines.</p>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-calendar-week"></i></div>
                <h5 class="fw-bold">Weekly Timetable Grid</h5>
                <p class="text-muted small mb-0">Class schedules dynamically mapped across time slots and days with instructor details.</p>
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-cash-stack"></i></div>
                <h5 class="fw-bold">Fee Status & Receipts</h5>
                <p class="text-muted small mb-0">Track monthly/semester tuition fees, due dates, paid amounts, and download fee vouchers.</p>
            </div>
        </div>

        <!-- Feature 4 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-award"></i></div>
                <h5 class="fw-bold">Exam Results & Grades</h5>
                <p class="text-muted small mb-0">Detailed breakdown of quizzes, midterms, finals, GPAs, and overall performance sheets.</p>
            </div>
        </div>

        <!-- Feature 5 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-megaphone"></i></div>
                <h5 class="fw-bold">Campus Notice Board</h5>
                <p class="text-muted small mb-0">Instant access to urgent announcements, exam alerts, holiday updates, and events.</p>
            </div>
        </div>

        <!-- Feature 6 -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="feature-box">
                <div class="feature-icon"><i class="bi bi-person-gear"></i></div>
                <h5 class="fw-bold">Account Management</h5>
                <p class="text-muted small mb-0">Update personal info, change password securely with modern hashing, and track roll details.</p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-4 border-top border-secondary">
    <div class="container text-center">
        <p class="mb-1 opacity-75">&copy; <?php echo date("Y"); ?> Forces Academy LMS. All Rights Reserved.</p>
        <small class="text-muted">Designed for fast, reliable, and smooth academic administration.</small>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>