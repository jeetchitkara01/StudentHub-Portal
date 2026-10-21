<?php
require_once "auth_check.php";
checkAdmin();
require_once "db.php";
require_once "audit.php";

$msg = "";
$err = "";
$uploadDir = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function handlePosterUpload($file, &$errorMsg) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = "File upload error occurred.";
        return false;
    }
    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $errorMsg = "File size exceeds 2MB limit.";
        return false;
    }
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/pjpeg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        $errorMsg = "Invalid file type. Only JPG, PNG, and WebP images are allowed.";
        return false;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = "event_" . time() . "_" . bin2hex(random_bytes(4)) . "." . strtolower($ext);
    $targetPath = "uploads/" . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newFileName;
    } else {
        $errorMsg = "Failed to save uploaded file.";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Technical');
        $eventDate = trim($_POST['event_date'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = trim($_POST['status'] ?? 'open');
        $description = trim($_POST['description'] ?? '');

        if (empty($title) || empty($eventDate) || empty($location)) {
            $err = "Title, date, and location are required.";
        } else {
            $posterFileName = null;
            if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadRes = handlePosterUpload($_FILES['poster'], $err);
                if ($uploadRes === false) {
                    $posterFileName = null;
                } else {
                    $posterFileName = $uploadRes;
                }
            }

            if (empty($err)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO events (title, category, event_date, location, poster, status, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssssss", $title, $category, $eventDate, $location, $posterFileName, $status, $description);
                if (mysqli_stmt_execute($stmt)) {
                    logAction($conn, "Add Event", "Created event " . $title);
                    $msg = "Event created successfully.";
                } else {
                    $err = "Failed to create event: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    } elseif ($_POST['action'] === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Technical');
        $eventDate = trim($_POST['event_date'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = trim($_POST['status'] ?? 'open');
        $description = trim($_POST['description'] ?? '');

        if ($id > 0 && !empty($title) && !empty($eventDate)) {
            $posterFileName = null;
            $hasNewPoster = false;

            if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadRes = handlePosterUpload($_FILES['poster'], $err);
                if ($uploadRes !== false) {
                    $posterFileName = $uploadRes;
                    $hasNewPoster = true;
                }
            }

            if (empty($err)) {
                if ($hasNewPoster) {
                    $stmt = mysqli_prepare($conn, "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, poster = ?, status = ?, description = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "sssssssi", $title, $category, $eventDate, $location, $posterFileName, $status, $description, $id);
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, status = ?, description = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt, "ssssssi", $title, $category, $eventDate, $location, $status, $description, $id);
                }
                if (mysqli_stmt_execute($stmt)) {
                    logAction($conn, "Edit Event", "Updated event ID " . $id . " (" . $title . ")");
                    $msg = "Event updated successfully.";
                } else {
                    $err = "Failed to update event: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $evQuery = mysqli_query($conn, "SELECT poster, title FROM events WHERE id = $id LIMIT 1");
        if ($evRow = mysqli_fetch_assoc($evQuery)) {
            if (!empty($evRow['poster']) && file_exists("uploads/" . $evRow['poster'])) {
                unlink("uploads/" . $evRow['poster']);
            }
            mysqli_query($conn, "DELETE FROM events WHERE id = $id");
            logAction($conn, "Delete Event", "Deleted event ID " . $id . " (" . $evRow['title'] . ")");
            $msg = "Event deleted successfully.";
        }
    }
}

$catFilter = trim($_GET['cat'] ?? '');
$statFilter = trim($_GET['status'] ?? '');

$evSql = "SELECT * FROM events WHERE 1=1";
if (!empty($catFilter)) {
    $safeCat = mysqli_real_escape_string($conn, $catFilter);
    $evSql .= " AND category = '$safeCat'";
}
if (!empty($statFilter)) {
    $safeStat = mysqli_real_escape_string($conn, $statFilter);
    $evSql .= " AND status = '$safeStat'";
}
$evSql .= " ORDER BY event_date ASC";
$allEvents = mysqli_query($conn, $evSql);

$editEvent = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editRes = mysqli_query($conn, "SELECT * FROM events WHERE id = $editId LIMIT 1");
    $editEvent = mysqli_fetch_assoc($editRes);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Event Management CRUD</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>Event Management & Poster Upload</h2>
                <p>Manage campus events with validated poster uploads (JPG/PNG, &lt;2MB).</p>
            </div>
            <div>
                <a href="#eventFormBox" class="btn" style="background: #2563eb;">+ Create New Event</a>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success" style="margin-top: 15px;"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($err)): ?>
        <div class="alert alert-error" style="margin-top: 15px;"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <div class="box" style="margin-top: 20px;">
        <form method="GET" action="manage_events.php" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 150px;">
                <label for="cat">Category</label>
                <select id="cat" name="cat">
                    <option value="">All Categories</option>
                    <option value="Technical" <?php if ($catFilter === 'Technical') echo 'selected'; ?>>Technical</option>
                    <option value="Cultural" <?php if ($catFilter === 'Cultural') echo 'selected'; ?>>Cultural</option>
                    <option value="Sports" <?php if ($catFilter === 'Sports') echo 'selected'; ?>>Sports</option>
                    <option value="Workshop" <?php if ($catFilter === 'Workshop') echo 'selected'; ?>>Workshop</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="open" <?php if ($statFilter === 'open') echo 'selected'; ?>>Open</option>
                    <option value="closed" <?php if ($statFilter === 'closed') echo 'selected'; ?>>Closed</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn">Apply Filter</button>
                <a href="manage_events.php" class="btn" style="background: #64748b;">Reset</a>
            </div>
        </form>
    </div>

    <div class="box" style="margin-top: 20px;">
        <h3>Published Campus Events</h3>
        <div style="overflow-x: auto; margin-top: 10px;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($allEvents) > 0): ?>
                        <?php while ($ev = mysqli_fetch_assoc($allEvents)): ?>
                            <tr>
                                <td style="width: 70px;">
                                    <?php if (!empty($ev['poster']) && file_exists("uploads/" . $ev['poster'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($ev['poster']); ?>" alt="Poster" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #cbd5e1; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #475569;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($ev['title']); ?></strong></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($ev['category']); ?></span></td>
                                <td><?php echo htmlspecialchars($ev['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($ev['location']); ?></td>
                                <td>
                                    <span class="badge <?php echo $ev['status'] === 'open' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo ucfirst($ev['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="manage_events.php?edit_id=<?php echo $ev['id']; ?>#eventFormBox" class="btn-sm" style="background: #2563eb; color: #fff; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px;">Edit</a>
                                        <a href="manage_events.php?action=delete&id=<?php echo $ev['id']; ?>" onclick="return confirm('Delete this event?');" class="btn-sm" style="background: #ef4444; color: #fff; padding: 4px 8px; border-radius: 3px; text-decoration: none; font-size: 12px;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">No events found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box" id="eventFormBox" style="margin-top: 25px; max-width: 700px;">
        <h3><?php echo $editEvent ? "Edit Event (ID: " . $editEvent['id'] . ")" : "Create New Campus Event"; ?></h3>
        
        <form method="POST" action="manage_events.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $editEvent ? 'edit' : 'add'; ?>">
            <?php if ($editEvent): ?>
                <input type="hidden" name="id" value="<?php echo $editEvent['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="form_title">Event Title</label>
                <input type="text" id="form_title" name="title" required value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="form_cat">Category</label>
                    <select id="form_cat" name="category">
                        <option value="Technical" <?php if (($editEvent['category'] ?? '') === 'Technical') echo 'selected'; ?>>Technical</option>
                        <option value="Cultural" <?php if (($editEvent['category'] ?? '') === 'Cultural') echo 'selected'; ?>>Cultural</option>
                        <option value="Sports" <?php if (($editEvent['category'] ?? '') === 'Sports') echo 'selected'; ?>>Sports</option>
                        <option value="Workshop" <?php if (($editEvent['category'] ?? '') === 'Workshop') echo 'selected'; ?>>Workshop</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="form_date">Event Date</label>
                    <input type="date" id="form_date" name="event_date" required value="<?php echo htmlspecialchars($editEvent['event_date'] ?? ''); ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="form_loc">Location / Venue</label>
                    <input type="text" id="form_loc" name="location" required value="<?php echo htmlspecialchars($editEvent['location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="form_stat">Status</label>
                    <select id="form_stat" name="status">
                        <option value="open" <?php if (($editEvent['status'] ?? '') === 'open') echo 'selected'; ?>>Open for Registration</option>
                        <option value="closed" <?php if (($editEvent['status'] ?? '') === 'closed') echo 'selected'; ?>>Closed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="form_desc">Description</label>
                <textarea id="form_desc" name="description" rows="3"><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="form_poster">Event Poster (Max 2MB, JPG/PNG)</label>
                <input type="file" id="form_poster" name="poster" accept="image/jpeg,image/png,image/webp">
                <?php if (!empty($editEvent['poster'])): ?>
                    <small style="color: #64748b; display: block; margin-top: 4px;">Current file: <?php echo htmlspecialchars($editEvent['poster']); ?></small>
                <?php endif; ?>
            </div>

            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" class="btn"><?php echo $editEvent ? "Update Event" : "Save Event"; ?></button>
                <?php if ($editEvent): ?>
                    <a href="manage_events.php" class="btn" style="background: #64748b;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
