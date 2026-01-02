<?php
include 'header.php';
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$s_id = $_SESSION['user_id'];
$message = "";


$s_info = $conn->query("SELECT department FROM student WHERE s_id='$s_id'")->fetch_assoc();
$dept = strtoupper($s_info['department']);

// course limit
$limit = ($dept == 'PHR' || $dept == 'LLB') ? 7 : 5;

//total enroll

$count_res = $conn->query("SELECT COUNT(*) as total FROM student_enrolls WHERE s_id='$s_id'");
$current_count = $count_res->fetch_assoc()['total'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $c_id = $_POST['c_id'];
    $section = $_POST['section'];

   
    if ($current_count >= $limit) {
        $message = "<div class='alert alert-danger'>Limit Reached! $dept students can max take $limit courses.</div>";
    } else {

        //duplicate course check
       
        $dup_check = $conn->query("SELECT * FROM student_enrolls WHERE s_id='$s_id' AND c_id='$c_id'");
        
        if ($dup_check->num_rows > 0) {
            $message = "<div class='alert alert-danger'>You are already enrolled in $c_id. You cannot take two sections of the same course.</div>";
        } else {
           
            $course_check = $conn->query("SELECT * FROM course WHERE c_id='$c_id' AND section='$section'");
            
            if ($course_check->num_rows > 0) {
               
                $sql = "INSERT INTO student_enrolls (s_id, c_id, section) VALUES ('$s_id', '$c_id', '$section')";
                if ($conn->query($sql)) {
                    $message = "<div class='alert alert-success'>Enrolled in $c_id ($section) successfully!</div>";
                    $current_count++;
                } else {
                    $message = "<div class='alert alert-danger'>Database Error.</div>";
                }
            } else {
                $message = "<div class='alert alert-warning'>Course/Section not found.</div>";
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h4>Enrollment</h4>
            <div>
                <span class="badge bg-secondary">Dept: <?php echo $dept; ?></span>
                <span class="badge <?php echo ($current_count >= $limit)?'bg-danger':'bg-success'; ?>">
                    Count: <?php echo $current_count; ?> / <?php echo $limit; ?>
                </span>
            </div>
        </div>
    </div>
    
    <?php echo $message; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card p-4">
                <h5>Add Course</h5>
                <form method="post">
                    <div class="mb-3">
                        <label>Course ID</label>
                        <select name="c_id" class="form-select" required>
                            <option value="">Select...</option>
                            <?php 
                            $c_list = $conn->query("SELECT DISTINCT c_id FROM course ORDER BY c_id");
                            while($c = $c_list->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $c['c_id']; ?>"><?php echo $c['c_id']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Section</label>
                        <input type="text" name="section" class="form-control" required placeholder="e.g. A">
                    </div>
                    <button type="submit" class="btn btn-primary w-100" <?php echo ($current_count >= $limit)?'disabled':''; ?>>Enroll</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">Enrolled Courses</div>
                <ul class="list-group list-group-flush">
                    <?php 
                    $my_courses = $conn->query("SELECT * FROM student_enrolls WHERE s_id='$s_id'");
                    if($my_courses->num_rows > 0):
                        while($row = $my_courses->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <?php echo $row['c_id']; ?> - Section <?php echo $row['section']; ?>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No enrollments.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>