<?php
session_start();

// Admin Session Protection Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';

// Dynamic Active Page Tracking
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to safely get table count
function getTableCount($conn, $table) {
    $query = "SELECT COUNT(*) AS total FROM `$table`";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }
    return 0;
}

$totalStudents    = getTableCount($conn, 'students');
$totalCourses     = getTableCount($conn, 'courses');
$totalAssignments = getTableCount($conn, 'assignments');
$totalNotices     = getTableCount($conn, 'notices');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Forces LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            overflow-x: hidden; 
            background: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 250px; 
            height: 100vh; 
            background: #212529; 
            color: #fff; 
            padding-top: 20px; 
            z-index: 1040;
            transition: all 0.3s ease;
        }

        .sidebar h3 { 
            text-align: center; 
            margin-bottom: 30px; 
            font-weight: bold; 
        }

        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            display: block; 
            padding: 14px 25px; 
            transition: .3s; 
        }

        .sidebar a:hover, .sidebar a.active { 
            color: #fff; 
            background: rgba(255,255,255,.1); 
            font-weight: bold; 
        }

        .content { 
            margin-left: 250px; 
            padding: 30px; 
            transition: all 0.3s ease;
        }

        .stat-card { 
            border: none; 
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,.05); 
        }

        .mobile-header {
            display: none;
        }

        /* Responsive Breakpoints & Screen Media Adjustments */
        @media (max-width: 991.98px) {
            .mobile-header {
                display: flex;
                background: #212529;
                color: #fff;
                padding: 12px 20px;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                top: 0;
                z-index: 1050;
            }

            .sidebar {
                position: fixed;
                top: 55px;
                left: -250px;
                width: 250px;
                height: calc(100vh - 55px);
                z-index: 1040;
            }

            .sidebar.show {
                left: 0;
            }

            .sidebar h3 {
                display: none;
            }

            .content {
                margin-left: 0 !important;
                padding: 15px;
            }
        }

        @media (max-width: 575.98px) {
            .content h2 {
                font-size: 1.5rem;
            }
            .stat-card {
                padding: 1rem !important;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Screen Top Header Bar -->
<div class="mobile-header">
    <h4 class="m-0 fw-bold fs-5 text-white">Admin Panel</h4>
    <button class="btn btn-outline-light btn-sm" type="button" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
</div>

<!-- Sidebar -->
<div class="sidebar" id="sidebarMenu">
    <h3 class="text-white">Admin Panel</h3>
    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a href="students.php" class="<?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
        <i class="bi bi-people me-2"></i> Manage Students
    </a>
    <a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
        <i class="bi bi-book me-2"></i> Manage Courses
    </a>
    <a href="fees.php" class="<?php echo ($current_page == 'fees.php') ? 'active' : ''; ?>">
        <i class="bi bi-cash-stack me-2"></i> Fee Management
    </a>
    <a href="assignments.php" class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text me-2"></i> Manage Assignments
    </a>
    <a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
        <i class="bi bi-award me-2"></i> Upload Results
    </a>
    <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-week me-2"></i> Timetable
    </a>
    <a href="notices.php" class="<?php echo ($current_page == 'notices.php') ? 'active' : ''; ?>">
        <i class="bi bi-megaphone me-2"></i> Post Notice
    </a>
    <a href="logout.php" class="text-danger mt-4">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Overview</h2>
            <p class="text-muted mb-0">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?></strong>!</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary btn-sm align-self-start align-self-sm-center shadow-sm">
            <i class="bi bi-globe me-1"></i> View Main Site
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="card stat-card p-3 border-start border-primary border-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Students</h6>
                        <h2 class="fw-bold mb-0 text-primary"><?php echo $totalStudents; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1 text-primary opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="card stat-card p-3 border-start border-success border-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Courses</h6>
                        <h2 class="fw-bold mb-0 text-success"><?php echo $totalCourses; ?></h2>
                    </div>
                    <i class="bi bi-book fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="card stat-card p-3 border-start border-warning border-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Assignments</h6>
                        <h2 class="fw-bold mb-0 text-warning"><?php echo $totalAssignments; ?></h2>
                    </div>
                    <i class="bi bi-journal-text fs-1 text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3 mb-3">
            <div class="card stat-card p-3 border-start border-info border-4 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Notices</h6>
                        <h2 class="fw-bold mb-0 text-info"><?php echo $totalNotices; ?></h2>
                    </div>
                    <i class="bi bi-megaphone fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarMenu = document.getElementById('sidebarMenu');

    if (sidebarToggle && sidebarMenu) {
        sidebarToggle.addEventListener('click', () => {
            sidebarMenu.classList.toggle('show');
        });
    }
</script>
</body>
</html>