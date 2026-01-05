<?php
// reports_management.php - COMPLETE REPORTS MANAGEMENT MODULE
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login_admin.php");
    exit();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login_admin.php");
    exit();
}

if (!isset($_SESSION['user_info'])) {
    $_SESSION['user_info'] = [
        'name' => 'System Administrator',
        'email' => 'admin@example.com',
        'department' => 'IT Department',
        'position' => 'System Admin',
        'username' => 'admin',
        'created_at' => '2024-01-01',
        'last_login' => date('Y-m-d H:i:s')
    ];
}

$admin_info = $_SESSION['user_info'];

// Sample reports data (replace with database)
$reports = [
    [
        'id' => 1,
        'report_code' => 'ENR-2024-001',
        'report_name' => 'Enrollment Summary Report',
        'report_type' => 'enrollment',
        'description' => 'Summary of student enrollments for the semester',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-01-20 14:30:00',
        'date_range' => 'Jan 1, 2024 - Jan 20, 2024',
        'status' => 'completed',
        'file_size' => '2.5 MB',
        'file_format' => 'PDF',
        'download_count' => 15,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Department: All, Status: All'
    ],
    [
        'id' => 2,
        'report_code' => 'GRD-2024-001',
        'report_name' => 'Grade Distribution Report',
        'report_type' => 'grades',
        'description' => 'Grade distribution across all courses and sections',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-01-25 10:15:00',
        'date_range' => '1st Semester 2023-2024',
        'status' => 'completed',
        'file_size' => '1.8 MB',
        'file_format' => 'Excel',
        'download_count' => 8,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Course: All, Year Level: All'
    ],
    [
        'id' => 3,
        'report_code' => 'ATT-2024-001',
        'report_name' => 'Attendance Summary Report',
        'report_type' => 'attendance',
        'description' => 'Student attendance summary with absence patterns',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-01-28 16:45:00',
        'date_range' => 'Jan 1, 2024 - Jan 28, 2024',
        'status' => 'completed',
        'file_size' => '3.2 MB',
        'file_format' => 'PDF',
        'download_count' => 12,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Status: Present/Absent/Late'
    ],
    [
        'id' => 4,
        'report_code' => 'FIN-2024-001',
        'report_name' => 'Financial Summary Report',
        'report_type' => 'financial',
        'description' => 'Revenue and fee collection summary',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-01-30 09:20:00',
        'date_range' => 'Jan 1, 2024 - Jan 30, 2024',
        'status' => 'completed',
        'file_size' => '1.5 MB',
        'file_format' => 'Excel',
        'download_count' => 6,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Transaction Type: All'
    ],
    [
        'id' => 5,
        'report_code' => 'STU-2024-001',
        'report_name' => 'Student Demographics Report',
        'report_type' => 'students',
        'description' => 'Student population by course, year level, and gender',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-02-01 11:30:00',
        'date_range' => '1st Semester 2023-2024',
        'status' => 'processing',
        'file_size' => '0 MB',
        'file_format' => 'PDF',
        'download_count' => 0,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Department: All, Course: All'
    ],
    [
        'id' => 6,
        'report_code' => 'ENR-2024-002',
        'report_name' => 'Course Enrollment Statistics',
        'report_type' => 'enrollment',
        'description' => 'Detailed course enrollment statistics and trends',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-02-02 14:00:00',
        'date_range' => '1st Semester 2023-2024',
        'status' => 'failed',
        'file_size' => '0 MB',
        'file_format' => 'PDF',
        'download_count' => 0,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Course: All, Instructor: All'
    ],
    [
        'id' => 7,
        'report_code' => 'GRD-2024-002',
        'report_name' => 'Academic Performance Report',
        'report_type' => 'grades',
        'description' => 'Academic performance analysis by department',
        'generated_by' => 'System Administrator',
        'generation_date' => '2024-02-03 10:45:00',
        'date_range' => '1st Semester 2023-2024',
        'status' => 'scheduled',
        'file_size' => '0 MB',
        'file_format' => 'PDF',
        'download_count' => 0,
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'parameters' => 'Department: All, GPA Range: All'
    ]
];

// Sample report types and formats
$report_types = [
    'enrollment' => 'Enrollment Reports',
    'grades' => 'Grade Reports',
    'attendance' => 'Attendance Reports',
    'financial' => 'Financial Reports',
    'students' => 'Student Reports',
    'courses' => 'Course Reports',
    'instructors' => 'Instructor Reports',
    'system' => 'System Reports'
];

$report_formats = [
    'pdf' => 'PDF Document',
    'excel' => 'Excel Spreadsheet',
    'csv' => 'CSV File',
    'html' => 'Web Page',
    'json' => 'JSON Data'
];

$academic_years = ['2023-2024', '2024-2025', '2025-2026'];
$semesters = ['1st Semester', '2nd Semester', 'Summer'];
$status_types = ['completed', 'processing', 'scheduled', 'failed'];

// Calculate report statistics
$total_reports = count($reports);
$completed_reports = count(array_filter($reports, function($report) { 
    return $report['status'] === 'completed'; 
}));
$popular_reports = array_reduce($reports, function($carry, $report) {
    return $carry + $report['download_count'];
}, 0);
$recent_reports = count(array_filter($reports, function($report) { 
    return strtotime($report['generation_date']) >= strtotime('-7 days'); 
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Reports Management - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <style>
        .msu-maroon { color: #800000; }
        .bg-msu-maroon { background-color: #800000; color: #fff; }
        .btn-msu { background: #800000; color: white; border: none; padding: 10px 18px; border-radius: 8px; }
        .btn-msu-sm { background: #800000; color: white; border: none; padding: 6px 12px; border-radius: 6px; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .navbar { background: #800000 !important; padding: 1rem 0; position: fixed; top: 0; width: 100%; z-index: 1030; }
        .sidebar { background: #5a0000; min-height: 100vh; position: fixed; width: 280px; left: 0; top: 0; padding-top: 70px; }
        .sidebar .nav-link { color: white; padding: 15px 20px; border-left: 4px solid transparent; }
        .sidebar .nav-link:hover { background: #a30000; border-left-color: #FFD700; color: #FFD700; }
        .sidebar .nav-link.active { background: #a30000; border-left-color: #FFD700; color: #FFD700; font-weight:600; }
        .main-content { padding: 20px 15px; min-height: 100vh; margin-top: 70px; margin-left: 280px; width: calc(100% - 280px); }
        .dashboard-card { background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 4px solid #800000; margin-bottom: 20px; padding: 1.5rem; }
        .welcome-banner { background: linear-gradient(135deg, #800000, #5a0000); color: white; border-radius: 10px; padding: 20px; margin-bottom: 25px; border: 2px solid #FFD700; }
        .user-avatar { width: 80px; height: 80px; background: #800000; border-radius: 50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; border:3px solid #FFD700; font-size: 28px; }
        
        /* Reports Management Specific Styles */
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-completed { background: #28a745; }
        .badge-processing { background: #17a2b8; }
        .badge-scheduled { background: #ffc107; color: #000; }
        .badge-failed { background: #dc3545; }
        .report-card { 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); 
            margin-bottom: 20px; 
            padding: 1.5rem;
            border-left: 4px solid #800000;
            transition: transform 0.2s ease;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        
        /* Status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-completed { background: #28a745; }
        .status-processing { background: #17a2b8; }
        .status-scheduled { background: #ffc107; }
        .status-failed { background: #dc3545; }

        /* Report type indicators */
        .report-type-enrollment { border-left-color: #28a745; }
        .report-type-grades { border-left-color: #17a2b8; }
        .report-type-attendance { border-left-color: #ffc107; }
        .report-type-financial { border-left-color: #dc3545; }
        .report-type-students { border-left-color: #6f42c1; }
        .report-type-courses { border-left-color: #e83e8c; }
        .report-type-instructors { border-left-color: #20c997; }
        .report-type-system { border-left-color: #6c757d; }

        /* Progress bars */
        .report-progress {
            height: 6px;
            border-radius: 3px;
            background: #e9ecef;
            margin-top: 5px;
        }
        .report-progress-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        .progress-completed { background: linear-gradient(90deg, #28a745, #20c997); }
        .progress-processing { 
            background: linear-gradient(90deg, #17a2b8, #20c997);
            width: 65% !important;
            animation: pulse 1.5s infinite;
        }
        .progress-scheduled { background: linear-gradient(90deg, #ffc107, #fd7e14); width: 30% !important; }
        .progress-failed { background: #dc3545; }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Quick stats cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-top: 4px solid #800000;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
        }
        .stats-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #800000;
            margin: 10px 0;
        }

        @media (max-width: 991.98px) {
            .sidebar { left: -280px; }
            .sidebar.show { left: 0; }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle d-lg-none btn btn-link text-white" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand nav-brand" href="admin_dashboard.php">
                <i class="fas fa-chart-bar me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Reports Management</span>
                <span class="d-inline d-sm-none">Reports Management</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($admin_info['name']); ?></strong></div>
                    <small>System Administrator</small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2" style="width:44px; height:44px; font-size:18px;">
                            <?php echo strtoupper(substr(explode(' ', $admin_info['name'])[0], 0, 1)); ?>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2 text-msu"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2 text-msu"></i>Settings</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item text-danger" href="logout_admin.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-user-info d-lg-none p-3 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3" style="width:48px; height:48px; font-size:16px;">
                    <?php echo strtoupper(substr(explode(' ', $admin_info['name'])[0], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($admin_info['name']); ?></strong><br>
                    <small>System Administrator</small>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column pt-3">
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            <a href="user_management.php" class="nav-link"><i class="fas fa-users me-2"></i> User Management</a>
            <a href="course_management.php" class="nav-link"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
<a href="fees_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Fees</a>

            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
            <a href="reports_management.php" class="nav-link active"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
     
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Reports Management System</h2>
                    <p class="mb-1">Generate, manage, and analyze institutional reports and analytics</p>
                    <small>Total Reports: <?php echo $total_reports; ?> | Last Generated: <?php 
                        $latest_report = end($reports);
                        echo date('M j, Y g:i A', strtotime($latest_report['generation_date'])); 
                    ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="number"><?php echo $total_reports; ?></div>
                    <p class="small">Total Reports</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="number"><?php echo $completed_reports; ?></div>
                    <p class="small">Completed</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="number"><?php echo $popular_reports; ?></div>
                    <p class="small">Total Downloads</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="number"><?php echo $recent_reports; ?></div>
                    <p class="small">Last 7 Days</p>
                </div>
            </div>
        </div>

        <!-- Quick Report Actions -->
        <div class="dashboard-card mb-4">
            <div class="card-body">
                <h5 class="text-msu mb-3">Quick Report Generation</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-msu" onclick="generateQuickReport('enrollment')">
                                <i class="fas fa-clipboard-list me-2"></i>Enrollment Summary
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-msu" onclick="generateQuickReport('grades')">
                                <i class="fas fa-chart-line me-2"></i>Grade Report
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-msu" onclick="generateQuickReport('attendance')">
                                <i class="fas fa-clipboard-check me-2"></i>Attendance Summary
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-outline-msu" onclick="generateQuickReport('financial')">
                                <i class="fas fa-money-bill-wave me-2"></i>Financial Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="search-filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search reports..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <?php foreach ($report_types as $key => $value): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($value); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <?php foreach ($status_types as $status): ?>
                            <option value="<?php echo htmlspecialchars($status); ?>"><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="semesterFilter">
                        <option value="">All Semesters</option>
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo htmlspecialchars($semester); ?>"><?php echo htmlspecialchars($semester); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                            <i class="fas fa-plus me-1"></i> Generate Report
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#scheduleReportModal">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Management Table -->
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-msu mb-0">Generated Reports</h5>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="refreshReports()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-sort me-1"></i> Sort By
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="sortReports('date')">Generation Date</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sortReports('name')">Report Name</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sortReports('type')">Report Type</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sortReports('downloads')">Download Count</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="reportsTable">
                        <thead class="table-msu">
                            <tr>
                                <th>Report Code</th>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Generated By</th>
                                <th>Generation Date</th>
                                <th>File Format</th>
                                <th>Status</th>
                                <th>Downloads</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr data-report-id="<?php echo $report['id']; ?>" class="report-type-<?php echo $report['report_type']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($report['report_code']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($report['date_range']); ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($report['report_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($report['description']); ?></small>
                                    <br><small class="text-muted">Parameters: <?php echo htmlspecialchars($report['parameters']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo ucfirst($report['report_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($report['generated_by']); ?></td>
                                <td>
                                    <?php echo date('M j, Y g:i A', strtotime($report['generation_date'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($report['file_format']); ?>
                                    </span>
                                    <?php if ($report['file_size'] !== '0 MB'): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($report['file_size']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $report['status']; ?>">
                                        <span class="status-indicator status-<?php echo $report['status']; ?>"></span>
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                    <div class="report-progress">
                                        <div class="report-progress-bar <?php echo getProgressBarClass($report['status']); ?>"></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong><?php echo $report['download_count']; ?></strong>
                                        <br><small class="text-muted">downloads</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($report['status'] === 'completed'): ?>
                                            <button class="btn btn-success btn-sm" onclick="downloadReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm" onclick="previewReport(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php elseif ($report['status'] === 'processing'): ?>
                                            <button class="btn btn-warning btn-sm" onclick="viewProgress(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        <?php elseif ($report['status'] === 'scheduled'): ?>
                                            <button class="btn btn-secondary btn-sm" onclick="editSchedule(<?php echo $report['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-msu-sm" onclick="regenerateReport(<?php echo $report['id']; ?>)">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteReport(<?php echo $report['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Reports pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- ===================== MODALS ===================== -->

    <!-- GENERATE REPORT MODAL -->
    <div class="modal fade" id="generateReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="generateReportForm" method="POST">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Generate New Report</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Type</label>
                                <select class="form-select" name="report_type" id="reportType" required>
                                    <option value="" selected disabled>Select Report Type</option>
                                    <?php foreach ($report_types as $key => $value): ?>
                                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Report Format</label>
                                <select class="form-select" name="report_format" required>
                                    <option value="" selected disabled>Select Format</option>
                                    <?php foreach ($report_formats as $key => $value): ?>
                                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Report Name</label>
                                <input type="text" class="form-control" name="report_name" placeholder="Enter report name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="description" placeholder="Brief description of the report">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Academic Year</label>
                                <select class="form-select" name="academic_year" required>
                                    <option value="" selected disabled>Select Year</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="" selected disabled>Select Semester</option>
                                    <?php foreach ($semesters as $semester): ?>
                                        <option value="<?php echo htmlspecialchars($semester); ?>"><?php echo htmlspecialchars($semester); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date Range</label>
                                <select class="form-select" name="date_range">
                                    <option value="current_semester">Current Semester</option>
                                    <option value="last_30_days">Last 30 Days</option>
                                    <option value="last_90_days">Last 90 Days</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                        </div>

                        <div class="row" id="customDateRange" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" name="from_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" name="to_date">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Additional Parameters</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_charts" id="includeCharts" checked>
                                <label class="form-check-label" for="includeCharts">Include charts and graphs</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_summary" id="includeSummary" checked>
                                <label class="form-check-label" for="includeSummary">Include executive summary</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_details" id="includeDetails">
                                <label class="form-check-label" for="includeDetails">Include detailed data</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Notification</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="Email address for notification (optional)">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCHEDULE REPORT MODAL -->
    <div class="modal fade" id="scheduleReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon">
                    <h5 class="modal-title text-white">Schedule Recurring Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select class="form-select">
                            <option value="" selected disabled>Select Report Type</option>
                            <?php foreach ($report_types as $key => $value): ?>
                                <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Schedule Frequency</label>
                        <select class="form-select">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="semesterly">Every Semester</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendEmail">
                        <label class="form-check-label" for="sendEmail">
                            Send report via email when generated
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-msu" type="button">Schedule Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report? This action cannot be undone.</p>
                    <p class="fw-bold" id="deleteReportInfo"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="button" id="confirmDeleteReportBtn">Delete Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterReports();
        });

        document.getElementById('typeFilter').addEventListener('change', filterReports);
        document.getElementById('statusFilter').addEventListener('change', filterReports);
        document.getElementById('semesterFilter').addEventListener('change', filterReports);

        function filterReports() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            
            const rows = document.querySelectorAll('#reportsTable tbody tr');
            
            rows.forEach(row => {
                const reportCode = row.cells[0].textContent.toLowerCase();
                const reportName = row.cells[1].textContent.toLowerCase();
                const type = row.cells[2].textContent.toLowerCase();
                const status = row.cells[6].textContent.toLowerCase();
                
                const matchesSearch = reportCode.includes(searchText) || reportName.includes(searchText);
                const matchesType = !typeFilter || type.includes(typeFilter);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                
                // Note: Semester filtering would require additional data attributes
                const matchesFilters = matchesType && matchesStatus;
                
                row.style.display = matchesSearch && matchesFilters ? '' : 'none';
            });
        }

        // Report management functions
        function generateQuickReport(type) {
            alert(`Generating quick ${type} report...`);
            // In real application, this would trigger report generation
        }

        function downloadReport(reportId) {
            const report = <?php echo json_encode($reports); ?>.find(r => r.id == reportId);
            if (report) {
                alert(`Downloading report: ${report.report_name}`);
                // In real application, this would trigger file download
            }
        }

        function previewReport(reportId) {
            const report = <?php echo json_encode($reports); ?>.find(r => r.id == reportId);
            if (report) {
                alert(`Previewing report: ${report.report_name}\n\nThis would open a preview window in a real application.`);
            }
        }

        function viewProgress(reportId) {
            alert(`Viewing generation progress for report ID: ${reportId}`);
        }

        function editSchedule(reportId) {
            alert(`Editing schedule for report ID: ${reportId}`);
        }

        function regenerateReport(reportId) {
            if (confirm('Are you sure you want to regenerate this report?')) {
                alert(`Report ${reportId} regeneration started`);
                refreshReports();
            }
        }

        function deleteReport(reportId) {
            const report = <?php echo json_encode($reports); ?>.find(r => r.id == reportId);
            if (report) {
                document.getElementById('deleteReportInfo').textContent = 
                    `${report.report_code} - ${report.report_name}`;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteReportModal'));
                deleteModal.show();
                
                document.getElementById('confirmDeleteReportBtn').onclick = function() {
                    alert(`Report ${reportId} deleted successfully`);
                    deleteModal.hide();
                    refreshReports();
                };
            }
        }

        function refreshReports() {
            alert('Refreshing reports data...');
        }

        function sortReports(criteria) {
            alert(`Sorting reports by: ${criteria}`);
            // In real application, this would sort the table
        }

        // Show/hide custom date range
        document.querySelector('select[name="date_range"]').addEventListener('change', function() {
            const customRange = document.getElementById('customDateRange');
            if (this.value === 'custom') {
                customRange.style.display = 'flex';
            } else {
                customRange.style.display = 'none';
            }
        });

        // Form submission handlers
        document.getElementById('generateReportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Report generation started! You will be notified when it is ready.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('generateReportModal'));
            modal.hide();
            refreshReports();
        });

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Set active link based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
// Helper functions for report styling
function getProgressBarClass($status) {
    switch ($status) {
        case 'completed': return 'progress-completed';
        case 'processing': return 'progress-processing';
        case 'scheduled': return 'progress-scheduled';
        case 'failed': return 'progress-failed';
        default: return '';
    }
}
?>