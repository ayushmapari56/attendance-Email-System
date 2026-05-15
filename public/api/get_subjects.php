<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireFacultyOrHod(); // Both Faculty and HOD can see subjects

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT subject_id, subject_name, subject_code, department, semester FROM subjects ORDER BY subject_name ASC");
    $subjects = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $subjects]);
} catch (Exception $e) {
    error_log('get_subjects error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load subjects. Please try again.']);
}
?>