<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';

class AttendanceProcessor
{
    private $db;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->logger = new Logger();
    }

    public function getDailyAttendance($date)
    {
        $this->logger->info("Fetching attendance for date: {$date}");

        $sql = "
            SELECT 
                a.student_id,
                s.student_name,
                s.email,
                s.parent_email,
                sub.subject_name,
                sub.teacher_name,
                a.status,
                f.faculty_name
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN subjects sub ON a.subject_id = sub.subject_id
            LEFT JOIN faculty f ON a.faculty_id = f.faculty_id
            WHERE a.attendance_date = :date
            ORDER BY s.student_id, sub.subject_name
        ";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['date' => $date]);
            $results = $stmt->fetchAll();

            $this->logger->info("Found " . count($results) . " attendance records.");
            return $this->groupAttendanceByStudent($results);

        } catch (PDOException $e) {
            $this->logger->error("Database error: " . $e->getMessage());
            return [];
        }
    }

    private function groupAttendanceByStudent($records)
    {
        $grouped = [];
        foreach ($records as $row) {
            $studentId = $row['student_id'];
            if (!isset($grouped[$studentId])) {
                $grouped[$studentId] = [
                    'name' => $row['student_name'],
                    'email' => $row['email'],
                    'parent_email' => $row['parent_email'],
                    'attendance' => []
                ];
            }
            $grouped[$studentId]['attendance'][] = [
                'subject' => $row['subject_name'],
                'status' => $row['status'],
                'faculty' => $row['teacher_name'] ?: $row['faculty_name']
            ];
        }
        return $grouped;
    }
}
?>