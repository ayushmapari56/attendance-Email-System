<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireFacultyOrHod(); // Both Faculty and HOD can see student list

$semester = $_GET['semester'] ?? null;
$department = $_GET['branch'] ?? null;

if (!$semester || !$department) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Semester and Branch are required']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT student_id, student_name, roll_number FROM students WHERE semester = :semester AND department = :branch ORDER BY roll_number ASC");
    $stmt->execute(['semester' => $semester, 'branch' => $department]);
    $students = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $students]);
} catch (Exception $e) {
    error_log('get_attendance_students error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load students. Please try again.']);
}
?>