<?php
require_once "auth_check.php";
checkAdmin();
require_once "db.php";
require_once "audit.php";

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');
        $course = trim($_POST['course'] ?? 'B.Tech IT');
        $year = trim($_POST['year'] ?? '1st Year');
        $status = trim($_POST['status'] ?? 'active');

        if (empty($name) || empty($email) || empty($phone)) {
            $err = "Name, email, and phone number are required.";
        } else {
            $chk = mysqli_query($conn, "SELECT id FROM students WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
            if (mysqli_num_rows($chk) > 0) {
                $err = "Student with this email already exists.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, phone, gender, course, year, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $phone, $gender, $course, $year, $status);
                if (mysqli_stmt_execute($stmt)) {
                    logAction($conn, "Add Student", "Added student " . $name . " (" . $email . ")");
                    $msg = "Student record created successfully.";
                } else {
                    $err = "Failed to create student: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');
        $course = trim($_POST['course'] ?? 'B.Tech IT');
        $year = trim($_POST['year'] ?? '1st Year');
        $status = trim($_POST['status'] ?? 'active');

        if ($id > 0 && !empty($name) && !empty($email)) {
            $stmt = mysqli_prepare($conn, "UPDATE students SET name = ?, email = ?, phone = ?, gender = ?, course = ?, year = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssssi", $name, $email, $phone, $gender, $course, $year, $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                logAction($conn, "Edit Student", "Updated student ID " . $id . " (" . $name . ")");
                $msg = "Student record updated successfully.";
            } else {
                $err = "Failed to update student: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET['action'])) {
    $id = (int)($_GET['id'] ?? 0);
    if ($_GET['action'] === 'delete' && $id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            logAction($conn, "Delete Student", "Deleted student ID " . $id);
            $msg = "Student record deleted successfully.";
        } else {
            $err = "Failed to delete student.";
        }
        mysqli_stmt_close($stmt);
    } elseif ($_GET['action'] === 'toggle' && $id > 0) {
        $cur = mysqli_query($conn, "SELECT status FROM students WHERE id = $id LIMIT 1");
        if ($r = mysqli_fetch_assoc($cur)) {
            $newSt = ($r['status'] === 'active') ? 'inactive' : 'active';
            mysqli_query($conn, "UPDATE students SET status = '$newSt' WHERE id = $id");
            logAction($conn, "Toggle Student Status", "Student ID $id set to $newSt");
            $msg = "Student status set to " . ucfirst($newSt);
        }
    }
}

$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = "SELECT * FROM students WHERE 1=1";
if (!empty($search)) {
    $safeSearch = mysqli_real_escape_string($conn, $search);
    $query .= " AND (name LIKE '%$safeSearch%' OR email LIKE '%$safeSearch%' OR phone LIKE '%$safeSearch%')";
}
if (!empty($courseFilter)) {
    $safeCourse = mysqli_real_escape_string($conn, $courseFilter);
    $query .= " AND course LIKE '%$safeCourse%'";
}
if (!empty($statusFilter)) {
    $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
    $query .= " AND status = '$safeStatus'";
}
$query .= " ORDER BY id DESC";
$studentsList = mysqli_query($conn, $query);

$editStudent = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editRes = mysqli_query($conn, "SELECT * FROM students WHERE id = $editId LIMIT 1");
    $editStudent = mysqli_fetch_assoc($editRes);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Management CRUD</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>Student Management (CRUD)</h2>
                <p>Add, edit, delete, search, filter students, and export records to CSV.</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="#studentFormBox" class="btn" style="background: #2563eb;">+ Add New Student</a>
                <a href="export_students.php" class="btn" style="background: #059669;">Export to CSV</a>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success" style="margin-top: 15px;"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($err)): ?>
        <div class="alert alert-error" style="margin-top: 15px;"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <div class="box" style="margin-top: 20px;">
        <form method="GET" action="students.php" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 180px;">
                <label for="search">Search Name/Email/Phone</label>
                <input type="text" id="search" name="search" placeholder="Type to search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label for="course">Filter Course</label>
                <select id="course" name="course">
                    <option value="">All Courses</option>
                    <option value="IT" <?php if ($courseFilter === 'IT') echo 'selected'; ?>>Information Technology</option>
                    <option value="CE" <?php if ($courseFilter === 'CE') echo 'selected'; ?>>Computer Engineering</option>
                    <option value="CS" <?php if ($courseFilter === 'CS') echo 'selected'; ?>>Computer Science</option>
                    <option value="EC" <?php if ($courseFilter === 'EC') echo 'selected'; ?>>Electronics</option>
                    <option value="ME" <?php if ($courseFilter === 'ME') echo 'selected'; ?>>Mechanical</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 120px;">
                <label for="status">Filter Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="active" <?php if ($statusFilter === 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($statusFilter === 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn">Filter</button>
                <a href="students.php" class="btn" style="background: #64748b;">Reset</a>
            </div>
        </form>
    </div>

    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3>Enrolled Students Database (<?php echo mysqli_num_rows($studentsList); ?> Records)</h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($studentsList) > 0): ?>
                        <?php while ($s = mysqli_fetch_assoc($studentsList)): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['phone']); ?></td>
                                <td><?php echo htmlspecialchars($s['gender']); ?></td>
                                <td><?php echo htmlspecialchars($s['course']); ?></td>
                                <td><?php echo htmlspecialchars($s['year']); ?></td>
                                <td>
                                    <a href="students.php?action=toggle&id=<?php echo $s['id']; ?>" title="Click to toggle status">
                                        <span class="badge <?php echo $s['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ucfirst($s['status']); ?>
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="students.php?edit_id=<?php echo $s['id']; ?>#studentFormBox" class="btn-sm" style="background: #2563eb; color: #fff; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px;">Edit</a>
                                        <a href="students.php?action=delete&id=<?php echo $s['id']; ?>" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($s['name'])); ?>?');" class="btn-sm" style="background: #ef4444; color: #fff; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: #64748b; padding: 20px;">No student records found matching the criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box" id="studentFormBox" style="margin-top: 25px; max-width: 700px;">
        <h3><?php echo $editStudent ? "Edit Student Details (ID: " . $editStudent['id'] . ")" : "Add New Student Record"; ?></h3>
        
        <form method="POST" action="students.php">
            <input type="hidden" name="action" value="<?php echo $editStudent ? 'edit' : 'add'; ?>">
            <?php if ($editStudent): ?>
                <input type="hidden" name="id" value="<?php echo $editStudent['id']; ?>">
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="form_name">Full Name</label>
                    <input type="text" id="form_name" name="name" required value="<?php echo htmlspecialchars($editStudent['name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="form_email">Email Address</label>
                    <input type="email" id="form_email" name="email" required value="<?php echo htmlspecialchars($editStudent['email'] ?? ''); ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="form_phone">Mobile Number</label>
                    <input type="text" id="form_phone" name="phone" required value="<?php echo htmlspecialchars($editStudent['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="form_gender">Gender</label>
                    <select id="form_gender" name="gender">
                        <option value="Male" <?php if (($editStudent['gender'] ?? '') === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if (($editStudent['gender'] ?? '') === 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if (($editStudent['gender'] ?? '') === 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="form_course">Course</label>
                    <select id="form_course" name="course">
                        <option value="B.Tech IT" <?php if (($editStudent['course'] ?? '') === 'B.Tech IT') echo 'selected'; ?>>B.Tech IT</option>
                        <option value="B.Tech CE" <?php if (($editStudent['course'] ?? '') === 'B.Tech CE') echo 'selected'; ?>>B.Tech CE</option>
                        <option value="B.Tech CS" <?php if (($editStudent['course'] ?? '') === 'B.Tech CS') echo 'selected'; ?>>B.Tech CS</option>
                        <option value="B.Tech EC" <?php if (($editStudent['course'] ?? '') === 'B.Tech EC') echo 'selected'; ?>>B.Tech EC</option>
                        <option value="B.Tech ME" <?php if (($editStudent['course'] ?? '') === 'B.Tech ME') echo 'selected'; ?>>B.Tech ME</option>
                        <option value="B.Tech EE" <?php if (($editStudent['course'] ?? '') === 'B.Tech EE') echo 'selected'; ?>>B.Tech EE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="form_year">Year of Study</label>
                    <select id="form_year" name="year">
                        <option value="1st Year" <?php if (($editStudent['year'] ?? '') === '1st Year') echo 'selected'; ?>>1st Year</option>
                        <option value="2nd Year" <?php if (($editStudent['year'] ?? '') === '2nd Year') echo 'selected'; ?>>2nd Year</option>
                        <option value="3rd Year" <?php if (($editStudent['year'] ?? '') === '3rd Year') echo 'selected'; ?>>3rd Year</option>
                        <option value="4th Year" <?php if (($editStudent['year'] ?? '') === '4th Year') echo 'selected'; ?>>4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="form_status">Account Status</label>
                    <select id="form_status" name="status">
                        <option value="active" <?php if (($editStudent['status'] ?? '') === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if (($editStudent['status'] ?? '') === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" class="btn"><?php echo $editStudent ? "Update Student" : "Save Student"; ?></button>
                <?php if ($editStudent): ?>
                    <a href="students.php" class="btn" style="background: #64748b;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
