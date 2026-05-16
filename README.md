# 🎓 JD College Attendance Email System

This is a comprehensive, production-ready web portal designed to digitize daily attendance tracking for educational institutions. It allows faculty to mark student attendance efficiently and automatically sends daily attendance reports to both students and their parents via email using a scheduled Windows Task.

---

## 🚀 Features
*   **Role-Based Access Control**: Separate dashboards for Faculty, Head of Departments (HOD), and the Principal.
*   **Automated Daily Emails**: A Windows Scheduled Task triggers emails at 6:00 PM automatically.
*   **Smart Skip Logic**: Automatically skips sending emails on Sundays, declared holidays, or if no attendance was marked.
*   **Principal Analytics Dashboard**: Real-time monitoring of branch-wise attendance percentages.
*   **Bulk Management**: HODs can upload Students and Subjects via CSV files.

---

## 🛠️ Technology Stack Used

*   **Frontend**: HTML5, CSS3, Vanilla JavaScript (Modern UI with Glassmorphism, FontAwesome 6, Google Fonts 'Inter' & 'Outfit').
*   **Backend**: PHP 8.x (OOP Architecture).
*   **Database**: MySQL / MariaDB (Hosted via XAMPP).
*   **Security**: 100% PDO Prepared Statements (SQL Injection prevention), CSRF Tokens on all forms, 15-minute brute-force lockout, and `.htaccess` file protections.
*   **Libraries**: 
    *   `phpmailer/phpmailer` (for secure SMTP emails)
    *   `vlucas/phpdotenv` (for `.env` management)

---

## 🔑 Default Administrator Credentials

Upon fresh installation, use the following credentials to access the Principal/Admin dashboard. 
*(Note: Please change the password immediately after your first login).*

*   **Username:** `admin`
*   **Password:** `password123`
*   **Role:** Principal / Admin

---

## 📖 How to Run the Project (Installation Guide)

Follow these steps to deploy the project on a local XAMPP server or a college production server:

### Step 1: Clone or Copy the Project
Clone or copy the entire repository into your XAMPP `htdocs` directory.
*   Path: `C:\xampp\htdocs\attendance-email-system`

### Step 2: Configure Environment Variables
1.  Duplicate the `.env.example` file and rename the copy to `.env`.
2.  Open `.env` in a text editor and configure your database and email credentials:
    ```env
    DB_HOST=localhost
    DB_PORT=3306
    DB_NAME=attendance_db
    DB_USER=root
    DB_PASS=
    
    SMTP_HOST=smtp.gmail.com
    SMTP_PORT=587
    SMTP_USERNAME=your-college-email@gmail.com
    SMTP_PASSWORD=your-google-app-password
    ```

### Step 3: Install PHP Dependencies (Composer)
1.  Open a terminal inside the project folder.
2.  Run the command: `composer install`

### Step 4: Run the One-Click Database Setup
1.  Start **Apache** and **MySQL** from your XAMPP Control Panel.
2.  Open your web browser and go to: 
    `http://localhost/attendance-email-system/public/setup_database.php`
3.  The system will automatically create the database, tables, and the default admin user. *(Note: After a successful run, this file automatically locks itself for security).*

### Step 5: Log In
1.  Navigate to the login page: `http://localhost/attendance-email-system/public/index.php`
2.  Use the default credentials to log in.

### Step 6: Enable Automated 6:00 PM Emails
1.  Navigate to the project root folder in Windows Explorer.
2.  Right-click the `Setup_Daily_Mailer_Task.bat` file and select **"Run as Administrator"**.
3.  This automatically configures the Windows Task Scheduler to send attendance emails every day at exactly 6:00 PM.
