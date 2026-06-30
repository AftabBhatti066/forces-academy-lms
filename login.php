<?php
require_once 'config/db.php';
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password  = $_POST['password'];

    $sql = "SELECT id, full_name, password FROM students WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] =$student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        header('Location: dashboard.php');
        exit();
    } else{
        $error = 'Invalid email or password.';
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-5 col-md-7">

            <div class="card shadow-lg border-0">

                <div class="card-header bg-primary text-white text-center py-3">
                    <h3>Student Login</h3>
                    <p class="mb-0">Forces Academy LMS</p>
                </div>

                <div class="card-body">

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                placeholder="Enter your email"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                class="form-control"
                                name="password"
                                placeholder="Enter your password"
                                required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Login
                            </button>
                        </div>

                    </form>

                </div>

                <div class="card-footer text-center">
                    Don't have an account?
                    <a href="register.php" class="text-decoration-none">
                        Register
                    </a>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>