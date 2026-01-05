<?php
// faculty_analytics.php - FACULTY ANALYTICS & REPORTS PAGE
// ==================== SESSION FIXES ====================
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
session_set_cookie_params(7200);
session_start();
// ==================== END SESSION FIXES ====================

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Session check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'faculty') {
    header("Location: faculty_login.php");
    exit();
}

// ==================== DATABASE CLASS (SAME AS DASHBOARD) ====================
class Database {
    private $host = "localhost";
    private $db_name = "msubuug_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// ==================== GET FACULTY SETTINGS (THEME & LANGUAGE) ====================
function getFacultySettings($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        try {
            $query = "SELECT theme_color, language FROM faculty_settings WHERE faculty_id = :faculty_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':faculty_id', $faculty_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return [
                    'theme_color' => $result['theme_color'] ?? 'maroon',
                    'language' => $result['language'] ?? 'en'
                ];
            }
        } catch(PDOException $e) {
            error_log("Settings error: " . $e->getMessage());
        }
    }
    return ['theme_color' => 'maroon', 'language' => 'en'];
}

// ==================== THEME COLOR FUNCTION ====================
function getThemeColor($theme, $type = 'primary') {
    $colors = [
        'maroon' => ['primary' => '#800000', 'secondary' => '#5a0000', 'light' => '#a30000'],
        'blue' => ['primary' => '#007bff', 'secondary' => '#0056b3', 'light' => '#3399ff'],
        'green' => ['primary' => '#28a745', 'secondary' => '#1e7e34', 'light' => '#34ce57'],
        'purple' => ['primary' => '#6f42c1', 'secondary' => '#5a36a8', 'light' => '#8768d6'],
        'dark' => ['primary' => '#343a40', 'secondary' => '#23272b', 'light' => '#495057']
    ];
    
    return $colors[$theme][$type] ?? $colors['maroon'][$type];
}

// ==================== TRANSLATION FUNCTION ====================
function translateAnalytics($key) {
    $current_lang = $_SESSION['language'] ?? 'en';
    
    $translations = [
        'en' => [
            'analytics_reports' => 'Analytics & Reports',
            'dashboard' => 'Dashboard',
            'my_profile' => 'My Profile',
            'my_classes' => 'My Classes',
            'grade_management' => 'Grade Management',
            'assignments' => 'Assignments',
            'settings' => 'Settings',
            'logout' => 'Logout',
            'academic_year' => 'Academic Year',
            'semester' => 'Semester',
            'class_for_detailed_report' => 'Class (for detailed report)',
            'select_class' => 'Select Class...',
            'apply_filter' => 'Apply Filter',
            'summary' => 'Summary',
            'grade_distribution' => 'Grade Distribution',
            'at_risk_students' => 'At-Risk Students',
            'historical_trends' => 'Historical Trends',
            'by_course' => 'By Course',
            'print_report' => 'Print Report',
            'active_classes' => 'Active Classes',
            'total_students' => 'Total Students',
            'passing_rate' => 'Passing Rate',
            'failing_students' => 'Failing Students',
            'grade_overview' => 'Grade Overview',
            'average_grade' => 'Average Grade',
            'highest_grade' => 'Highest Grade',
            'lowest_grade' => 'Lowest Grade',
            'grade_variance' => 'Grade Variance',
            'average_class_size' => 'Average Class Size',
            'top_performing_classes' => 'Top Performing Classes',
            'course' => 'Course',
            'section' => 'Section',
            'students' => 'students',
            'status' => 'Status',
            'excellent' => 'Excellent',
            'good' => 'Good',
            'needs_attention' => 'Needs Attention',
            'none' => 'None',
            'no_data_available' => 'No data available',
            'export' => 'Export',
            'total' => 'Total',
            'pass_rate' => 'Pass Rate',
            'avg_grade' => 'Avg Grade',
            'no_grade_distribution_data' => 'No grade distribution data available for the selected filters.',
            'students_count' => 'Students',
            'current_grade' => 'Current Grade',
            'risk_level' => 'Risk Level',
            'points_needed' => 'Points Needed',
            'action' => 'Action',
            'notify' => 'Notify',
            'no_at_risk_students' => 'No at-risk students found! All students are performing well.',
            'last_3_years' => 'Last 3 Years',
            'failed_students' => 'Failed Students',
            'failure_rate' => 'Failure Rate',
            'no_historical_data' => 'No historical data available.',
            'class_detailed_report' => 'Class Detailed Report',
            'semester_display' => 'Semester',
            'prelim' => 'Prelim',
            'midterm' => 'Midterm',
            'final' => 'Final',
            'overall' => 'Overall',
            'final_grade' => 'Final Grade',
            'please_select_class' => 'Please select a class to view the detailed report.',
            'performance_by_course' => 'Performance by Course',
            'classes' => 'Classes',
            'excellent_90_plus' => 'Excellent (90+)',
            'failing_less_75' => 'Failing (<75)',
            'performance' => 'Performance',
            'needs_improvement' => 'Needs Improvement',
            'no_course_performance_data' => 'No course performance data available.',
            'first_semester' => 'First Semester',
            'second_semester' => 'Second Semester',
            'summer' => 'Summer'
        ],
        'fil' => [
            'analytics_reports' => 'Analytics at Mga Ulat',
            'dashboard' => 'Dashboard',
            'my_profile' => 'Aking Profile',
            'my_classes' => 'Aking mga Klase',
            'grade_management' => 'Pamamahala ng Marka',
            'assignments' => 'Mga Gawain',
            'settings' => 'Mga Setting',
            'logout' => 'Logout',
            'academic_year' => 'Taong Akademiko',
            'semester' => 'Semestre',
            'class_for_detailed_report' => 'Klase (para sa detalyadong ulat)',
            'select_class' => 'Pumili ng Klase...',
            'apply_filter' => 'Ilapat ang Filter',
            'summary' => 'Buod',
            'grade_distribution' => 'Distribusyon ng Marka',
            'at_risk_students' => 'Mga Estudyanteng Nanganganib',
            'historical_trends' => 'Makasaysayang Mga Trend',
            'by_course' => 'Ayon sa Kurso',
            'print_report' => 'I-print ang Ulat',
            'active_classes' => 'Aktibong mga Klase',
            'total_students' => 'Kabuuang mga Estudyante',
            'passing_rate' => 'Rate ng Pagpasa',
            'failing_students' => 'Mga Estudyanteng Bagsak',
            'grade_overview' => 'Pangkalahatang Marka',
            'average_grade' => 'Average na Marka',
            'highest_grade' => 'Pinakamataas na Marka',
            'lowest_grade' => 'Pinakamababang Marka',
            'grade_variance' => 'Pagkakaiba ng Marka',
            'average_class_size' => 'Average na Laki ng Klase',
            'top_performing_classes' => 'Nangungunang mga Klase',
            'course' => 'Kurso',
            'section' => 'Seksyon',
            'students' => 'mga estudyante',
            'status' => 'Katayuan',
            'excellent' => 'Napakahusay',
            'good' => 'Mahusay',
            'needs_attention' => 'Nangangailangan ng Pansin',
            'none' => 'Wala',
            'no_data_available' => 'Walang available na datos',
            'export' => 'I-export',
            'total' => 'Kabuuan',
            'pass_rate' => 'Rate ng Pagpasa',
            'avg_grade' => 'Avg na Marka',
            'no_grade_distribution_data' => 'Walang available na datos sa distribusyon ng marka para sa napiling filter.',
            'students_count' => 'mga Estudyante',
            'current_grade' => 'Kasalukuyang Marka',
            'risk_level' => 'Antas ng Panganib',
            'points_needed' => 'Mga Puntos na Kailangan',
            'action' => 'Aksyon',
            'notify' => 'Paalalahanan',
            'no_at_risk_students' => 'Walang nakitang mga estudyanteng nanganganib! Lahat ng estudyante ay maayos ang pagganap.',
            'last_3_years' => 'Huling 3 Taon',
            'failed_students' => 'Mga Estudyanteng Bagsak',
            'failure_rate' => 'Rate ng Pagkabagsak',
            'no_historical_data' => 'Walang available na makasaysayang datos.',
            'class_detailed_report' => 'Detalyadong Ulat ng Klase',
            'semester_display' => 'Semestre',
            'prelim' => 'Prelim',
            'midterm' => 'Midterm',
            'final' => 'Pinal',
            'overall' => 'Pangkalahatan',
            'final_grade' => 'Pinal na Marka',
            'please_select_class' => 'Pumili ng klase para makita ang detalyadong ulat.',
            'performance_by_course' => 'Pagganap Ayon sa Kurso',
            'classes' => 'Mga Klase',
            'excellent_90_plus' => 'Napakahusay (90+)',
            'failing_less_75' => 'Bagsak (<75)',
            'performance' => 'Pagganap',
            'needs_improvement' => 'Nangangailangan ng Pagpapabuti',
            'no_course_performance_data' => 'Walang available na datos sa pagganap ng kurso.',
            'first_semester' => 'Unang Semestre',
            'second_semester' => 'Ikalawang Semestre',
            'summer' => 'Tag-araw'
        ]
    ];
    
    return $translations[$current_lang][$key] ?? $translations['en'][$key] ?? $key;
}

// ==================== GET CURRENT THEME & LANGUAGE ====================
$faculty_id = $_SESSION['username'] ?? null;

// Get faculty settings for theme and language
$faculty_settings = getFacultySettings($faculty_id);

// Set theme and language in session if not set
if (!isset($_SESSION['theme_color'])) {
    $_SESSION['theme_color'] = $faculty_settings['theme_color'];
}
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = $faculty_settings['language'];
}

$current_theme = $_SESSION['theme_color'] ?? 'maroon';
$current_language = $_SESSION['language'] ?? 'en';

// ==================== ANALYTICS CLASS ====================
class FacultyAnalytics {
    private $db;
    private $faculty_id;
    
    public function __construct($db_connection, $faculty_id) {
        $this->db = $db_connection;
        $this->faculty_id = $faculty_id;
    }
    
    // [REST OF THE ANALYTICS CLASS REMAINS THE SAME]
    // 1. DASHBOARD SUMMARY WIDGETS
    public function getDashboardSummary($academic_year = '2024-2025', $semester = '1st') {
        return [
            'teaching_load' => $this->getTeachingLoadSummary($academic_year, $semester),
            'student_stats' => $this->getStudentStatistics($academic_year, $semester),
            'grade_overview' => $this->getGradeOverview($academic_year, $semester),
            'performance_summary' => $this->getPerformanceSummary($academic_year, $semester)
        ];
    }
    
    private function getTeachingLoadSummary($academic_year, $semester) {
        $sql = "SELECT 
                    COUNT(DISTINCT c.class_id) as total_classes,
                    COUNT(DISTINCT c.subject_id) as total_subjects,
                    SUM(c.current_enrollment) as total_students,
                    ROUND(AVG(c.current_enrollment), 1) as avg_class_size
                FROM classes c
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ? 
                AND c.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getStudentStatistics($academic_year, $semester) {
        $sql = "SELECT 
                    COUNT(DISTINCT e.student_id) as total_students,
                    SUM(CASE WHEN e.grade >= 75 THEN 1 ELSE 0 END) as passing,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as failing,
                    ROUND((SUM(CASE WHEN e.grade >= 75 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as passing_rate
                FROM enrollments e
                JOIN classes c ON e.class_id = c.class_id
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ?
                AND e.status = 'Active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getGradeOverview($academic_year, $semester) {
        $sql = "SELECT 
                    ROUND(MIN(e.grade), 1) as lowest_grade,
                    ROUND(MAX(e.grade), 1) as highest_grade,
                    ROUND(AVG(e.grade), 1) as average_grade,
                    ROUND(STDDEV(e.grade), 1) as grade_variance
                FROM enrollments e
                JOIN classes c ON e.class_id = c.class_id
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ?
                AND e.status = 'Active'
                AND e.grade IS NOT NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getPerformanceSummary($academic_year, $semester) {
        $sql = "SELECT 
                    s.course_code,
                    s.course_name,
                    c.section,
                    COUNT(e.enrollment_id) as total_students,
                    ROUND(AVG(e.grade), 2) as average_grade,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as at_risk_count
                FROM classes c
                JOIN courses s ON c.subject_id = s.course_id
                LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'Active'
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ?
                GROUP BY c.class_id
                ORDER BY average_grade DESC
                LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 2. GRADE DISTRIBUTION ANALYTICS
    public function getGradeDistribution($academic_year = '2024-2025', $semester = '1st') {
        $sql = "SELECT 
                    s.course_code,
                    s.course_name,
                    c.section,
                    COUNT(e.enrollment_id) as total_students,
                    SUM(CASE WHEN e.grade >= 90 THEN 1 ELSE 0 END) as excellent,
                    SUM(CASE WHEN e.grade >= 80 AND e.grade < 90 THEN 1 ELSE 0 END) as very_good,
                    SUM(CASE WHEN e.grade >= 75 AND e.grade < 80 THEN 1 ELSE 0 END) as satisfactory,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as failing,
                    ROUND(AVG(e.grade), 2) as class_average,
                    ROUND((SUM(CASE WHEN e.grade >= 75 THEN 1 ELSE 0 END) / COUNT(e.enrollment_id)) * 100, 1) as passing_rate
                FROM classes c
                JOIN courses s ON c.subject_id = s.course_id
                LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'Active'
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ?
                GROUP BY c.class_id, s.course_code, s.course_name, c.section
                ORDER BY s.course_code, c.section";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 3. AT-RISK STUDENTS REPORT
    public function getAtRiskStudents($academic_year = '2024-2025', $semester = '1st') {
        $sql = "SELECT 
                    st.student_id,
                    CONCAT(st.first_name, ' ', st.last_name) as student_name,
                    s.course_code,
                    s.course_name,
                    c.section,
                    e.grade as current_grade,
                    g.prelim_grade,
                    g.midterm_grade,
                    g.final_grade,
                    CASE 
                        WHEN e.grade < 75 THEN 'HIGH RISK - Failing'
                        WHEN e.grade < 80 THEN 'MEDIUM RISK - Below Average'
                        ELSE 'LOW RISK'
                    END as risk_level,
                    ROUND((75 - e.grade), 1) as points_needed_to_pass
                FROM enrollments e
                JOIN students st ON e.student_id = st.student_id
                JOIN classes c ON e.class_id = c.class_id
                JOIN courses s ON c.subject_id = s.course_id
                LEFT JOIN grades g ON e.student_id = g.student_id AND c.class_id = g.class_id
                WHERE c.faculty_id = ?
                AND c.academic_year = ?
                AND c.semester = ?
                AND e.status = 'Active'
                AND e.grade IS NOT NULL
                HAVING risk_level != 'LOW RISK'
                ORDER BY e.grade ASC
                LIMIT 30";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 4. HISTORICAL TRENDS
    public function getHistoricalPerformance($years_back = 3) {
        $current_year = date('Y');
        $start_year = $current_year - $years_back;
        
        $sql = "SELECT 
                    c.academic_year,
                    c.semester,
                    COUNT(DISTINCT e.student_id) as total_students,
                    ROUND(AVG(e.grade), 2) as average_grade,
                    MIN(e.grade) as lowest_grade,
                    MAX(e.grade) as highest_grade,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as failed_count,
                    ROUND((SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) / COUNT(e.enrollment_id)) * 100, 1) as failure_rate
                FROM classes c
                LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'Active'
                WHERE c.faculty_id = ?
                AND c.academic_year REGEXP '^[0-9]{4}'
                AND CAST(SUBSTRING(c.academic_year, 1, 4) AS UNSIGNED) >= ?
                GROUP BY c.academic_year, c.semester
                ORDER BY c.academic_year DESC, 
                         FIELD(c.semester, '1st', '2nd', 'Summer')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $start_year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 5. CLASS DETAILED REPORT
    public function getClassDetailedReport($class_id) {
        $sql = "SELECT 
                    s.course_code,
                    s.course_name,
                    c.section,
                    c.academic_year,
                    c.semester,
                    COUNT(e.enrollment_id) as total_students,
                    ROUND(AVG(e.grade), 2) as class_average,
                    SUM(CASE WHEN e.grade >= 75 THEN 1 ELSE 0 END) as passing,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as failing,
                    ROUND((SUM(CASE WHEN e.grade >= 75 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as passing_rate
                FROM classes c
                JOIN courses s ON c.subject_id = s.course_id
                LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'Active'
                WHERE c.class_id = ?
                AND c.faculty_id = ?
                GROUP BY c.class_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$class_id, $this->faculty_id]);
        $class_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($class_info) {
            $sql_details = "SELECT 
                                st.student_id,
                                CONCAT(st.first_name, ' ', st.last_name) as student_name,
                                e.grade as final_grade,
                                g.prelim_grade,
                                g.midterm_grade,
                                g.final_grade,
                                g.overall_grade,
                                CASE 
                                    WHEN e.grade >= 75 THEN 'Passed'
                                    ELSE 'Failed'
                                END as status
                            FROM enrollments e
                            JOIN students st ON e.student_id = st.student_id
                            LEFT JOIN grades g ON e.student_id = g.student_id AND e.class_id = g.class_id
                            WHERE e.class_id = ?
                            AND e.status = 'Active'
                            ORDER BY st.last_name, st.first_name";
            
            $stmt_details = $this->db->prepare($sql_details);
            $stmt_details->execute([$class_id]);
            $students = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
            
            $class_info['students'] = $students;
        }
        
        return $class_info;
    }
    
    // 6. GET AVAILABLE CLASSES FOR FILTERING
    public function getAvailableClasses($academic_year = '2024-2025', $semester = '1st') {
        $sql = "SELECT 
                    c.class_id,
                    s.course_code,
                    s.course_name,
                    c.section,
                    c.current_enrollment
                FROM classes c
                JOIN courses s ON c.subject_id = s.course_id
                WHERE c.faculty_id = ?
                AND c.academic_year = ?
                AND c.semester = ?
                AND c.status = 'active'
                ORDER BY s.course_code, c.section";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 7. GET PERFORMANCE BY COURSE
    public function getPerformanceByCourse($academic_year = '2024-2025', $semester = '1st') {
        $sql = "SELECT 
                    s.course_code,
                    s.course_name,
                    COUNT(DISTINCT c.class_id) as total_classes,
                    COUNT(DISTINCT e.student_id) as total_students,
                    ROUND(AVG(e.grade), 2) as average_grade,
                    SUM(CASE WHEN e.grade >= 90 THEN 1 ELSE 0 END) as excellent_count,
                    SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) as failing_count,
                    ROUND((SUM(CASE WHEN e.grade < 75 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as failure_rate
                FROM classes c
                JOIN courses s ON c.subject_id = s.course_id
                LEFT JOIN enrollments e ON c.class_id = e.class_id AND e.status = 'Active'
                WHERE c.faculty_id = ? 
                AND c.academic_year = ? 
                AND c.semester = ?
                GROUP BY s.course_id, s.course_code, s.course_name
                ORDER BY average_grade DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->faculty_id, $academic_year, $semester]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ==================== MAIN ANALYTICS LOGIC ====================
$faculty_id = $_SESSION['username'] ?? null;
$database = new Database();
$db = $database->getConnection();
$analytics = new FacultyAnalytics($db, $faculty_id);

// Get filter parameters
$academic_year = $_GET['academic_year'] ?? '2024-2025';
$semester = $_GET['semester'] ?? '1st';
$report_type = $_GET['report'] ?? 'summary';
$class_id = $_GET['class_id'] ?? null;

// Get data based on report type
$dashboard_data = $analytics->getDashboardSummary($academic_year, $semester);
$grade_distribution = [];
$at_risk_students = [];
$historical_data = [];
$class_report = [];
$performance_by_course = [];
$available_classes = $analytics->getAvailableClasses($academic_year, $semester);

switch ($report_type) {
    case 'grade-distribution':
        $grade_distribution = $analytics->getGradeDistribution($academic_year, $semester);
        break;
    case 'at-risk':
        $at_risk_students = $analytics->getAtRiskStudents($academic_year, $semester);
        break;
    case 'historical':
        $historical_data = $analytics->getHistoricalPerformance(3);
        break;
    case 'class-report':
        if ($class_id) {
            $class_report = $analytics->getClassDetailedReport($class_id);
        }
        break;
    case 'performance-by-course':
        $performance_by_course = $analytics->getPerformanceByCourse($academic_year, $semester);
        break;
}

// ==================== HELPER FUNCTIONS ====================
function getDisplayName($info) {
    return $_SESSION['user_info']['name'] ?? 'Faculty Member';
}

function getFirstLetter($info) {
    $name = $_SESSION['user_info']['first_name'] ?? 'F';
    return !empty($name) ? strtoupper(substr($name, 0, 1)) : 'F';
}

function formatPercentage($value) {
    return number_format($value, 1) . '%';
}

function getRiskBadge($risk_level) {
    if (strpos($risk_level, 'HIGH') !== false) {
        return '<span class="badge bg-danger">' . translateAnalytics('high_risk') . '</span>';
    } elseif (strpos($risk_level, 'MEDIUM') !== false) {
        return '<span class="badge bg-warning text-dark">' . translateAnalytics('medium_risk') . '</span>';
    } else {
        return '<span class="badge bg-success">' . translateAnalytics('low_risk') . '</span>';
    }
}

function getStatusBadge($grade) {
    if ($grade >= 75) {
        return '<span class="badge bg-success">' . translateAnalytics('passed') . '</span>';
    } else {
        return '<span class="badge bg-danger">' . translateAnalytics('failed') . '</span>';
    }
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $current_language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translateAnalytics('analytics_reports'); ?> - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --maroon: <?php echo getThemeColor($current_theme, 'primary'); ?>;
            --maroon-dark: <?php echo getThemeColor($current_theme, 'secondary'); ?>;
            --maroon-light: <?php echo getThemeColor($current_theme, 'light'); ?>;
            --gold: #FFD700;
            --light-bg: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 12px 0;
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            background: linear-gradient(45deg, #FFD700, #FFED4E);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--maroon-dark) 0%, var(--maroon) 100%);
            min-height: 100vh;
            position: fixed;
            width: 280px;
            top: 70px;
            left: 0;
            bottom: 0;
            z-index: 1020;
            overflow-y: auto;
            box-shadow: 5px 0 25px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
            width: calc(100% - 280px);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 18px 25px;
            border-left: 4px solid transparent;
            margin: 8px 15px;
            border-radius: 12px;
            transition: all 0.4s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: linear-gradient(135deg, rgba(255,215,0,0.15) 0%, rgba(255,255,255,0.1) 100%);
            color: var(--gold);
            border-left-color: var(--gold);
            transform: translateX(8px) scale(1.02);
            box-shadow: 0 5px 20px rgba(255,215,0,0.2);
        }
        
        .sidebar .nav-link i {
            font-size: 1.2rem;
            width: 30px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.2);
        }
        
        /* ANALYTICS CARD STYLES */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            border: none;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .card-header .btn-group {
            float: right;
        }
        
        /* STATS CARDS */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .stats-icon.blue { background: linear-gradient(135deg, #4e73df, #2e59d9); color: white; }
        .stats-icon.green { background: linear-gradient(135deg, #1cc88a, #17a673); color: white; }
        .stats-icon.yellow { background: linear-gradient(135deg, #f6c23e, #f4b619); color: white; }
        .stats-icon.red { background: linear-gradient(135deg, #e74a3b, #d52a1e); color: white; }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--maroon);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* FILTER PANEL */
        .filter-panel {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid #eaeaea;
        }
        
        /* DATA TABLE STYLES */
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 2px solid #dee2e6;
            color: var(--maroon);
            font-weight: 600;
            text-align: left;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .data-table .badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        /* CHART CONTAINER */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            padding: 20px;
        }
        
        /* REPORTS NAV */
        .reports-nav {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .reports-nav .nav-link {
            color: #666;
            padding: 12px 20px;
            border-radius: 10px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .reports-nav .nav-link:hover,
        .reports-nav .nav-link.active {
            background: linear-gradient(135deg, var(--maroon), var(--maroon-dark));
            color: white;
            box-shadow: 0 5px 15px rgba(128,0,0,0.2);
        }
        
        /* GRADE DISTRIBUTION BARS */
        .grade-bar {
            height: 30px;
            border-radius: 15px;
            background: linear-gradient(90deg, #e74a3b, #f6c23e, #1cc88a, #4e73df);
            margin: 10px 0;
            position: relative;
            overflow: hidden;
        }
        
        .grade-segment {
            height: 100%;
            float: left;
            transition: all 0.3s ease;
        }
        
        .grade-segment:hover {
            opacity: 0.9;
            transform: scaleY(1.1);
        }
        
        /* USER AVATAR */
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, var(--gold), #ffed4e);
            color: var(--maroon);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            border-color: var(--gold);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .letter-avatar {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
        }
        
        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.4s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .chart-container {
                height: 250px;
            }
        }
        
        @media (max-width: 768px) {
            .stats-card {
                padding: 20px;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
            
            .data-table {
                font-size: 0.9rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px;
            }
        }
        
        @media (max-width: 576px) {
            .filter-panel {
                padding: 20px;
            }
            
            .reports-nav .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
        
        /* PRINT STYLES */
        @media print {
            .sidebar,
            .navbar,
            .filter-panel,
            .reports-nav,
            .btn {
                display: none !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 20px !important;
                width: 100% !important;
            }
            
            .dashboard-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar (SAME AS DASHBOARD) -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="mobile-menu-toggle me-3" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="faculty_dashboard.php">
                <i class="fas fa-chart-bar me-2"></i>
                MSU BUUG - <?php echo translateAnalytics('analytics_reports'); ?>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <div class="me-3 text-white d-none d-md-block text-end">
                    <div><strong><?php echo htmlspecialchars($_SESSION['user_info']['name'] ?? 'Faculty Member'); ?></strong></div>
                    <small><?php echo htmlspecialchars($_SESSION['user_info']['position'] ?? 'Faculty'); ?></small>
                </div>
                <div class="dropdown">
                    <a class="text-white text-decoration-none d-flex align-items-center" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- PROFILE PICTURE IN NAVIGATION -->
                        <div class="user-avatar me-2">
                            <?php 
                            $profile_pic_path = $_SESSION['user_info']['profile_picture'] ?? null;
                            $first_letter = getFirstLetter($_SESSION['user_info']);
                            ?>
                            
                            <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                                <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" 
                                      alt="Profile">
                            <?php else: ?>
                                <div class="letter-avatar d-flex align-items-center justify-content-center w-100 h-100">
                                    <?php echo $first_letter; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-chevron-down fa-xs ms-1"></i>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="faculty_dashboard.php"><i class="fas fa-tachometer-alt me-2 text-maroon"></i><?php echo translateAnalytics('dashboard'); ?></a></li>
                        <li><a class="dropdown-item" href="faculty_profile.php"><i class="fas fa-user-circle me-2 text-maroon"></i><?php echo translateAnalytics('my_profile'); ?></a></li>
                        <li><a class="dropdown-item active" href="faculty_analytics.php"><i class="fas fa-chart-bar me-2 text-maroon"></i><?php echo translateAnalytics('analytics_reports'); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="logout_faculty.php">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo translateAnalytics('logout'); ?>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="mobile-user-info d-lg-none p-4 border-bottom">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">
                    <?php 
                    $profile_pic_path = $_SESSION['user_info']['profile_picture'] ?? null;
                    $first_letter = getFirstLetter($_SESSION['user_info']);
                    ?>
                    
                    <?php if ($profile_pic_path && file_exists($profile_pic_path)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic_path); ?>" alt="Profile">
                    <?php else: ?>
                        <div class="letter-avatar d-flex align-items-center justify-content-center w-100 h-100">
                            <?php echo $first_letter; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <strong class="text-white"><?php echo htmlspecialchars($_SESSION['user_info']['name'] ?? 'Faculty'); ?></strong><br>
                    <small class="text-gold"><?php echo htmlspecialchars($_SESSION['user_info']['position'] ?? 'Faculty'); ?></small>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column pt-4">
            <a href="faculty_dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt me-3"></i> <?php echo translateAnalytics('dashboard'); ?>
            </a>
            <a href="my_classes.php" class="nav-link">
                <i class="fas fa-book me-3"></i> <?php echo translateAnalytics('my_classes'); ?>
            </a>
            <a href="my_grades.php" class="nav-link">
                <i class="fas fa-chart-line me-3"></i> <?php echo translateAnalytics('grade_management'); ?>
            </a>
            <a href="my_assignments.php" class="nav-link">
                <i class="fas fa-tasks me-3"></i> <?php echo translateAnalytics('assignments'); ?>
            </a>
            <a href="faculty_analytics.php" class="nav-link active">
                <i class="fas fa-chart-bar me-3"></i> <?php echo translateAnalytics('analytics_reports'); ?>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- FILTER PANEL -->
        <div class="filter-panel">
            <form method="GET" action="faculty_analytics.php" class="row g-3 align-items-end">
                <input type="hidden" name="report" value="<?php echo htmlspecialchars($report_type); ?>">
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo translateAnalytics('academic_year'); ?></label>
                    <select name="academic_year" class="form-select">
                        <?php
                        $current_year = date('Y');
                        for ($i = $current_year - 2; $i <= $current_year + 1; $i++):
                            $year_option = $i . '-' . ($i + 1);
                            $selected = ($year_option == $academic_year) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $year_option; ?>" <?php echo $selected; ?>>
                                <?php echo $year_option; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label"><?php echo translateAnalytics('semester'); ?></label>
                    <select name="semester" class="form-select">
                        <option value="1st" <?php echo ($semester == '1st') ? 'selected' : ''; ?>><?php echo translateAnalytics('first_semester'); ?></option>
                        <option value="2nd" <?php echo ($semester == '2nd') ? 'selected' : ''; ?>><?php echo translateAnalytics('second_semester'); ?></option>
                        <option value="Summer" <?php echo ($semester == 'Summer') ? 'selected' : ''; ?>><?php echo translateAnalytics('summer'); ?></option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label"><?php echo translateAnalytics('class_for_detailed_report'); ?></label>
                    <select name="class_id" class="form-select">
                        <option value=""><?php echo translateAnalytics('select_class'); ?></option>
                        <?php foreach ($available_classes as $class): ?>
                            <option value="<?php echo $class['class_id']; ?>" 
                                <?php echo ($class_id == $class['class_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['course_code'] . ' - ' . $class['section'] . ' (' . $class['current_enrollment'] . ' ' . strtolower(translateAnalytics('students')) . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-maroon w-100">
                        <i class="fas fa-filter me-1"></i> <?php echo translateAnalytics('apply_filter'); ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- REPORTS NAVIGATION -->
        <div class="reports-nav">
            <div class="row">
                <div class="col">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($report_type == 'summary') ? 'active' : ''; ?>" 
                               href="?report=summary&academic_year=<?php echo $academic_year; ?>&semester=<?php echo $semester; ?>">
                                <i class="fas fa-tachometer-alt me-2"></i> <?php echo translateAnalytics('summary'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($report_type == 'grade-distribution') ? 'active' : ''; ?>" 
                               href="?report=grade-distribution&academic_year=<?php echo $academic_year; ?>&semester=<?php echo $semester; ?>">
                                <i class="fas fa-chart-pie me-2"></i> <?php echo translateAnalytics('grade_distribution'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($report_type == 'at-risk') ? 'active' : ''; ?>" 
                               href="?report=at-risk&academic_year=<?php echo $academic_year; ?>&semester=<?php echo $semester; ?>">
                                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo translateAnalytics('at_risk_students'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($report_type == 'historical') ? 'active' : ''; ?>" 
                               href="?report=historical&academic_year=<?php echo $academic_year; ?>&semester=<?php echo $semester; ?>">
                                <i class="fas fa-chart-line me-2"></i> <?php echo translateAnalytics('historical_trends'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($report_type == 'performance-by-course') ? 'active' : ''; ?>" 
                               href="?report=performance-by-course&academic_year=<?php echo $academic_year; ?>&semester=<?php echo $semester; ?>">
                                <i class="fas fa-book me-2"></i> <?php echo translateAnalytics('by_course'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    <button onclick="window.print()" class="btn btn-outline-maroon">
                        <i class="fas fa-print me-1"></i> <?php echo translateAnalytics('print_report'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php if ($report_type == 'summary'): ?>
            <!-- DASHBOARD SUMMARY -->
            <div class="row mb-4">
                <!-- STATS CARDS -->
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-icon blue">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stats-number"><?php echo $dashboard_data['teaching_load']['total_classes'] ?? 0; ?></div>
                        <div class="stats-label"><?php echo translateAnalytics('active_classes'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-number"><?php echo $dashboard_data['student_stats']['total_students'] ?? 0; ?></div>
                        <div class="stats-label"><?php echo translateAnalytics('total_students'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-icon yellow">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stats-number"><?php echo formatPercentage($dashboard_data['student_stats']['passing_rate'] ?? 0); ?></div>
                        <div class="stats-label"><?php echo translateAnalytics('passing_rate'); ?></div>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="stats-icon red">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="stats-number"><?php echo $dashboard_data['student_stats']['failing'] ?? 0; ?></div>
                        <div class="stats-label"><?php echo translateAnalytics('failing_students'); ?></div>
                    </div>
                </div>
            </div>

            <!-- GRADE OVERVIEW -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i><?php echo translateAnalytics('grade_overview'); ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container">
                                <canvas id="gradeOverviewChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong><?php echo translateAnalytics('average_grade'); ?></strong></td>
                                        <td class="text-end"><?php echo $dashboard_data['grade_overview']['average_grade'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo translateAnalytics('highest_grade'); ?></strong></td>
                                        <td class="text-end"><?php echo $dashboard_data['grade_overview']['highest_grade'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo translateAnalytics('lowest_grade'); ?></strong></td>
                                        <td class="text-end"><?php echo $dashboard_data['grade_overview']['lowest_grade'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo translateAnalytics('grade_variance'); ?></strong></td>
                                        <td class="text-end"><?php echo $dashboard_data['grade_overview']['grade_variance'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php echo translateAnalytics('average_class_size'); ?></strong></td>
                                        <td class="text-end"><?php echo $dashboard_data['teaching_load']['avg_class_size'] ?? 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOP PERFORMING CLASSES -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-trophy me-2"></i><?php echo translateAnalytics('top_performing_classes'); ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><?php echo translateAnalytics('course'); ?></th>
                                    <th><?php echo translateAnalytics('section'); ?></th>
                                    <th><?php echo translateAnalytics('total_students'); ?></th>
                                    <th><?php echo translateAnalytics('average_grade'); ?></th>
                                    <th><?php echo translateAnalytics('at_risk_students'); ?></th>
                                    <th><?php echo translateAnalytics('status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($dashboard_data['performance_summary'])): ?>
                                    <?php foreach ($dashboard_data['performance_summary'] as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['course_code'] . ' - ' . $class['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($class['section']); ?></td>
                                            <td><?php echo $class['total_students']; ?></td>
                                            <td><strong><?php echo $class['average_grade']; ?></strong></td>
                                            <td>
                                                <?php if ($class['at_risk_count'] > 0): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo $class['at_risk_count']; ?> <?php echo translateAnalytics('students'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo translateAnalytics('none'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($class['average_grade'] >= 85): ?>
                                                    <span class="badge bg-success"><?php echo translateAnalytics('excellent'); ?></span>
                                                <?php elseif ($class['average_grade'] >= 75): ?>
                                                    <span class="badge bg-info"><?php echo translateAnalytics('good'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?php echo translateAnalytics('needs_attention'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center"><?php echo translateAnalytics('no_data_available'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($report_type == 'grade-distribution'): ?>
            <!-- GRADE DISTRIBUTION REPORT -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i><?php echo translateAnalytics('grade_distribution'); ?>
                    <div class="btn-group float-end">
                        <button onclick="downloadChart()" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-download me-1"></i> <?php echo translateAnalytics('export'); ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($grade_distribution)): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container">
                                    <canvas id="gradeDistributionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo translateAnalytics('course'); ?></th>
                                                <th><?php echo translateAnalytics('section'); ?></th>
                                                <th><?php echo translateAnalytics('total'); ?></th>
                                                <th><?php echo translateAnalytics('pass_rate'); ?></th>
                                                <th><?php echo translateAnalytics('avg_grade'); ?></th>
                                                <th><?php echo translateAnalytics('failing'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grade_distribution as $distribution): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($distribution['course_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($distribution['section']); ?></td>
                                                    <td><?php echo $distribution['total_students']; ?></td>
                                                    <td><?php echo formatPercentage($distribution['passing_rate']); ?></td>
                                                    <td><strong><?php echo $distribution['class_average']; ?></strong></td>
                                                    <td>
                                                        <?php if ($distribution['failing'] > 0): ?>
                                                            <span class="badge bg-danger"><?php echo $distribution['failing']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">0</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> <?php echo translateAnalytics('no_grade_distribution_data'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($report_type == 'at-risk'): ?>
            <!-- AT-RISK STUDENTS REPORT -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo translateAnalytics('at_risk_students'); ?>
                    <span class="badge bg-danger float-end"><?php echo count($at_risk_students); ?> <?php echo translateAnalytics('students_count'); ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($at_risk_students)): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th><?php echo translateAnalytics('student_id'); ?></th>
                                        <th><?php echo translateAnalytics('student_name'); ?></th>
                                        <th><?php echo translateAnalytics('course'); ?></th>
                                        <th><?php echo translateAnalytics('section'); ?></th>
                                        <th><?php echo translateAnalytics('current_grade'); ?></th>
                                        <th><?php echo translateAnalytics('risk_level'); ?></th>
                                        <th><?php echo translateAnalytics('points_needed'); ?></th>
                                        <th><?php echo translateAnalytics('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($at_risk_students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($student['section']); ?></td>
                                            <td><strong><?php echo $student['current_grade']; ?></strong></td>
                                            <td><?php echo getRiskBadge($student['risk_level']); ?></td>
                                            <td>
                                                <?php if ($student['current_grade'] < 75): ?>
                                                    <span class="text-danger"><?php echo $student['points_needed_to_pass']; ?> points</span>
                                                <?php else: ?>
                                                    <span class="text-success">Passing</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-maroon">
                                                    <i class="fas fa-envelope me-1"></i> <?php echo translateAnalytics('notify'); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> <?php echo translateAnalytics('no_at_risk_students'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($report_type == 'historical'): ?>
            <!-- HISTORICAL TRENDS -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i><?php echo translateAnalytics('historical_trends'); ?>
                    <span class="badge bg-info float-end"><?php echo translateAnalytics('last_3_years'); ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($historical_data)): ?>
                        <div class="chart-container">
                            <canvas id="historicalTrendsChart"></canvas>
                        </div>
                        <div class="table-responsive mt-4">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th><?php echo translateAnalytics('academic_year'); ?></th>
                                        <th><?php echo translateAnalytics('semester_display'); ?></th>
                                        <th><?php echo translateAnalytics('total_students'); ?></th>
                                        <th><?php echo translateAnalytics('average_grade'); ?></th>
                                        <th><?php echo translateAnalytics('lowest_grade'); ?></th>
                                        <th><?php echo translateAnalytics('highest_grade'); ?></th>
                                        <th><?php echo translateAnalytics('failed_students'); ?></th>
                                        <th><?php echo translateAnalytics('failure_rate'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historical_data as $history): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($history['academic_year']); ?></td>
                                            <td><?php echo htmlspecialchars($history['semester']); ?></td>
                                            <td><?php echo $history['total_students']; ?></td>
                                            <td><strong><?php echo $history['average_grade']; ?></strong></td>
                                            <td><?php echo $history['lowest_grade']; ?></td>
                                            <td><?php echo $history['highest_grade']; ?></td>
                                            <td><?php echo $history['failed_count']; ?></td>
                                            <td>
                                                <?php if ($history['failure_rate'] > 20): ?>
                                                    <span class="badge bg-danger"><?php echo formatPercentage($history['failure_rate']); ?></span>
                                                <?php elseif ($history['failure_rate'] > 10): ?>
                                                    <span class="badge bg-warning text-dark"><?php echo formatPercentage($history['failure_rate']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo formatPercentage($history['failure_rate']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> <?php echo translateAnalytics('no_historical_data'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($report_type == 'class-report' && $class_id): ?>
            <!-- CLASS DETAILED REPORT -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-file-alt me-2"></i><?php echo translateAnalytics('class_detailed_report'); ?>
                    <button onclick="printClassReport()" class="btn btn-sm btn-outline-light float-end">
                        <i class="fas fa-print me-1"></i> <?php echo translateAnalytics('print_report'); ?>
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($class_report)): ?>
                        <!-- CLASS SUMMARY -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4><?php echo htmlspecialchars($class_report['course_code'] . ' - ' . $class_report['course_name']); ?></h4>
                                <p class="text-muted">
                                    <?php echo htmlspecialchars($class_report['section'] . ' | ' . $class_report['academic_year'] . ' - ' . $class_report['semester'] . ' ' . translateAnalytics('semester_display')); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="row text-center">
                                    <div class="col">
                                        <div class="stats-number"><?php echo $class_report['total_students']; ?></div>
                                        <div class="stats-label"><?php echo translateAnalytics('total_students'); ?></div>
                                    </div>
                                    <div class="col">
                                        <div class="stats-number"><?php echo $class_report['class_average']; ?></div>
                                        <div class="stats-label"><?php echo translateAnalytics('average_grade'); ?></div>
                                    </div>
                                    <div class="col">
                                        <div class="stats-number"><?php echo formatPercentage($class_report['passing_rate']); ?></div>
                                        <div class="stats-label"><?php echo translateAnalytics('passing_rate'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STUDENT LIST -->
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th><?php echo translateAnalytics('student_id'); ?></th>
                                        <th><?php echo translateAnalytics('student_name'); ?></th>
                                        <th><?php echo translateAnalytics('prelim'); ?></th>
                                        <th><?php echo translateAnalytics('midterm'); ?></th>
                                        <th><?php echo translateAnalytics('final'); ?></th>
                                        <th><?php echo translateAnalytics('overall'); ?></th>
                                        <th><?php echo translateAnalytics('final_grade'); ?></th>
                                        <th><?php echo translateAnalytics('status'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($class_report['students'])): ?>
                                        <?php foreach ($class_report['students'] as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                                <td><?php echo $student['prelim_grade'] ?? 'N/A'; ?></td>
                                                <td><?php echo $student['midterm_grade'] ?? 'N/A'; ?></td>
                                                <td><?php echo $student['final_grade'] ?? 'N/A'; ?></td>
                                                <td><?php echo $student['overall_grade'] ?? 'N/A'; ?></td>
                                                <td><strong><?php echo $student['final_grade']; ?></strong></td>
                                                <td><?php echo getStatusBadge($student['final_grade']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No students enrolled in this class</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo translateAnalytics('please_select_class'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($report_type == 'performance-by-course'): ?>
            <!-- PERFORMANCE BY COURSE -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-book me-2"></i><?php echo translateAnalytics('performance_by_course'); ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($performance_by_course)): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th><?php echo translateAnalytics('course_code'); ?></th>
                                        <th><?php echo translateAnalytics('course_name'); ?></th>
                                        <th><?php echo translateAnalytics('classes'); ?></th>
                                        <th><?php echo translateAnalytics('students'); ?></th>
                                        <th><?php echo translateAnalytics('average_grade'); ?></th>
                                        <th><?php echo translateAnalytics('excellent_90_plus'); ?></th>
                                        <th><?php echo translateAnalytics('failing_less_75'); ?></th>
                                        <th><?php echo translateAnalytics('failure_rate'); ?></th>
                                        <th><?php echo translateAnalytics('performance'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($performance_by_course as $course): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                            <td><?php echo $course['total_classes']; ?></td>
                                            <td><?php echo $course['total_students']; ?></td>
                                            <td><strong><?php echo $course['average_grade']; ?></strong></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $course['excellent_count']; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($course['failing_count'] > 0): ?>
                                                    <span class="badge bg-danger"><?php echo $course['failing_count']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($course['failure_rate'] > 20): ?>
                                                    <span class="text-danger"><?php echo formatPercentage($course['failure_rate']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-success"><?php echo formatPercentage($course['failure_rate']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($course['average_grade'] >= 85): ?>
                                                    <span class="badge bg-success"><?php echo translateAnalytics('excellent'); ?></span>
                                                <?php elseif ($course['average_grade'] >= 75): ?>
                                                    <span class="badge bg-info"><?php echo translateAnalytics('good'); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?php echo translateAnalytics('needs_improvement'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> <?php echo translateAnalytics('no_course_performance_data'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth <= 992 && 
                sidebar.classList.contains('active') &&
                !sidebar.contains(event.target) &&
                !mobileToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // GRADE OVERVIEW CHART
        <?php if ($report_type == 'summary' && isset($dashboard_data['performance_summary'])): ?>
        const gradeOverviewCtx = document.getElementById('gradeOverviewChart').getContext('2d');
        const gradeOverviewChart = new Chart(gradeOverviewCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($dashboard_data['performance_summary'] as $class): ?>
                        '<?php echo $class['course_code'] . ' - ' . $class['section']; ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: '<?php echo translateAnalytics('average_grade'); ?>',
                    data: [
                        <?php foreach ($dashboard_data['performance_summary'] as $class): ?>
                            <?php echo $class['average_grade']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(128, 0, 0, 0.7)',
                    borderColor: 'rgba(128, 0, 0, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }, {
                    label: '<?php echo translateAnalytics('at_risk_students'); ?>',
                    data: [
                        <?php foreach ($dashboard_data['performance_summary'] as $class): ?>
                            <?php echo $class['at_risk_count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2,
                    borderRadius: 5,
                    type: 'line',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '<?php echo translateAnalytics('average_grade'); ?>'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '<?php echo translateAnalytics('at_risk_students'); ?>'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
        <?php endif; ?>

        // GRADE DISTRIBUTION CHART
        <?php if ($report_type == 'grade-distribution' && !empty($grade_distribution)): ?>
        const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        const gradeDistributionChart = new Chart(gradeDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['<?php echo translateAnalytics('excellent'); ?> (90+)', '<?php echo translateAnalytics('good'); ?> (80-89)', '<?php echo translateAnalytics('satisfactory'); ?> (75-79)', '<?php echo translateAnalytics('failing'); ?> (<75)'],
                datasets: [{
                    data: [
                        <?php echo array_sum(array_column($grade_distribution, 'excellent')); ?>,
                        <?php echo array_sum(array_column($grade_distribution, 'very_good')); ?>,
                        <?php echo array_sum(array_column($grade_distribution, 'satisfactory')); ?>,
                        <?php echo array_sum(array_column($grade_distribution, 'failing')); ?>
                    ],
                    backgroundColor: [
                        '#1cc88a',
                        '#4e73df',
                        '#f6c23e',
                        '#e74a3b'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${context.label}: ${context.raw} <?php echo strtolower(translateAnalytics('students')); ?> (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        function downloadChart() {
            const link = document.createElement('a');
            link.download = 'grade-distribution.png';
            link.href = gradeDistributionChart.toBase64Image();
            link.click();
        }
        <?php endif; ?>

        // HISTORICAL TRENDS CHART
        <?php if ($report_type == 'historical' && !empty($historical_data)): ?>
        const historicalTrendsCtx = document.getElementById('historicalTrendsChart').getContext('2d');
        const historicalTrendsChart = new Chart(historicalTrendsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($historical_data as $history): ?>
                        '<?php echo $history['academic_year'] . ' ' . $history['semester']; ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: '<?php echo translateAnalytics('average_grade'); ?>',
                    data: [
                        <?php foreach ($historical_data as $history): ?>
                            <?php echo $history['average_grade']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgb(128, 0, 0)',
                    backgroundColor: 'rgba(128, 0, 0, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: '<?php echo translateAnalytics('failure_rate'); ?>',
                    data: [
                        <?php foreach ($historical_data as $history): ?>
                            <?php echo $history['failure_rate']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgb(231, 74, 59)',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: '<?php echo translateAnalytics('average_grade'); ?>'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: '<?php echo translateAnalytics('failure_rate'); ?> (%)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
        <?php endif; ?>

        // PRINT CLASS REPORT
        function printClassReport() {
            window.print();
        }

        // AUTO-REFRESH EVERY 5 MINUTES
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>