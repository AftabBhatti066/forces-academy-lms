<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php';

$message = "";
$messageType = "";
$current_page = basename($_SERVER['PHP_SELF']); // Active link identification

// 1. ADD ASSIGNMENT LOGIC (Req 10 & 11)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $course_id   = intval($_POST['course_id']);
    $due_date    = trim($_POST['due_date']);

    if (!empty($title) && $course_id > 0 && !empty($due_date)) {
        $stmt = $conn->prepare("INSERT INTO assignments (title, description, course_id, due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $title, $description, $course_id, $due_date);
        
        if ($stmt->execute()) {
            $message = "Assignment created successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to create assignment.";
            $messageType = "danger";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all required fields.";
        $messageType = "warning";
    }
}

// 2. DELETE ASSIGNMENT LOGIC (Req 9)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "Assignment deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete assignment.";
        $messageType = "danger";
    }
    $stmt->close();
}

// Fetch all courses for the dropdown
$courses_query = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

// Fetch assignments joined with courses table
$assignments_query = "SELECT a.*, c.course_name 
                      FROM assignments a 
                      LEFT JOIN courses c ON a.course_id = c.id 
                      ORDER BY a.id DESC";
$assignments_list = mysqli_query($conn, $assignments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - Admin Panel</title>
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

        /* Mobile & Tablet Responsive Media Queries */
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

<!-- Admin Sidebar Navigation -->
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
            <h2 class="fw-bold mb-1">Manage Assignments</h2>
            <p class="text-muted mb-0">Create coursework for students and track assigned tasks.</p>
        </div>
        <button class="btn btn-primary align-self-start align-self-sm-center shadow-sm" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
            <i class="bi bi-plus-lg me-1"></i> Add New Assignment
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Assignments List Table -->
    <div class="card p-4 bg-white">
        <?php if ($assignments_list && mysqli_num_rows($assignments_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Description</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($assign = mysqli_fetch_assoc($assignments_list)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($assign['title']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($assign['course_name'] ?? 'General'); ?></span></td>
                                <td><span class="badge bg-danger"><?php echo date('M d, Y', strtotime($assign['due_date'])); ?></span></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($assign['description'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="assignments.php?delete_id=<?php echo $assign['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this assignment?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-journal-text display-4 text-muted d-block mb-2"></i>
                <h5>No Assignments Found!</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD ASSIGNMENT MODAL -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="assignments.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assignment Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Building a CRUD app">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="" selected disabled>-- Select Course --</option>
                            <?php if ($courses_query && mysqli_num_rows($courses_query) > 0): ?>
                                <?php while ($c = mysqli_fetch_assoc($courses_query)): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['course_name']); ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description / Instructions</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Provide details on submission format..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Assignment</button>
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