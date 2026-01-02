<?php
include 'header.php'; 
include 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$message = "";



// course add
if (isset($_POST['add_course'])) {
    $c_id = strtoupper(trim($_POST['c_id']));
    $c_name = trim($_POST['c_name']);
    $sec = strtoupper(trim($_POST['section']));

    
    $check = $conn->query("SELECT * FROM course WHERE c_id='$c_id' AND section='$sec'");
    
    if ($check->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Error: Course $c_id Section $sec already exists!</div>";
    } else {
        //new course insert
        $sql = "INSERT INTO course (c_id, c_name, section) VALUES ('$c_id', '$c_name', '$sec')";
        if ($conn->query($sql)) {
            $message = "<div class='alert alert-success'>Course $c_id ($sec) added successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}



//course delete
if (isset($_POST['delete_course'])) {
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];
    
    if ($conn->query("DELETE FROM course WHERE c_id='$c_id' AND section='$sec'")) {
        $message = "<div class='alert alert-warning'>Course $c_id ($sec) deleted.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

//remove user
if (isset($_POST['remove_user'])) {
    $target_id = $_POST['target_id'];
    $type = $_POST['type']; 
    
    if ($type == 'student') {
        if ($conn->query("DELETE FROM student WHERE s_id='$target_id'")) {
            $message = "<div class='alert alert-danger'>Student $target_id removed completely.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } 
    elseif ($type == 'teacher') {
        $conn->query("UPDATE course SET t_id=NULL WHERE t_id='$target_id'");
        if ($conn->query("DELETE FROM teacher WHERE t_id='$target_id'")) {
            $message = "<div class='alert alert-warning'>Teacher $target_id removed. Courses marked TBA.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

//user verify




if (isset($_POST['verify_user'])) {
    $target_id = $_POST['target_id'];
    $type = $_POST['type']; 
    $status = $_POST['status'];
    $table = ($type == 'student') ? 'student' : 'teacher';
    $id_col = ($type == 'student') ? 's_id' : 't_id';
    $conn->query("UPDATE $table SET account_status='$status', a_id='$admin_id' WHERE $id_col='$target_id'");
    $message = "<div class='alert alert-success'>User updated!</div>";
}
//resources


// resource verify
if (isset($_POST['verify_resource'])) {
    $r_id = $_POST['r_id'];
    $status = $_POST['status'];
    $conn->query("UPDATE resource SET status='$status', a_id='$admin_id' WHERE r_id='$r_id'");
    $message = "<div class='alert alert-success'>Resource updated!</div>";
}

//resource delete
if (isset($_POST['delete_resource'])) {
    $r_id = $_POST['r_id'];
    if($conn->query("DELETE FROM resource WHERE r_id='$r_id'")) {
        $message = "<div class='alert alert-warning'>Resource deleted permanently.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error deleting resource: " . $conn->error . "</div>";
    }
}

//teacher add
if (isset($_POST['assign_teacher'])) {
    $t_id = $_POST['t_id'];
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];
    $conn->query("UPDATE course SET t_id='$t_id' WHERE c_id='$c_id' AND section='$sec'");
    $message = "<div class='alert alert-success'>Teacher assigned!</div>";
}

//remove teacher 
if (isset($_POST['remove_assign'])) {
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];
    $conn->query("UPDATE course SET t_id=NULL WHERE c_id='$c_id' AND section='$sec'");
    $message = "<div class='alert alert-warning'>Teacher unassigned. Course is TBA.</div>";
}

//remove student
if (isset($_POST['remove_enroll'])) {
    $s_id = $_POST['s_id'];
    $c_id = $_POST['c_id'];
    $sec = $_POST['section'];
    $conn->query("DELETE FROM student_enrolls WHERE s_id='$s_id' AND c_id='$c_id' AND section='$sec'");
    $message = "<div class='alert alert-warning'>Student removed from course.</div>";
}

$view = isset($_GET['view']) ? $_GET['view'] : 'users';
?>

<div class="container mt-4">
    <h2>Admin Control Panel</h2>
    <?php echo $message; ?>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link <?php echo ($view=='users')?'active':''; ?>" href="?view=users">Verify Users</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($view=='resources')?'active':''; ?>" href="?view=resources">Manage Resources</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($view=='courses')?'active':''; ?>" href="?view=courses">Teacher Assignments</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($view=='enrollments')?'active':''; ?>" href="?view=enrollments">Student Enrollments</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($view=='add_course')?'active':''; ?>" href="?view=add_course">Manage Courses</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($view=='directory')?'active':''; ?>" href="?view=directory">User Directory</a></li>
    </ul>

    <?php if($view == 'users'): 
        $students = $conn->query("SELECT * FROM student WHERE account_status='pending'");
        $teachers = $conn->query("SELECT * FROM teacher WHERE account_status='pending'");
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">Pending Students</div>
                <div class="card-body">
                    <?php while($row = $students->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?php echo $row['name']; ?> (<?php echo $row['s_id']; ?>)</span>
                            <form method="post">
                                <input type="hidden" name="target_id" value="<?php echo $row['s_id']; ?>">
                                <input type="hidden" name="type" value="student">
                                <button name="verify_user" class="btn btn-success btn-sm" onclick="this.form.status.value='active';">✔</button>
                                <button name="verify_user" class="btn btn-danger btn-sm" onclick="this.form.status.value='rejected';">✖</button>
                                <input type="hidden" name="status" value="">
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">Pending Teachers</div>
                <div class="card-body">
                    <?php while($row = $teachers->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?php echo $row['name']; ?> (<?php echo $row['t_id']; ?>)</span>
                            <form method="post">
                                <input type="hidden" name="target_id" value="<?php echo $row['t_id']; ?>">
                                <input type="hidden" name="type" value="teacher">
                                <button name="verify_user" class="btn btn-success btn-sm" onclick="this.form.status.value='active';">✔</button>
                                <button name="verify_user" class="btn btn-danger btn-sm" onclick="this.form.status.value='rejected';">✖</button>
                                <input type="hidden" name="status" value="">
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($view == 'resources'): 
        
        $limit = 10;
        
        $search_query = "";
        $search_sql = "";
        if(isset($_POST['search_r_id']) && !empty($_POST['search_r_id'])){
            $search_id = $_POST['search_r_id'];
            $search_sql = " AND r.r_id = '$search_id' ";
            $search_query = "&search=".$search_id;
        }

        
        $page_pending = isset($_GET['p_pending']) ? (int)$_GET['p_pending'] : 1;
        $offset_pending = ($page_pending - 1) * $limit;
        
        $sql_count_p = "SELECT COUNT(*) as total FROM resource r WHERE r.status='pending' $search_sql";
        $total_pending = $conn->query($sql_count_p)->fetch_assoc()['total'];
        $pages_pending = ceil($total_pending / $limit);

        $pending_resources = $conn->query("SELECT r.*, s.name FROM resource r JOIN student s ON r.s_id = s.s_id WHERE r.status='pending' $search_sql LIMIT $limit OFFSET $offset_pending");

        
        $page_approved = isset($_GET['p_approved']) ? (int)$_GET['p_approved'] : 1;
        $offset_approved = ($page_approved - 1) * $limit;

        $sql_count_a = "SELECT COUNT(*) as total FROM resource r WHERE r.status='approved' $search_sql";
        $total_approved = $conn->query($sql_count_a)->fetch_assoc()['total'];
        $pages_approved = ceil($total_approved / $limit);

        $approved_resources = $conn->query("SELECT r.*, s.name FROM resource r JOIN student s ON r.s_id = s.s_id WHERE r.status='approved' $search_sql LIMIT $limit OFFSET $offset_approved");
    ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card p-3 bg-light">
                <form method="post" class="d-flex gap-2">
                    <input type="number" name="search_r_id" class="form-control" placeholder="Search Resource ID (e.g., 101)" value="<?php echo isset($_POST['search_r_id']) ? $_POST['search_r_id'] : ''; ?>">
                    <button type="submit" class="btn btn-dark">Search</button>
                    <a href="?view=resources" class="btn btn-secondary">Reset</a>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>Pending Resources (Requires Approval)</span>
            <span class="badge bg-light text-dark"><?php echo $total_pending; ?> Found</span>
        </div>
        <div class="card-body">
            <?php if ($pending_resources->num_rows > 0): ?>
            <table class="table">
                <thead><tr><th>ID</th><th>Student</th><th>Description/Link</th><th>Action</th></tr></thead>
                <tbody>
                <?php while($row = $pending_resources->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['r_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['description']; ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="r_id" value="<?php echo $row['r_id']; ?>">
                            <button name="verify_resource" class="btn btn-success btn-sm" onclick="this.form.status.value='approved'">Approve</button>
                            <button name="verify_resource" class="btn btn-danger btn-sm" onclick="this.form.status.value='rejected'">Reject</button>
                            <input type="hidden" name="status" value="">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item <?php echo ($page_pending <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=resources&p_pending=<?php echo $page_pending-1; ?>&p_approved=<?php echo $page_approved; ?><?php echo $search_query; ?>">Previous</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Page <?php echo $page_pending; ?> of <?php echo $pages_pending; ?></span></li>
                    <li class="page-item <?php echo ($page_pending >= $pages_pending) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=resources&p_pending=<?php echo $page_pending+1; ?>&p_approved=<?php echo $page_approved; ?><?php echo $search_query; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php else: ?>
                <p class="text-muted">No pending resources matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span>Approved Resources (Live)</span>
            <span class="badge bg-light text-dark"><?php echo $total_approved; ?> Found</span>
        </div>
        <div class="card-body">
            <?php if ($approved_resources->num_rows > 0): ?>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Student</th><th>Description/Link</th><th>Action</th></tr></thead>
                <tbody>
                <?php while($row = $approved_resources->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['r_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>
                        <?php echo $row['description']; ?>
                        <?php if(!empty($row['link'])): ?>
                            <br><a href="<?php echo $row['link']; ?>" target="_blank" class="small">Open Link</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this approved resource?');">
                            <input type="hidden" name="r_id" value="<?php echo $row['r_id']; ?>">
                            <button type="submit" name="delete_resource" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <nav>
                <ul class="pagination pagination-sm justify-content-center">
                    <li class="page-item <?php echo ($page_approved <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=resources&p_approved=<?php echo $page_approved-1; ?>&p_pending=<?php echo $page_pending; ?><?php echo $search_query; ?>">Previous</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Page <?php echo $page_approved; ?> of <?php echo $pages_approved; ?></span></li>
                    <li class="page-item <?php echo ($page_approved >= $pages_approved) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=resources&p_approved=<?php echo $page_approved+1; ?>&p_pending=<?php echo $page_pending; ?><?php echo $search_query; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php else: ?>
                <p class="text-muted">No approved resources matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if($view == 'courses'): 
        $assignments = $conn->query("SELECT c.*, t.name, t.t_id FROM course c LEFT JOIN teacher t ON c.t_id = t.t_id");
        $teachers = $conn->query("SELECT * FROM teacher WHERE account_status='active'");
    ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Assign Teacher</h5>
                <form method="post">
                    <div class="mb-2">
                        <label>Select Teacher</label>
                        <select name="t_id" class="form-select">
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['t_id']; ?>"><?php echo $t['name']; ?> (<?php echo $t['t_id']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Select Course</label>
                        <select name="c_selector" id="c_select" class="form-select">
                            <?php 
                            $assignments->data_seek(0); 
                            while($c = $assignments->fetch_assoc()): 
                                $current = $c['t_id'] ? $c['name'] : "TBA";
                            ?>
                                <option value="<?php echo $c['c_id'].'|'.$c['section']; ?>">
                                    <?php echo $c['c_id']; ?> - <?php echo $c['section']; ?> (Current: <?php echo $current; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <input type="hidden" name="c_id" id="h_cid">
                    <input type="hidden" name="section" id="h_sec">
                    <button type="submit" name="assign_teacher" class="btn btn-primary w-100">Update Assignment</button>
                </form>
                <script>
                    document.querySelector('form').addEventListener('submit', function() {
                        var parts = document.getElementById('c_select').value.split('|');
                        document.getElementById('h_cid').value = parts[0];
                        document.getElementById('h_sec').value = parts[1];
                    });
                </script>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Course Assignments</div>
                <table class="table table-sm">
                    <thead><tr><th>Course</th><th>Section</th><th>Assigned Teacher</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php 
                        $assignments->data_seek(0); 
                        while($row = $assignments->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $row['c_id']; ?></td>
                            <td><?php echo $row['section']; ?></td>
                            <td>
                                <?php if($row['t_id']): ?>
                                    <span class="text-success fw-bold"><?php echo $row['name']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">TBA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['t_id']): ?>
                                <form method="post">
                                    <input type="hidden" name="c_id" value="<?php echo $row['c_id']; ?>">
                                    <input type="hidden" name="section" value="<?php echo $row['section']; ?>">
                                    <button type="submit" name="remove_assign" class="btn btn-outline-danger btn-sm">Unassign</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($view == 'enrollments'): 
        $all_courses = $conn->query("SELECT * FROM course");
        $sql = "SELECT se.*, s.name FROM student_enrolls se JOIN student s ON se.s_id = s.s_id";
        
        if (isset($_POST['search_student'])) {
            $search_id = $_POST['s_id_search'];
            $sql .= " WHERE se.s_id = '$search_id'";
        } 
        elseif (isset($_POST['filter_section'])) {
            $c_id = $_POST['c_id'];
            $sec = $_POST['section'];
            $sql .= " WHERE se.c_id = '$c_id' AND se.section = '$sec'";
        } 
        else {
            $sql .= " LIMIT 50";
        }
        $enroll_results = $conn->query($sql);
    ?>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card p-3 bg-light">
                <form method="post" class="d-flex gap-2">
                    <select name="c_selector" id="enroll_c_select" class="form-select">
                        <option value="">Filter by Course...</option>
                        <?php while($c = $all_courses->fetch_assoc()): ?>
                            <option value="<?php echo $c['c_id'].'|'.$c['section']; ?>"><?php echo $c['c_id'].'-'.$c['section']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="hidden" name="c_id" id="en_cid">
                    <input type="hidden" name="section" id="en_sec">
                    <button type="submit" name="filter_section" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 bg-light">
                <form method="post" class="d-flex gap-2">
                    <input type="number" name="s_id_search" class="form-control" placeholder="Search Student ID">
                    <button type="submit" name="search_student" class="btn btn-dark">Search</button>
                </form>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>Student</th><th>ID</th><th>Course</th><th>Section</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if ($enroll_results && $enroll_results->num_rows > 0): ?>
                        <?php while($row = $enroll_results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['s_id']; ?></td>
                            <td><?php echo $row['c_id']; ?></td>
                            <td><?php echo $row['section']; ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Remove enrollment?');">
                                    <input type="hidden" name="s_id" value="<?php echo $row['s_id']; ?>">
                                    <input type="hidden" name="c_id" value="<?php echo $row['c_id']; ?>">
                                    <input type="hidden" name="section" value="<?php echo $row['section']; ?>">
                                    <button type="submit" name="remove_enroll" class="btn btn-warning btn-sm">Unenroll</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('enroll_c_select').addEventListener('change', function() {
            var parts = this.value.split('|');
            document.getElementById('en_cid').value = parts[0];
            document.getElementById('en_sec').value = parts[1];
        });
    </script>
    <?php endif; ?>

    <?php if($view == 'add_course'): 
        $all_courses_list = $conn->query("SELECT * FROM course ORDER BY c_id, section");
    ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">Add New Course</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="c_id" class="form-control" placeholder="e.g. CSE101" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" name="c_name" class="form-control" placeholder="e.g. Intro to CS" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="section" class="form-control" placeholder="e.g. 1, 2, A, B" required>
                        </div>
                        <button type="submit" name="add_course" class="btn btn-success w-100">Add Course</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">Existing Courses</div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Code</th><th>Name</th><th>Sec</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php while($c = $all_courses_list->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $c['c_id']; ?></td>
                                <td><?php echo $c['c_name']; ?></td>
                                <td><?php echo $c['section']; ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Delete this course section? All enrollments/tasks will be deleted.');">
                                        <input type="hidden" name="c_id" value="<?php echo $c['c_id']; ?>">
                                        <input type="hidden" name="section" value="<?php echo $c['section']; ?>">
                                        <button type="submit" name="delete_course" class="btn btn-danger btn-sm py-0">Del</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($view == 'directory'): 
        $dir_type = isset($_GET['type']) ? $_GET['type'] : 'student';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        if ($dir_type == 'student') {
            $total_sql = "SELECT COUNT(*) as total FROM student";
            $data_sql = "SELECT s_id as id, name, g_suit, department FROM student LIMIT $limit OFFSET $offset";
        } else {
            $total_sql = "SELECT COUNT(*) as total FROM teacher";
            $data_sql = "SELECT t_id as id, name, g_suit, department FROM teacher LIMIT $limit OFFSET $offset";
        }

        $total_rows = $conn->query($total_sql)->fetch_assoc()['total'];
        $total_pages = ceil($total_rows / $limit);
        $directory_data = $conn->query($data_sql);
    ?>
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white d-flex justify-content-between">
            <span>User Directory (<?php echo ucfirst($dir_type); ?>s)</span>
            <div>
                <a href="?view=directory&type=student" class="btn btn-sm btn-light <?php echo ($dir_type=='student')?'active fw-bold':''; ?>">Students</a>
                <a href="?view=directory&type=teacher" class="btn btn-sm btn-light <?php echo ($dir_type=='teacher')?'active fw-bold':''; ?>">Teachers</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Dept</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while($row = $directory_data->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['g_suit']; ?></td>
                        <td><?php echo $row['department']; ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Confirm deletion?');">
                                <input type="hidden" name="target_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="type" value="<?php echo $dir_type; ?>">
                                <button type="submit" name="remove_user" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=directory&type=<?php echo $dir_type; ?>&page=<?php echo $page-1; ?>">Previous</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span></li>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?view=directory&type=<?php echo $dir_type; ?>&page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>








</html>