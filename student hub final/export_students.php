<?php
require_once "auth_check.php";
checkAdmin();
require_once "db.php";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Gender', 'Course', 'Year', 'Status', 'Created At']);

$res = mysqli_query($conn, "SELECT id, name, email, phone, gender, course, year, status, created_at FROM students ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
