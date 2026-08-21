<?php
session_start();

// Student Auth Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php'; 
$student_id = $_SESSION['student_id'];

// Current page for sidebar active state
$current_page = basename($_SERVER['PHP_SELF']);

// 1. Calculate Total Pending & Paid Amounts (Prepared Statements)
$stmt = $conn->prepare("SELECT 
            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) AS total_pending,
            SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS total_paid
        FROM fees WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$totals = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_pending = $totals['total_pending'] ?? 0;
$total_paid    = $totals['total_paid'] ?? 0;

// 2. Fetch all fee records for logged in student
$stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY due_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fees_res = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Status - Student Portal</title>
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
        /* Blue Student Portal Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #0d6efd; /* Theme Primary Blue */
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
            transition: transform 0.2s;
        }
        .badge-status-pending {
            background-color: #ffe0e3;
            color: #dc3545;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-status-paid {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .mobile-header {
            display: none;
        }

        /* Responsive Media Queries */
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

<!-- Main Content Area -->
<div class="content">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Fee Management</h2>
        <p class="text-muted">Track your fee dues, payment history, and pending balances.</p>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card card-custom p-3 bg-white border-start border-danger border-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-exclamation-triangle text-danger fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold uppercase">Total Pending Fee</small>
                        <h3 class="mb-0 fw-bold text-danger">Rs. <?php echo number_format($total_pending, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card card-custom p-3 bg-white border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-bold uppercase">Total Paid Fee</small>
                        <h3 class="mb-0 fw-bold text-success">Rs. <?php echo number_format($total_paid, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Details Table -->
    <div class="card card-custom p-4 bg-white">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-receipt me-2"></i>Fee Statement History</h5>
        
        <?php if ($fees_res && $fees_res->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php while ($row = $fees_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $count++; ?></td>
                                <td class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>
                                <td class="fw-bold">
                                    Rs. <?php echo number_format($row['amount'], 2); ?>
                                </td>
                                <td>
                                    <i class="bi bi-calendar3 me-1 text-muted"></i>
                                    <?php echo htmlspecialchars(date('d M, Y', strtotime($row['due_date']))); ?>
                                </td>
                                <td class="text-center">
                                    <?php if (strtolower($row['status']) === 'pending'): ?>
                                        <span class="badge-status-pending">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-status-paid">
                                            <i class="bi bi-check2-all me-1"></i> Paid
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-wallet2 display-4 text-muted d-block mb-3"></i>
                <h5 class="text-muted">No Fee Records Found</h5>
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