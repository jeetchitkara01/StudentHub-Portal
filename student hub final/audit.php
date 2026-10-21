<?php
function logAction($conn, $action, $details = "") {
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $userName = isset($_SESSION['name']) ? $_SESSION['name'] : "Guest";
    $stmt = mysqli_prepare($conn, "INSERT INTO audit_logs (user_id, user_name, action, details) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $userId, $userName, $action, $details);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
?>
