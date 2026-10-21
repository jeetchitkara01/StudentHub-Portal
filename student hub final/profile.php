<?php
require_once "auth_check.php";
require_once "db.php";
require_once "audit.php";

$userId = $_SESSION['user_id'];
$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!empty($name)) {
        $upUser = mysqli_prepare($conn, "UPDATE users SET name = ? WHERE id = ?");
        mysqli_stmt_bind_param($upUser, "si", $name, $userId);
        mysqli_stmt_execute($upUser);
        mysqli_stmt_close($upUser);

        $_SESSION['name'] = $name;

        $upStud = mysqli_prepare($conn, "UPDATE students SET name = ?, phone = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($upStud, "ssi", $name, $phone, $userId);
        mysqli_stmt_execute($upStud);
        mysqli_stmt_close($upStud);

        logAction($conn, "Update Profile", "User updated profile name/phone");
        $msg = "Profile updated successfully.";
    } else {
        $err = "Full name cannot be empty.";
    }
}

$uQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = $userId LIMIT 1");
$userData = mysqli_fetch_assoc($uQuery);

$sQuery = mysqli_query($conn, "SELECT * FROM students WHERE user_id = $userId OR email = '" . mysqli_real_escape_string($conn, $userData['email']) . "' LIMIT 1");
$studentData = mysqli_fetch_assoc($sQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - User Profile</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="max-width: 600px; margin: 30px auto;">
        <h2>My User Profile</h2>
        <p>View and manage your account details.</p>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($err)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($userData['name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" disabled style="background: rgba(0,0,0,0.05);">
                <small style="color: #64748b;">Email cannot be changed.</small>
            </div>

            <div class="form-group">
                <label for="role">Account Role</label>
                <input type="text" id="role" value="<?php echo ucfirst(htmlspecialchars($userData['role'] ?? '')); ?>" disabled style="background: rgba(0,0,0,0.05);">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($studentData['phone'] ?? ''); ?>" placeholder="10-digit mobile">
            </div>

            <?php if ($studentData): ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" value="<?php echo htmlspecialchars($studentData['course']); ?>" disabled style="background: rgba(0,0,0,0.05);">
                    </div>
                    <div class="form-group">
                        <label>Year of Study</label>
                        <input type="text" value="<?php echo htmlspecialchars($studentData['year']); ?>" disabled style="background: rgba(0,0,0,0.05);">
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" class="btn">Update Profile</button>
                <a href="<?php echo $userData['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php'; ?>" class="btn" style="background: #64748b;">Back to Dashboard</a>
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
