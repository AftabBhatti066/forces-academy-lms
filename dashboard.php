<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}
$student_name = $_SESSION['student_name'];
?>

<h2>Welcome, <?php echo htmlspecialchars($student_name); ?></h2>