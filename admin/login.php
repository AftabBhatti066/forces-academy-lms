<?php
session_start();

// Agar admin pehle se logged in hai to direct dashboard par bhejo
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../config/db.php'; // Main directory ki DB connection file

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Updated table name: `admin`
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Plaintext / Hash verification
            if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                // Session flags set
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['role'] = 'admin';

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password!";
            }
        } else {
            $error = "Admin account not found!";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Forces LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="card login-card p-4 bg-white">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">Forces LMS</h3>
        <p class="text-muted">Admin Control Panel Login</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label font-weight-bold">Username</label>
            <input type="text" name="username" class="form-control" required placeholder="Enter admin username">
        </div>

        <div class="mb-4">
            <label class="form-label font-weight-bold">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Enter password">
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Login to Admin Panel</button>
    </form>
</div>

</body>
</html>