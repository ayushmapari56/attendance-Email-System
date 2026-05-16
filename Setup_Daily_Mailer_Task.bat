@echo off
title Setup Automated Attendance Mailer
cd /d "%~dp0"

echo =======================================================
echo     JD COLLEGE - AUTOMATED EMAIL SCHEDULER SETUP
echo =======================================================
echo.
echo This script will configure your Windows Server to run the
echo Attendance Mailer every day automatically at 6:00 PM.
echo.
echo Important: Run this script as Administrator.
echo.

:: Get the full path to the mailer script
set "MAILER_SCRIPT=%~dp0Run_Attendance_Mailer.bat"

:: Attempt to create the scheduled task
schtasks /create /tn "JD_College_Attendance_Mailer" /tr "\"%MAILER_SCRIPT%\" --cron" /sc daily /st 18:00 /f

if %ERRORLEVEL% == 0 (
    echo.
    echo [SUCCESS] The mailer task has been successfully scheduled!
    echo Emails will now be sent automatically every day at 18:00 (6:00 PM).
) else (
    echo.
    echo [ERROR] Failed to schedule task. 
    echo Please make sure you right-click and "Run as Administrator".
)

echo.
pause
