<?php
require_once "auth_check.php";
require_once "db.php";

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'];

$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $userEmail);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$eventsRes = mysqli_query($conn, "SELECT * FROM events WHERE status = 'open' ORDER BY event_date ASC LIMIT 4");

$regStmt = mysqli_prepare($conn, "SELECT * FROM registrations WHERE student_email = ? ORDER BY registered_at DESC");
mysqli_stmt_bind_param($regStmt, "s", $userEmail);
mysqli_stmt_execute($regStmt);
$myRegistrations = mysqli_stmt_get_result($regStmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                <p>Course: <strong><?php echo htmlspecialchars($student['course'] ?? 'B.Tech IT'); ?></strong> | Year: <strong><?php echo htmlspecialchars($student['year'] ?? '3rd Year'); ?></strong> | Status: <span class="badge badge-success"><?php echo htmlspecialchars($student['status'] ?? 'Active'); ?></span></p>
            </div>
            <div>
                <a href="profile.php" class="btn">My Profile</a>
                <a href="events.html" class="btn" style="background: #2563eb;">Browse Events</a>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 20px;">
        
        <div class="box">
            <h3>My Event Registrations</h3>
            <?php if (mysqli_num_rows($myRegistrations) > 0): ?>
                <table style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Date Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reg = mysqli_fetch_assoc($myRegistrations)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reg['event_title']); ?></td>
                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($reg['registered_at']))); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #64748b; margin-top: 10px;">You have not registered for any events yet.</p>
                <a href="events.html" class="btn" style="margin-top: 10px;">Explore Campus Events</a>
            <?php endif; ?>
        </div>

        <div class="box">
            <h3>Upcoming Campus Events</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                <?php while ($ev = mysqli_fetch_assoc($eventsRes)): ?>
                    <div style="border-left: 4px solid #2563eb; padding: 8px 12px; background: rgba(0,0,0,0.02); border-radius: 0 4px 4px 0;">
                        <strong><?php echo htmlspecialchars($ev['title']); ?></strong><br>
                        <small style="color: #64748b;">Date: <?php echo htmlspecialchars($ev['event_date']); ?> | Location: <?php echo htmlspecialchars($ev['location']); ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
            <a href="events.html" style="display: inline-block; margin-top: 15px; font-weight: bold;">View All Events &rarr;</a>
        </div>

    </div>

    <div class="box" style="margin-top: 20px;">
        <h3>Quick Portal Shortcuts</h3>
        <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 10px;">
            <a href="directory.html" class="btn" style="background: #475569;">Student Directory</a>
            <a href="courses.html" class="btn" style="background: #475569;">Course Catalog</a>
            <a href="results.html" class="btn" style="background: #475569;">Semester Results</a>
            <a href="view_submissions.php" class="btn" style="background: #475569;">View Submissions</a>
            <a href="feedback.html" class="btn" style="background: #475569;">Submit Feedback</a>
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
