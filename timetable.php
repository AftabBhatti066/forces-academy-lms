<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php'; // Path verify kar lein: 'db.php' ya 'config/db.php'

$student_id   = $_SESSION['student_id'];
$current_page = basename($_SERVER['PHP_SELF']); // Current page for active sidebar

// Get Student Class / Program (Prepared Statement for Security)
$stmt = $conn->prepare("SELECT class FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$student_class = $student_res['class'] ?? 'BSCS'; // Fallback to BSCS

// Fetch Timetable entries for this class
$stmt = $conn->prepare("SELECT * FROM timetable WHERE class = ? OR class = 'BSCS' ORDER BY time_slot ASC");
$stmt->bind_param("s", $student_class);
$stmt->execute();
$result = $stmt->get_result();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$schedule = [];
$time_slots = [];

while ($row = $result->fetch_assoc()) {
    $slot = $row['time_slot'];
    $day  = $row['day'];
    
    if (!in_array($slot, $time_slots)) {
        $time_slots[] = $slot;
    }
    
    $schedule[$slot][$day] = [
        'subject' => $row['subject'],
        'teacher' => $row['teacher']
    ];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable - Student Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        /* Fixed Blue Student Portal Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #0d6efd;
            color: #fff;
            padding-top: 25px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .sidebar h3 {
            font-size: 1.5rem;
            font-weight: 700;
            padding: 0 25px;
            margin-bottom: 30px;
        }
        .sidebar a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 25px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.15);
        }
        .sidebar a.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.25);
            font-weight: 600;
            border-left: 4px solid #fff;
        }
        .sidebar a i {
            font-size: 1.2rem;
            margin-right: 12px;
        }
        /* Main Content Layout */
        .content {
            margin-left: 250px;
            padding: 35px;
            transition: all 0.3s ease;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        }
        .table-timetable th {
            text-align: center;
            background-color: #0d6efd;
            color: white;
            font-weight: 600;
            padding: 14px;
        }
        .table-timetable td {
            text-align: center;
            vertical-align: middle;
            height: 75px;
            min-width: 130px;
        }

        .mobile-header {
            display: none;
        }

        /* Mobile & Tablet Media Screening */
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
            .card-custom {
                padding: 1rem !important;
            }

            .content h2 {
                font-size: 1.4rem;
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

<!-- Fixed Sidebar Navigation -->
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
            <h2 class="fw-bold text-dark mb-1">Weekly Timetable Grid</h2>
            <p class="text-muted">Class Schedule for <span class="badge bg-primary px-2 py-1 fs-6"><?php echo htmlspecialchars($student_class); ?></span></p>
        </div>
    </div>

    <div class="card card-custom p-4 bg-white">
        <?php if (!empty($time_slots)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-timetable align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Time / Day</th>
                            <?php foreach ($days as $day): ?>
                                <th><?php echo $day; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($time_slots as $slot): ?>
                            <tr>
                                <td class="fw-bold bg-light text-dark"><?php echo htmlspecialchars($slot); ?></td>
                                <?php foreach ($days as $day): ?>
                                    <td>
                                        <?php if (isset($schedule[$slot][$day])): ?>
                                            <div class="p-2 bg-primary-subtle text-primary border border-primary-subtle rounded shadow-sm">
                                                <strong class="d-block text-dark"><?php echo htmlspecialchars($schedule[$slot][$day]['subject']); ?></strong>
                                                <small class="text-secondary"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($schedule[$slot][$day]['teacher']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
                <h5 class="text-muted fw-bold">No Schedule Found for Your Class!</h5>
                <p class="text-muted">Contact admin if timetable hasn't been uploaded yet.</p>
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