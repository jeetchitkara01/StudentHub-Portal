<?php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "studenthub";

$mysqliSuccess = false;
$pdoSuccess = false;
$mysqliMsg = "";
$pdoMsg = "";
$counts = [];

try {
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    if ($conn) {
        $mysqliSuccess = true;
        $mysqliMsg = "MySQLi Connection Successful (Host: $dbHost, DB: $dbName)";
    }
} catch (Exception $e) {
    $mysqliMsg = "MySQLi Error: " . $e->getMessage();
}

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdoSuccess = true;
    $pdoMsg = "PDO Connection Successful with Prepared Statements Enabled";

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM students");
    $stmt->execute();
    $counts['students'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM events");
    $stmt->execute();
    $counts['events'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM registrations");
    $stmt->execute();
    $counts['registrations'] = $stmt->fetch()['total'] ?? 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM users");
    $stmt->execute();
    $counts['users'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $pdoMsg = "PDO Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Practical 8 Database Connection Test</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: #f1f5f9; padding: 30px 15px;">

<div class="box" style="max-width: 750px; margin: 0 auto; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 25px;">
    <h2 style="color: #1e3a8a; margin-top: 0;">Practical 8: Database Connectivity Verification</h2>
    <p style="color: #475569; font-size: 14px;">Testing MySQL database connection using both <strong>MySQLi</strong> and <strong>PHP Data Objects (PDO)</strong> with Prepared Statements.</p>

    <div style="margin-top: 20px;">
        <div style="background: <?php echo $mysqliSuccess ? '#dcfce7' : '#fee2e2'; ?>; border: 1px solid <?php echo $mysqliSuccess ? '#86efac' : '#fca5a5'; ?>; color: <?php echo $mysqliSuccess ? '#166534' : '#991b1b'; ?>; padding: 12px 16px; border-radius: 6px; margin-bottom: 12px;">
            <strong>[MySQLi Status]</strong> <?php echo htmlspecialchars($mysqliMsg); ?>
        </div>

        <div style="background: <?php echo $pdoSuccess ? '#dcfce7' : '#fee2e2'; ?>; border: 1px solid <?php echo $pdoSuccess ? '#86efac' : '#fca5a5'; ?>; color: <?php echo $pdoSuccess ? '#166534' : '#991b1b'; ?>; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px;">
            <strong>[PDO Status]</strong> <?php echo htmlspecialchars($pdoMsg); ?>
        </div>
    </div>

    <?php if ($pdoSuccess): ?>
        <h3 style="color: #334155; margin-bottom: 10px;">Verified Database Tables & Seed Counts:</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #2b4c7e; color: #ffffff;">
                    <th style="padding: 10px; border: 1px solid #cbd5e1;">Table Name</th>
                    <th style="padding: 10px; border: 1px solid #cbd5e1;">Description / Scope</th>
                    <th style="padding: 10px; border: 1px solid #cbd5e1;">Record Count</th>
                    <th style="padding: 10px; border: 1px solid #cbd5e1;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;"><code>students</code></td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;">Enrolled student records & courses</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; font-weight: bold;"><?php echo $counts['students']; ?> Rows</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; color: #166534; font-weight: bold;">Verified</td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;"><code>events</code></td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;">Campus fests, hackathons & workshops</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; font-weight: bold;"><?php echo $counts['events']; ?> Rows</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; color: #166534; font-weight: bold;">Verified</td>
                </tr>
                <tr>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;"><code>registrations</code></td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;">Student-to-Event participation entries</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; font-weight: bold;"><?php echo $counts['registrations']; ?> Rows</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; color: #166534; font-weight: bold;">Verified</td>
                </tr>
                <tr style="background: #f8fafc;">
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;"><code>users</code></td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1;">Authentication credentials & roles</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; font-weight: bold;"><?php echo $counts['users']; ?> Rows</td>
                    <td style="padding: 8px 10px; border: 1px solid #cbd5e1; color: #166534; font-weight: bold;">Verified</td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top: 25px; text-align: center;">
        <a href="index.html" class="btn" style="background: #2b4c7e; color: #fff; padding: 8px 18px; border-radius: 4px; text-decoration: none; font-weight: bold;">Go to StudentHub Portal</a>
    </div>
</div>

</body>
</html>
