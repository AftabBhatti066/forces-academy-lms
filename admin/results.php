<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';

// Dynamic Active Page Tracking
$current_page = basename($_SERVER['PHP_SELF']);

$message = "";
$messageType = "";

// 1. UPLOAD RESULT LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id  = intval($_POST['student_id']);
    $course_id   = intval($_POST['course_id']);
    $subject     = trim($_POST['subject']);
    $marks       = intval($_POST['marks']);
    $total_marks = intval($_POST['total_marks']);
    $grade       = trim($_POST['grade']);
    $exam_type   = trim($_POST['exam_type']);

    if ($student_id > 0 && !empty($subject) && !empty($grade) && !empty($exam_type)) {
        $stmt = $conn->prepare("INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiiss", $student_id, $course_id, $subject, $marks, $total_marks, $grade, $exam_type);
        
        if ($stmt->execute()) {
            $message = "Result uploaded successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to upload result.";
            $messageType = "danger";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
        $messageType = "warning";
    }
}

// Fetch all students for Dropdown
$students_query = mysqli_query($conn, "SELECT id, full_name, roll_no FROM students ORDER BY full_name ASC");

// Fetch all courses for Dropdown
$courses_query = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

// Fetch recently uploaded results with JOIN
$results_query = "SELECT r.*, s.full_name, s.roll_no 
                 FROM results r 
                 JOIN students s ON r.student_id = s.id 
                 ORDER BY r.id DESC LIMIT 15";
$results_list = mysqli_query($conn, $results_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Results - Admin Panel</title>
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

        .card { 
            border: none; 
            box-shadow: 0 4px 12px rgba(0,0,0,.05); 
            border-radius: 10px; 
        }

        .mobile-header {
            display: none;
        }

        /* Responsive Breakpoints & Media Adjustments */
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
            .card {
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
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Upload Exam Results</h2>
        <p class="text-muted mb-0">Select student and assign marks for recent exams/quizzes.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form Section -->
    <div class="card p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i> Enter New Result Details</h5>
        <form action="results.php" method="POST" class="row g-3">
            
            <!-- Select Student -->
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label fw-bold">Select Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="" selected disabled>-- Choose Student --</option>
                    <?php if ($students_query && mysqli_num_rows($students_query) > 0): ?>
                        <?php while ($st = mysqli_fetch_assoc($students_query)): ?>
                            <option value="<?php echo $st['id']; ?>">
                                <?php echo htmlspecialchars($st['full_name']); ?> (Roll: <?php echo htmlspecialchars($st['roll_no'] ?? 'N/A'); ?>)
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Select Course -->
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label fw-bold">Select Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="" selected disabled>-- Choose Course --</option>
                    <?php if ($courses_query && mysqli_num_rows($courses_query) > 0): ?>
                        <?php while ($c = mysqli_fetch_assoc($courses_query)): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Subject Name -->
            <div class="col-12 col-md-6 col-lg-4">
                <label class="form-label fw-bold">Subject Title</label>
                <input type="text" name="subject" class="form-control" placeholder="e.g. Database Systems" required>
            </div>

            <!-- Obtained Marks -->
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold">Obtained Marks</label>
                <input type="number" name="marks" class="form-control" min="0" max="500" required placeholder="85">
            </div>

            <!-- Total Marks -->
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold">Total Marks</label>
                <input type="number" name="total_marks" class="form-control" min="1" max="500" value="100" required>
            </div>

            <!-- Grade -->
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold">Grade</label>
                <select name="grade" class="form-select" required>
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="B+">B+</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="F">F</option>
                </select>
            </div>

            <!-- Exam Type -->
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold">Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="Midterm">Midterm</option>
                    <option value="Final Term">Final Term</option>
                    <option value="Quiz 1">Quiz 1</option>
                    <option value="Quiz 2">Quiz 2</option>
                    <option value="Assignment Test">Assignment Test</option>
                </select>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary px-4 fw-bold w-100 w-sm-auto">
                    <i class="bi bi-cloud-upload me-1"></i> Upload Result
                </button>
            </div>
        </form>
    </div>

    <!-- Uploaded Results Table -->
    <div class="card p-4 bg-white">
        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Recently Uploaded Results</h5>
        <?php if ($results_list && mysqli_num_rows($results_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Roll No</th>
                            <th>Subject</th>
                            <th>Exam Type</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Uploaded On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($res = mysqli_fetch_assoc($results_list)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($res['full_name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($res['roll_no'] ?? 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($res['subject']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($res['exam_type']); ?></span></td>
                                <td class="fw-bold"><?php echo $res['marks']; ?> / <?php echo $res['total_marks']; ?></td>
                                <td><span class="badge bg-success"><?php echo htmlspecialchars($res['grade']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($res['created_at'] ?? 'now')); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-award display-4 text-muted d-block mb-2"></i>
                <p class="text-muted mb-0">No results uploaded yet.</p>
            </div>
        <?php endif; ?>
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