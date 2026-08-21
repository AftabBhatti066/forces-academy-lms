<?php
session_start();

// 1. Session Protection Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$studentName = $_SESSION['student_name'];
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Database Connection Include
require_once 'config/db.php'; 

// 3. Courses Fetch Query (Step 3 - Req 10)
$query = "SELECT * FROM courses ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Forces LMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            overflow-x: hidden;
            background: #f8f9fa;
        }

        /* Desktop Sidebar Setup */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #0d6efd;
            color: #fff;
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 14px 25px;
            transition: .3s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, .15);
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, .25);
            font-weight: bold;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            transition: all 0.3s ease;
        }

        .course-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,.12);
        }

        .mobile-header {
            display: none;
        }

        /* Responsive Media Queries */
        @media (max-width: 991.98px) {
            .mobile-header {
                display: flex;
                background: #0d6efd;
                color: #fff;
                padding: 12px 20px;
                align-items: center;
                justify-content: space-between;
                position: sticky;
                top: 0;
                z-index: 1050;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: auto;
                padding-top: 0;
                display: none;
            }

            .sidebar.show {
                display: block;
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

            .course-card .card-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Screen Top Header Bar -->
<div class="mobile-header">
    <h4 class="m-0 fw-bold fs-5">Student Portal</h4>
    <button class="btn btn-outline-light btn-sm" type="button" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
</div>

<!-- Sidebar Menu -->
<div class="sidebar" id="sidebarMenu">
    <h3>Student Portal</h3>
    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
        <i class="bi bi-book me-2"></i> My Courses
    </a>
    <a href="assignments.php" class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text me-2"></i> Assignments
    </a>
    <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-week me-2"></i> Timetable
    </a>
    <a href="fees.php" class="<?php echo ($current_page == 'fees.php') ? 'active' : ''; ?>">
        <i class="bi bi-cash-stack me-2"></i> Fee Details
    </a>
    <a href="notices.php" class="<?php echo ($current_page == 'notices.php') ? 'active' : ''; ?>">
        <i class="bi bi-megaphone me-2"></i> Notice Board
    </a>
    <a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
        <i class="bi bi-award me-2"></i> Results
    </a>
    <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <i class="bi bi-person-circle me-2"></i> Profile
    </a>
    <a href="logout.php" class="text-warning mt-4">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
</div>

<!-- Main Content Area -->
<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Available Courses</h2>
            <p class="text-muted">Explore and manage your enrolled courses.</p>
        </div>
    </div>

    <div class="row">

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            
            <?php while ($course = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 course-card p-3">
                        <div class="card-body d-flex flex-column">
                            
                            <h4 class="card-title text-primary fw-bold mb-3">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </h4>

                            <p class="card-text text-secondary flex-grow-1">
                                <?php echo htmlspecialchars($course['description']); ?>
                            </p>

                            <hr class="my-3">

                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-person-badge me-2 fs-5 text-dark"></i>
                                <span><strong>Instructor:</strong> <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">
                <div class="alert alert-info text-center p-5" role="alert">
                    <i class="bi bi-journal-x display-4 text-info d-block mb-3"></i>
                    <h4>No Courses Found!</h4>
                    <p class="mb-0 text-muted">Currently, there are no active courses in the database.</p>
                </div>
            </div>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mobile Burger Menu Toggle
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