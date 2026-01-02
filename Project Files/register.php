<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $id = $_POST['user_id'];
    $password = $_POST['password']; 
    $email = $_POST['g_suit'];
    $contact = $_POST['contact'];
    $dept = $_POST['department'];
    $name = $_POST['name']; 
    
    
    $valid_domain = false;
    
    if ($role == 'student') {
        if (strpos($email, '@g.bracu.ac.bd') !== false) $valid_domain = true;
        else $message = "<div class='alert alert-danger'>Students must use <b>@g.bracu.ac.bd</b></div>";
    } elseif ($role == 'teacher') {
        if (strpos($email, '@bracu.ac.bd') !== false) $valid_domain = true;
        else $message = "<div class='alert alert-danger'>Teachers must use <b>@bracu.ac.bd</b></div>";
    }

    if ($valid_domain) {
        
        $table = ($role == 'student') ? 'student' : 'teacher';
        $check_sql = "SELECT * FROM $table WHERE g_suit='$email' OR " . (($role=='student')?'s_id':'t_id') . "='$id'";
        
        if ($conn->query($check_sql)->num_rows > 0) {
            $message = "<div class='alert alert-warning'>ID or Email already exists.</div>";
        } else {
           
            if ($role == 'student') {
                $sql = "INSERT INTO student (s_id, password, g_suit, s_contact, name, department, account_status) 
                        VALUES ('$id', '$password', '$email', '$contact', '$name', '$dept', 'pending')";
            } else {
                $sql = "INSERT INTO teacher (t_id, password, g_suit, t_contact, name, department, account_status) 
                        VALUES ('$id', '$password', '$email', '$contact', '$name', '$dept', 'pending')";
            }

            if ($conn->query($sql) === TRUE) {
                $message = "<div class='alert alert-success'>Registration successful! Wait for Admin approval. <a href='index.php'>Login</a></div>";
            } else {
                $message = "<div class='alert alert-danger'>DB Error: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="card shadow-lg" style="width: 100%; max-width: 500px;">
    <div class="card-header bg-primary text-white"><h4>Create Account</h4></div>
    <div class="card-body p-4">
        <?php echo $message; ?>
        
        <form method="post">
            <div class="mb-3">
                <label class="form-label">I am a:</label>
                <select name="role" class="form-select">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">University Email</label>
                <input type="email" name="g_suit" class="form-control" placeholder="@bracu.ac.bd / @g.bracu.ac.bd" required>
            </div>

            <div class="mb-3">
                <label class="form-label">ID Number</label>
                <input type="number" name="user_id" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
    </div>
    <div class="card-footer text-center bg-white">
        <small>Already have an account? <a href="index.php">Login</a></small>
    </div>
</div>
</body>
</html>