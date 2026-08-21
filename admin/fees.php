<?php
session_start();

// Admin Session Protection Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db.php'; // DB Connection

// Dynamic Active Page Tracking
$current_page = basename($_SERVER['PHP_SELF']);

$message = "";
$messageType = "";

// 1. Fee Record Add Karne Ki Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_fee'])) {
    $student_id = intval($_POST['student_id']);
    $amount = floatval($_POST['amount']);
    $due_date = $_POST['due_date'];
    $description = trim($_POST['description']);

    // Prepared Statement for SQL Injection Security
    $stmt = $conn->prepare("INSERT INTO fees (student_id, amount, due_date, status, description) VALUES (?, ?, ?, 'pending', ?)");
    $stmt->bind_param("idss", $student_id, $amount, $due_date, $description);

    if ($stmt->execute()) {
        $message = "Fee record added successfully!";
        $messageType = "success";
    } else {
        $message = "Failed to add fee record. Please try again.";
        $messageType = "danger";
    }
    $stmt->close();
}

// 2. Fee Status Update Logic (Paid / Pending Toggle)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $fee_id = intval($_GET['id']);
    $new_status = ($_GET['action'] == 'mark_paid') ? 'paid' : 'pending';

    $stmt = $conn->prepare("UPDATE fees SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $fee_id);
    
    if ($stmt->execute()) {
        $message = "Fee status updated to " . ucfirst($new_status) . "!";
        $messageType = "info";
    }
    $stmt->close();
}

// Students drop-down ke liye fetch
$students_result = mysqli_query($conn, "SELECT id, full_name, roll_no FROM students ORDER BY full_name ASC");

// Pehle se added fee records view karne ke liye fetch (JOIN with students)
$fees_list = mysqli_query($conn, "SELECT fees.*, students.full_name, students.roll_no 
                                  FROM fees 
                                  JOIN students ON fees.student_id = students.id 
                                  ORDER BY fees.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management - Admin Panel</title>
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

        /* Responsive Breakpoints & Screen Media Adjustments */
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
            <h2 class="fw-bold mb-1">Fee Management</h2>
            <p class="text-muted mb-0">Issue student fee vouchers and update payment statuses.</p>
        </div>
    </div>

    <!-- Alert Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card p-4 bg-white mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i>Issue New Fee Voucher</h5>
        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Select Student</label>
                    <select name="student_id" class="form-select" required>
                        <option value="" selected disabled>-- Choose Student --</option>
                        <?php while($s = mysqli_fetch_assoc($students_result)): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['full_name']); ?> (Roll #: <?php echo htmlspecialchars($s['roll_no'] ?? 'N/A'); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Amount (PKR)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 5500" required>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Description / Fee Title</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Tuition Fee - Semester 4 / August 2026" required>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" name="add_fee" class="btn btn-primary px-4 w-100 w-sm-auto">
                        <i class="bi bi-file-earmark-plus me-1"></i> Issue Fee Record
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card p-4 bg-white">
        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2"></i>Issued Fee Records</h5>
        <?php if ($fees_list && mysqli_num_rows($fees_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Roll No</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($fee = mysqli_fetch_assoc($fees_list)): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($fee['full_name']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($fee['roll_no'] ?? 'N/A'); ?></span></td>
                                <td><?php echo htmlspecialchars($fee['description']); ?></td>
                                <td class="fw-semibold">PKR <?php echo number_format($fee['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($fee['due_date'])); ?></td>
                                <td>
                                    <?php if ($fee['status'] == 'paid'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($fee['status'] == 'pending'): ?>
                                        <a href="fees.php?action=mark_paid&id=<?php echo $fee['id']; ?>" class="btn btn-sm btn-outline-success">
                                            Mark as Paid
                                        </a>
                                    <?php else: ?>
                                        <a href="fees.php?action=mark_pending&id=<?php echo $fee['id']; ?>" class="btn btn-sm btn-outline-warning">
                                            Mark Pending
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-light text-center p-4 mb-0">
                <i class="bi bi-cash-stack display-4 text-muted d-block mb-2"></i>
                <h5>No Fee Records Issued Yet!</h5>
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