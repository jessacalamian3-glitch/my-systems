<?php
// faculty_data.php - CENTRAL DATA SOURCE FOR FACULTY

class FacultyData {
    private $pdo;
    private $faculty_id;
    
    public function __construct($faculty_id = null) {
        $this->pdo = $this->getConnection();
        $this->faculty_id = $faculty_id;
    }
    
    private function getConnection() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=msubuug_db", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    // GET FACULTY BASIC INFO
    public function getFacultyInfo() {
        if (!$this->pdo || !$this->faculty_id) {
            return [
                'full_name' => 'Faculty Member',
                'first_name' => 'Faculty',
                'last_name' => 'Member',
                'email' => 'faculty@msubuug.edu.ph',
                'department' => 'College of Information Technology',
                'position' => 'Professor'
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT *, CONCAT(first_name, ' ', last_name) as full_name 
                FROM faculty WHERE faculty_id = ?
            ");
            $stmt->execute([$this->faculty_id]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result;
            } else {
                return [
                    'full_name' => 'Faculty Member',
                    'first_name' => 'Faculty',
                    'last_name' => 'Member',
                    'email' => 'faculty@msubuug.edu.ph',
                    'department' => 'College of Information Technology',
                    'position' => 'Professor'
                ];
            }
        } catch(PDOException $e) {
            error_log("Error getting faculty info: " . $e->getMessage());
            return [
                'full_name' => 'Faculty Member',
                'first_name' => 'Faculty',
                'last_name' => 'Member',
                'email' => 'faculty@msubuug.edu.ph',
                'department' => 'College of Information Technology',
                'position' => 'Professor'
            ];
        }
    }
    
    // GET FACULTY CLASSES
    public function getFacultyClasses() {
        if (!$this->pdo || !$this->faculty_id) return [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.class_id,
                    c.course_code,
                    c.course_name,
                    c.schedule,
                    c.room,
                    (SELECT COUNT(*) FROM enrollments WHERE class_id = c.class_id) as enrolled_students
                FROM classes c
                WHERE c.faculty_id = ?
                ORDER BY c.course_code
            ");
            $stmt->execute([$this->faculty_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getting faculty classes: " . $e->getMessage());
            return [];
        }
    }
    
    // GET TODAY'S SCHEDULE
    public function getTodaysSchedule() {
        if (!$this->pdo || !$this->faculty_id) return [];
        
        try {
            $today = date('l');
            $stmt = $this->pdo->prepare("
                SELECT 
                    c.course_code,
                    c.course_name,
                    c.schedule,
                    c.room
                FROM classes c
                WHERE c.faculty_id = ? 
                AND c.schedule LIKE ?
                ORDER BY c.schedule
            ");
            $today_pattern = "%" . $today . "%";
            $stmt->execute([$this->faculty_id, $today_pattern]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getting today's schedule: " . $e->getMessage());
            return [];
        }
    }
    
    // GET PENDING ASSIGNMENTS COUNT
    public function getPendingAssignmentsCount() {
        if (!$this->pdo || !$this->faculty_id) return 0;
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as pending 
                FROM assignments a 
                INNER JOIN classes c ON a.class_id = c.class_id 
                WHERE c.faculty_id = ? 
                AND a.deadline >= CURDATE()
            ");
            $stmt->execute([$this->faculty_id]);
            $result = $stmt->fetch();
            return $result['pending'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error getting pending assignments: " . $e->getMessage());
            return 0;
        }
    }
    
    // GET TOTAL STUDENTS COUNT
    public function getTotalStudentsCount() {
        $classes = $this->getFacultyClasses();
        return array_sum(array_column($classes, 'enrolled_students'));
    }
    
    // GET ALL STUDENTS FOR FACULTY
    public function getFacultyStudents() {
        if (!$this->pdo || !$this->faculty_id) return [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT DISTINCT 
                    s.student_id,
                    s.first_name,
                    s.last_name,
                    s.email,
                    s.program,
                    c.course_code,
                    c.course_name
                FROM students s
                INNER JOIN enrollments e ON s.student_id = e.student_id
                INNER JOIN classes c ON e.class_id = c.class_id
                WHERE c.faculty_id = ?
                ORDER BY s.last_name, s.first_name
            ");
            $stmt->execute([$this->faculty_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getting faculty students: " . $e->getMessage());
            return [];
        }
    }
    
    // GET GRADES FOR FACULTY
    public function getFacultyGrades() {
        if (!$this->pdo || !$this->faculty_id) return [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    g.grade_id,
                    g.student_id,
                    g.course_code,
                    g.prelim_grade,
                    g.midterm_grade,
                    g.final_grade,
                    g.overall_grade,
                    g.remarks,
                    s.first_name,
                    s.last_name,
                    c.course_name
                FROM grades g
                INNER JOIN students s ON g.student_id = s.student_id
                INNER JOIN classes c ON g.course_code = c.course_code
                WHERE c.faculty_id = ?
                ORDER BY g.course_code, s.last_name
            ");
            $stmt->execute([$this->faculty_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Error getting faculty grades: " . $e->getMessage());
            return [];
        }
    }
}

// No automatic instantiation here - let the calling file handle it
?>