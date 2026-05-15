<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Logger.php';

class EmailSender
{
    private $mailer;
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->mailer = new PHPMailer(true);

        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_SECURE;
            $this->mailer->Port       = SMTP_PORT;

            // Sender
            $this->mailer->setFrom(SMTP_USERNAME, COLLEGE_NAME . ' Attendance');
        } catch (Exception $e) {
            $this->logger->error("Mailer Config Error: {$this->mailer->ErrorInfo}");
        }
    }

    public function sendAttendanceReport($studentEmail, $studentName, $attendanceData, $date)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($studentEmail, $studentName);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Daily Attendance Report - ' . $date;

            // Embed Logo
            $logoPath = realpath(__DIR__ . '/../public/assets/images/dashboard-logo.png');
            if ($logoPath && file_exists($logoPath)) {
                $this->mailer->addEmbeddedImage($logoPath, 'college_logo', 'logo.png');
            }

            $this->mailer->Body = $this->generateEmailBody($studentName, $attendanceData, $date);

            $this->mailer->send();

            $this->logger->success("Daily summary email sent to {$studentEmail}");
            return true;
        } catch (Exception $e) {
            $this->logger->error("Message could not be sent to {$studentEmail}. Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    private function generateEmailBody($name, $data, $date)
    {
        $rows = '';
        $absentCount = 0;
        $totalClasses = count($data);

        foreach ($data as $record) {
            if ($record['status'] == 'Absent') {
                $absentCount++;
            }
            $isPresent = ($record['status'] == 'Present');
            $statusColor = $isPresent ? '#059669' : '#dc2626';
            $statusBg = $isPresent ? '#ecfdf5' : '#fef2f2';
            $statusBorder = $isPresent ? '#10b981' : '#f87171';
            $statusIcon = $isPresent ? '✅' : '❌';
            $faculty = htmlspecialchars($record['faculty'] ?: 'TBA');

            $rows .= "
                <div style='margin-bottom: 20px; border: 1px solid #e2e8f0; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);'>
                    <div style='background-color: #f8fafc; padding: 12px 15px; border-bottom: 1px solid #e2e8f0;'>
                        <div style='font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 4px;'>Subject</div>
                        <h3 style='margin: 0; font-size: 16px; color: #0f172a; line-height: 1.4; font-weight: 600;'>{$record['subject']}</h3>
                    </div>
                    <div style='padding: 15px; border-left: 4px solid {$statusColor};'>
                        <table width='100%' cellpadding='0' cellspacing='0' border='0' style='width: 100%;'>
                            <tr>
                                <td align='left' valign='middle' style='padding-right: 10px;'>
                                    <div style='display: inline-block; background-color: {$statusBg}; padding: 4px 10px; border-radius: 4px; font-weight: 700; font-size: 13px; color: {$statusColor}; border: 1px solid {$statusBorder}; text-transform: uppercase;'>
                                        {$statusIcon} {$record['status']}
                                    </div>
                                </td>
                                <td align='right' valign='middle' style='font-size: 13px; color: #334155; text-align: right;'>
                                    <span style='color: #64748b;'>Course Faculty:</span><br>
                                    <strong style='color: #1e293b;'>{$faculty}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            ";
        }

        if ($absentCount === 0) {
            $summaryBg = '#f0fdf4';
            $summaryColor = '#059669';
            $summaryMsg = "<strong>Outstanding!</strong> You attended all {$totalClasses} classes today. Keep up the great consistency!";
        } else {
            $summaryBg = '#fef2f2';
            $summaryColor = '#dc2626';
            $summaryMsg = "<strong>Attention Required:</strong> You were marked <strong>Absent</strong> in {$absentCount} out of {$totalClasses} classes today. Please ensure regular attendance.";
        }

        return "
            <div style='font-family: \"Inter\", \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                
                <div style='background-color: #ffffff; padding: 25px 15px; text-align: center; border-bottom: 2px solid #f1f5f9;'>
                    <div style='max-width: 100%; overflow: hidden;'>
                        <img src='cid:college_logo' alt='College Logo' style='max-width: 100%; height: auto; max-height: 85px; margin: 0 auto; display: block;'>
                    </div>
                    <div style='margin-top: 15px; display: inline-block; background: #f1f5f9; padding: 6px 14px; border-radius: 20px; color: #475569; font-size: 12px; font-weight: 600; border: 1px solid #e2e8f0;'>
                        🗓️ Daily Attendance Report: {$date}
                    </div>
                </div>
                    
                <div style='padding: 30px 25px;'>
                    <p style='font-size: 16px; color: #1e293b; margin-top: 0;'>Dear <strong>{$name}</strong>,</p>
                    <p style='font-size: 15px; color: #475569; line-height: 1.6;'>Here is your structured summary of today's attendance records recorded by your respective faculty.</p>
                    
                    <div style='background-color: {$summaryBg}; border-left: 4px solid {$summaryColor}; padding: 16px; margin: 25px 0; border-radius: 4px; color: #1e293b; font-size: 15px; line-height: 1.5;'>
                        {$summaryMsg}
                    </div>
                    
                    <h2 style='font-size: 14px; color: #64748b; margin: 30px 0 15px 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;'>Today's Subject Breakdown</h2>
                    
                    <div style='margin-top: 20px;'>
                        {$rows}
                    </div>

                    <p style='margin-top: 35px; font-size: 13px; color: #94a3b8; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 20px;'>
                        This is an automated report. For any discrepancies, please contact your respective faculty.
                    </p>
                </div>
                
                <div style='background-color: #1e293b; padding: 20px; text-align: center; color: #94a3b8; font-size: 12px;'>
                    &copy; " . date('Y') . " " . COLLEGE_NAME . ". All rights reserved.
                </div>
            </div>
        ";
    }
}
?>