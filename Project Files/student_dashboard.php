<?php

session_start();
include 'db_connect.php';


if (isset($_POST['fetch_sections']) && isset($_POST['course_id'])) {
    $c_id = $_POST['course_id'];
    
    
    $stmt = $conn->prepare("SELECT section FROM course WHERE c_id = ? ORDER BY section ASC");
    $stmt->bind_param("s", $c_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
  
    if ($result->num_rows > 0) {
        echo '<option value="">Select Section</option>';
        while($row = $result->fetch_assoc()) {
            echo '<option value="'.$row['section'].'">'.$row['section'].'</option>';
        }
    } else {
        echo '<option value="">No sections found</option>';
    }
    exit; 
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$s_id = $_SESSION['user_id'];
$message = "";


if (isset($_POST['enroll_course'])) {
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];

    
    $check = $conn->query("SELECT * FROM student_enrolls WHERE s_id='$s_id' AND c_id='$c_id' AND section='$sec'");
    
    if ($check->num_rows > 0) {
        $message = "<div class='alert alert-danger'>You are already enrolled in $c_id - $sec.</div>";
    } else {
        $sql = "INSERT INTO student_enrolls (s_id, c_id, section) VALUES ('$s_id', '$c_id', '$sec')";
        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success'>Successfully enrolled in $c_id ($sec)!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

//resource submit
if (isset($_POST['upload_resource'])) {
    $r_id_input = trim($_POST['r_id_text']); 
    $desc = trim($_POST['description']);
    
    if(empty($r_id_input) || empty($desc)) {
        $message = "<div class='alert alert-danger'>Error: All fields are required.</div>";
    } else {
        $check = $conn->query("SELECT * FROM resource WHERE r_id = '$r_id_input'");
        if($check->num_rows > 0) {
            $r_id_input = $r_id_input . "-" . rand(100, 999);
        }

        $sql = "INSERT INTO resource (r_id, s_id, description, status) VALUES ('$r_id_input', '$s_id', '$desc', 'pending')";
        
        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success'>Resource submitted!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Database Error: " . $conn->error . "</div>";
        }
    }
}

include 'header.php'; 


$page_view = isset($_GET['page']) ? $_GET['page'] : 'dashboard';


if ($page_view == 'dashboard') {
    $today = date('Y-m-d');
    $task_sql = "SELECT t.* FROM task t 
                 JOIN student_enrolls se ON t.c_id = se.c_id AND t.section = se.section 
                 WHERE se.s_id = '$s_id' AND t.due_date >= '$today' 
                 ORDER BY t.due_date ASC";
    $tasks = $conn->query($task_sql);

  
    $search_query = "";
    $res_sql = "SELECT r.*, s.name FROM resource r 
                JOIN student s ON r.s_id = s.s_id 
                WHERE r.status='approved'";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_query = $conn->real_escape_string($_GET['search']);
        $res_sql .= " AND (r.r_id LIKE '%$search_query%' OR r.description LIKE '%$search_query%')";
    }
    $res_sql .= " ORDER BY r.r_id DESC LIMIT 20";
    $public_resources = $conn->query($res_sql);

    
    $my_res = $conn->query("SELECT * FROM resource WHERE s_id = '$s_id' ORDER BY r_id DESC");
}
?>

<div class="container mt-4">
    <?php echo $message; ?>

    <?php if ($page_view == 'enroll'): ?>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Enroll in a New Course</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Select a course to see available sections.</p>
                        <form method="post">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Course</label>
                                <select name="c_id" id="course_dropdown" class="form-select form-select-lg" required>
                                    <option value="">-- Choose Course --</option>
                                    <?php
                                    $courses = $conn->query("SELECT DISTINCT c_id, c_name FROM course ORDER BY c_id");
                                    while($c = $courses->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $c['c_id']; ?>">
                                            <?php echo $c['c_id'] . " - " . $c['c_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Section</label>
                                <select name="section" id="section_dropdown" class="form-select form-select-lg" required disabled>
                                    <option value="">Select a Course First</option>
                                </select>
                            </div>

                            <button type="submit" name="enroll_course" class="btn btn-success w-100 btn-lg">Enroll Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

        <ul class="nav nav-tabs mb-4" id="mainTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-tasks">My Tasks</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-resources">Resources Center</button>
            </li>
        </ul>

        <div class="tab-content">
            
            <div class="tab-pane fade show active" id="tab-tasks">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Upcoming Tasks</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr><th>Task</th><th>Description</th><th>Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($tasks && $tasks->num_rows > 0): ?>
                                    <?php while($row = $tasks->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?php echo $row['c_id']; ?> (<?php echo $row['section']; ?>)</div>
                                            <span class="badge bg-info text-white">
                                                <?php echo ucfirst($row['type']); ?> <?php echo $row['task_number']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['description']; ?></td>
                                        <td>
                                            <div class="text-danger fw-bold"><?php echo $row['due_date']; ?></div>
                                        </td>
                                        <td><span class="badge bg-warning text-dark">Open</span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted">No active tasks.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-resources">
                <div class="card mb-4 bg-light border-0">
                    <div class="card-body">
                        <form method="get" class="d-flex w-100">
                            <input type="text" name="search" class="form-control" placeholder="Search resources..." value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-dark ms-2">Search</button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <h5 class="text-secondary">Public Resources</h5>
                        <div class="accordion shadow-sm" id="publicAccordion">
                            <?php if($public_resources && $public_resources->num_rows > 0): ?>
                                <?php $i = 0; while($row = $public_resources->fetch_assoc()): $i++; ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>">
                                                <?php echo $row['r_id']; ?> <small class="text-muted ms-2">by <?php echo $row['name']; ?></small>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse" data-bs-parent="#publicAccordion">
                                            <div class="accordion-body bg-light">
                                                <?php echo nl2br($row['description']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">No resources found.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h5 class="text-secondary">My Submissions</h5>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6>Upload New</h6>
                                <form method="post">
                                    <input type="text" name="r_id_text" class="form-control mb-2" placeholder="Resource Title/ID" required>
                                    <textarea name="description" class="form-control mb-2" placeholder="Description/Link" required></textarea>
                                    <button type="submit" name="upload_resource" class="btn btn-sm btn-primary w-100">Submit</button>
                                </form>
                            </div>
                        </div>
                        <ul class="list-group">
                            <?php while($me = $my_res->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $me['r_id']; ?>
                                    <span class="badge bg-secondary"><?php echo $me['status']; ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>

var courseDropdown = document.getElementById('course_dropdown');
if (courseDropdown) {
    courseDropdown.addEventListener('change', function() {
        var courseId = this.value;
        var sectionDropdown = document.getElementById('section_dropdown');

        
        sectionDropdown.innerHTML = '<option value="">Loading...</option>';
        sectionDropdown.disabled = true;

        if (courseId) {
            var formData = new FormData();
            formData.append('fetch_sections', true);
            formData.append('course_id', courseId);

            fetch('student_dashboard.php', {
                method: 'POST',
                body: formData
            })

            .then(response => response.text())
            .then(data => {
                sectionDropdown.innerHTML = data;
                sectionDropdown.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                sectionDropdown.innerHTML = '<option value="">Error loading sections</option>';
            });
        } else {
            sectionDropdown.innerHTML = '<option value="">Select a Course First</option>';
            sectionDropdown.disabled = true;
        }
    });
}
</script>

<?php if(isset($_GET['search']) && $page_view == 'dashboard'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var triggerEl = document.querySelector('#mainTab button[data-bs-target="#tab-resources"]')
        if(triggerEl) {
            var tab = new bootstrap.Tab(triggerEl)
            tab.show()
        }
    });
</script>
<?php endif; ?>

</body>
</html>