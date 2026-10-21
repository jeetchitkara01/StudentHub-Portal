# StudentHub Portal - Complete Full-Stack Web Development Project

StudentHub is a responsive full-stack university web application covering Practicals 1 through 15.

---

## 1. Practical Mapping (Practicals 1 to 15)

- **Practical 1 & 2:** Semantic HTML5 layout, accessibility tags, 10+ connected pages.
- **Practical 3:** Responsive CSS3, CSS Grid (2D) & Flexbox (1D), mobile-first media queries.
- **Practical 4:** JavaScript DOM manipulation, FAQ accordion, announcement modal, dismissible banner, hamburger menu, light/dark theme toggle with `localStorage`.
- **Practical 5:** Registration form with frontend Regular Expression (RegEx) validations, real-time error messages, Password Strength Meter, and HTML5 Canvas CAPTCHA.
- **Practical 6:** Rendering external JSON datasets (`data/events.json`, `data/students.json`) using Fetch API (`async/await`), real-time search, category filter, sorting, and pagination.
- **Practical 7:** PHP form processing (`$_POST`), input sanitization (`htmlspecialchars`), server validation, dual CSV & JSON storage (`data/registrations.csv`, `data/registrations.json`).
- **Practical 8:** MySQL relational database schema design (`database.sql`), primary/foreign keys, and `db.php` connection.
- **Practical 9:** Secure registration with duplicate email check, password hashing (`password_hash()`), and MySQL insert.
- **Practical 10:** Secure authentication with PHP sessions, `password_verify()`, `session_regenerate_id()`, 15-minute inactivity timeout, Remember-Me token, and role-based access control (RBAC).
- **Practical 11:** Student Management CRUD (`students.php`) with search, course/status filter, edit/delete modal, delete confirmation, status toggle, and CSV export (`export_students.php`).
- **Practical 12:** Event Management CRUD (`manage_events.php`) with validated poster file uploads (JPG/PNG, <2MB) stored in `uploads/` directory.
- **Practical 13:** PHP JSON REST API (`api_events.php`) and asynchronous AJAX/Fetch API Live CRUD (`ajax_crud.php`) without page reload.
- **Practical 14:** Admin Control Center (`admin_dashboard.php`) with dynamic SQL aggregation metrics (`COUNT(*)`), recent registrations, system audit logs (`audit_logs` table), and user status moderation.
- **Practical 15:** Full-stack integration, complete documentation, SQL dump, and local deployment guide.

---

## 2. Local Setup & Deployment Guide (XAMPP)

1. **Copy Project to XAMPP htdocs:**
   - Copy the `student-hub-final` folder into your XAMPP web root directory:
     `C:\xampp\htdocs\student-hub-final`

2. **Start Services:**
   - Open **XAMPP Control Panel**.
   - Start **Apache** and **MySQL**.

3. **Import Database:**
   - Open your browser and go to `http://localhost/phpmyadmin/`.
   - Click **New** and create a database named `studenthub`.
   - Click the **Import** tab.
   - Choose `database.sql` from the project folder and click **Import**.

4. **Run the Application:**
   - Open your browser and navigate to:
     `http://localhost/student-hub-final/`

---

## 3. Default Login Credentials

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Admin** | `admin@studenthub.edu` | `Admin@123` | Full Administrative CRUD, Logs, Analytics |
| **Student** | `student@studenthub.edu` | `Student@123` | Student Dashboard, Profile, Event Browsing |

---

## 4. Key Project Files

- `database.sql`: Full SQL schema and seed data (20+ students, events, users, audit logs).
- `db.php`: MySQL database connection script.
- `auth_check.php`: Session authentication, RBAC, and timeout validator.
- `audit.php`: Centralized activity logger.
- `navbar.php`: Dynamic role-based header navigation.
- `login.php`: Secure login handler with session regeneration and remember-me.
- `logout.php`: Session destruction and cookie cleaner.
- `students.php`: Student CRUD with search, filter, and delete confirmation.
- `export_students.php`: Streams MySQL student records into downloadable CSV.
- `manage_events.php`: Event CRUD with validated poster uploads.
- `api_events.php`: RESTful JSON API endpoint.
- `ajax_crud.php`: Live Fetch API CRUD interface without page refresh.
- `admin_dashboard.php`: Admin analytics, moderation, and audit logs.
- `dashboard.php`: Student dashboard.
- `profile.php`: Student/User profile editor.
- `view_submissions.php`: Practical 7 CSV/JSON viewer.
- `style.css`: Unified responsive stylesheet with light/dark themes.
- `theme.js`: Theme switcher logic.
- `app.js`: Interactive UI components, Fetch API, and validation logic.
