<?php
require_once __DIR__ . '/Database.php';

class ReportingManager
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get attendance summary for all branches for a specific date
     */
    public function getBranchWiseAttendanceSummary($date)
    {
        // Get all departments
        $deptStmt = $this->db->query("SELECT DISTINCT department FROM students WHERE department IS NOT NULL AND department != ''");
        $departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

        $summary = [];

        foreach ($departments as $dept) {
            // Total students in this department
            $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE department = :dept");
            $totalStmt->execute(['dept' => $dept]);
            $totalStudents = $totalStmt->fetchColumn();

            // Total present today in this department (Unique students marked present in any class)
            $presentStmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.student_id) 
                FROM attendance a 
                JOIN students s ON a.student_id = s.student_id 
                WHERE s.department = :dept 
                AND a.attendance_date = :date 
                AND a.status = 'Present'
            ");
            $presentStmt->execute(['dept' => $dept, 'date' => $date]);
            $presentCount = $presentStmt->fetchColumn();

            // Total absent today in this department (Unique students marked absent and NOT present in any class today)
            $absentStmt = $this->db->prepare("
                SELECT COUNT(DISTINCT a.student_id) 
                FROM attendance a 
                JOIN students s ON a.student_id = s.student_id 
                WHERE s.department = :dept 
                AND a.attendance_date = :date 
                AND a.status = 'Absent'
                AND a.student_id NOT IN (
                    SELECT student_id FROM attendance 
                    WHERE attendance_date = :date_check AND status = 'Present'
                )
            ");
            $absentStmt->execute(['dept' => $dept, 'date' => $date, 'date_check' => $date]);
            $absentCount = $absentStmt->fetchColumn();

            $percentage = ($totalStudents > 0) ? round(($presentCount / $totalStudents) * 100, 1) : 0;

            $summary[] = [
                'branch' => $dept,
                'total_students' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'percentage' => $percentage,
                'status' => $this->getAlertStatus($percentage)
            ];
        }

        return $summary;
    }

    /**
     * Get overall institution stats for a specific date
     */
    public function getInstitutionStats($date)
    {
        $stats = [
            'total_students' => $this->db->query("SELECT COUNT(*) FROM students")->fetchColumn(),
            'total_branches' => $this->db->query("SELECT COUNT(DISTINCT department) FROM students WHERE department IS NOT NULL AND department != ''")->fetchColumn(),
            'present_today' => 0,
            'overall_percentage' => 0
        ];

        // Total students
        $totalStudents = $stats['total_students'];

        // Total unique students present today across all branches
        $presentStmt = $this->db->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance WHERE attendance_date = :date AND status = 'Present'");
        $presentStmt->execute(['date' => $date]);
        $presentToday = $presentStmt->fetchColumn();

        $stats['present_today'] = $presentToday;

        if ($totalStudents > 0) {
            $stats['overall_percentage'] = round(($presentToday / $totalStudents) * 100, 1);
        }

        return $stats;
    }

    private function getAlertStatus($percentage)
    {
        if ($percentage >= 90)
            return 'Excellent';
        if ($percentage >= 75)
            return 'Good';
        if ($percentage >= 50)
            return 'Average';
        return 'Low';
    }
}
