<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/AttendanceProcessor.php';
require_once __DIR__ . '/../src/EmailSender.php';
require_once __DIR__ . '/../src/Logger.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$logger = new Logger();
$date   = date('Y-m-d');
$logger->info("Automation triggered for date: {$date}");

// ─────────────────────────────────────────────
// STEP 1: Skip if today is SUNDAY
// ─────────────────────────────────────────────
$dayOfWeek = date('N'); // 1=Monday ... 7=Sunday
if ($dayOfWeek == 7) {
    $logger->info("Today is Sunday ({$date}). No college. Email skipped.");
    echo "SKIPPED|REASON:Sunday";
    exit;
}

// ─────────────────────────────────────────────
// STEP 2: Skip if today is a declared HOLIDAY
// ─────────────────────────────────────────────
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT holiday_name FROM holidays WHERE holiday_date = :date LIMIT 1");
    $stmt->execute(['date' => $date]);
    $holiday = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($holiday) {
        $holidayName = $holiday['holiday_name'];
        $logger->info("Today is a holiday: '{$holidayName}' ({$date}). Email skipped.");
        echo "SKIPPED|REASON:Holiday|NAME:{$holidayName}";
        exit;
    }
} catch (Exception $e) {
    // If holiday check fails, log a warning but continue — don't block emails
    $logger->info("Warning: Holiday check failed - " . $e->getMessage() . ". Continuing anyway.");
}

// ─────────────────────────────────────────────
// STEP 3: Normal working day — process attendance
// ─────────────────────────────────────────────
$logger->info("Working day confirmed. Processing attendance emails for {$date}.");

$processor  = new AttendanceProcessor();
$emailSender = new EmailSender();

$studentsData = $processor->getDailyAttendance($date);

if (empty($studentsData)) {
    echo "NO_RECORDS_FOUND_FOR_" . $date;
    $logger->info("No attendance records found for {$date}. No emails sent.");
    exit;
}

$count  = 0;
$failed = 0;
foreach ($studentsData as $studentId => $data) {
    // PRODUCTION: Use real student email address
    $studentEmail = trim($data['email'] ?? '');
    if (empty($studentEmail)) {
        $logger->info("Skipping student ID {$studentId} — no email on record.");
        $failed++;
        continue;
    }

    // Send to Student
    if ($emailSender->sendAttendanceReport($studentEmail, $data['name'], $data['attendance'], $date)) {
        $count++;
    } else {
        $failed++;
    }

    // Send to Parent (if parent_email exists)
    $parentEmail = trim($data['parent_email'] ?? '');
    if (!empty($parentEmail)) {
        if ($emailSender->sendAttendanceReport($parentEmail, $data['name'] . ' (Parent Copy)', $data['attendance'], $date)) {
            $count++;
        } else {
            $failed++;
        }
    }

    usleep(150000); // Small delay to avoid SMTP rate limiting
}

echo "COMPLETED|SENT:{$count}|FAILED:{$failed}";
$logger->info("Automation Finished. Sent: {$count}, Failed: {$failed}");
?>