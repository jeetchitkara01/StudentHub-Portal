<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $isLoggedIn ? $_SESSION['role'] : '';
$userName = $isLoggedIn ? $_SESSION['name'] : '';
?>
<header>
    <div class="container header-top">
        <div>
            <h1>StudentHub Portal</h1>
            <p style="margin: 2px 0 0 0; font-size: 13px; opacity: 0.9;">Academic & Campus Activity Hub</p>
        </div>
        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <span class="user-badge"><?php echo htmlspecialchars($userName); ?> (<?php echo ucfirst($userRole); ?>)</span>
            <?php endif; ?>
            <button id="themeToggleBtn" class="theme-toggle-btn" type="button">Dark Mode</button>
            <button id="hamburgerBtn" class="hamburger-btn" type="button" aria-label="Toggle navigation">☰ Menu</button>
        </div>
    </div>
</header>
<nav id="mainNav">
    <div class="container nav-links">
        <?php if (!$isLoggedIn): ?>
            <a href="index.html">Home</a>
            <a href="about.html">About</a>
            <a href="events.html">Events</a>
            <a href="directory.html">Directory</a>
            <a href="courses.html">Courses</a>
            <a href="results.html">Results</a>
            <a href="faq.html">FAQ</a>
            <a href="contact.html">Contact</a>
            <a href="register.html">Register</a>
            <a href="login.php" class="nav-btn-highlight">Login</a>
        <?php elseif ($userRole === 'admin'): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="students.php">Manage Students</a>
            <a href="manage_events.php">Manage Events</a>
            <a href="ajax_crud.php">AJAX CRUD</a>
            <a href="view_submissions.php">Submissions</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="nav-btn-highlight">Logout</a>
        <?php else: ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="events.html">Events</a>
            <a href="directory.html">Directory</a>
            <a href="courses.html">Courses</a>
            <a href="results.html">Results</a>
            <a href="profile.php">Profile</a>
            <a href="view_submissions.php">Submissions</a>
            <a href="contact.html">Contact</a>
            <a href="faq.html">FAQ</a>
            <a href="logout.php" class="nav-btn-highlight">Logout</a>
        <?php endif; ?>
    </div>
</nav>
