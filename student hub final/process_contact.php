<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function clean($d) { return htmlspecialchars(stripslashes(trim($d))); }

    $name = clean($_POST["cName"] ?? "");
    $email = clean($_POST["cEmail"] ?? "");
    $subject = clean($_POST["cSubject"] ?? "");
    $message = clean($_POST["cMessage"] ?? "");

    if (!empty($name) && !empty($email) && !empty($message)) {
        $timestamp = date("Y-m-d H:i:s");
        $file = fopen("data/contacts.csv", "a");
        if ($file) {
            fputcsv($file, [$timestamp, $name, $email, $subject, $message]);
            fclose($file);
        }
        $msg = "Thank you $name! Your message has been recorded.";
    } else {
        $msg = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Status</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container" style="margin-top:50px;">
    <div class="box" style="max-width:500px; margin:auto; text-align:center;">
      <h2>Message Status</h2>
      <p><?php echo $msg; ?></p>
      <a href="contact.html" class="btn">Back to Contact Page</a>
    </div>
  </div>
</body>
</html>
