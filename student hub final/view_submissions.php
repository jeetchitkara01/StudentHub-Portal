<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StudentHub - View Stored Submissions</title>
  <link rel="stylesheet" href="style.css">
  <script src="theme.js"></script>
</head>
<body>

  <header>
    <div class="container">
      <div class="header-top">
        <h1>StudentHub Portal</h1>
        <div class="header-actions">
          <button id="themeToggleBtn" class="theme-toggle-btn" type="button">Dark Mode</button>
        </div>
      </div>
      <nav id="mainNav">
        <a href="index.html">Home</a>
        <a href="register.html">Register</a>
        <a href="view_submissions.php" class="active">View Submissions (CSV/JSON)</a>
        <a href="events.html">Events</a>
        <a href="directory.html">Student Directory</a>
        <a href="contact.html">Contact</a>
      </nav>
    </div>
  </header>

  <main class="container">

    <div class="box">
      <h2>Stored Student Registrations (Read from `data/registrations.json` & `registrations.csv`)</h2>
      <p>This table dynamically reads records saved on the server in <strong>JSON</strong> and <strong>CSV</strong> files by PHP.</p>

      <table>
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Full Name</th>
            <th>Enrollment</th>
            <th>Department</th>
            <th>Semester</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Gender</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $jsonFile = "data/registrations.json";
          if (file_exists($jsonFile)) {
              $data = json_decode(file_get_contents($jsonFile), true);
              if (!empty($data)) {
                  foreach ($data as $row) {
                      echo "<tr>";
                      echo "<td>" . htmlspecialchars($row['timestamp'] ?? '') . "</td>";
                      echo "<td><strong>" . htmlspecialchars($row['name'] ?? '') . "</strong></td>";
                      echo "<td>" . htmlspecialchars($row['enrollment'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($row['department'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($row['semester'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($row['mobile'] ?? '') . "</td>";
                      echo "<td>" . htmlspecialchars($row['gender'] ?? '') . "</td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='8' style='text-align:center;'>No registration records found.</td></tr>";
              }
          } else {
              echo "<tr><td colspan='8' style='text-align:center;'>File data/registrations.json does not exist.</td></tr>";
          }
          ?>
        </tbody>
      </table>

      <a href="register.html" class="btn">Register Another Student</a>
    </div>

  </main>

  <footer>
    <p>Copyright &copy; 2026 StudentHub Portal | Practical 7 PHP File Storage</p>
  </footer>

</body>
</html>
