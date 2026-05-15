<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireHod(); // Only HOD can upload subjects

// CSRF validation — token submitted as a hidden form field
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['subject_csv'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['subject_csv']['tmp_name'];
$handle = fopen($file, "r");

if (!$handle) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to open file']);
    exit;
}

// Skip header row
$header = fgetcsv($handle);

$db = Database::getInstance()->getConnection();
$db->beginTransaction();

$successCount = 0;
$errorCount = 0;

try {
    $stmt = $db->prepare("
        INSERT INTO subjects (subject_name, subject_code, department, semester, units, teacher_name)
        VALUES (:name, :code, :branch, :sem, :units, :teacher)
        ON DUPLICATE KEY UPDATE 
            subject_name = VALUES(subject_name),
            department = VALUES(department),
            semester = VALUES(semester),
            units = VALUES(units),
            teacher_name = VALUES(teacher_name)
    ");

    while (($data = fgetcsv($handle)) !== FALSE) {
        // CSV columns: Subject Code, Subject Name, Teacher, Branch, Semester, Action
        if (count($data) < 5) {
            $errorCount++;
            continue;
        }

        try {
            $subjectCode = trim($data[0]);                                  // Col 0: Subject Code
            $subjectName = trim($data[1]);                                  // Col 1: Subject Name
            $teacherName = trim($data[2]);                                  // Col 2: Teacher
            $branch      = trim($data[3]);                                  // Col 3: Branch
            $semester    = (int) trim($data[4]);                            // Col 4: Semester
            // Auto-detect units: Lab/Practical = 2, otherwise 1
            $units = (stripos($subjectName, 'Lab') !== false || stripos($subjectName, 'Practical') !== false) ? 2 : 1;

            $stmt->execute([
                'name'    => $subjectName,
                'code'    => $subjectCode,
                'branch'  => $branch,
                'sem'     => $semester,
                'units'   => $units,
                'teacher' => $teacherName
            ]);
            $successCount++;
        } catch (Exception $e) {
            $errorCount++;
        }
    }

    $db->commit();
    fclose($handle);

    echo json_encode([
        'success' => true,
        'message' => "Successfully processed subjects",
        'added' => $successCount,
        'errors' => $errorCount
    ]);

} catch (Exception $e) {
    $db->rollBack();
    fclose($handle);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>