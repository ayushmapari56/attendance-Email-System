<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireFaculty(); // Only Faculty can mark attendance

// CSRF validation — token sent via X-CSRF-Token header from JS
$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['subject_id'], $data['date'], $data['records']) || !is_array($data['records'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    // We assume the teacher's faculty_id is linked to the user. 
    // For now, we'll try to find a faculty record matching the user's name or just use a default/null if not found.
    $user = $auth->getUser();
    $facultyStmt = $db->prepare("SELECT faculty_id FROM faculty WHERE faculty_name = :name LIMIT 1");
    $facultyStmt->execute(['name' => $user['full_name']]);
    $faculty = $facultyStmt->fetch();
    $facultyId = $faculty ? $faculty['faculty_id'] : null;

    $stmt = $db->prepare("
        INSERT INTO attendance (student_id, subject_id, faculty_id, attendance_date, status) 
        VALUES (:student_id, :subject_id, :faculty_id, :date, :status)
        ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = CURRENT_TIMESTAMP
    ");

    foreach ($data['records'] as $studentId => $status) {
        $stmt->execute([
            'student_id' => $studentId,
            'subject_id' => $data['subject_id'],
            'faculty_id' => $facultyId,
            'date' => $data['date'],
            'status' => $status
        ]);
    }

    $db->commit();

    // Log the action for faculty verification
    require_once __DIR__ . '/../../src/Logger.php';
    $logger = new Logger();
    $subjectStmt = $db->prepare("SELECT subject_name FROM subjects WHERE subject_id = :id");
    $subjectStmt->execute(['id' => $data['subject_id']]);
    $subjectName = $subjectStmt->fetchColumn();
    $logger->success("Attendance marked for Subject: " . ($subjectName ?: 'Unknown') . " by " . $user['full_name'] . " [Date: " . $data['date'] . "]");

    echo json_encode(['success' => true, 'message' => 'Attendance marked successfully']);
} catch (Exception $e) {
    if ($db->inTransaction())
        $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>