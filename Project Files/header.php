<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">UniSystem</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if(isset($_SESSION['user_id'])): ?>
            
            <?php if($_SESSION['role'] == 'student'): ?>
                <a href="student_dashboard.php" class="nav-link">Dashboard</a>
                <a href="student_dashboard.php?page=enroll" class="nav-link">Enrollment</a>
                
                <li class="nav-item ms-2">
                    <button class="btn btn-outline-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#headerSubmitModal">
                        + Submit Resource
                    </button>
                </li>
            
            <?php elseif($_SESSION['role'] == 'teacher'): ?>
                <li class="nav-item"><a class="nav-link" href="teacher_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="teacher_dashboard.php?view=courses">My Courses</a></li>
            
            <?php elseif($_SESSION['role'] == 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php?view=users">Verify Users</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php?view=resources">Resources</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php?view=directory">Directory</a></li>
            <?php endif; ?>

            <li class="nav-item"><a class="nav-link btn btn-danger text-white btn-sm ms-3" href="logout.php">Logout</a></li>
        
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="index.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'student'): ?>
<div class="modal fade" id="headerSubmitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Submit Resource</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="student_dashboard.php" method="post">
          <div class="modal-body">
            
            <div class="mb-3">
                <label class="form-label">Student ID</label>
                <input type="number" name="manual_s_id" class="form-control" value="<?php echo $_SESSION['user_id']; ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Resource ID (Course & Topic)</label>
                <input type="text" name="r_id_text" class="form-control" placeholder="e.g. CSE101-Recursion" required>
                <div class="form-text">This will be the unique ID for this resource.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description / Link</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Paste drive link or details..." required></textarea>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="upload_resource" class="btn btn-success">Submit for Approval</button>
          </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="container">