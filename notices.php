<?php
session_start();

// 1. Session Protection Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$studentName = $_SESSION['student_name'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Database Connection Include
require_once 'config/db.php'; 

// 3. Search & Notices Fetch Query (Step 2 - Req 15)
$search = $_GET['search'] ?? '';

// Real-time SQL Injection protection using mysqli_real_escape_string
$search_clean = mysqli_real_escape_string($conn, $search);

$sql = "SELECT * FROM notices WHERE title LIKE '%$search_clean%' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board - Forces LMS</title>

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

        .notice-card {
            border: none;
            border-left: 5px solid #0d6efd; /* Blue accent line on left */
            box-shadow: 0 4px 12px rgba(0,0,0,.05);
            border-radius: 8px;
        }

        .mobile-header {
            display: none;
        }

        /* Mobile & Tablet Responsive Media Queries */
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
            .notice-card {
                padding: 1rem !important;
            }

            .notice-title {
                font-size: 1.1rem;
            }

            .notice-content {
                font-size: 0.95rem !important;
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

<!-- Sidebar -->
<div class="sidebar" id="sidebarMenu">
    <h3>Student Portal</h3>
    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
        <i class="bi bi-book"></i> My Courses
    </a>
    <a href="assignments.php" class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text"></i> Assignments
    </a>
    <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-week"></i> Timetable
    </a>
    <a href="fees.php" class="<?php echo ($current_page == 'fees.php') ? 'active' : ''; ?>">
        <i class="bi bi-cash-stack"></i> Fee Details
    </a>
    <a href="notices.php" class="<?php echo ($current_page == 'notices.php') ? 'active' : ''; ?>">
        <i class="bi bi-megaphone"></i> Notice Board
    </a>
    <a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
        <i class="bi bi-award"></i> Results
    </a>
    <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <i class="bi bi-person-circle"></i> Profile
    </a>
    <a href="logout.php" class="text-warning mt-4">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Main Content Area -->
<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Campus Notice Board</h2>
            <p class="text-muted mb-0">Stay updated with the latest announcements from the academy.</p>
        </div>
    </div>

    <!-- Clean Bootstrap Styled Search Bar -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <form method="GET" action="" class="input-group">
                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search notices by title...">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Search
                </button>
                <?php if(!empty($search)): ?>
                    <a href="notices.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">

            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                
                <?php while ($notice = mysqli_fetch_assoc($result)): ?>
                    <div class="card notice-card p-4 mb-4 bg-white">
                        
                        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start gap-2 mb-2">
                            <h4 class="text-dark fw-bold mb-0 notice-title">
                                <i class="bi bi-pin-angle-fill text-danger me-2"></i>
                                <?php echo htmlspecialchars($notice['title']); ?>
                            </h4>
                            
                            <span class="badge bg-light text-dark border p-2 align-self-start">
                                <i class="bi bi-calendar3 me-1 text-primary"></i>
                                <?php echo date('F d, Y', strtotime($notice['created_at'])); ?>
                            </span>
                        </div>

                        <div class="card-body px-0 py-2">
                            <p class="text-secondary fs-5 mb-0 notice-content" style="line-height: 1.6;">
                                <?php echo htmlspecialchars($notice['content']); ?>
                            </p>
                        </div>

                    </div>
                <?php endwhile; ?>

            <?php else: ?>

                <div class="alert alert-warning text-center p-4 p-md-5" role="alert">
                    <i class="bi bi-megaphone-fill display-4 text-warning d-block mb-3"></i>
                    <h4>No Notices Found!</h4>
                    <p class="mb-0 text-muted">
                        <?php echo !empty($search) ? 'No notices matched your search query.' : 'There are no notices posted on the board right now.'; ?>
                    </p>
                </div>

            <?php endif; ?>

        </div>
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