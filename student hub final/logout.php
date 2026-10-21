<?php
session_start();
require_once "db.php";
require_once "audit.php";

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    logAction($conn, "User Logout", "User ID " . $userId . " logged out");
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

setcookie("remember_user", "", time() - 3600, "/");

session_destroy();

header("Location: login.php?msg=logged_out");
exit;
?>
