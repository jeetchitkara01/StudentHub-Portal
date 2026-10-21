CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    remember_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year VARCHAR(20) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(150) NOT NULL,
    poster VARCHAR(255) NULL,
    status ENUM('open', 'closed') DEFAULT 'open',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100) NOT NULL,
    event_title VARCHAR(150) NOT NULL,
    course VARCHAR(50) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (id, name, email, password, role, status) VALUES
(1, 'System Administrator', 'admin@studenthub.edu', '$2y$10$DVwm6I2QGi4j7Zwi0A2mXeey2Tqewb6pHBfM95qsykm28c0ookU3i', 'admin', 'active'),
(2, 'Aarav Patel', 'student@studenthub.edu', '$2y$10$csvEOL3rhK0ye9n2y/W3C.84jFTo5k0a/iymaQprAolCCWaHb4j7G', 'student', 'active'),
(3, 'Diya Sharma', 'diya.sharma@studenthub.edu', '$2y$10$csvEOL3rhK0ye9n2y/W3C.84jFTo5k0a/iymaQprAolCCWaHb4j7G', 'student', 'active'),
(4, 'Rohan Mehta', 'rohan.mehta@studenthub.edu', '$2y$10$csvEOL3rhK0ye9n2y/W3C.84jFTo5k0a/iymaQprAolCCWaHb4j7G', 'student', 'active')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO students (id, user_id, name, email, phone, gender, course, year, status) VALUES
(1, 2, 'Aarav Patel', 'student@studenthub.edu', '9876543210', 'Male', 'B.Tech IT', '3rd Year', 'active'),
(2, 3, 'Diya Sharma', 'diya.sharma@studenthub.edu', '9876543211', 'Female', 'B.Tech CE', '2nd Year', 'active'),
(3, 4, 'Rohan Mehta', 'rohan.mehta@studenthub.edu', '9876543212', 'Male', 'B.Tech CS', '4th Year', 'active'),
(4, NULL, 'Ananya Iyer', 'ananya.iyer@studenthub.edu', '9876543213', 'Female', 'B.Tech EC', '1st Year', 'active'),
(5, NULL, 'Kabir Verma', 'kabir.verma@studenthub.edu', '9876543214', 'Male', 'B.Tech ME', '3rd Year', 'active'),
(6, NULL, 'Pooja Joshi', 'pooja.joshi@studenthub.edu', '9876543215', 'Female', 'B.Tech IT', '2nd Year', 'active'),
(7, NULL, 'Siddharth Rao', 'siddharth.rao@studenthub.edu', '9876543216', 'Male', 'B.Tech CE', '4th Year', 'active'),
(8, NULL, 'Neha Gupta', 'neha.gupta@studenthub.edu', '9876543217', 'Female', 'B.Tech CS', '3rd Year', 'active'),
(9, NULL, 'Vikram Singh', 'vikram.singh@studenthub.edu', '9876543218', 'Male', 'B.Tech EE', '2nd Year', 'active'),
(10, NULL, 'Tanvi Kulkarni', 'tanvi.kulkarni@studenthub.edu', '9876543219', 'Female', 'B.Tech IT', '1st Year', 'active'),
(11, NULL, 'Aditya Desai', 'aditya.desai@studenthub.edu', '9876543220', 'Male', 'B.Tech CE', '3rd Year', 'active'),
(12, NULL, 'Rhea Sen', 'rhea.sen@studenthub.edu', '9876543221', 'Female', 'B.Tech CS', '2nd Year', 'active'),
(13, NULL, 'Yash Trivedi', 'yash.trivedi@studenthub.edu', '9876543222', 'Male', 'B.Tech IT', '4th Year', 'active'),
(14, NULL, 'Ishita Nair', 'ishita.nair@studenthub.edu', '9876543223', 'Female', 'B.Tech EC', '3rd Year', 'active'),
(15, NULL, 'Kunal Shah', 'kunal.shah@studenthub.edu', '9876543224', 'Male', 'B.Tech ME', '2nd Year', 'active'),
(16, NULL, 'Meera Pillai', 'meera.pillai@studenthub.edu', '9876543225', 'Female', 'B.Tech CE', '1st Year', 'active'),
(17, NULL, 'Devansh Dave', 'devansh.dave@studenthub.edu', '9876543226', 'Male', 'B.Tech CS', '3rd Year', 'active'),
(18, NULL, 'Sneha Roy', 'sneha.roy@studenthub.edu', '9876543227', 'Female', 'B.Tech IT', '4th Year', 'active'),
(19, NULL, 'Manish Bhatia', 'manish.bhatia@studenthub.edu', '9876543228', 'Male', 'B.Tech EE', '2nd Year', 'active'),
(20, NULL, 'Priya Saxena', 'priya.saxena@studenthub.edu', '9876543229', 'Female', 'B.Tech CE', '3rd Year', 'active')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO events (id, title, category, event_date, location, poster, status, description) VALUES
(1, 'Annual Tech Symposium 2026', 'Technical', '2026-11-15', 'Main Auditorium', 'tech_symposium.jpg', 'open', 'Flagship university technical conference and paper presentations.'),
(2, 'Inter-College Hackathon 24H', 'Technical', '2026-11-20', 'IT Building Lab 4', 'hackathon.jpg', 'open', '24-hour continuous coding hackathon with industry mentors.'),
(3, 'Robotics Championship', 'Technical', '2026-11-28', 'Mechanical Workshop', 'robotics.jpg', 'open', 'Line follower and battle bot competition for engineering students.'),
(4, 'Cultural Night & Tarang', 'Cultural', '2026-12-05', 'Open Amphitheatre', 'cultural.jpg', 'open', 'Annual cultural festival with dance, drama, and music performances.'),
(5, 'AI & Deep Learning Workshop', 'Workshop', '2026-12-10', 'Seminar Hall 2', 'ai_workshop.jpg', 'open', 'Hands-on training session on Neural Networks and Computer Vision.'),
(6, 'Annual Sports Meet 2026', 'Sports', '2026-12-15', 'University Sports Ground', 'sports.jpg', 'open', 'Track and field events, cricket, football, and badminton tournaments.'),
(7, 'Web Development BootCamp', 'Workshop', '2026-10-10', 'Computer Center', 'web_dev.jpg', 'closed', 'Weekend full-stack web development crash course.'),
(8, 'Campus Placement Preparation', 'Technical', '2026-10-18', 'Auditorium B', 'placement.jpg', 'closed', 'Mock interview rounds and aptitude training by alumni.')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO registrations (id, student_name, student_email, event_title, course, registered_at) VALUES
(1, 'Aarav Patel', 'student@studenthub.edu', 'Annual Tech Symposium 2026', 'B.Tech IT', '2026-10-01 10:30:00'),
(2, 'Diya Sharma', 'diya.sharma@studenthub.edu', 'Inter-College Hackathon 24H', 'B.Tech CE', '2026-10-02 11:15:00'),
(3, 'Rohan Mehta', 'rohan.mehta@studenthub.edu', 'Robotics Championship', 'B.Tech CS', '2026-10-03 14:00:00'),
(4, 'Pooja Joshi', 'pooja.joshi@studenthub.edu', 'Cultural Night & Tarang', 'B.Tech IT', '2026-10-04 09:45:00'),
(5, 'Siddharth Rao', 'siddharth.rao@studenthub.edu', 'AI & Deep Learning Workshop', 'B.Tech CE', '2026-10-05 16:20:00')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO audit_logs (id, user_id, user_name, action, details, log_time) VALUES
(1, 1, 'System Administrator', 'System Initialized', 'Database tables created and seeded.', '2026-10-01 09:00:00'),
(2, 1, 'System Administrator', 'Event Added', 'Added Annual Tech Symposium 2026.', '2026-10-01 09:30:00'),
(3, 2, 'Aarav Patel', 'User Login', 'Successful student login.', '2026-10-01 10:00:00'),
(4, 2, 'Aarav Patel', 'Event Registration', 'Registered for Annual Tech Symposium 2026.', '2026-10-01 10:30:00')
ON DUPLICATE KEY UPDATE id=id;
