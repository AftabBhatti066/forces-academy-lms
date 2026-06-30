<?php
require_once 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $roll_no = trim($_POST['roll_no']);
    $class = trim($_POST['class']);
    if(empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($roll_no) || empty($class)){
        $error = 'All fields are requird.';
    } elseif ($password !== $confirm_password){
        $error = 'Password do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql   = "INSERT INTO students (full_name, email, password, roll_no, class) VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss', $full_name, $email, $hashed, $roll_no, $class);

        if(mysqli_stmt_execute($stmt)){
            header("Location: login.php?registerd=1");
            exit();
        } else{
            $error = 'Registration failed. Email or roll number may already eist.';
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8">

            <div class="card shadow-lg border-0">

                <div class="card-header bg-primary text-white text-center">
                    <h3>Student Registration</h3>
                    <p class="mb-0">Forces Academy LMS</p>
                </div>

                <div class="card-body">

                    <form action="" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter your full name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter your email">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Roll No.</label>
                                <input type="text" class="form-control" name="roll_no">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Class</label>
                                <input type="text" class="form-control" name="class">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg" type="submit">
                                Register
                            </button>
                        </div>

                    </form>

                </div>

                <div class="card-footer text-center">
                    Already have an account?
                    <a href="login.php" class="text-decoration-none">Login</a>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

