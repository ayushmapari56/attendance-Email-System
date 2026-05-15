<?php
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/AttendanceProcessor.php';
require_once __DIR__ . '/../../src/EmailSender.php';
require_once __DIR__ . '/../../src/Logger.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Set execution limits for scale (3000+ students)
set_time_limit(0);
ini_set('memory_limit', '512M');

$logger = new Logger();
$date = date('Y-m-d');

try {
    $processor = new AttendanceProcessor();
    $emailSender = new EmailSender();

    $studentsData = $processor->getDailyAttendance($date);

    if (empty($studentsData)) {
        echo json_encode(['success' => true, 'message' => "No attendance records found for today ({$date}).", 'sent' => 0]);
        exit;
    }

    $count = 0;
    $failed = 0;

    // We will use the same logic as the cron script
    foreach ($studentsData as $studentId => $data) {
        // PRODUCTION: Use real student email
        $studentEmail = trim($data['email'] ?? '');
        if (empty($studentEmail)) {
            $failed++;
            continue; // Skip students with no email on record
        }

        // Send to Student
        $sentStudent = $emailSender->sendAttendanceReport($studentEmail, $data['name'], $data['attendance'], $date);
        if ($sentStudent) {
            $count++;
        } else {
            $failed++;
        }

        // Send to Parent (if parent email exists)
        $parentEmail = trim($data['parent_email'] ?? '');
        if (!empty($parentEmail)) {
            $sentParent = $emailSender->sendAttendanceReport($parentEmail, $data['name'] . ' (Parent Copy)', $data['attendance'], $date);
            if ($sentParent) {
                $count++;
            } else {
                $failed++;
            }
        }

        usleep(150000); // Small delay for SMTP stability
    }

    echo json_encode([
        'success' => true,
        'message' => "Emails processed successfully.",
        'sent' => $count,
        'failed' => $failed
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>