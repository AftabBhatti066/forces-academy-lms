<?php
session_start();

// 1. Protect Page
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$studentName = $_SESSION['student_name'];
$current_page = basename($_SERVER['PHP_SELF']);

require_once 'config/db.php'; // DB Connection

$message = "";
$messageType = "";

// -------------------------------------------------------------
// STEP 3: ASSIGNMENT SUBMISSION LOGIC (PHP Processing)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    
    // File Upload Handling
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['assignment_file']['tmp_name'];
        $fileName = $_FILES['assignment_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed Extensions Check (PDF & Images Only)
        $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Unique Filename Creation (e.g., assignment_3_student_5_66a1b2c3.pdf)
            $newFileName = 'assignment_' . $assignment_id . '_student_' . $student_id . '_' . uniqid() . '.' . $fileExtension;
            
            $uploadFileDir = './uploads/';
            
            // Create uploads directory if not exists
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Database Entry Insertion
                $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')");
                $stmt->bind_param("iis", $assignment_id, $student_id, $dest_path);
                
                if ($stmt->execute()) {
                    $message = "Assignment submitted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Database error: Could not record submission.";
                    $messageType = "danger";
                }
                $stmt->close();
            } else {
                $message = "Error moving the uploaded file to server directory.";
                $messageType = "danger";
            }
        } else {
            $message = "Invalid file type! Only PDF, PNG, JPG, and JPEG files are allowed.";
            $messageType = "danger";
        }
    } else {
        $message = "Please select a valid file to upload.";
        $messageType = "danger";
    }
}

// -------------------------------------------------------------
// STEP 2: FETCH ASSIGNMENTS & SUBMISSION STATUSES
// -------------------------------------------------------------
$query = "SELECT a.*, c.course_name 
          FROM assignments a 
          LEFT JOIN courses c ON a.course_id = c.id 
          ORDER BY a.due_date ASC";
$result = mysqli_query($conn, $query);

// Fetch current student's submissions to check badges
$submissions = [];
$subQuery = "SELECT assignment_id FROM submissions WHERE student_id = '$student_id'";
$subResult = mysqli_query($conn, $subQuery);
if ($subResult) {
    while ($row = mysqli_fetch_assoc($subResult)) {
        $submissions[] = $row['assignment_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Forces LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { 
            overflow-x: hidden; 
            background: #f8f9fa; 
        }

        /* Desktop Sidebar Setup */
        .sidebar { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 250px; 
            height: 100vh; 
            background: #0d6efd; 
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
            color: white; 
            text-decoration: none; 
            display: block; 
            padding: 14px 25px; 
            transition: .3s; 
        }

        .sidebar a:hover { 
            background: rgba(255,255,255,.15); 
        }

        .sidebar a.active { 
            background: rgba(255,255,255,.25); 
            font-weight: bold; 
        }

        .content { 
            margin-left: 250px; 
            padding: 30px; 
            transition: all 0.3s ease;
        }

        .assignment-card { 
            border: none; 
            border-radius: 10px; 
            box-shadow: 0 4px 12px rgba(0,0,0,.08); 
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

            .card-title {
                font-size: 1.2rem;
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

<!-- Sidebar Menu -->
<div class="sidebar" id="sidebarMenu">
    <h3>Student Portal</h3>
    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a href="courses.php" class="<?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
        <i class="bi bi-book me-2"></i> My Courses
    </a>
    <a href="assignments.php" class="<?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text me-2"></i> Assignments
    </a>
    <a href="timetable.php" class="<?php echo ($current_page == 'timetable.php') ? 'active' : ''; ?>">
        <i class="bi bi-calendar-week me-2"></i> Timetable
    </a>
    <a href="fees.php" class="<?php echo ($current_page == 'fees.php') ? 'active' : ''; ?>">
        <i class="bi bi-cash-stack me-2"></i> Fee Details
    </a>
    <a href="notices.php" class="<?php echo ($current_page == 'notices.php') ? 'active' : ''; ?>">
        <i class="bi bi-megaphone me-2"></i> Notice Board
    </a>
    <a href="results.php" class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
        <i class="bi bi-award me-2"></i> Results
    </a>
    <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <i class="bi bi-person-circle me-2"></i> Profile
    </a>
    <a href="logout.php" class="text-warning mt-4">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
    </a>
</div>

<!-- Main Content Area -->
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Course Assignments</h2>
            <p class="text-muted">View pending tasks and submit your coursework.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($assignment = mysqli_fetch_assoc($result)): ?>
                <?php $isSubmitted = in_array($assignment['id'], $submissions); ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card assignment-card h-100 p-3">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle w-auto mb-2 align-self-start">
                                <?php echo htmlspecialchars($assignment['course_name'] ?? 'General Course'); ?>
                            </span>

                            <h4 class="card-title fw-bold text-dark mb-2">
                                <?php echo htmlspecialchars($assignment['title']); ?>
                            </h4>

                            <p class="card-text text-secondary flex-grow-1">
                                <?php echo htmlspecialchars($assignment['description']); ?>
                            </p>

                            <div class="text-muted mb-3">
                                <small><i class="bi bi-clock me-1 text-danger"></i> <strong>Due Date:</strong> 
                                    <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?>
                                </small>
                            </div>

                            <div class="mt-auto">
                                <?php if ($isSubmitted): ?>
                                    <span class="badge bg-success fs-6 w-100 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Submitted
                                    </span>
                                <?php else: ?>
                                    <button class="btn btn-outline-primary w-100 fw-semibold" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#uploadModal<?php echo $assignment['id']; ?>">
                                        <i class="bi bi-upload me-1"></i> Submit Assignment
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Modal -->
                <div class="modal fade" id="uploadModal<?php echo $assignment['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="assignments.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Submit: <?php echo htmlspecialchars($assignment['title']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">Upload File (PDF or Images only)</label>
                                        <input type="file" name="assignment_file" class="form-control" accept=".pdf, .png, .jpg, .jpeg" required>
                                        <small class="text-muted">Allowed types: .pdf, .jpg, .png</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="submit_assignment" class="btn btn-primary">Upload & Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center p-5" role="alert">
                    <i class="bi bi-journal-check display-4 text-info d-block mb-3"></i>
                    <h4>No Pending Assignments!</h4>
                    <p class="mb-0 text-muted">There are currently no assignments uploaded for your courses.</p>
                </div>
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