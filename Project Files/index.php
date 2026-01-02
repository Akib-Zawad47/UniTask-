<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['user_id'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

   
    if ($role == 'student') {
        $sql = "SELECT * FROM student WHERE s_id='$id' AND password='$pass' AND account_status='active'";
    } elseif ($role == 'teacher') {
        $sql = "SELECT * FROM teacher WHERE t_id='$id' AND password='$pass' AND account_status='active'";
    } else {
        
        die("Invalid role selected");
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        header("Location: " . $role . "_dashboard.php");
        exit();
    } else {
        $error = "Login Failed! check your ID/Password or wait for Admin Approval.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 900px;
            max-width: 95%;
            display: flex;
            min-height: 500px;
        }
        .welcome-section {
            background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
            color: white;
            position: relative;
        }
        .welcome-section::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); 
        }
        .welcome-text {
            position: relative;
            z-index: 1;
        }
        .login-section {
            width: 50%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-control, .form-select {
            background-color: #f4f6f8;
            border: none;
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-login {
            background-color: #764ba2;
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-login:hover { background-color: #5a367f; }
        
        
        @media (max-width: 768px) {
            .container-box { flex-direction: column; height: auto; }
            .welcome-section { width: 100%; height: 200px; }
            .login-section { width: 100%; padding: 30px; }
        }
    </style>
</head>
<body>

<div class="container-box">
    <div class="welcome-section">
        <div class="welcome-text">
            <h2>Welcome Back!</h2>
            <p>Access your courses, tasks, and resources in one place.</p>
        </div>
    </div>

    <div class="login-section">
        <h3 class="mb-4 fw-bold text-dark">Login</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger text-center small py-2"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <label class="text-muted small mb-1">I am a:</label>
            <select name="role" class="form-select">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>

            <label class="text-muted small mb-1">User ID</label>
            <input type="text" name="user_id" class="form-control" placeholder="Enter ID" required>

            <label class="text-muted small mb-1">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter Password" required>

            <button type="submit" class="btn btn-primary w-100 btn-login">Login</button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted small">New to the platform?</p>
            <a href="register.php" class="btn btn-outline-dark btn-sm w-50">Register Now</a>
        </div>
    </div>
</div>

</body>
</html>