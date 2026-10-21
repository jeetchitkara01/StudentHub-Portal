<?php
header('Content-Type: application/json; charset=utf-8');
require_once "db.php";

$method = $_SERVER['REQUEST_METHOD'];

function jsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'get') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $event = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($event) {
            jsonResponse(true, "Event retrieved", $event);
        } else {
            jsonResponse(false, "Event not found", null, 404);
        }
    } else {
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');
        
        $sql = "SELECT * FROM events WHERE 1=1";
        if (!empty($search)) {
            $safeSearch = mysqli_real_escape_string($conn, $search);
            $sql .= " AND (title LIKE '%$safeSearch%' OR location LIKE '%$safeSearch%' OR description LIKE '%$safeSearch%')";
        }
        if (!empty($category)) {
            $safeCat = mysqli_real_escape_string($conn, $category);
            $sql .= " AND category = '$safeCat'";
        }
        $sql .= " ORDER BY event_date ASC";

        $res = mysqli_query($conn, $sql);
        $events = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $events[] = $row;
        }
        jsonResponse(true, "Events fetched successfully", $events);
    }
}

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);

    $action = $_POST['action'] ?? ($jsonData['action'] ?? '');
    
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? ($jsonData['title'] ?? ''));
        $category = trim($_POST['category'] ?? ($jsonData['category'] ?? 'Technical'));
        $eventDate = trim($_POST['event_date'] ?? ($jsonData['event_date'] ?? ''));
        $location = trim($_POST['location'] ?? ($jsonData['location'] ?? ''));
        $status = trim($_POST['status'] ?? ($jsonData['status'] ?? 'open'));
        $description = trim($_POST['description'] ?? ($jsonData['description'] ?? ''));

        if (empty($title) || empty($eventDate) || empty($location)) {
            jsonResponse(false, "Title, event date, and location are required fields.", null, 400);
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO events (title, category, event_date, location, status, description) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $title, $category, $eventDate, $location, $status, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            jsonResponse(true, "Event created successfully.", ['id' => $newId, 'title' => $title], 201);
        } else {
            jsonResponse(false, "Failed to create event: " . mysqli_error($conn), null, 500);
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? ($jsonData['id'] ?? 0));
        $title = trim($_POST['title'] ?? ($jsonData['title'] ?? ''));
        $category = trim($_POST['category'] ?? ($jsonData['category'] ?? 'Technical'));
        $eventDate = trim($_POST['event_date'] ?? ($jsonData['event_date'] ?? ''));
        $location = trim($_POST['location'] ?? ($jsonData['location'] ?? ''));
        $status = trim($_POST['status'] ?? ($jsonData['status'] ?? 'open'));
        $description = trim($_POST['description'] ?? ($jsonData['description'] ?? ''));

        if ($id <= 0 || empty($title) || empty($eventDate)) {
            jsonResponse(false, "Invalid ID, title, or date.", null, 400);
        }

        $stmt = mysqli_prepare($conn, "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, status = ?, description = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssssssi", $title, $category, $eventDate, $location, $status, $description, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            jsonResponse(true, "Event updated successfully.");
        } else {
            jsonResponse(false, "Failed to update event.", null, 500);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? ($jsonData['id'] ?? 0));
        if ($id <= 0) {
            jsonResponse(false, "Invalid event ID for deletion.", null, 400);
        }

        $stmt = mysqli_prepare($conn, "DELETE FROM events WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            jsonResponse(true, "Event deleted successfully.");
        } else {
            jsonResponse(false, "Failed to delete event.", null, 500);
        }
    } else {
        jsonResponse(false, "Unknown API action requested.", null, 400);
    }
}
?>
