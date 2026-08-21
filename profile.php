<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php'; // Verify path: db.php or config/db.php

$student_id   = $_SESSION['student_id'];
$current_page = basename($_SERVER['PHP_SELF']); // Fixed: Added current_page definition
$message      = "";
$messageType  = "";

// 1. UPDATE PROFILE LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);

    if (!empty($full_name) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $student_id);
        
        if ($stmt->execute()) {
            // Update session data
            $_SESSION['student_name']  = $full_name;
            $_SESSION['student_email'] = $email;
            
            $message     = "Profile updated successfully!";
            $messageType = "success";
        } else {
            $message     = "Failed to update profile.";
            $messageType = "danger";
        }
        $stmt->close();
    }
}

// 2. CHANGE PASSWORD LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password !== $confirm_password) {
            $message     = "New password and Confirm password do not match!";
            $messageType = "danger";
        } else {
            // Fetch stored password
            $stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();

            // Validate current password
            $is_valid = false;
            if (password_verify($current_password, $user['password'])) {
                $is_valid = true;
            } elseif (md5($current_password) === $user['password'] || $current_password === $user['password']) {
                $is_valid = true;
            }

            if ($is_valid) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $update_stmt     = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $student_id);
                
                if ($update_stmt->execute()) {
                    $message     = "Password changed successfully!";
                    $messageType = "success";
                } else {
                    $message     = "Failed to update password.";
                    $messageType = "danger";
                }
                $update_stmt->close();
            } else {
                $message     = "Incorrect current password!";
                $messageType = "danger";
            }
        }
    }
}

// Fetch Latest Student Details (Prepared Statement for Security)
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Student Portal</title>
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
        /* Fixed Blue Sidebar */
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
        /* Content Margin Adjustment */
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
                padding: 1.25rem !important;
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
    <h4 class="m-0 fw-bold fs-5">Student Portal</h4>
    <button class="btn btn-outline-light btn-sm" type="button" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
</div>

<!-- Sidebar Navigation -->
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

<!-- Main Body Area -->
<div class="content">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Account & Profile Management</h2>
        <p class="text-muted">Manage your personal information and update login credentials.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Personal Details Card -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Personal Details</h5>
                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Roll Number</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student['roll_no'] ?? 'N/A'); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Class / Program</label>
                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student['class'] ?? 'BSCS'); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Save Profile Changes</button>
                </form>
            </div>
        </div>

        <!-- Security & Password Card -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 bg-white">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-lock text-primary me-2"></i> Security & Password</h5>
                <form action="profile.php" method="POST">
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn btn-dark w-100 fw-bold mt-2">Update Password</button>
                </form>
            </div>
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