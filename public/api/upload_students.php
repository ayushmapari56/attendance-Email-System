<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Database.php';

header('Content-Type: application/json');

$auth = new Auth();
$auth->requireHod(); // Only HOD can upload students

// CSRF validation — token submitted as a hidden form field
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh the page and try again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['student_csv'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['student_csv']['tmp_name'];
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
        INSERT INTO students (roll_number, student_name, email, parent_email, department, semester)
        VALUES (:roll, :name, :email, :parent_email, :branch, :sem)
        ON DUPLICATE KEY UPDATE 
            student_name = VALUES(student_name),
            email = VALUES(email),
            parent_email = VALUES(parent_email),
            department = VALUES(department),
            semester = VALUES(semester)
    ");

    while (($data = fgetcsv($handle)) !== FALSE) {
        // CSV columns: Roll Number, Student Name, Email, Parent Email, Branch, Semester, Status
        if (count($data) < 5) {
            $errorCount++;
            continue;
        }

        try {
            $stmt->execute([
                'roll'         => trim($data[0]),                          // Col 0: Roll Number
                'name'         => trim($data[1]),                          // Col 1: Student Name
                'email'        => trim($data[2]),                          // Col 2: Email
                'parent_email' => !empty(trim($data[3])) ? trim($data[3]) : null, // Col 3: Parent Email
                'branch'       => trim($data[4]),                          // Col 4: Branch
                'sem'          => (int) trim($data[5])                     // Col 5: Semester
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
        'message' => "Successfully processed students",
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