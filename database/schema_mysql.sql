-- Database Schema for Attendance Email System & Teacher Portal (MySQL Version)

-- Users table (for Teachers/Admins)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    role VARCHAR(20) DEFAULT 'teacher', -- 'admin', 'teacher', 'principal'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Students table
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    parent_email VARCHAR(100) NULL,
    department VARCHAR(50),
    semester INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Faculty/Teachers table (linked to users, or standalone if needed for emails)
CREATE TABLE IF NOT EXISTS faculty (
    faculty_id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    department VARCHAR(50)
);

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    department VARCHAR(50),
    semester INTEGER,
    units INT DEFAULT 1,
    teacher_name VARCHAR(100) NULL
);

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject_id INT,
    faculty_id INT,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Leave'),
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(student_id, subject_id, attendance_date),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
);

-- Holidays table (used by automation to skip emails on holidays/Sundays)
CREATE TABLE IF NOT EXISTS holidays (
    holiday_id INT AUTO_INCREMENT PRIMARY KEY,
    holiday_date DATE NOT NULL UNIQUE,
    holiday_name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data | Common password for ALL users: 'admin123'
INSERT INTO users (username, password_hash, email, full_name, role) VALUES 
('admin', '$2y$10$IPOLb9kplArksn9Km0/0vO1vB9Ut/zvhboKnEcRWMvxSBnr1TuoeK', 'admin@college.edu', 'System Admin', 'admin'),
('principal', '$2y$10$IPOLb9kplArksn9Km0/0vO1vB9Ut/zvhboKnEcRWMvxSBnr1TuoeK', 'principal@college.edu', 'College Principal', 'principal'),
('hod', '$2y$10$IPOLb9kplArksn9Km0/0vO1vB9Ut/zvhboKnEcRWMvxSBnr1TuoeK', 'hod@college.edu', 'Head of Department', 'hod');
