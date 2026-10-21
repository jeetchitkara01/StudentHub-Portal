<?php
require_once "db.php";
require_once "audit.php";

$errors = [];
$success = false;
$submittedData = [];

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = sanitizeInput($_POST["fullName"] ?? "");
    $enrollment = sanitizeInput($_POST["enrollment"] ?? "");
    $department = sanitizeInput($_POST["department"] ?? "");
    $semester = sanitizeInput($_POST["semester"] ?? "");
    $email = sanitizeInput($_POST["email"] ?? "");
    $mobile = sanitizeInput($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $gender = sanitizeInput($_POST["gender"] ?? "");
    $terms = isset($_POST["terms"]);

    if (empty($name)) {
        $errors[] = "Full Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]{3,30}$/", $name)) {
        $errors[] = "Full Name must contain only letters and spaces (3-30 characters).";
    }

    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email Address format.";
    }

    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be exactly 10 numerical digits.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($gender)) {
        $errors[] = "Please select your gender.";
    }

    if (!$terms) {
        $errors[] = "You must accept the Terms and Conditions.";
    }

    if (empty($errors)) {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkRes = mysqli_stmt_get_result($checkStmt);
        if (mysqli_num_rows($checkRes) > 0) {
            $errors[] = "This email is already registered. Please login or use a different email.";
        }
        mysqli_stmt_close($checkStmt);
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = "student";
        $status = "active";
        $course = $department ? ($department . " " . $semester) : "B.Tech IT";
        $year = $semester ? $semester : "1st Year";

        $userStmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($userStmt, "sssss", $name, $email, $hashedPassword, $role, $status);
        mysqli_stmt_execute($userStmt);
        $userId = mysqli_insert_id($conn);
        mysqli_stmt_close($userStmt);

        $studStmt = mysqli_prepare($conn, "INSERT INTO students (user_id, name, email, phone, gender, course, year, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($studStmt, "isssssss", $userId, $name, $email, $mobile, $gender, $course, $year, $status);
        mysqli_stmt_execute($studStmt);
        mysqli_stmt_close($studStmt);

        $timestamp = date("Y-m-d H:i:s");

        $csvFile = "data/registrations.csv";
        $csvRow = [$timestamp, $name, $enrollment, $department, $semester, $email, $mobile, $gender];
        $fileHandle = fopen($csvFile, "a");
        if ($fileHandle) {
            fputcsv($fileHandle, $csvRow);
            fclose($fileHandle);
        }

        $jsonFile = "data/registrations.json";
        $currentJsonData = [];
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $currentJsonData = json_decode($jsonContent, true) ?? [];
        }

        $newRecord = [
            "timestamp" => $timestamp,
            "name" => $name,
            "enrollment" => $enrollment,
            "department" => $department,
            "semester" => $semester,
            "email" => $email,
            "mobile" => $mobile,
            "gender" => $gender
        ];
        $currentJsonData[] = $newRecord;
        file_put_contents($jsonFile, json_encode($currentJsonData, JSON_PRETTY_PRINT));

        logAction($conn, "User Registration", "New student account created for " . $email);

        $success = true;
        $submittedData = $newRecord;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registration Status</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="max-width: 650px; margin: 30px auto;">
        <?php if ($success): ?>
            <h2 style="color: #10b981;">Registration Successful!</h2>
            <p>Your student account has been created in the database and saved to CSV/JSON files.</p>
            
            <table>
                <tbody>
                    <tr><th>Timestamp</th><td><?php echo htmlspecialchars($submittedData['timestamp']); ?></td></tr>
                    <tr><th>Full Name</th><td><?php echo htmlspecialchars($submittedData['name']); ?></td></tr>
                    <tr><th>Enrollment</th><td><?php echo htmlspecialchars($submittedData['enrollment']); ?></td></tr>
                    <tr><th>Department</th><td><?php echo htmlspecialchars($submittedData['department']); ?></td></tr>
                    <tr><th>Semester</th><td><?php echo htmlspecialchars($submittedData['semester']); ?></td></tr>
                    <tr><th>Email</th><td><?php echo htmlspecialchars($submittedData['email']); ?></td></tr>
                    <tr><th>Mobile</th><td><?php echo htmlspecialchars($submittedData['mobile']); ?></td></tr>
                    <tr><th>Gender</th><td><?php echo htmlspecialchars($submittedData['gender']); ?></td></tr>
                </tbody>
            </table>

            <div style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="login.php" class="btn">Proceed to Login</a>
                <a href="view_submissions.php" class="btn" style="background: #475569;">View File Records</a>
                <a href="register.html" class="btn" style="background: #64748b;">Register Another</a>
            </div>
        <?php else: ?>
            <h2 style="color: #ef4444;">Registration Error</h2>
            <p>The following errors occurred during processing:</p>
            
            <ul style="color: #dc2626; line-height: 1.8;">
                <?php foreach ($errors as $err): ?>
                    <li><strong><?php echo htmlspecialchars($err); ?></strong></li>
                <?php endforeach; ?>
            </ul>

            <p style="margin-top: 20px;">
                <a href="javascript:history.back()" class="btn">Go Back and Edit Form</a>
            </p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
