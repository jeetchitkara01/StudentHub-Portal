<?php
session_start();
require_once "db.php";
require_once "audit.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = "";
$message = "";

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') $message = "You have been successfully logged out.";
    if ($_GET['msg'] === 'timeout') $error = "Session timed out due to inactivity. Please log in again.";
    if ($_GET['msg'] === 'unauthorized') $error = "Unauthorized access. Please log in with appropriate privileges.";
    if ($_GET['msg'] === 'registered') $message = "Registration successful! You can now log in with your credentials.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'inactive') {
                $error = "Your account is inactive. Please contact the administrator.";
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['last_activity'] = time();

                if ($remember) {
                    $token = bin2hex(random_bytes(16));
                    $tokenHash = hash('sha256', $token);
                    $upStmt = mysqli_prepare($conn, "UPDATE users SET remember_token = ? WHERE id = ?");
                    mysqli_stmt_bind_param($upStmt, "si", $tokenHash, $user['id']);
                    mysqli_stmt_execute($upStmt);
                    mysqli_stmt_close($upStmt);
                    setcookie("remember_user", $user['id'] . ":" . $token, time() + (86400 * 30), "/");
                }

                logAction($conn, "User Login", "Login successful for " . $user['email']);

                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - User Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box form-container" style="max-width: 450px; margin: 30px auto;">
        <h2>Portal Login</h2>
        <p>Enter your university credentials to access your dashboard.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="name@studenthub.edu" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="remember" name="remember" style="width: auto;">
                <label for="remember" style="margin-bottom: 0; cursor: pointer;">Remember Me on this device</label>
            </div>

            <button type="submit" class="btn" style="width: 100%;">Sign In</button>
        </form>

        <div style="margin-top: 20px; font-size: 13px; background: #e2e8f0; padding: 12px; border-radius: 4px; color: #1e293b;">
            <strong>Demo Credentials:</strong><br>
            - Admin: <code>admin@studenthub.edu</code> | Pass: <code>Admin@123</code><br>
            - Student: <code>student@studenthub.edu</code> | Pass: <code>Student@123</code>
        </div>

        <div style="margin-top: 15px; text-align: center; font-size: 14px;">
            <p>Don't have an account? <a href="register.html">Register here</a></p>
        </div>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
