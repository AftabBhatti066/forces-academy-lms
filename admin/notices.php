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

// 1. ADD NOTICE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO notices (title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            $message = "Notice posted successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to post notice.";
            $messageType = "danger";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
        $messageType = "warning";
    }
}

// 2. DELETE NOTICE LOGIC
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Notice deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to delete notice.";
        $messageType = "danger";
    }
    $stmt->close();
}

// Fetch all notices
$notices_result = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice - Admin Panel</title>
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
        <h2 class="fw-bold mb-1">Post New Notice</h2>
        <p class="text-muted mb-0">Broadcast important announcements to all students.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form & Notice List Row -->
    <div class="row g-4">
        <!-- Form Section -->
        <div class="col-12 col-lg-5">
            <div class="card p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-pencil-square text-primary me-2"></i> Create Announcement</h5>
                <form action="notices.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notice Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Midterm Exam Schedule">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content</label>
                        <textarea name="content" class="form-control" rows="5" required placeholder="Write announcement details here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-send me-1"></i> Post Announcement
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Notices List -->
        <div class="col-12 col-lg-7">
            <div class="card p-4 bg-white h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-task text-primary me-2"></i> Recent Notices</h5>
                <?php if ($notices_result && mysqli_num_rows($notices_result) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php while ($notice = mysqli_fetch_assoc($notices_result)): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-1 mb-1">
                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($notice['title']); ?></h6>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($notice['created_at'] ?? 'now')); ?></small>
                                </div>
                                <p class="mb-2 text-muted small text-break"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                                <a href="notices.php?delete_id=<?php echo $notice['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notice?');">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-megaphone display-4 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">No notices posted yet.</p>
                    </div>
                <?php endif; ?>
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