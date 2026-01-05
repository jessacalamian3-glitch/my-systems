<?php
// course_management.php - COMPLETE COURSE MANAGEMENT MODULE
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

// Sample course data (replace with database)
$courses = [
    [
        'id' => 1,
        'course_code' => 'IT101',
        'course_name' => 'Introduction to Information Technology',
        'department' => 'College of Computer Studies',
        'credits' => 3,
        'units' => 3,
        'year_level' => '1st Year',
        'semester' => '1st Semester',
        'instructor' => 'Dr. Maria Reyes',
        'schedule' => 'MWF 8:00-9:00 AM',
        'room' => 'CCS Lab 1',
        'enrolled_students' => 45,
        'max_students' => 50,
        'status' => 'active',
        'created_at' => '2024-01-15'
    ],
    [
        'id' => 2,
        'course_code' => 'CS201',
        'course_name' => 'Data Structures and Algorithms',
        'department' => 'College of Computer Studies',
        'credits' => 3,
        'units' => 3,
        'year_level' => '2nd Year',
        'semester' => '1st Semester',
        'instructor' => 'Prof. Robert Lim',
        'schedule' => 'TTH 1:00-2:30 PM',
        'room' => 'CCS Lab 2',
        'enrolled_students' => 38,
        'max_students' => 40,
        'status' => 'active',
        'created_at' => '2024-01-10'
    ],
    [
        'id' => 3,
        'course_code' => 'MATH101',
        'course_name' => 'College Algebra',
        'department' => 'College of Arts and Sciences',
        'credits' => 3,
        'units' => 3,
        'year_level' => '1st Year',
        'semester' => '1st Semester',
        'instructor' => 'Dr. James Wilson',
        'schedule' => 'MWF 10:00-11:00 AM',
        'room' => 'CAS Room 201',
        'enrolled_students' => 60,
        'max_students' => 60,
        'status' => 'active',
        'created_at' => '2024-01-12'
    ],
    [
        'id' => 4,
        'course_code' => 'BUS101',
        'course_name' => 'Principles of Management',
        'department' => 'College of Business',
        'credits' => 3,
        'units' => 3,
        'year_level' => '1st Year',
        'semester' => '2nd Semester',
        'instructor' => 'Prof. Anna Santos',
        'schedule' => 'TTH 9:00-10:30 AM',
        'room' => 'CB Room 101',
        'enrolled_students' => 35,
        'max_students' => 40,
        'status' => 'inactive',
        'created_at' => '2024-01-08'
    ],
    [
        'id' => 5,
        'course_code' => 'ENG101',
        'course_name' => 'Technical Writing',
        'department' => 'College of Education',
        'credits' => 3,
        'units' => 3,
        'year_level' => '1st Year',
        'semester' => '1st Semester',
        'instructor' => 'Dr. Lisa Garcia',
        'schedule' => 'MWF 2:00-3:00 PM',
        'room' => 'CED Room 301',
        'enrolled_students' => 42,
        'max_students' => 45,
        'status' => 'active',
        'created_at' => '2024-01-18'
    ]
];

// Sample departments and instructors for dropdowns
$departments = [
    'College of Computer Studies',
    'College of Education',
    'College of Business',
    'College of Engineering',
    'College of Arts and Sciences'
];

$instructors = [
    'Dr. Maria Reyes',
    'Prof. Robert Lim',
    'Dr. James Wilson',
    'Prof. Anna Santos',
    'Dr. Lisa Garcia',
    'Prof. Michael Tan'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Course Management - MSU Buug</title>
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
        
        /* Course Management Specific Styles */
        .table-msu th { background: #800000; color: white; }
        .action-buttons .btn { margin-right: 5px; }
        .badge-active { background: #28a745; }
        .badge-inactive { background: #6c757d; }
        .badge-full { background: #dc3545; }
        .search-filter-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 1.5rem; }
        
        /* Progress bar for enrollment */
        .enrollment-progress {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .enrollment-progress-bar {
            height: 100%;
            background: #800000;
            transition: width 0.3s ease;
        }

        /* View toggle buttons */
        .view-toggle .btn {
            border: 1px solid #dee2e6;
        }
        
        .view-toggle .btn.active {
            background: #800000;
            color: white;
            border-color: #800000;
        }

        /* Course card for grid view */
        .course-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        
        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .course-card-header {
            background: #800000;
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
        }
        
        .course-code {
            font-weight: bold;
            font-size: 1.1rem;
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
                <i class="fas fa-book me-2"></i>
                <span class="d-none d-sm-inline">MSU BUUG - Course Management</span>
                <span class="d-inline d-sm-none">Course Management</span>
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
                        <li><a class="dropdown-item text-danger" href="admin_login.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
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
            <a href="course_management.php" class="nav-link active"><i class="fas fa-book me-2"></i> Course Management</a>
            <a href="enrollment_management.php" class="nav-link"><i class="fas fa-clipboard-list me-2"></i> Enrollment</a>
            <a href="grades_management.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Grades</a>
 <a href="fees_management.php" class="nav-link"><i class="fas fa-cogs me-2"></i> Fees</a>
         
            <a href="fines_management.php" class="nav-link"><i class="fas fa-money-bill-wave me-2"></i> Fines</a>
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
                    <h2 class="h4">Course Management System</h2>
                    <p class="mb-1">Manage all courses, curriculum, and academic programs</p>
                    <small>Total Courses: <?php echo count($courses); ?> | Active: <?php echo count(array_filter($courses, function($course) { return $course['status'] === 'active'; })); ?></small>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="icon-circle" style="width:56px; height:56px; border-radius:50%; background:#5a0000; display:inline-flex; align-items:center; justify-content:center; color:#fff;">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-book-open"></i></div>
                    <h3 class="stats-number"><?php echo count($courses); ?></h3>
                    <p class="small">Total Courses</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-graduate"></i></div>
                    <h3 class="stats-number"><?php echo array_sum(array_column($courses, 'enrolled_students')); ?></h3>
                    <p class="small">Total Enrollments</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3 class="stats-number"><?php echo count(array_unique(array_column($courses, 'instructor'))); ?></h3>
                    <p class="small">Active Instructors</p>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div style="width:48px;height:48px;border-radius:50%;background:#800000;color:#fff;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-building"></i></div>
                    <h3 class="stats-number"><?php echo count($departments); ?></h3>
                    <p class="small">Departments</p>
                </div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="search-filter-card">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search courses..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="departmentFilter">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="semesterFilter">
                        <option value="">All Semesters</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn btn-msu w-100" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-1"></i> Add Course
                        </button>
                        <div class="btn-group view-toggle">
                            <button class="btn btn-outline-secondary active" id="tableViewBtn">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="cardViewBtn">
                                <i class="fas fa-grip"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div id="tableView" class="view-section">
            <div class="dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-msu mb-0">All Courses</h5>
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
                        <table class="table table-hover" id="coursesTable">
                            <thead class="table-msu">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Department</th>
                                    <th>Instructor</th>
                                    <th>Schedule</th>
                                    <th>Enrollment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr data-course-id="<?php echo $course['id']; ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($course['course_code']); ?></strong>
                                        <br><small class="text-muted"><?php echo $course['units']; ?> units</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                        <small class="text-muted"><?php echo $course['year_level']; ?> • <?php echo $course['semester']; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($course['department']); ?></td>
                                    <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($course['schedule']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($course['room']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <?php echo $course['enrolled_students']; ?>/<?php echo $course['max_students']; ?>
                                            </div>
                                            <div class="enrollment-progress" style="width: 80px;">
                                                <div class="enrollment-progress-bar" style="width: <?php echo ($course['enrolled_students'] / $course['max_students']) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $course['status']; ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-msu-sm" onclick="editCourse(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-info btn-sm" onclick="viewCourse(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm" onclick="manageEnrollment(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            <?php if ($course['status'] === 'active'): ?>
                                                <button class="btn btn-secondary btn-sm" onclick="toggleCourseStatus(<?php echo $course['id']; ?>, 'inactive')">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success btn-sm" onclick="toggleCourseStatus(<?php echo $course['id']; ?>, 'active')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-danger btn-sm" onclick="deleteCourse(<?php echo $course['id']; ?>)">
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
                    <nav aria-label="Course pagination">
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

        <!-- Card View -->
        <div id="cardView" class="view-section" style="display: none;">
            <div class="row">
                <?php foreach ($courses as $course): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="course-card">
                        <div class="course-card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                                    <small><?php echo $course['units']; ?> units • <?php echo $course['credits']; ?> credits</small>
                                </div>
                                <span class="badge <?php echo $course['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($course['course_name']); ?></h6>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($course['department']); ?>
                            </p>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-chalkboard-teacher me-1"></i><?php echo htmlspecialchars($course['instructor']); ?>
                            </p>
                            <p class="card-text small text-muted mb-2">
                                <i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($course['schedule']); ?>
                            </p>
                            <p class="card-text small text-muted mb-3">
                                <i class="fas fa-door-open me-1"></i><?php echo htmlspecialchars($course['room']); ?>
                            </p>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Enrollment</span>
                                    <span><?php echo $course['enrolled_students']; ?>/<?php echo $course['max_students']; ?></span>
                                </div>
                                <div class="enrollment-progress">
                                    <div class="enrollment-progress-bar" style="width: <?php echo ($course['enrolled_students'] / $course['max_students']) * 100; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-msu-sm" onclick="editCourse(<?php echo $course['id']; ?>)">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="manageEnrollment(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    <?php if ($course['status'] === 'active'): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="toggleCourseStatus(<?php echo $course['id']; ?>, 'inactive')">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="toggleCourseStatus(<?php echo $course['id']; ?>, 'active')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===================== MODALS ===================== -->

    <!-- ADD COURSE MODAL -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addCourseForm" method="POST">
                    <div class="modal-header bg-msu-maroon">
                        <h5 class="modal-title text-white">Add New Course</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Course Code</label>
                                <input type="text" class="form-control" name="course_code" placeholder="e.g. IT101" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Department</label>
                                <select class="form-select" name="department" required>
                                    <option value="" selected disabled>Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Course Name</label>
                            <input type="text" class="form-control" name="course_name" placeholder="e.g. Introduction to Information Technology" required>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Units</label>
                                <input type="number" class="form-control" name="units" min="1" max="6" value="3" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Credits</label>
                                <input type="number" class="form-control" name="credits" min="1" max="6" value="3" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Year Level</label>
                                <select class="form-select" name="year_level" required>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instructor</label>
                                <select class="form-select" name="instructor" required>
                                    <option value="" selected disabled>Select Instructor</option>
                                    <?php foreach ($instructors as $instructor): ?>
                                        <option value="<?php echo htmlspecialchars($instructor); ?>"><?php echo htmlspecialchars($instructor); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Max Students</label>
                                <input type="number" class="form-control" name="max_students" min="1" max="100" value="40" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Schedule</label>
                                <input type="text" class="form-control" name="schedule" placeholder="e.g. MWF 8:00-9:00 AM" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Room</label>
                                <input type="text" class="form-control" name="room" placeholder="e.g. CCS Lab 1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Course Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Enter course description..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prerequisites</label>
                            <input type="text" class="form-control" name="prerequisites" placeholder="e.g. IT100, MATH101 (optional)">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-msu" type="submit">Create Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this course? This action cannot be undone.</p>
                    <p class="fw-bold" id="deleteCourseName"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="button" id="confirmDeleteCourseBtn">Delete Course</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // View Toggle
        document.getElementById('tableViewBtn').addEventListener('click', function() {
            document.getElementById('tableView').style.display = 'block';
            document.getElementById('cardView').style.display = 'none';
            this.classList.add('active');
            document.getElementById('cardViewBtn').classList.remove('active');
        });

        document.getElementById('cardViewBtn').addEventListener('click', function() {
            document.getElementById('tableView').style.display = 'none';
            document.getElementById('cardView').style.display = 'block';
            this.classList.add('active');
            document.getElementById('tableViewBtn').classList.remove('active');
        });

        // Search and Filter functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            filterCourses();
        });

        document.getElementById('departmentFilter').addEventListener('change', filterCourses);
        document.getElementById('statusFilter').addEventListener('change', filterCourses);
        document.getElementById('semesterFilter').addEventListener('change', filterCourses);

        function filterCourses() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const semesterFilter = document.getElementById('semesterFilter').value;
            
            // Filter table view
            const tableRows = document.querySelectorAll('#coursesTable tbody tr');
            tableRows.forEach(row => {
                const code = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                const department = row.cells[2].textContent;
                const status = row.cells[6].textContent.toLowerCase();
                
                const matchesSearch = code.includes(searchText) || name.includes(searchText);
                const matchesDept = !departmentFilter || department === departmentFilter;
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                
                row.style.display = matchesSearch && matchesDept && matchesStatus ? '' : 'none';
            });

            // Filter card view
            const cards = document.querySelectorAll('#cardView .col-md-6');
            cards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                const cardDept = card.querySelector('.fa-building')?.parentElement?.textContent || '';
                const cardStatus = card.querySelector('.badge')?.textContent?.toLowerCase() || '';
                
                const matchesSearch = cardText.includes(searchText);
                const matchesDept = !departmentFilter || cardDept.includes(departmentFilter);
                const matchesStatus = !statusFilter || cardStatus.includes(statusFilter);
                
                card.style.display = matchesSearch && matchesDept && matchesStatus ? '' : 'none';
            });
        }

        // Course management functions
        function editCourse(courseId) {
            alert('Edit course ID: ' + courseId + '\nIn a real application, this would load course data into an edit modal.');
        }

        function viewCourse(courseId) {
            const course = <?php echo json_encode($courses); ?>.find(c => c.id == courseId);
            if (course) {
                alert(`Course Details:\n\nCode: ${course.course_code}\nName: ${course.course_name}\nDepartment: ${course.department}\nInstructor: ${course.instructor}\nSchedule: ${course.schedule}\nRoom: ${course.room}\nEnrollment: ${course.enrolled_students}/${course.max_students}`);
            }
        }

        function manageEnrollment(courseId) {
            const course = <?php echo json_encode($courses); ?>.find(c => c.id == courseId);
            if (course) {
                alert(`Manage Enrollment for:\n${course.course_code} - ${course.course_name}\n\nCurrent: ${course.enrolled_students} students\nCapacity: ${course.max_students} students`);
            }
        }

        function toggleCourseStatus(courseId, newStatus) {
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this course?`)) {
                alert(`Course ${courseId} status changed to ${newStatus}`);
                refreshTable();
            }
        }

        function deleteCourse(courseId) {
            const courseName = document.querySelector(`tr[data-course-id="${courseId}"] td:nth-child(2)`).textContent.trim();
            document.getElementById('deleteCourseName').textContent = courseName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteCourseModal'));
            deleteModal.show();
            
            document.getElementById('confirmDeleteCourseBtn').onclick = function() {
                alert(`Course ${courseId} deleted successfully`);
                deleteModal.hide();
                refreshTable();
            };
        }

        function refreshTable() {
            alert('Refreshing course data...');
        }

        // Form submission handlers
        document.getElementById('addCourseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Course added successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCourseModal'));
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