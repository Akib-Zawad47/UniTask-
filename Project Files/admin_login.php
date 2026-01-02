<?php
session_start();
include 'db_connect.php';



$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['a_id'];
    $pass = $_POST['password'];

    // Check ONLY Admin table
    $sql = "SELECT * FROM admin WHERE a_id='$id' AND password='$pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #2c3e50; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 400px; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-admin { background-color: #e74c3c; color: white; }
        .btn-admin:hover { background-color: #c0392b; color: white; }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4 text-danger">Admin Access</h3>
    <?php if($error): ?>
        <div class="alert alert-danger p-2 text-center"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Admin ID</label>
            <input type="text" name="a_id" class="form-control" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-admin w-100">Login to Control Panel</button>
    </form>
    <div class="text-center mt-3">
        <a href="index.php" class="text-muted small">Back to Main Site</a>
    </div>
</div>

</body>
</html>