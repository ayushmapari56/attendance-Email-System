-- ============================================================
-- Seed Data for JD College Attendance System
-- ============================================================
-- All data below is commented out by default.
-- The college admin should import real data via CSV upload
-- (HOD panel → Students / Subjects → Bulk Upload).
--
-- These are reference examples only.
-- Replace placeholder emails with actual college emails before use.
-- ============================================================


-- 1. Insert Faculty (use actual faculty emails provided by college)
-- INSERT INTO faculty (faculty_name, email, department) VALUES
-- ('Faculty Name 1', 'faculty1@college.ac.in', 'CSE'),
-- ('Faculty Name 2', 'faculty2@college.ac.in', 'ME'),
-- ('Faculty Name 3', 'faculty3@college.ac.in', 'EE');


-- 2. Insert Subjects (or use Subjects → Bulk Upload CSV in the portal)
-- INSERT INTO subjects (subject_name, subject_code, department, semester) VALUES
-- ('Computer Networks',   'CS401', 'CSE', 4),
-- ('Operating Systems',   'CS402', 'CSE', 4),
-- ('Database Management', 'CS403', 'CSE', 4),
-- ('Data Structures',     'CS201', 'CSE', 2),
-- ('Machine Design',      'ME601', 'ME',  6),
-- ('Power Electronics',   'EE401', 'EE',  4);


-- 3. Insert Students (or use Students → Bulk Upload CSV in the portal)
-- CSV format: Roll_Number, Student_Name, Email, Parent_Email, Branch, Semester

-- CSE Semester 4
-- INSERT INTO students (roll_number, student_name, email, parent_email, department, semester) VALUES
-- ('CSE22001', 'Student Name 1', 'student1@college.ac.in', 'parent1@email.com', 'CSE', 4),
-- ('CSE22002', 'Student Name 2', 'student2@college.ac.in', 'parent2@email.com', 'CSE', 4);

-- ME Semester 6
-- INSERT INTO students (roll_number, student_name, email, parent_email, department, semester) VALUES
-- ('ME21001', 'Student Name 3', 'student3@college.ac.in', 'parent3@email.com', 'ME', 6);

-- EE Semester 4
-- INSERT INTO students (roll_number, student_name, email, parent_email, department, semester) VALUES
-- ('EE22001', 'Student Name 4', 'student4@college.ac.in', 'parent4@email.com', 'EE', 4);
