<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';

$message = "";
$messageType = "";
$current_page = basename($_SERVER['PHP_SELF']); // Dynamic active link identification

// 1. ADD COURSE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $course_name  = trim($_POST['course_name']);
    $description  = trim($_POST['description']);
    $teacher_name = trim($_POST['teacher_name']);

    if (!empty($course_name)) {
        $stmt = $conn->prepare("INSERT INTO courses (course_name, description, teacher_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $course_name, $description, $teacher_name);
        if ($stmt->execute()) {
            $message = "Course added successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to add course.";
            $messageType = "danger";
        }
        $stmt->close();
    }
}

// 2. EDIT / UPDATE COURSE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $course_id   = intval($_POST['course_id']);
    $course_name  = trim($_POST['course_name']);
    $description  = trim($_POST['description']);
    $teacher_name = trim($_POST['teacher_name']);

    if (!empty($course_name) && $course_id > 0) {
        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, description = ?, teacher_name = ? WHERE id = ?");
        $stmt->bind_param("sssi", $course_name, $description, $teacher_name, $course_id);
        if ($stmt->execute()) {
            $message = "Course updated successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to update course.";
            $messageType = "danger";
        }
        $stmt->close();
    }
}

// 3. DELETE COURSE LOGIC
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Course deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete course.";
        $messageType = "danger";
    }
    $stmt->close();
}

// Fetch all courses
$courses_result = mysqli_query($conn, "SELECT * FROM courses ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin Panel</title>
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
            z-index: 1000;
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

        /* Responsive Breakpoints */
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

            .content h2 {
                font-size: 1.5rem;
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

<!-- Sidebar Navigation -->
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

<div class="content">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manage Courses</h2>
            <p class="text-muted mb-0">Add, edit, or remove courses from the LMS.</p>
        </div>
        <button class="btn btn-primary align-self-start align-self-sm-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Course
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Courses Table -->
    <div class="card p-4 bg-white">
        <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Course Name</th>
                            <th>Teacher Name</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($course['course_name'] ?? $course['name'] ?? 'N/A'); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($course['teacher_name'] ?? 'Instructor'); ?></span></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($course['description'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <!-- Edit Button -->
                                    <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $course['id']; ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $course['id']; ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>

                                    <!-- EDIT MODAL -->
                                    <div class="modal fade" id="editModal<?php echo $course['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form action="courses.php" method="POST">
                                                    <input type="hidden" name="action" value="edit">
                                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Edit Course</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">Course Name</label>
                                                            <input type="text" name="course_name" class="form-control" value="<?php echo htmlspecialchars($course['course_name'] ?? $course['name'] ?? ''); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">Teacher Name</label>
                                                            <input type="text" name="teacher_name" class="form-control" value="<?php echo htmlspecialchars($course['teacher_name'] ?? ''); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label font-weight-bold">Description</label>
                                                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($course['description'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-warning">Update Course</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DELETE MODAL -->
                                    <div class="modal fade" id="deleteModal<?php echo $course['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger fw-bold">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete course <strong><?php echo htmlspecialchars($course['course_name'] ?? $course['name'] ?? ''); ?></strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="courses.php?delete_id=<?php echo $course['id']; ?>" class="btn btn-danger">Yes, Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-book display-4 text-muted d-block mb-2"></i>
                <h5>No Courses Found!</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD COURSE MODAL -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="courses.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="course_name" class="form-control" required placeholder="e.g. Web Development">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher Name</label>
                        <input type="text" name="teacher_name" class="form-control" required placeholder="e.g. Engr. Ali Khan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief course overview..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Course</button>
                </div>
            </form>
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