<?php
include 'header.php';
include 'db_connect.php';

$s_id = $_SESSION['user_id'];


if (isset($_POST['upload'])) {
    $desc = $_POST['description'];
  
    $sql = "INSERT INTO resource (s_id, description, status) VALUES ('$s_id', '$desc', 'pending')";
    if($conn->query($sql)) {
        echo "<div class='alert alert-success'>Resource shared! Waiting for Admin approval.</div>";
    }
}

//Only show status if approved or active
$resources = $conn->query("SELECT r.*, s.name FROM resource r JOIN student s ON r.s_id = s.s_id WHERE r.status='approved'");
?>

<div class="row">
    <div class="col-md-4">
        <div class="card p-3">
            <h5>Share a Resource</h5>
            <form method="post">
                <textarea name="description" class="form-control mb-3" placeholder="Enter link or description..." required></textarea>
                <button type="submit" name="upload" class="btn btn-success w-100">Share Resource</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <h3>Public Resources</h3>
        <?php while($row = $resources->fetch_assoc()): ?>
            <div class="card p-3">
                <p class="mb-1 fw-bold"><?php echo $row['description']; ?></p>
                <small class="text-muted">Shared by: <?php echo $row['name']; ?> (ID: <?php echo $row['s_id']; ?>)</small>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>