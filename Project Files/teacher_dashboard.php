<?php
include 'header.php';
include 'db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$t_id = $_SESSION['user_id'];
$t_info = $conn->query("SELECT name FROM teacher WHERE t_id='$t_id'")->fetch_assoc();
$teacher_name = $t_info['name'];
$active_c_id = "";
$active_section = "";
$message = "";


if (isset($_POST['post_task'])) {
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];
    
    
    $check_auth = $conn->query("SELECT * FROM course WHERE c_id='$c_id' AND section='$sec' AND t_id='$t_id'");
    
    if ($check_auth->num_rows > 0) {
        $task_id = uniqid("T"); 
        $type = $_POST['type'];
        $due = $_POST['due_date'];
        
       
        $task_num = $_POST['task_number'];
        $desc = $_POST['description'];
        
        $sql = "INSERT INTO task (task_id, t_id, c_id, section, due_date, type, task_number, description) 
                VALUES ('$task_id', '$t_id', '$c_id', '$sec', '$due', '$type', '$task_num', '$desc')";
        
        if ($conn->query($sql)) $message = "<div class='alert alert-success'>Task posted successfully!</div>";
        else $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    } else {
        $message = "<div class='alert alert-danger'>Security Alert: You are not assigned to this course.</div>";
    }
}

//task delete
if (isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $conn->query("DELETE FROM task WHERE task_id='$task_id' AND teacher_id='$t_id'");
    $message = "<div class='alert alert-warning'>Task deleted.</div>";
}


if (isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $new_date = $_POST['new_due_date'];
    $conn->query("UPDATE task SET due_date='$new_date' WHERE task_id='$task_id' AND teacher_id='$t_id'");
    $message = "<div class='alert alert-success'>Date updated!</div>";
}



$my_courses = $conn->query("SELECT * FROM course WHERE t_id='$t_id'");

$students_result = false;
$tasks_result = false;

if (isset($_GET['c_id']) && isset($_GET['section'])) {
    $active_c_id = $_GET['c_id'];
    $active_section = $_GET['section'];

    $verify_course = $conn->query("SELECT * FROM course WHERE c_id='$active_c_id' AND section='$active_section' AND t_id='$t_id'");

    if ($verify_course->num_rows > 0) {
        
        $students_result = $conn->query("SELECT s.s_id, s.name, s.department, s.g_suit 
                                         FROM student s 
                                         JOIN student_enrolls se ON s.s_id = se.s_id 
                                         WHERE se.c_id = '$active_c_id' AND se.section = '$active_section'");
        
        //date
        $tasks_result = $conn->query("SELECT * FROM task WHERE c_id='$active_c_id' AND section='$active_section' ORDER BY due_date ASC");
    } else {
        $active_c_id = "";
        $message = "<div class='alert alert-danger'>Access Denied.</div>";
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Teacher Dashboard</h2>
        <span class="badge bg-primary fs-6">Teacher ID: <?php echo $t_id; ?></span>
    </div>

    <?php echo $message; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">Select Section</div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-center">
                <div class="col-auto"><label class="col-form-label fw-bold">My Courses:</label></div>
                <div class="col-auto flex-grow-1">
                    <select name="c_selector" id="courseSelector" class="form-select" required>
                        <option value="">-- Choose a Section --</option>
                        <?php 
                        if($my_courses->num_rows > 0) {
                            while($c = $my_courses->fetch_assoc()) {
                                $val = $c['c_id'] . '|' . $c['section'];
                                $selected = ($active_c_id == $c['c_id'] && $active_section == $c['section']) ? 'selected' : '';
                                echo "<option value='$val' $selected>" . $c['c_id'] . " - " . $c['c_name'] . " (Sec: " . $c['section'] . ")</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" onclick="loadCourse()">Manage</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($active_c_id): ?>
        
        <div class="row">
            <div class="col-md-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">Post New Task</div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="c_id" value="<?php echo $active_c_id; ?>">
                            <input type="hidden" name="section" value="<?php echo $active_section; ?>">
                            
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Type</label>
                                    <select name="type" id="taskType" class="form-select" onchange="toggleLabel()">
                                        <option value="assignment">Assignment</option>
                                        <option value="quiz">Quiz</option>
                                        <option value="project">Project</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Number</label>
                                    <input type="number" name="task_number" class="form-control" placeholder="1" required>
                                </div>
                                <div class="col-md-5 mb-2">
                                    <label class="form-label" id="dateLabel">Due Date</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description / Topics</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="e.g. Chapter 1-3, or Link to file..." required></textarea>
                            </div>
                            <button type="submit" name="post_task" class="btn btn-success w-100">Post Task</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Current Tasks</div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0 text-center">
                            <thead class="small"><tr><th>Task</th><th>Description</th><th>Date</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php if($tasks_result && $tasks_result->num_rows > 0): ?>
                                    <?php while($task = $tasks_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo ucfirst($task['type']); ?> <?php echo $task['task_number']; ?></span>
                                        </td>
                                        <td class="text-start small text-muted w-50"><?php echo $task['description']; ?></td>
                                        <td class="fw-bold"><?php echo $task['due_date']; ?></td>
                                        <td>
                                            <button class="btn btn-primary btn-sm px-2" 
                                                onclick="openEditModal('<?php echo $task['task_id']; ?>', '<?php echo $task['due_date']; ?>')">
                                                Edit
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this task?');">
                                                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                                <button type="submit" name="delete_task" class="btn btn-danger btn-sm px-2">Del</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-muted py-3">No tasks posted.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white d-flex justify-content-between">
                        <span>Enrolled Students</span>
                        <span class="badge bg-light text-dark"><?php echo ($students_result) ? $students_result->num_rows : 0; ?></span>
                    </div>
                    <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>ID</th><th>Name</th></tr></thead>
                            <tbody>
                                <?php if($students_result && $students_result->num_rows > 0): ?>
                                    <?php while($stu = $students_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $stu['s_id']; ?></td>
                                        <td>
                                            <?php echo $stu['name']; ?><br>
                                            <small class="text-muted"><?php echo $stu['g_suit']; ?></small>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="text-center py-4 text-muted">No students yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Change Date</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="post">
          <div class="modal-body">
            <input type="hidden" name="task_id" id="modal_task_id">
            <label class="form-label">New Date</label>
            <input type="date" name="new_due_date" id="modal_due_date" class="form-control" required>
          </div>
          <div class="modal-footer">
            <button type="submit" name="update_task" class="btn btn-primary w-100">Update</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    function loadCourse() {
        var val = document.getElementById("courseSelector").value;
        if(val) {
            var parts = val.split("|");
            window.location.href = "teacher_dashboard.php?c_id=" + parts[0] + "&section=" + parts[1];
        } else {
            alert("Please select a course.");
        }
    }

    function openEditModal(taskId, currentdate) {
        document.getElementById('modal_task_id').value = taskId;
        document.getElementById('modal_due_date').value = currentdate;
        var myModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        myModal.show();
    }

    function toggleLabel() {
        var type = document.getElementById("taskType").value;
        var label = document.getElementById("dateLabel");
        if(type === "quiz") {
            label.innerText = "Quiz Date";
        } else {
            label.innerText = "Due Date";
        }
    }
</script>

</body>
</html>