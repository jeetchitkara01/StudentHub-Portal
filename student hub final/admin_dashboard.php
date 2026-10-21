<?php
require_once "auth_check.php";
checkAdmin();
require_once "db.php";
require_once "audit.php";

$msg = "";
if (isset($_GET['toggle_user_id'])) {
    $toggleId = (int)$_GET['toggle_user_id'];
    $uQuery = mysqli_query($conn, "SELECT status, email FROM users WHERE id = $toggleId LIMIT 1");
    if ($uRow = mysqli_fetch_assoc($uQuery)) {
        $newStatus = ($uRow['status'] === 'active') ? 'inactive' : 'active';
        $upStmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($upStmt, "si", $newStatus, $toggleId);
        mysqli_stmt_execute($upStmt);
        mysqli_stmt_close($upStmt);
        logAction($conn, "Toggle User Status", "Changed " . $uRow['email'] . " status to " . $newStatus);
        $msg = "User status updated to " . ucfirst($newStatus);
    }
}

$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM students WHERE status = 'active'"))['total'] ?? 0;
$totalEvents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM events"))['total'] ?? 0;
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$totalRegistrations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM registrations"))['total'] ?? 0;

$catRes = mysqli_query($conn, "SELECT category, COUNT(*) AS count FROM events GROUP BY category");
$catStats = [];
while ($cr = mysqli_fetch_assoc($catRes)) {
    $catStats[$cr['category']] = (int)$cr['count'];
}

$recentRegs = mysqli_query($conn, "SELECT r.*, s.phone FROM registrations r LEFT JOIN students s ON r.student_email = s.email ORDER BY r.registered_at DESC LIMIT 6");
$allUsers = mysqli_query($conn, "SELECT id, name, email, role, status, created_at FROM users ORDER BY id ASC");

$adminEmail = $_SESSION['email'] ?? 'admin@studenthub.edu';
$adminName = $_SESSION['name'] ?? 'System Administrator';
$sessionId = session_id();
$maskedSession = substr($sessionId, 0, 8) . '...' . substr($sessionId, -4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Administrator Control Panel</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
    <style>
        .admin-top-card {
            background-color: #1e293b;
            color: #ffffff;
            border-radius: 8px;
            padding: 22px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }
        .admin-top-card h1 { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px; }
        .admin-top-card p { font-size: 13px; color: #94a3b8; }
        .admin-badge {
            background: #9333ea;
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 800;
        }

        .admin-nav-bar {
            background: #111827;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .admin-nav-left { display: flex; flex-wrap: wrap; gap: 6px; }
        .nav-pill {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .nav-pill:hover, .nav-pill.active {
            background: #374151;
            color: #ffffff;
        }
        .logout-pill {
            background: #ef4444;
            color: #ffffff !important;
            padding: 6px 14px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: bold;
        }
        .logout-pill:hover { background: #dc2626; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #2563eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .stat-card.c-students { border-left-color: #10b981; }
        .stat-card.c-events { border-left-color: #8b5cf6; }
        .stat-card.c-regs { border-left-color: #f59e0b; }
        .stat-val { font-size: 32px; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-lbl { font-size: 13px; color: #64748b; font-weight: 600; margin-top: 6px; }
        .stat-icon { font-size: 30px; opacity: 0.85; }

        .panel-row {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 820px) {
            .panel-row { grid-template-columns: 1fr; }
        }

        .chart-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 24px;
        }
        .chart-bar-wrap {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 140px;
            padding: 15px 0 5px 0;
            border-bottom: 1.5px solid #cbd5e1;
            margin-top: 15px;
        }
        .chart-bar {
            width: 50px;
            background: #60a5fa;
            border-radius: 4px 4px 0 0;
            text-align: center;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            padding-top: 4px;
            transition: height 0.3s ease;
        }
        .chart-labels {
            display: flex;
            justify-content: space-around;
            margin-top: 8px;
            font-size: 11.5px;
            color: #64748b;
            font-weight: 600;
        }

        .session-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px 24px;
            font-size: 13px;
        }
        .session-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .session-row span:first-child { color: #64748b; font-weight: 600; }
        .session-row span:last-child { color: #0f172a; font-family: monospace; font-weight: 600; }
    </style>
</head>
<body>

<div class="container" style="margin-top: 20px;">

    <div class="admin-top-card">
        <div>
            <h1>ADMINISTRATOR CONTROL PANEL</h1>
            <p>Role-Based Administration, Live Analytics &amp; Audit Trail</p>
        </div>
        <div style="font-size: 13px; color: #f8fafc;">
            Welcome, <strong><?php echo htmlspecialchars($adminName); ?></strong> <span class="admin-badge">ADMIN</span>
        </div>
    </div>

    <div class="admin-nav-bar">
        <div class="admin-nav-left">
            <a href="admin_dashboard.php" class="nav-pill active">&#128202; Overview</a>
            <a href="students.php" class="nav-pill">&#127891; Student Management</a>
            <a href="manage_events.php" class="nav-pill">&#128197; Event Management</a>
            <a href="ajax_crud.php" class="nav-pill">&#9889; Live AJAX Events</a>
            <a href="verify_system.php" class="nav-pill" style="color: #34d399;">&#10003; System Health &amp; Tests</a>
            <a href="view_submissions.php" class="nav-pill">&#128196; Submissions</a>
            <a href="test_db.php" class="nav-pill">&#128452; Database Test</a>
            <a href="index.html" class="nav-pill">&#127979; Public Portal</a>
        </div>
        <div>
            <a href="logout.php" class="logout-pill">LOGOUT &#8618;</a>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?php echo $totalUsers; ?></div>
                <div class="stat-lbl">Total User Accounts</div>
            </div>
            <div class="stat-icon">&#128101;</div>
        </div>
        <div class="stat-card c-students">
            <div>
                <div class="stat-val"><?php echo $totalStudents; ?></div>
                <div class="stat-lbl">Active Students</div>
            </div>
            <div class="stat-icon">&#127891;</div>
        </div>
        <div class="stat-card c-events">
            <div>
                <div class="stat-val"><?php echo $totalEvents; ?></div>
                <div class="stat-lbl">Campus Events</div>
            </div>
            <div class="stat-icon">&#128197;</div>
        </div>
        <div class="stat-card c-regs">
            <div>
                <div class="stat-val"><?php echo $totalRegistrations; ?></div>
                <div class="stat-lbl">Event Registrations</div>
            </div>
            <div class="stat-icon">&#128221;</div>
        </div>
    </div>

    <div class="panel-row">
        <div class="chart-box">
            <h3 style="margin-top: 0; color: #1e293b; font-size: 15px;">&#128200; Event Distribution by Category</h3>
            <div class="chart-bar-wrap">
                <div class="chart-bar" style="height: <?php echo max(35, ($catStats['Technical'] ?? 0) * 25); ?>px; background: #3b82f6;">
                    <?php echo $catStats['Technical'] ?? 0; ?>
                </div>
                <div class="chart-bar" style="height: <?php echo max(35, ($catStats['Workshop'] ?? 0) * 25); ?>px; background: #10b981;">
                    <?php echo $catStats['Workshop'] ?? 0; ?>
                </div>
                <div class="chart-bar" style="height: <?php echo max(35, ($catStats['Cultural'] ?? 0) * 25); ?>px; background: #8b5cf6;">
                    <?php echo $catStats['Cultural'] ?? 0; ?>
                </div>
                <div class="chart-bar" style="height: <?php echo max(35, ($catStats['Sports'] ?? 0) * 25); ?>px; background: #f59e0b;">
                    <?php echo $catStats['Sports'] ?? 0; ?>
                </div>
            </div>
            <div class="chart-labels">
                <span>Technical</span>
                <span>Workshop</span>
                <span>Cultural</span>
                <span>Sports</span>
            </div>
        </div>

        <div class="session-box">
            <h3 style="margin-top: 0; color: #1e293b; font-size: 15px;">&#128272; Security &amp; Active Session</h3>
            <div style="margin-top: 10px;">
                <div class="session-row">
                    <span>User ID:</span>
                    <span><?php echo $_SESSION['user_id']; ?></span>
                </div>
                <div class="session-row">
                    <span>Admin Email:</span>
                    <span><?php echo htmlspecialchars($adminEmail); ?></span>
                </div>
                <div class="session-row">
                    <span>Privilege Level:</span>
                    <span>Full Administrator (RBAC)</span>
                </div>
                <div class="session-row">
                    <span>Session ID:</span>
                    <span><?php echo $maskedSession; ?></span>
                </div>
                <div class="session-row">
                    <span>Session Timeout:</span>
                    <span>15 Minutes (Inactivity)</span>
                </div>
            </div>
            <div style="margin-top: 14px; text-align: center;">
                <a href="login.php?msg=timeout" style="font-size: 12px; color: #d97706; text-decoration: none; font-weight: 700;">&#8635; Test Session Inactivity Timeout</a>
            </div>
        </div>
    </div>

    <div class="box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 15px;">&#128101; User Access Management &amp; Status Control</h3>
            <small style="color: #64748b;">One-Click Active/Inactive Toggle</small>
        </div>
        <table style="width: 100%; margin-top: 12px; font-size: 13px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($allUsers)): ?>
                    <tr>
                        <td><strong>#<?php echo $u['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge <?php echo $u['role'] === 'admin' ? 'badge-info' : 'badge-success'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $u['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo ucfirst($u['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="admin_dashboard.php?toggle_user_id=<?php echo $u['id']; ?>" class="btn-sm" style="background: <?php echo $u['status'] === 'active' ? '#ef4444' : '#10b981'; ?>; color: #fff; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-size: 11px;">
                                    <?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </a>
                            <?php else: ?>
                                <small style="color: #64748b; font-weight: bold;">Current Account</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="box">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 15px;">&#128221; Recent Event Registrations</h3>
            <a href="view_submissions.php" style="font-size: 12px; font-weight: 700;">View All Records &rarr;</a>
        </div>
        <table style="width: 100%; margin-top: 12px; font-size: 13px;">
            <thead>
                <tr>
                    <th>Reg ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Event Title</th>
                    <th>Registration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = mysqli_fetch_assoc($recentRegs)): ?>
                    <tr>
                        <td><strong>#<?php echo $r['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($r['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['student_email']); ?></td>
                        <td><?php echo htmlspecialchars($r['course']); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($r['event_title']); ?></span></td>
                        <td><small><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($r['registered_at']))); ?></small></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
