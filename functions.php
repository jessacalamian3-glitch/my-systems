<?php
// functions.php - SHARED FUNCTIONS (STUDENT + FACULTY)
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

// ==================== STUDENT FUNCTIONS (EXISTING) ====================
function getStudentGrades($student_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    grade_id,
                    student_id,
                    course_code as subject_code,
                    subject_name,
                    instructor,
                    prelim_grade, 
                    midterm_grade, 
                    final_grade,
                    overall_grade,
                    remarks,
                    grade_date,
                    '3' as units
                  FROM grades 
                  WHERE student_id = :student_id
                  ORDER BY course_code";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
}

function calculateGPA($grades) {
    if (empty($grades)) return 0;
    
    $total_grade = 0;
    $count = 0;
    
    foreach ($grades as $grade) {
        if (!empty($grade['overall_grade']) && is_numeric($grade['overall_grade'])) {
            $total_grade += $grade['overall_grade'];
            $count++;
        }
    }
    
    return $count > 0 ? round($total_grade / $count, 2) : 0;
}

function getGradeStatistics($grades) {
    $statistics = [
        'total_subjects' => count($grades),
        'passed' => 0,
        'failed' => 0,
        'in_progress' => 0,
        'average_grade' => 0
    ];
    
    $total_grade = 0;
    $count = 0;
    
    foreach ($grades as $grade) {
        if (!empty($grade['overall_grade'])) {
            $total_grade += $grade['overall_grade'];
            $count++;
            
            if ($grade['overall_grade'] <= 3.00) {
                $statistics['passed']++;
            } else {
                $statistics['failed']++;
            }
        } else {
            $statistics['in_progress']++;
        }
    }
    
    $statistics['average_grade'] = $count > 0 ? round($total_grade / $count, 2) : 0;
    
    return $statistics;
}

// ==================== FACULTY FUNCTIONS (NEW) ====================

// Get basic faculty information
function getFacultyInfo($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT *, CONCAT(first_name, ' ', last_name) as full_name 
                  FROM faculty 
                  WHERE faculty_id = :faculty_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        $faculty_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$faculty_info) {
            return [
                'full_name' => 'Faculty Member',
                'first_name' => 'Faculty',
                'last_name' => 'Member',
                'position' => 'Professor',
                'department' => 'College of Information Technology',
                'email' => 'faculty@msubuug.edu.ph',
                'office' => 'Faculty Room 101',
                'contact_number' => 'N/A'
            ];
        }
        
        return $faculty_info;
    }
    
    // Fallback data
    return [
        'full_name' => 'Faculty Member',
        'first_name' => 'Faculty',
        'last_name' => 'Member',
        'position' => 'Professor',
        'department' => 'College of Information Technology',
        'email' => 'faculty@msubuug.edu.ph',
        'office' => 'Faculty Room 101',
        'contact_number' => 'N/A'
    ];
}

// Get all classes assigned to faculty
function getFacultyClasses($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.class_id,
                    c.course_code,
                    c.course_name,
                    c.schedule,
                    c.room,
                    c.academic_year,
                    c.semester,
                    COUNT(DISTINCT e.student_id) as enrolled_students,
                    COUNT(DISTINCT a.assignment_id) as total_assignments,
                    AVG(g.grade_value) as average_grade
                  FROM classes c
                  LEFT JOIN enrollments e ON c.class_id = e.class_id
                  LEFT JOIN assignments a ON c.class_id = a.class_id
                  LEFT JOIN grades g ON c.class_id = g.class_id
                  WHERE c.faculty_id = :faculty_id
                  GROUP BY c.class_id, c.course_code, c.course_name, c.schedule, c.room
                  ORDER BY c.course_code";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fallback sample data
    return [
        [
            'class_id' => 1,
            'course_code' => 'IT 101',
            'course_name' => 'Introduction to Information Technology',
            'schedule' => 'MWF 8:00-9:00 AM',
            'room' => 'IT Lab 1',
            'enrolled_students' => 25,
            'total_assignments' => 5,
            'average_grade' => 85.5
        ],
        [
            'class_id' => 2,
            'course_code' => 'CS 201',
            'course_name' => 'Data Structures and Algorithms',
            'schedule' => 'TTH 10:00-11:30 AM',
            'room' => 'CS Lab 2',
            'enrolled_students' => 20,
            'total_assignments' => 3,
            'average_grade' => 82.0
        ]
    ];
}

// Get detailed information about a specific class
function getClassDetails($class_id, $faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    c.*,
                    COUNT(DISTINCT e.student_id) as enrolled_students,
                    COUNT(DISTINCT a.assignment_id) as total_assignments,
                    COUNT(DISTINCT CASE WHEN a.due_date > NOW() THEN a.assignment_id END) as pending_assignments,
                    AVG(g.grade_value) as average_grade,
                    MIN(g.grade_value) as min_grade,
                    MAX(g.grade_value) as max_grade
                  FROM classes c
                  LEFT JOIN enrollments e ON c.class_id = e.class_id
                  LEFT JOIN assignments a ON c.class_id = a.class_id
                  LEFT JOIN grades g ON c.class_id = g.class_id
                  WHERE c.class_id = :class_id AND c.faculty_id = :faculty_id
                  GROUP BY c.class_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    return null;
}

// Get students for a faculty (all or by specific class)
function getFacultyStudents($faculty_id, $class_id = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        if ($class_id) {
            // Get students for specific class
            $query = "SELECT 
                        s.student_id,
                        s.first_name,
                        s.last_name,
                        s.email,
                        s.program,
                        p.program_name,
                        AVG(g.grade_value) as student_avg_grade
                      FROM students s
                      INNER JOIN enrollments e ON s.student_id = e.student_id
                      INNER JOIN classes c ON e.class_id = c.class_id
                      LEFT JOIN programs p ON s.program = p.program_code
                      LEFT JOIN grades g ON s.student_id = g.student_id AND c.class_id = g.class_id
                      WHERE c.class_id = :class_id AND c.faculty_id = :faculty_id
                      GROUP BY s.student_id, s.first_name, s.last_name, s.email, s.program
                      ORDER BY s.last_name, s.first_name";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':class_id', $class_id);
            $stmt->bindParam(':faculty_id', $faculty_id);
        } else {
            // Get all students for faculty
            $query = "SELECT DISTINCT
                        s.student_id,
                        CONCAT(s.first_name, ' ', s.last_name) as student_name,
                        s.email,
                        s.contact_number,
                        p.program_name,
                        s.year_level,
                        s.semester,
                        COUNT(DISTINCT e.class_id) as total_classes,
                        AVG(g.grade_value) as average_grade
                      FROM students s
                      INNER JOIN enrollments e ON s.student_id = e.student_id
                      INNER JOIN classes c ON e.class_id = c.class_id
                      LEFT JOIN programs p ON s.program = p.program_code
                      LEFT JOIN grades g ON s.student_id = g.student_id AND c.class_id = g.class_id
                      WHERE c.faculty_id = :faculty_id
                      GROUP BY s.student_id, s.first_name, s.last_name, s.email, p.program_name, s.year_level, s.semester
                      ORDER BY s.last_name, s.first_name";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':faculty_id', $faculty_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return [];
}

// Get total number of students for a faculty
function getTotalStudents($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT COUNT(DISTINCT s.student_id) as total_students
                  FROM students s
                  INNER JOIN enrollments e ON s.student_id = e.student_id
                  INNER JOIN classes c ON e.class_id = c.class_id
                  WHERE c.faculty_id = :faculty_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_students'] ?? 0;
    }
    
    return 75; // Fallback
}

// Get students grouped by program
function getStudentsByProgram($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    p.program_name,
                    p.program_code,
                    COUNT(DISTINCT s.student_id) as student_count
                  FROM programs p
                  LEFT JOIN students s ON p.program_code = s.program
                  LEFT JOIN enrollments e ON s.student_id = e.student_id
                  LEFT JOIN classes c ON e.class_id = c.class_id
                  WHERE c.faculty_id = :faculty_id
                  GROUP BY p.program_code, p.program_name
                  ORDER BY student_count DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fallback sample data
    return [
        ['program_name' => 'BS Information Technology', 'program_code' => 'BSIT', 'student_count' => 35],
        ['program_name' => 'BS Computer Science', 'program_code' => 'BSCS', 'student_count' => 25],
        ['program_name' => 'BS Information Systems', 'program_code' => 'BSIS', 'student_count' => 15]
    ];
}

// Get grade distribution for faculty's classes
function getGradeDistribution($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    CASE 
                        WHEN g.grade_value >= 90 THEN '90-100 (Excellent)'
                        WHEN g.grade_value >= 80 THEN '80-89 (Very Good)'
                        WHEN g.grade_value >= 75 THEN '75-79 (Good)'
                        ELSE 'Below 75 (Needs Improvement)'
                    END as grade_range,
                    COUNT(*) as count
                  FROM grades g
                  INNER JOIN classes c ON g.class_id = c.class_id
                  WHERE c.faculty_id = :faculty_id
                  GROUP BY grade_range
                  ORDER BY grade_range";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fallback sample data
    return [
        ['grade_range' => '90-100 (Excellent)', 'count' => 25],
        ['grade_range' => '80-89 (Very Good)', 'count' => 35],
        ['grade_range' => '75-79 (Good)', 'count' => 12],
        ['grade_range' => 'Below 75 (Needs Improvement)', 'count' => 3]
    ];
}

// Get assignments for a class
function getClassAssignments($class_id, $faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "SELECT 
                    a.*,
                    COUNT(s.submission_id) as submissions_count
                  FROM assignments a
                  LEFT JOIN assignment_submissions s ON a.assignment_id = s.assignment_id
                  WHERE a.class_id = :class_id 
                    AND EXISTS (SELECT 1 FROM classes c WHERE c.class_id = a.class_id AND c.faculty_id = :faculty_id)
                  GROUP BY a.assignment_id
                  ORDER BY a.due_date DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return [];
}

// Get faculty dashboard statistics
function getFacultyStatistics($faculty_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    $stats = [
        'total_classes' => 0,
        'total_students' => 0,
        'total_assignments' => 0,
        'average_grade' => 0
    ];
    
    if ($db) {
        // Total classes
        $query = "SELECT COUNT(*) as total_classes FROM classes WHERE faculty_id = :faculty_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_classes'] = $result['total_classes'] ?? 0;
        
        // Total students
        $stats['total_students'] = getTotalStudents($faculty_id);
        
        // Total assignments
        $query = "SELECT COUNT(*) as total_assignments 
                  FROM assignments a 
                  INNER JOIN classes c ON a.class_id = c.class_id 
                  WHERE c.faculty_id = :faculty_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_assignments'] = $result['total_assignments'] ?? 0;
        
        // Average grade
        $query = "SELECT AVG(g.grade_value) as average_grade
                  FROM grades g
                  INNER JOIN classes c ON g.class_id = c.class_id
                  WHERE c.faculty_id = :faculty_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['average_grade'] = round($result['average_grade'] ?? 0, 1);
    }
    
    return $stats;
}

// Get recent activities for faculty
function getFacultyActivities($faculty_id, $limit = 10) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // This is a simplified version - you can expand based on your activity tracking
        $query = "(
                    SELECT 
                        'assignment_created' as activity_type,
                        a.title as description,
                        a.created_date as activity_date,
                        c.course_code
                    FROM assignments a
                    INNER JOIN classes c ON a.class_id = c.class_id
                    WHERE c.faculty_id = :faculty_id
                    ORDER BY a.created_date DESC
                    LIMIT :limit
                  ) 
                  UNION ALL
                  (
                    SELECT 
                        'grade_submitted' as activity_type,
                        CONCAT('Grades submitted for ', c.course_code) as description,
                        MAX(g.grade_date) as activity_date,
                        c.course_code
                    FROM grades g
                    INNER JOIN classes c ON g.class_id = c.class_id
                    WHERE c.faculty_id = :faculty_id2
                    GROUP BY c.course_code
                    ORDER BY activity_date DESC
                    LIMIT :limit2
                  )
                  ORDER BY activity_date DESC
                  LIMIT :limit3";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':faculty_id', $faculty_id);
        $stmt->bindParam(':faculty_id2', $faculty_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':limit2', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':limit3', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return [];
}

// Update faculty profile
function updateFacultyProfile($faculty_id, $data) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "UPDATE faculty SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    contact_number = :contact_number,
                    office = :office,
                    position = :position
                  WHERE faculty_id = :faculty_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':contact_number', $data['contact_number']);
        $stmt->bindParam(':office', $data['office']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':faculty_id', $faculty_id);
        
        return $stmt->execute();
    }
    
    return false;
}
?>