<?php
session_start();

// 1. Session Protection Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id   = $_SESSION['student_id'];
$studentName  = $_SESSION['student_name'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']); // Current active page identifier

require_once 'config/db.php'; // DB Connection

// Step 4 - Req 13: Pull results for logged-in student ONLY
$query = "SELECT * FROM results WHERE student_id = '$student_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results - Forces LMS</title>
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
            background: rgba(255,255,255,.15); 
        }

        .sidebar a.active { 
            background: rgba(255,255,255,.25); 
            font-weight: bold; 
        }

        .content { 
            margin-left: 250px; 
            padding: 30px; 
            transition: all 0.3s ease;
        }

        .card { 
            border: none; 
            box-shadow: 0 4px 12px rgba(0,0,0,.08); 
            border-radius: 10px; 
        }

        .mobile-header {
            display: none;
        }

        .print-header {
            display: none; /* Screen par hide rahega */
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
            .card {
                padding: 1rem !important;
            }
        }

        /* Step 3 - Req 20: CSS @media print Rules */
        @media print {
            /* Hide Sidebar, Buttons & Non-essential Elements when printing */
            .sidebar, .btn-print, header, footer, .mobile-header {
                display: none !important;
            }
            
            /* Reset Content Margin for Print Page */
            .content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            /* Watermark / Header for Print Sheet */
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
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

<div class="content">

    <!-- Header Visible only on Printout -->
    <div class="print-header">
        <h2>Forces LMS - Student Result Sheet</h2>
        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($studentName); ?></p>
        <hr>
    </div>

    <!-- Screen Header with Print Button -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Academic Results</h2>
            <p class="text-muted mb-0">View your grades and exam performance summary.</p>
        </div>
        
        <!-- Step 3 - Req 18 & 19: Print Button -->
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <button onclick="window.print()" class="btn btn-success btn-print shadow-sm align-self-start align-self-sm-center">
                <i class="bi bi-printer me-2"></i> Print Results
            </button>
        <?php endif; ?>
    </div>

    <div class="card p-4 bg-white">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Exam Type</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['exam_type']); ?></span></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($row['marks']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_marks']); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($row['grade'] == 'A' || $row['grade'] == 'A+') ? 'bg-success' : 
                                            (($row['grade'] == 'F') ? 'bg-danger' : 'bg-primary'); 
                                    ?> fs-6">
                                        <?php echo htmlspecialchars($row['grade']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center p-4 mb-0" role="alert">
                <i class="bi bi-award display-4 text-muted d-block mb-2"></i>
                <h5>No Results Published Yet!</h5>
                <p class="text-muted mb-0">There are no exam results available for your account at this moment.</p>
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