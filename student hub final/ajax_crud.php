<?php
require_once "auth_check.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - AJAX / Fetch API Live CRUD</title>
    <link rel="stylesheet" href="style.css">
    <script src="theme.js" defer></script>
</head>
<body>

<?php include "navbar.php"; ?>

<main class="container">
    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>Asynchronous Live CRUD (Fetch API & JSON)</h2>
                <p>Perform List, Add, Update, and Delete operations dynamically without full page reloads.</p>
            </div>
            <div>
                <button type="button" id="btnShowAddModal" class="btn" style="background: #059669;">+ Add Event (AJAX)</button>
            </div>
        </div>
    </div>

    <div id="ajaxAlert" class="alert" style="display: none; margin-top: 15px;"></div>

    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;">
                <label for="ajaxSearchInput">Live Search (Debounced)</label>
                <input type="text" id="ajaxSearchInput" placeholder="Search events in real-time...">
            </div>
            <div style="flex: 1; min-width: 150px;">
                <label for="ajaxCatFilter">Category Filter</label>
                <select id="ajaxCatFilter">
                    <option value="">All Categories</option>
                    <option value="Technical">Technical</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Sports">Sports</option>
                    <option value="Workshop">Workshop</option>
                </select>
            </div>
            <div>
                <button type="button" id="btnRefreshList" class="btn" style="background: #475569;">Refresh</button>
            </div>
        </div>
    </div>

    <div class="box" style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h3>Live Events Data Stream</h3>
            <span id="ajaxLoadingSpinner" style="display: none; color: #2563eb; font-weight: bold; font-size: 13px;">Loading data...</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ajaxEventsTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 20px;">Fetching records from PHP JSON API...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box" id="ajaxFormContainer" style="display: none; margin-top: 25px; max-width: 650px;">
        <h3 id="ajaxFormTitle">Add New Event (AJAX)</h3>
        
        <form id="ajaxEventForm">
            <input type="hidden" id="event_id" name="id" value="">
            <input type="hidden" id="form_action" name="action" value="create">

            <div class="form-group">
                <label for="event_title">Event Title</label>
                <input type="text" id="event_title" name="title" required placeholder="e.g. AI Hackathon 2026">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="event_category">Category</label>
                    <select id="event_category" name="category">
                        <option value="Technical">Technical</option>
                        <option value="Cultural">Cultural</option>
                        <option value="Sports">Sports</option>
                        <option value="Workshop">Workshop</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="event_location">Location / Venue</label>
                    <input type="text" id="event_location" name="location" required placeholder="e.g. Auditorium A">
                </div>
                <div class="form-group">
                    <label for="event_status">Status</label>
                    <select id="event_status" name="status">
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="event_desc">Description</label>
                <textarea id="event_desc" name="description" rows="2" placeholder="Brief event details"></textarea>
            </div>

            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="submit" id="btnSubmitAjax" class="btn">Save Event via Fetch</button>
                <button type="button" id="btnCancelAjax" class="btn" style="background: #64748b;">Cancel</button>
            </div>
        </form>
    </div>
</main>

<footer>
    <div class="container footer-content">
        <p>StudentHub Portal &copy; 2026. All rights reserved.</p>
    </div>
</footer>

<script>
let debounceTimer;

function showAlert(message, isSuccess) {
    const alertBox = document.getElementById("ajaxAlert");
    alertBox.className = "alert " + (isSuccess ? "alert-success" : "alert-error");
    alertBox.textContent = message;
    alertBox.style.display = "block";
    setTimeout(() => {
        alertBox.style.display = "none";
    }, 4000);
}

function loadEvents() {
    const spinner = document.getElementById("ajaxLoadingSpinner");
    const tbody = document.getElementById("ajaxEventsTableBody");
    const searchVal = document.getElementById("ajaxSearchInput").value;
    const catVal = document.getElementById("ajaxCatFilter").value;

    spinner.style.display = "inline";

    const url = "api_events.php?search=" + encodeURIComponent(searchVal) + "&category=" + encodeURIComponent(catVal);

    fetch(url)
        .then(response => response.json())
        .then(res => {
            spinner.style.display = "none";
            if (res.success && res.data) {
                renderTable(res.data);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:15px;">Failed to load events.</td></tr>';
            }
        })
        .catch(err => {
            spinner.style.display = "none";
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:15px;">Network or API Error.</td></tr>';
        });
}

function renderTable(events) {
    const tbody = document.getElementById("ajaxEventsTableBody");
    tbody.innerHTML = "";

    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#64748b;padding:20px;">No matching campus events found.</td></tr>';
        return;
    }

    events.forEach(ev => {
        const tr = document.createElement("tr");
        const statusBadge = ev.status === 'open' ? '<span class="badge badge-success">Open</span>' : '<span class="badge badge-danger">Closed</span>';
        
        tr.innerHTML = `
            <td>${ev.id}</td>
            <td><strong>${escapeHtml(ev.title)}</strong></td>
            <td><span class="badge badge-info">${escapeHtml(ev.category)}</span></td>
            <td>${escapeHtml(ev.event_date)}</td>
            <td>${escapeHtml(ev.location)}</td>
            <td>${statusBadge}</td>
            <td>
                <div style="display:flex; gap:5px;">
                    <button class="btn-sm btn-edit" style="background:#2563eb; color:#fff; border:none; padding:4px 8px; border-radius:3px; cursor:pointer;" data-id="${ev.id}">Edit</button>
                    <button class="btn-sm btn-del" style="background:#ef4444; color:#fff; border:none; padding:4px 8px; border-radius:3px; cursor:pointer;" data-id="${ev.id}">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    attachRowEvents();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function attachRowEvents() {
    document.querySelectorAll(".btn-del").forEach(btn => {
        btn.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete event ID " + id + " via AJAX?")) {
                deleteEvent(id);
            }
        });
    });

    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function() {
            const id = this.getAttribute("data-id");
            loadEventForEdit(id);
        });
    });
}

function deleteEvent(id) {
    fetch("api_events.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "delete", id: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            showAlert("Event deleted successfully via Fetch API.", true);
            loadEvents();
        } else {
            showAlert(res.message || "Failed to delete event.", false);
        }
    })
    .catch(() => showAlert("Network error while deleting event.", false));
}

function loadEventForEdit(id) {
    fetch("api_events.php?action=get&id=" + id)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const ev = res.data;
                document.getElementById("event_id").value = ev.id;
                document.getElementById("form_action").value = "update";
                document.getElementById("event_title").value = ev.title;
                document.getElementById("event_category").value = ev.category;
                document.getElementById("event_date").value = ev.event_date;
                document.getElementById("event_location").value = ev.location;
                document.getElementById("event_status").value = ev.status;
                document.getElementById("event_desc").value = ev.description || "";

                document.getElementById("ajaxFormTitle").textContent = "Edit Event ID " + ev.id + " (AJAX)";
                document.getElementById("btnSubmitAjax").textContent = "Update via Fetch";
                document.getElementById("ajaxFormContainer").style.display = "block";
                document.getElementById("ajaxFormContainer").scrollIntoView({ behavior: 'smooth' });
            }
        });
}

document.addEventListener("DOMContentLoaded", function() {
    loadEvents();

    document.getElementById("btnRefreshList").addEventListener("click", loadEvents);
    document.getElementById("ajaxCatFilter").addEventListener("change", loadEvents);

    document.getElementById("ajaxSearchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadEvents, 300);
    });

    document.getElementById("btnShowAddModal").addEventListener("click", function() {
        document.getElementById("ajaxEventForm").reset();
        document.getElementById("event_id").value = "";
        document.getElementById("form_action").value = "create";
        document.getElementById("ajaxFormTitle").textContent = "Add New Event (AJAX)";
        document.getElementById("btnSubmitAjax").textContent = "Save Event via Fetch";
        document.getElementById("ajaxFormContainer").style.display = "block";
        document.getElementById("ajaxFormContainer").scrollIntoView({ behavior: 'smooth' });
    });

    document.getElementById("btnCancelAjax").addEventListener("click", function() {
        document.getElementById("ajaxFormContainer").style.display = "none";
    });

    document.getElementById("ajaxEventForm").addEventListener("submit", function(e) {
        e.preventDefault();
        
        const action = document.getElementById("form_action").value;
        const payload = {
            action: action,
            id: document.getElementById("event_id").value,
            title: document.getElementById("event_title").value,
            category: document.getElementById("event_category").value,
            event_date: document.getElementById("event_date").value,
            location: document.getElementById("event_location").value,
            status: document.getElementById("event_status").value,
            description: document.getElementById("event_desc").value
        };

        fetch("api_events.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showAlert(res.message || "Event saved successfully!", true);
                document.getElementById("ajaxFormContainer").style.display = "none";
                document.getElementById("ajaxEventForm").reset();
                loadEvents();
            } else {
                showAlert(res.message || "Error saving event.", false);
            }
        })
        .catch(() => showAlert("Network error occurred during API call.", false));
    });
});
</script>

</body>
</html>
