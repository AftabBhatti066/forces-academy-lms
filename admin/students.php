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

$message = $_SESSION['message'] ?? "";
$messageType = $_SESSION['messageType'] ?? "";

// Clear flash messages after reading
unset($_SESSION['message'], $_SESSION['messageType']);

// DELETE Student Logic (Step 3 - Req 9)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Student deleted successfully!";
        $_SESSION['messageType'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete student.";
        $_SESSION['messageType'] = "danger";
    }
    $stmt->close();

    // Redirect back to avoid re-triggering delete on page refresh
    header("Location: students.php");
    exit();
}

// Search Logic (Step 3 - Req 8) -> Updated for `full_name`
$search = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE full_name LIKE ? OR roll_no LIKE ? ORDER BY id DESC");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Normal List Fetch (Step 3 - Req 7)
    $query = "SELECT * FROM students ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Panel</title>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manage Students</h2>
            <p class="text-muted mb-0">View, search, and manage registered student accounts.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Step 3 - Req 8: Search Bar -->
    <div class="card p-3 mb-4 bg-white">
        <form action="students.php" method="GET" class="row g-2">
            <div class="col-12 col-md-9 col-lg-10">
                <input type="text" name="search" class="form-control" placeholder="Search by Student Name or Roll Number..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-12 col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
                <?php if (!empty($search)): ?>
                    <a href="students.php" class="btn btn-outline-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Step 3 - Req 7: Table of Students -->
    <div class="card p-4 bg-white">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Roll No</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Class / Program</th>
                            <th>Registered Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($student = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($student['roll_no'] ?? 'N/A'); ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($student['created_at']) ? date('M d, Y', strtotime($student['created_at'])) : 'N/A'; ?></td>
                                <td class="text-center">
                                    <!-- Delete Confirmation Modal Trigger -->
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $student['id']; ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>

                                    <!-- Delete Confirmation Modal (Req 9) -->
                                    <div class="modal fade" id="deleteModal<?php echo $student['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">Confirm Student Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete student <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="students.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger">Yes, Delete</a>
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
            <div class="alert alert-light text-center p-4 mb-0">
                <i class="bi bi-people display-4 text-muted d-block mb-2"></i>
                <h5 class="mb-0">No Students Found!</h5>
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