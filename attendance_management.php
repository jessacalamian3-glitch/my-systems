<?php
// attendance_management.php - COMPLETE ATTENDANCE MANAGEMENT MODULE
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

// Sample attendance data (replace with database)
$attendance_records = [
    [
        'id' => 1,
        'student_id' => '2020-12345',
        'student_name' => 'Avril Lyza Suan',
        'course_code' => 'IT101',
        'course_name' => 'Introduction to Information Technology',
        'instructor' => 'Dr. Maria Reyes',
        'attendance_date' => '2024-03-20',
        'day' => 'Wednesday',
        'time_in' => '07:30 AM',
        'time_out' => '10:30 AM',
        'status' => 'present',
        'hours_rendered' => 3.0,
        'remarks' => 'On time',
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'section' => 'A'
    ],
    [
        'id' => 2,
        'student_id' => '2021-67890',
        'student_name' => 'John Michael Santos',
        'course_code' => 'CS201',
        'course_name' => 'Data Structures and Algorithms',
        'instructor' => 'Prof. Robert Lim',
        'attendance_date' => '2024-03-20',
        'day' => 'Wednesday',
        'time_in' => '08:15 AM',
        'time_out' => '11:15 AM',
        'status' => 'late',
        'hours_rendered' => 3.0,
        'remarks' => 'Late 45 minutes',
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'section' => 'B'
    ],
    [
        'id' => 3,
        'student_id' => '2020-12345',
        'student_name' => 'Avril Lyza Suan',
        'course_code' => 'MATH101',
        'course_name' => 'College Algebra',
        'instructor' => 'Dr. James Wilson',
        'attendance_date' => '2024-03-19',
        'day' => 'Tuesday',
        'time_in' => '01:00 PM',
        'time_out' => '04:00 PM',
        'status' => 'present',
        'hours_rendered' => 3.0,
        'remarks' => 'On time',
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'section' => 'C'
    ],
    [
        'id' => 4,
        'student_id' => '2022-54321',
        'student_name' => 'Maria Cristina Lopez',
        'course_code' => 'BUS101',
        'course_name' => 'Principles of Management',
        'instructor' => 'Prof. Anna Santos',
        'attendance_date' => '2024-03-19',
        'day' => 'Tuesday',
        'time_in' => '',
        'time_out' => '',
        'status' => 'absent',
        'hours_rendered' => 0.0,
        'remarks' => 'Excused absence - medical',
        'semester' => '2nd Semester',
        'academic_year' => '2023-2024',
        'section' => 'A'
    ],
    [
        'id' => 5,
        'student_id' => '2021-67890',
        'student_name' => 'John Michael Santos',
        'course_code' => 'ENG101',
        'course_name' => 'Technical Writing',
        'instructor' => 'Dr. Lisa Garcia',
        'attendance_date' => '2024-03-18',
        'day' => 'Monday',
        'time_in' => '10:00 AM',
        'time_out' => '12:30 PM',
        'status' => 'present',
        'hours_rendered' => 2.5,
        'remarks' => 'Left early - permission granted',
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'section' => 'B'
    ],
    [
        'id' => 6,
        'student_id' => '2022-98765',
        'student_name' => 'Carlos Reyes',
        'course_code' => 'CS201',
        'course_name' => 'Data Structures and Algorithms',
        'instructor' => 'Prof. Robert Lim',
        'attendance_date' => '2024-03-20',
        'day' => 'Wednesday',
        'time_in' => '',
        'time_out' => '',
        'status' => 'absent',
        'hours_rendered' => 0.0,
        'remarks' => 'Unexcused absence',
        'semester' => '1st Semester',
        'academic_year' => '2023-2024',
        'section' => 'B'
    ]
];

// Sample students and courses for dropdowns
$students = [
    ['id' => '2020-12345', 'name' => 'Avril Lyza Suan', 'course' => 'BS Information Technology'],
    ['id' => '2021-67890', 'name' => 'John Michael Santos', 'course' => 'BS Business Administration'],
    ['id' => '2022-54321', 'name' => 'Maria Cristina Lopez', 'course' => 'BS Education'],
    ['id' => '2022-98765', 'name' => 'Carlos Reyes', 'course' => 'BS Computer Science']
];

$courses = [
    ['code' => 'IT101', 'name' => 'Introduction to Information Technology'],
    ['code' => 'CS201', 'name' => 'Data Structures and Algorithms'],
    ['code' => 'MATH101', 'name' => 'College Algebra'],
    ['code' => 'BUS101', 'name' => 'Principles of Management'],
    ['code' => 'ENG101', 'name' => 'Technical Writing']
];

$academic_years = ['2023-2024', '2024-2025', '2025-2026'];
$semesters = ['1st Semester', '2nd Semester', 'Summer'];
$attendance_statuses = ['present', 'absent', 'late', 'excused'];
$sections = ['A', 'B', 'C', 'D'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Attendance Management - MSU Buug</title>
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
        
        /* Attendance Management Specific Styles */
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-present { background: #28a745; }
        .badge-absent { background: #dc3545; }
        .badge-late { background: #ffc107; color: #000; }
        .badge-excused { background: #17a2b8; }
        .attendance-present { color: #28a745; font-weight: bold; }
        .attendance-absent { color: #dc3545; font-weight: bold; }
        .attendance-late { color: #fd7e14; font-weight: bold; }
        .attendance-excused { color: #17a2b8; font-weight: bold; }
        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        
        /* Status indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-present { background: #28a745; }
        .status-absent { background: #dc3545; }
        .status-late { background: #ffc107; }
        .status-excused { background: #17a2b8; }

        /* Time indicators */
        .time-in { color: #28a745; font-weight: 500; }
        .time-out { color: #dc3545; font-weight: 500; }
        .time-missing { color: #6c757d; font-style: italic; }

        /* Progress bars for attendance */
        .attendance-progress {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            margin-top: 5px;
        }
        .attendance-progress-bar {
            height: 100%;
            border-radius: 4px;
        }
        .progress-present { background: linear-gradient(90deg, #28a745, #20c997); }
        .progress-late { background: linear-gradient(90deg, #ffc107, #fd7e14); }
        .progress-absent { background: #dc3545; }

        /* Calendar view styles */
        .calendar-day {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
        }
        .calendar-day.active {
            background: #800000;
            color: white;
        }
        .calendar-day.has-attendance {
            background: #d4edda;
            border-color: #c3e6cb;
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
                <i class="fas fa-clipboard-check me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Attendance Management</span>
                <span class="d-inline d-sm-none">Attendance Management</span>
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
            <a href="attendance_management.php" class="nav-link active"><i class="fas fa-clipboard-check me-2"></i> Attendance</a>
            <a href="financial_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Financial</a>
            <a href="reports_management.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i> Reports</a>
            <a href="system_settings.php" class="nav-link"><i class="fas fa-cogs me-2"></i> System Settings</a>
   
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="h4">Attendance Management System</h2>
                    <p class="mb-1">Track and manage student class attendance records</p>
                    <small>Total Records: <?php echo count($attendance_records); ?> | Today's Attendance: <?php 
                        $today = date('Y-m-d');
                        echo count(array_filter($attendance_records, function($record) use ($today) { 
                            return $record['attendance_date'] === $today; 
                        })); 
                    ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-check"></i></div>
                    <h3 class="stats-number"><?php echo count(array_filter($attendance_records, function($record) { return $record['status'] === 'present'; })); ?></h3>
                    <p class="small">Present</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-times"></i></div>
                    <h3 class="stats-number"><?php echo count(array_filter($attendance_records, function($record) { return $record['status'] === 'absent'; })); ?></h3>
                    <p class="small">Absent</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-clock"></i></div>
                    <h3 class="stats-number"><?php echo count(array_filter($attendance_records, function($record) { return $record['status'] === 'late'; })); ?></h3>
                    <p class="small">Late</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-clock"></i></div>
                    <h3 class="stats-number"><?php echo count(array_unique(array_column($attendance_records, 'student_id'))); ?></h3>
                    <p class="small">Students Tracked</p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="search-filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search attendance..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="excused">Excused</option>
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
                <div class="col-md-2">
                    <select class="form-select" id="dateFilter">
                        <option value="">All Dates</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                            <i class="fas fa-plus me-1"></i> Record Attendance
                        </button>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkAttendanceModal">
                            <i class="fas fa-users"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Date Navigation -->
        <div class="dashboard-card mb-4">
            <div class="card-body">
                <h6 class="text-msu mb-3">Quick Date Navigation</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-msu btn-sm active">Today</button>
                    <button class="btn btn-outline-secondary btn-sm">Yesterday</button>
                    <button class="btn btn-outline-secondary btn-sm">This Week</button>
                    <button class="btn btn-outline-secondary btn-sm">This Month</button>
                    <div class="ms-auto">
                        <input type="date" class="form-control form-control-sm" id="customDate" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Management Table -->
        <div class="dashboard-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-msu mb-0">Attendance Records</h5>
                    <div>
                        <button class="btn btn-outline-secondary btn-sm me-2" onclick="refreshTable()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="attendanceTable">
                        <thead class="table-msu">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_records as $record): ?>
                            <tr data-attendance-id="<?php echo $record['id']; ?>">
                                <td><strong><?php echo htmlspecialchars($record['student_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($record['course_code']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($record['course_name']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M j, Y', strtotime($record['attendance_date'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($record['day']); ?></td>
                                <td>
                                    <?php if ($record['time_in']): ?>
                                        <span class="time-in"><?php echo htmlspecialchars($record['time_in']); ?></span>
                                    <?php else: ?>
                                        <span class="time-missing">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($record['time_out']): ?>
                                        <span class="time-out"><?php echo htmlspecialchars($record['time_out']); ?></span>
                                    <?php else: ?>
                                        <span class="time-missing">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold <?php echo getAttendanceClass($record['status']); ?>">
                                        <?php echo number_format($record['hours_rendered'], 1); ?>h
                                    </span>
                                    <?php if ($record['status'] === 'present' || $record['status'] === 'late'): ?>
                                    <div class="attendance-progress">
                                        <div class="attendance-progress-bar <?php echo getProgressBarClass($record['status']); ?>" 
                                             style="width: <?php echo ($record['hours_rendered'] / 3) * 100; ?>%"></div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $record['status']; ?>">
                                        <span class="status-indicator status-<?php echo $record['status']; ?>"></span>
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($record['remarks']); ?></small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-msu-sm" onclick="editAttendance(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="viewAttendance(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="markAttendance(<?php echo $record['id']; ?>, 'present')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteAttendance(<?php echo $record['id']; ?>)">
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
                <nav aria-label="Attendance pagination">
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

    <!-- ADD ATTENDANCE MODAL -->
    <div class="modal fade" id="addAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addAttendanceForm" method="POST">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Record Student Attendance</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student</label>
                                <select class="form-select" name="student_id" id="studentSelect" required>
                                    <option value="" selected disabled>Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo htmlspecialchars($student['id']); ?>">
                                            <?php echo htmlspecialchars($student['id'] . ' - ' . $student['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Course</label>
                                <select class="form-select" name="course_code" id="courseSelect" required>
                                    <option value="" selected disabled>Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['code']); ?>">
                                            <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Attendance Date</label>
                                <input type="date" class="form-control" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Time In</label>
                                <input type="time" class="form-control" name="time_in" id="timeIn">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Time Out</label>
                                <input type="time" class="form-control" name="time_out" id="timeOut">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="statusSelect" required>
                                    <option value="" selected disabled>Select Status</option>
                                    <?php foreach ($attendance_statuses as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>"><?php echo ucfirst($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Section</label>
                                <select class="form-select" name="section" required>
                                    <option value="" selected disabled>Select Section</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?php echo htmlspecialchars($section); ?>">Section <?php echo htmlspecialchars($section); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hours Rendered</label>
                                <input type="number" class="form-control" name="hours_rendered" min="0" max="8" step="0.5" value="3.0" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year</label>
                                <select class="form-select" name="academic_year" required>
                                    <option value="" selected disabled>Select Year</option>
                                    <?php foreach ($academic_years as $year): ?>
                                        <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="" selected disabled>Select Semester</option>
                                    <?php foreach ($semesters as $semester): ?>
                                        <option value="<?php echo htmlspecialchars($semester); ?>"><?php echo htmlspecialchars($semester); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="send_notification" id="sendNotification">
                            <label class="form-check-label" for="sendNotification">
                                Send attendance notification to student
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Save Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK ATTENDANCE MODAL -->
    <div class="modal fade" id="bulkAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-msu-maroon">
                    <h5 class="modal-title text-white">Bulk Attendance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Take attendance for multiple students at once.</p>
                    <div class="mb-3">
                        <label class="form-label">Select Course</label>
                        <select class="form-select">
                            <option value="" selected disabled>Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['code']); ?>">
                                    <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Attendance Date</label>
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-text">
                        <small>You will be able to mark attendance for all students in the selected course.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-msu" type="button">Proceed to Mark</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this attendance record? This action cannot be undone.</p>
                    <p class="fw-bold" id="deleteAttendanceInfo"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="button" id="confirmDeleteAttendanceBtn">Delete Record</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterAttendance();
        });

        document.getElementById('statusFilter').addEventListener('change', filterAttendance);
        document.getElementById('semesterFilter').addEventListener('change', filterAttendance);
        document.getElementById('dateFilter').addEventListener('change', filterAttendance);

        function filterAttendance() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            
            const rows = document.querySelectorAll('#attendanceTable tbody tr');
            
            rows.forEach(row => {
                const studentId = row.cells[0].textContent.toLowerCase();
                const studentName = row.cells[1].textContent.toLowerCase();
                const course = row.cells[2].textContent.toLowerCase();
                const status = row.cells[8].textContent.toLowerCase();
                
                const matchesSearch = studentId.includes(searchText) || studentName.includes(searchText) || course.includes(searchText);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                
                // Note: Semester and Date filtering would require additional data attributes
                const matchesFilters = matchesStatus; // Add semester and date conditions here
                
                row.style.display = matchesSearch && matchesFilters ? '' : 'none';
            });
        }

        // Attendance management functions
        function editAttendance(attendanceId) {
            alert('Edit attendance ID: ' + attendanceId + '\nIn a real application, this would load attendance data into an edit modal.');
        }

        function viewAttendance(attendanceId) {
            const attendance = <?php echo json_encode($attendance_records); ?>.find(a => a.id == attendanceId);
            if (attendance) {
                alert(`Attendance Details:\n\nStudent: ${attendance.student_name} (${attendance.student_id})\nCourse: ${attendance.course_code} - ${attendance.course_name}\nDate: ${attendance.attendance_date} (${attendance.day})\nTime: ${attendance.time_in || 'N/A'} - ${attendance.time_out || 'N/A'}\nStatus: ${attendance.status}\nHours: ${attendance.hours_rendered}\nRemarks: ${attendance.remarks}\nInstructor: ${attendance.instructor}`);
            }
        }

        function markAttendance(attendanceId, status) {
            const statusText = status === 'present' ? 'present' : 'absent';
            if (confirm(`Are you sure you want to mark this attendance as ${statusText}?`)) {
                alert(`Attendance ${attendanceId} marked as ${statusText} successfully`);
                refreshTable();
            }
        }

        function deleteAttendance(attendanceId) {
            const attendance = <?php echo json_encode($attendance_records); ?>.find(a => a.id == attendanceId);
            if (attendance) {
                document.getElementById('deleteAttendanceInfo').textContent = 
                    `${attendance.student_name} - ${attendance.course_code} (${attendance.attendance_date})`;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
                deleteModal.show();
                
                document.getElementById('confirmDeleteAttendanceBtn').onclick = function() {
                    alert(`Attendance record ${attendanceId} deleted successfully`);
                    deleteModal.hide();
                    refreshTable();
                };
            }
        }

        function refreshTable() {
            alert('Refreshing attendance data...');
        }

        // Auto-calculate hours based on time in and time out
        document.getElementById('timeIn').addEventListener('change', calculateHours);
        document.getElementById('timeOut').addEventListener('change', calculateHours);

        function calculateHours() {
            const timeIn = document.getElementById('timeIn').value;
            const timeOut = document.getElementById('timeOut').value;
            
            if (timeIn && timeOut) {
                const start = new Date(`2000-01-01T${timeIn}`);
                const end = new Date(`2000-01-01T${timeOut}`);
                const diff = (end - start) / (1000 * 60 * 60); // Convert to hours
                
                if (diff > 0) {
                    document.querySelector('input[name="hours_rendered"]').value = diff.toFixed(1);
                }
            }
        }

        // Auto-set status based on time in
        document.getElementById('timeIn').addEventListener('change', function() {
            const timeIn = this.value;
            const statusSelect = document.getElementById('statusSelect');
            
            if (timeIn) {
                const [hours, minutes] = timeIn.split(':').map(Number);
                // If time in is after 8:00 AM, mark as late
                if (hours > 8 || (hours === 8 && minutes > 0)) {
                    statusSelect.value = 'late';
                } else {
                    statusSelect.value = 'present';
                }
            }
        });

        // Form submission handlers
        document.getElementById('addAttendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Attendance recorded successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAttendanceModal'));
            modal.hide();
            refreshTable();
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
// Helper functions for attendance styling
function getAttendanceClass($status) {
    switch ($status) {
        case 'present': return 'attendance-present';
        case 'absent': return 'attendance-absent';
        case 'late': return 'attendance-late';
        case 'excused': return 'attendance-excused';
        default: return '';
    }
}

function getProgressBarClass($status) {
    switch ($status) {
        case 'present': return 'progress-present';
        case 'late': return 'progress-late';
        case 'absent': return 'progress-absent';
        default: return '';
    }
}
?>