<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/ReportingManager.php';

$auth = new Auth();
$auth->requirePrincipal(); // Only Admin or Principal can access

$user = $auth->getUser();
$reporting = new ReportingManager();

// Handle Date Filter
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

$stats = $reporting->getInstitutionStats($selectedDate);
$branchSummary = $reporting->getBranchWiseAttendanceSummary($selectedDate);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal Portal | Monitoring Dashboard</title>
    <!-- Fonts & Icons -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .filter-section {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-section label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 14px;
        }

        .filter-section input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-excellent {
            background: rgba(42, 157, 143, 0.1);
            color: #2a9d8f;
        }

        .status-good {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
        }

        .status-average {
            background: rgba(251, 133, 0, 0.1);
            color: #fb8500;
        }

        .status-low {
            background: rgba(239, 35, 60, 0.1);
            color: #ef233c;
        }

        .percentage-bar-container {
            width: 100%;
            height: 8px;
            background: #eee;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .percentage-bar {
            height: 100%;
            border-radius: 4px;
        }
    </style>
</head>

<body class="dashboard-body">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="assets/images/dashboard-logo.png" alt="JD College Logo" class="sidebar-logo">
        </div>

        <nav class="sidebar-nav">
            <a href="principal_dashboard.php" class="nav-item active">
                <i class="fa-solid fa-chart-line"></i>
                <span>Summary Dashboard</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="api/logout.php" class="nav-item logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="page-title">
                <h1>Principal Dashboard</h1>
                <p>Institutional Attendance Monitoring</p>
            </div>

            <div class="user-profile">
                <div class="profile-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=random"
                        alt="Profile">
                    <div class="text">
                        <span class="name">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </span>
                        <span class="role">
                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                        </span>
                        <span class="role" style="font-size: 11px; margin-top:2px;">
                            Login ID: <?php echo htmlspecialchars($user['username']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Date Filter -->
        <form action="" method="GET" class="filter-section">
            <label for="date-filter">Select Date:</label>
            <input type="date" id="date-filter" name="date" value="<?php echo $selectedDate; ?>"
                onchange="this.form.submit()">
            <button type="submit" class="btn-primary" style="padding: 8px 15px;">Refresh</button>
        </form>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box purple">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div class="stat-info">
                    <h3>Institution Strength</h3>
                    <p class="number">
                        <?php echo number_format($stats['total_students']); ?>
                    </p>
                    <span class="trend neutral">Total Students</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box blue">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Branches</h3>
                    <p class="number">
                        <?php echo $stats['total_branches']; ?>
                    </p>
                    <span class="trend neutral">Active Departments</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box orange">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div class="stat-info">
                    <h3>Overall Attendance</h3>
                    <p class="number">
                        <?php echo $stats['overall_percentage']; ?>%
                    </p>
                    <span class="trend <?php echo $stats['overall_percentage'] >= 75 ? 'up' : 'down'; ?>">
                        <i
                            class="fa-solid <?php echo $stats['overall_percentage'] >= 75 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                        Today
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box green">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Present Students</h3>
                    <p class="number">
                        <?php echo number_format($stats['present_today']); ?>
                    </p>
                    <span class="trend neutral">Across All Branches</span>
                </div>
            </div>
        </div>

        <!-- Branch Summary Table -->
        <div class="content-card">
            <div class="card-header">
                <h3>Branch-wise Attendance Breakdown</h3>
                <span class="text-light" style="font-size: 13px;">Date:
                    <?php echo date('d M, Y', strtotime($selectedDate)); ?>
                </span>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Branch / Department</th>
                            <th>Total Students</th>
                            <th>Present Today</th>
                            <th>Absent Today</th>
                            <th>Attendance %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($branchSummary)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 30px;">No branch data found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branchSummary as $row): ?>
                                <tr>
                                    <td><strong>
                                            <?php echo htmlspecialchars($row['branch']); ?>
                                        </strong></td>
                                    <td>
                                        <?php echo number_format($row['total_students']); ?>
                                    </td>
                                    <td>
                                        <?php echo number_format($row['present']); ?>
                                    </td>
                                    <td>
                                        <?php echo number_format($row['absent']); ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <span>
                                                <?php echo $row['percentage']; ?>%
                                            </span>
                                            <div class="percentage-bar-container">
                                                <div class="percentage-bar" style="width: <?php echo $row['percentage']; ?>%; background: <?php
                                                   if ($row['percentage'] >= 90)
                                                       echo '#2a9d8f';
                                                   elseif ($row['percentage'] >= 75)
                                                       echo '#4361ee';
                                                   elseif ($row['percentage'] >= 50)
                                                       echo '#fb8500';
                                                   else
                                                       echo '#ef233c'; ?>;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>

</html>