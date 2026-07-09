<?php
session_start();

// Protect Page
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$studentName = $_SESSION['student_name'];

// 1. Database Connection Include Karein
// (Apni connection file ka naam yahan check kar lein, e.g., db.php ya config.php)
require_once 'config/db.php'; 

// 2. Database Queries for Step 2

// A. Total Courses Count (courses table se count fetch karna)
$totalCourses = 0;
$courseQuery = "SELECT COUNT(*) AS total FROM courses";
$courseResult = mysqli_query($conn, $courseQuery);
if ($courseResult && $row = mysqli_fetch_assoc($courseResult)) {
    $totalCourses = $row['total'];
}

// B. Latest Notice (Sab se recent 1 notice fetch karna)
$latestNoticeTitle = "No notices posted yet.";
$latestNoticeQuery = "SELECT title FROM notices ORDER BY created_at DESC LIMIT 1";
$latestNoticeResult = mysqli_query($conn, $latestNoticeQuery);
if ($latestNoticeResult && mysqli_num_rows($latestNoticeResult) > 0) {
    $noticeRow = mysqli_fetch_assoc($latestNoticeResult);
    $latestNoticeTitle = $noticeRow['title'];
}

// C. Recent 3 Notices (Last 3 notices fetch karna)
$recentNoticesQuery = "SELECT * FROM notices ORDER BY created_at DESC LIMIT 3";
$recentNoticesResult = mysqli_query($conn, $recentNoticesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{
            overflow-x:hidden;
            background:#f8f9fa;
        }

        .sidebar{
            position:fixed;
            top:0;
            left:0;
            width:250px;
            height:100vh;
            background:#0d6efd;
            color:#fff;
            padding-top:20px;
        }

        .sidebar h3{
            text-align:center;
            margin-bottom:30px;
            font-weight:bold;
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            padding:14px 25px;
            transition:.3s;
        }

        .sidebar a:hover{
            background:rgba(255,255,255,.15);
        }

        .content{
            margin-left:250px;
            padding:30px;
        }

        .card{
            border:none;
            box-shadow:0 0 15px rgba(0,0,0,.08);
        }
    </style>
</head>
<body>

<div class="sidebar">

    <h3>Forces LMS</h3>

    <a href="dashboard.php">
        <i class="bi bi-speedometer2"></i>
        Dashboard
    </a>

    <a href="courses.php">
        <i class="bi bi-book"></i>
        My Courses
    </a>

    <a href="assignments.php">
        <i class="bi bi-journal-text"></i>
        Assignments
    </a>

    <a href="results.php">
        <i class="bi bi-bar-chart"></i>
        My Results
    </a>

    <a href="notices.php">
        <i class="bi bi-megaphone"></i>
        Notices
    </a>

    <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        Logout
    </a>

</div>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Student Dashboard</h2>
            <p class="text-muted fs-5">
                Hello, <strong><?php echo htmlspecialchars($studentName); ?></strong>! 👋
            </p>
        </div>
    </div>

    <div class="row mb-4">

        <div class="col-md-4 mb-3">
            <div class="card p-4 border-start border-primary border-4">
                <h5 class="text-muted">Total Courses</h5>
                <h2 class="fw-bold text-primary"><?php echo $totalCourses; ?></h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4 border-start border-warning border-4">
                <h5 class="text-muted">Pending Assignments</h5>
                <h2 class="fw-bold text-warning">0</h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4 border-start border-info border-4">
                <h5 class="text-muted">Latest Notice</h5>
                <p class="mb-0 fw-semibold text-truncate" title="<?php echo htmlspecialchars($latestNoticeTitle); ?>">
                    <?php echo htmlspecialchars($latestNoticeTitle); ?>
                </p>
            </div>
        </div>

    </div>

    <div class="mb-4">
        <a href="courses.php" class="btn btn-primary me-2">
            <i class="bi bi-book me-1"></i> My Courses
        </a>
        <a href="assignments.php" class="btn btn-outline-secondary">
            <i class="bi bi-journal-text me-1"></i> Assignments
        </a>
    </div>

    <div class="card p-4">
        <h4 class="mb-3">Recent Notices</h4>
        
        <?php if ($recentNoticesResult && mysqli_num_rows($recentNoticesResult) > 0): ?>
            <div class="list-group list-group-flush">
                <?php while ($notice = mysqli_fetch_assoc($recentNoticesResult)): ?>
                    <div class="list-group-item px-0">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <h5 class="mb-1 text-dark"><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <small class="text-muted">
                                <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                            </small>
                        </div>
                        <p class="mb-1 text-secondary"><?php echo htmlspecialchars($notice['content']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light mb-0" role="alert">
                No notices available right now.
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>