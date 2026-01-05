<?php
// search.php - MSU Buug Campus Search
session_start();
require_once 'config/database.php';

// GET SEARCH QUERY
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$show_results = !empty($search_query);

// FUNCTION TO HIGHLIGHT SEARCH TERMS
function highlightText($text, $search) {
    if (empty($search) || empty($text)) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    $search_terms = array_filter(explode(' ', $search), function($term) {
        return strlen(trim($term)) > 2;
    });
    
    if (empty($search_terms)) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    $highlighted = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    
    foreach ($search_terms as $term) {
        $term = trim($term);
        $pattern = '/(' . preg_quote($term, '/') . ')/iu';
        $highlighted = preg_replace($pattern, '<mark class="search-highlight">$1</mark>', $highlighted);
    }
    
    return $highlighted;
}

// PERFORM SEARCH IF QUERY EXISTS
if ($show_results) {
    $search_term = '%' . $search_query . '%';
    $results_per_category = 5;
    
    // FUNCTION TO EXECUTE SEARCH QUERIES
    function searchDatabase($pdo, $query, $params) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search error: " . $e->getMessage());
            return [];
        }
    }
    
    // SEARCH ANNOUNCEMENTS - FIXED: walang status column
    $announcements_query = "
        SELECT id, title, content, date_posted, image_path, 'announcement' as type
        FROM announcements 
        WHERE (title LIKE ? OR content LIKE ?)
        ORDER BY date_posted DESC 
        LIMIT $results_per_category
    ";
    $announcements = searchDatabase($pdo, $announcements_query, [$search_term, $search_term]);
    
    // SEARCH EVENTS - FIXED: walang status column
    $events_query = "
        SELECT id, title, description as content, event_date, image_path, location, 'event' as type
        FROM upcoming_events 
        WHERE (title LIKE ? OR description LIKE ? OR location LIKE ?)
        ORDER BY event_date DESC 
        LIMIT $results_per_category
    ";
    $events = searchDatabase($pdo, $events_query, [$search_term, $search_term, $search_term]);
    
    // SEARCH UPDATES - FIXED: walang status column
    $updates_query = "
        SELECT id, title, content, date_posted, image_path, 'update' as type
        FROM latest_updates 
        WHERE (title LIKE ? OR content LIKE ?)
        ORDER BY date_posted DESC 
        LIMIT $results_per_category
    ";
    $updates = searchDatabase($pdo, $updates_query, [$search_term, $search_term]);
    
    // SEARCH PROGRAMS
    $programs_query = "
        SELECT program_code as id, program_name as title, department as content, 'program' as type
        FROM programs 
        WHERE (program_name LIKE ? OR department LIKE ?)
        LIMIT $results_per_category
    ";
    $programs = searchDatabase($pdo, $programs_query, [$search_term, $search_term]);
    
    // SEARCH COURSES
    $courses_query = "
        SELECT course_id as id, course_name as title, 
               CONCAT('Code: ', course_code, ' | Department: ', department) as content, 
               'course' as type
        FROM courses 
        WHERE (course_name LIKE ? OR course_code LIKE ? OR department LIKE ?)
        AND is_active = 1
        LIMIT $results_per_category
    ";
    $courses = searchDatabase($pdo, $courses_query, [$search_term, $search_term, $search_term]);
    
    // SEARCH FAQs
    $faqs_query = "
        SELECT faq_id as id, question as title, answer as content, category, 'faq' as type
        FROM faqs 
        WHERE (question LIKE ? OR answer LIKE ? OR category LIKE ?)
        AND status = 'active'
        ORDER BY display_order ASC
        LIMIT $results_per_category
    ";
    $faqs = searchDatabase($pdo, $faqs_query, [$search_term, $search_term, $search_term]);
    
    // COUNT TOTAL RESULTS
    $total_results = count($announcements) + count($events) + count($updates) + 
                     count($programs) + count($courses) + count($faqs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $show_results ? 'Search Results - MSU Buug' : 'Search - MSU Buug'; ?></title>
    
    <!-- CSS LINKS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* MSU COLORS */
        :root {
            --msu-green: #8B0000;
            --msu-gold: #FFD700;
            --msu-light: #A52A2A;
            --msu-dark: #600000;
            --msu-cream: #FFF8E1;
            --footer-gray: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: white;
        }

        /* TOP BAR */
        .top-bar {
            background: var(--footer-gray);
            padding: 0.3rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .top-bar a {
            color: black !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .top-bar a:hover {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .top-bar-search-icon.active {
            color: var(--msu-gold) !important;
            background: linear-gradient(135deg, rgba(255,215,0,0.2), rgba(255,165,0,0.2));
            border: 1px solid var(--msu-gold);
        }

        /* HEADER */
        .header {
            background: white;
        }

        .header-top {
            padding: 1rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .logo-container {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-img {
            height: 70px;
            margin-right: 20px;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-main {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--msu-green);
            margin-bottom: 5px;
        }

        .brand-address {
            font-size: 1rem;
            color: var(--msu-dark);
            font-weight: 600;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
            padding: 0;
        }

        .nav-link {
            color: white !important;
            font-weight: 600;
            padding: 1rem 0.8rem !important;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: var(--msu-gold) !important;
            background: rgba(255, 255, 255, 0.08);
        }

        /* SEARCH HERO */
        .search-hero {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-dark));
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
        }

        .search-box {
            max-width: 700px;
            margin: 0 auto;
        }

        .search-input {
            height: 60px;
            font-size: 1.1rem;
            border-radius: 30px 0 0 30px;
            border: 3px solid var(--msu-green);
            padding-left: 25px;
        }

        .search-btn {
            height: 60px;
            background: var(--msu-gold);
            color: var(--msu-green);
            border: 3px solid var(--msu-green);
            border-left: none;
            border-radius: 0 30px 30px 0;
            padding: 0 30px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #ffed4e;
            transform: translateY(-2px);
        }

        /* SEARCH RESULTS */
        .results-count {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border-left: 5px solid var(--msu-green);
        }

        .search-highlight {
            background-color: var(--msu-gold);
            color: var(--msu-green);
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }

        .result-card {
            border: none;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .result-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .type-announcement { background: #e3f2fd; color: #1565c0; }
        .type-event { background: #e8f5e9; color: #2e7d32; }
        .type-update { background: #fff3e0; color: #ef6c00; }
        .type-program { background: #f3e5f5; color: #7b1fa2; }
        .type-course { background: #e0f2f1; color: #00695c; }
        .type-faq { background: #f5f5f5; color: #424242; }

        .result-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .result-title {
            font-weight: 700;
            color: var(--msu-dark);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .result-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .read-more-btn {
            background: var(--msu-green);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .read-more-btn:hover {
            background: var(--msu-dark);
            color: white;
            transform: translateY(-2px);
        }

        /* NO RESULTS */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 15px;
            margin: 40px 0;
        }

        .suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .suggestion-tag {
            background: white;
            border: 1px solid #ddd;
            padding: 8px 15px;
            border-radius: 20px;
            text-decoration: none;
            color: var(--msu-green);
            transition: all 0.3s ease;
        }

        .suggestion-tag:hover {
            background: var(--msu-green);
            color: white;
            transform: translateY(-2px);
        }

        /* FOOTER */
        .footer {
            background: #f8f9fa;
            color: var(--msu-dark);
            padding: 50px 0 25px;
            border-top: 3px solid #dee2e6;
            margin-top: 50px;
        }

        .footer-column-title {
            color: var(--msu-green);
            font-weight: 800;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .systems-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .systems-list a {
            color: #444;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: block;
            padding: 5px 0;
        }

        .systems-list a:hover {
            color: var(--msu-green);
        }

        .footer-bottom {
            border-top: 2px solid #dee2e6;
            padding-top: 25px;
            margin-top: 30px;
            text-align: center;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .search-hero {
                padding: 60px 0;
            }
            
            .search-input {
                height: 50px;
                font-size: 1rem;
            }
            
            .search-btn {
                height: 50px;
                padding: 0 25px;
            }
            
            .brand-main {
                font-size: 1.2rem;
            }
            
            .brand-address {
                font-size: 0.9rem;
            }
            
            .logo-img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .search-hero {
                padding: 40px 0;
            }
            
            .search-input {
                height: 45px;
                font-size: 0.9rem;
                padding-left: 15px;
            }
            
            .search-btn {
                height: 45px;
                padding: 0 20px;
                font-size: 0.9rem;
            }
            
            .nav-link {
                font-size: 0.8rem;
                padding: 0.8rem 0.5rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-md-end">
                    <div class="top-bar-buttons">
                        <a href="search.php" class="top-bar-search-icon active" title="Search">
                            <i class="fas fa-search"></i>
                        </a>
                        <a href="faculty_login.php" class="enter-portal-top">
                            <i class="fas fa-sign-in-alt me-2"></i>Faculty Portal
                        </a>
                        <a href="student_login.php" class="enter-portal-top">
                            <i class="fas fa-sign-in-alt me-2"></i>Student Portal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <a class="logo-container" href="index.php">
                            <img src="msulogo.png" alt="MSU Buug Logo" class="logo-img">
                            <div class="brand-text">
                                <span class="brand-main">Mindanao State University - Buug</span>
                                <span class="brand-address">Datu Panas, Buug, Zamboanga Sibugay</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- NAVIGATION -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Transparency
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="university-profile.php">University Profile</a></li>
                                <li><a class="dropdown-item" href="mission-vision.php">Mission,Vision,Core Values</a></li>
                                <li><a class="dropdown-item" href="campus-officials.php">Campus Officials</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Admission
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admission-requirements.php">Requirements</a></li>
                                <li><a class="dropdown-item" href="application-process.php">Application Process</a></li>
                                <li><a class="dropdown-item" href="graduate-programs.php">Graduate programs</a></li>
                                <li><a class="dropdown-item" href="undergraduate-programs.php">Undergraduate Programs</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Academics
                            </a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Basic Education
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="senior-high.php">Senior High School</a></li>
                                        <li><a class="dropdown-item" href="junior-high.php">Junior High School</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">
                                        Colleges
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="college-agriculture.php" target="_blank">College of Agriculture</a></li>
                                        <li><a class="dropdown-item" href="college-arts.php" target="_blank">College of Arts and Sciences</a></li>
                                        <li><a class="dropdown-item" href="college-education.php" target="_blank">College of Education</a></li>
                                        <li><a class="dropdown-item" href="collge-public-affairs.php" target="_blank">College of Public Affairs</a></li>
                                        <li><a class="dropdown-item" href="college-fisheries.php" target="_blank">College of Fisheries</a></li>
                                        <li><a class="dropdown-item" href="college-forestry.php" target="_blank">College of Forestry & Environmental Studies</a></li>
                                        <li><a class="dropdown-item" href="college-hospitality.php" target="_blank">College of Hospitality Management</a></li>
                                        <li><a class="dropdown-item" href="collge-it.php" target="_blank">College of Information Technology</a></li>
                                        <li><a class="dropdown-item" href="college-nursing.php" target="_blank">College of Nursing</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Research & Extension
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="https://msubuug.edu.ph/journal/">AJMR Publication</a></li>
                                <li><a class="dropdown-item" href="stream-publication.php">The Stream Publication</a></li>
                                <li><a class="dropdown-item" href="phanalam-publication.php">Phanalam Publication</a></li>
                                <li><a class="dropdown-item" href="research-colloquium.php">The Research Colloquium Publication</a></li>
                                <li><a class="dropdown-item" href="https://msubuug.edu.ph/otcas/">Thesis and Capstone Archive System</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Administration
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="vc-research-extensions.php">Office of the Vice Chancellor for Research and Extensions</a></li>
                                <li><a class="dropdown-item" href="campus-secretary">Office of the Campus Secretary</a></li>
                                <li><a class="dropdown-item" href="vc-admin-finance.php">Office of the Vice Chancellor for Admin & Finance</a></li>
                                <li><a class="dropdown-item" href="vc-planning-development.php">Office of the Vice Chancellor for Planning & Development</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="sustainability.php">Sustainability</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Offices
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="property-custodian.php">Office of the Property Custodian</a></li>
                                <li><a class="dropdown-item" href="chief-security.php">Office of the Chief Security</a></li>
                                <li><a class="dropdown-item" href="campus-registrar.php">Office of the Campus Registrar</a></li>
                                <li><a class="dropdown-item" href="student-affairs.php">Office of the Student Affairs and Services</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- SEARCH HERO SECTION -->
    <section class="search-hero">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-5 fw-bold mb-4">
                        <?php echo $show_results ? 'Search Results' : 'Search MSU Buug'; ?>
                    </h1>
                    
                    <!-- SEARCH FORM -->
                    <form action="search.php" method="GET" class="search-box">
                        <div class="input-group">
                            <input type="text" 
                                   name="q" 
                                   class="form-control search-input" 
                                   value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Search announcements, events, programs, courses, FAQs..."
                                   autocomplete="off"
                                   autofocus>
                            <button class="btn search-btn" type="submit">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!$show_results): ?>
                    <p class="mt-4">
                        Search across announcements, events, news, academic programs, courses, and FAQs
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <main class="container">
        <?php if ($show_results): ?>
        <!-- SEARCH RESULTS -->
        <div class="row">
            <div class="col-12">
                <div class="results-count mb-4">
                    <h4 class="mb-0">
                        Found <strong class="text-msu-green"><?php echo $total_results; ?></strong> results for 
                        "<strong class="text-msu-dark"><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></strong>"
                    </h4>
                </div>
                
                <!-- ANNOUNCEMENTS RESULTS -->
                <?php if (count($announcements) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-bullhorn me-2 text-primary"></i>Announcements
                        <span class="badge bg-primary ms-2"><?php echo count($announcements); ?></span>
                    </h3>
                    
                    <div class="row">
                        <?php foreach($announcements as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card result-card">
                                <div class="card-body">
                                    <span class="result-type type-announcement">
                                        <i class="fas fa-bullhorn me-1"></i> Announcement
                                    </span>
                                    <div class="result-meta">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('F d, Y', strtotime($item['date_posted'])); ?>
                                    </div>
                                    <h5 class="result-title">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </h5>
                                    <div class="result-content">
                                        <?php 
                                        $content = strip_tags($item['content']);
                                        $content = substr($content, 0, 150);
                                        echo highlightText($content, $search_query);
                                        if (strlen($item['content']) > 150) echo '...';
                                        ?>
                                    </div>
                                    <a href="view_announcement.php?id=<?php echo $item['id']; ?>" 
                                       class="read-more-btn">
                                        Read More <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- EVENTS RESULTS -->
                <?php if (count($events) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-calendar-alt me-2 text-success"></i>Events
                        <span class="badge bg-success ms-2"><?php echo count($events); ?></span>
                    </h3>
                    
                    <div class="row">
                        <?php foreach($events as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card result-card">
                                <div class="card-body">
                                    <span class="result-type type-event">
                                        <i class="fas fa-calendar-alt me-1"></i> Event
                                    </span>
                                    <div class="result-meta">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?php echo date('M d, Y', strtotime($item['event_date'])); ?>
                                        <?php if (!empty($item['location'])): ?>
                                        <br><i class="fas fa-map-marker-alt me-1"></i> <?php echo $item['location']; ?>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="result-title">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </h5>
                                    <div class="result-content">
                                        <?php 
                                        $desc = strip_tags($item['content']);
                                        $desc = substr($desc, 0, 120);
                                        echo highlightText($desc, $search_query);
                                        if (strlen($item['content']) > 120) echo '...';
                                        ?>
                                    </div>
                                    <a href="view_event.php?id=<?php echo $item['id']; ?>" 
                                       class="read-more-btn">
                                        View Details <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- UPDATES RESULTS -->
                <?php if (count($updates) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-newspaper me-2 text-warning"></i>News & Updates
                        <span class="badge bg-warning ms-2"><?php echo count($updates); ?></span>
                    </h3>
                    
                    <div class="row">
                        <?php foreach($updates as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card result-card">
                                <div class="card-body">
                                    <span class="result-type type-update">
                                        <i class="fas fa-newspaper me-1"></i> News Update
                                    </span>
                                    <div class="result-meta">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('F d, Y', strtotime($item['date_posted'])); ?>
                                    </div>
                                    <h5 class="result-title">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </h5>
                                    <div class="result-content">
                                        <?php 
                                        $content = strip_tags($item['content']);
                                        $content = substr($content, 0, 150);
                                        echo highlightText($content, $search_query);
                                        if (strlen($item['content']) > 150) echo '...';
                                        ?>
                                    </div>
                                    <a href="view_update.php?id=<?php echo $item['id']; ?>" 
                                       class="read-more-btn">
                                        Read More <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- PROGRAMS RESULTS -->
                <?php if (count($programs) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-graduation-cap me-2 text-info"></i>Academic Programs
                        <span class="badge bg-info ms-2"><?php echo count($programs); ?></span>
                    </h3>
                    
                    <div class="row">
                        <?php foreach($programs as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card result-card">
                                <div class="card-body">
                                    <span class="result-type type-program">
                                        <i class="fas fa-graduation-cap me-1"></i> Program
                                    </span>
                                    <h5 class="result-title">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </h5>
                                    <div class="result-content">
                                        <p class="mb-0"><strong>Department:</strong> <?php echo $item['content']; ?></p>
                                        <p class="mb-0"><strong>Program Code:</strong> <?php echo $item['id']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- COURSES RESULTS -->
                <?php if (count($courses) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-book me-2 text-secondary"></i>Courses
                        <span class="badge bg-secondary ms-2"><?php echo count($courses); ?></span>
                    </h3>
                    
                    <div class="row">
                        <?php foreach($courses as $item): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card result-card">
                                <div class="card-body">
                                    <span class="result-type type-course">
                                        <i class="fas fa-book me-1"></i> Course
                                    </span>
                                    <h5 class="result-title">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </h5>
                                    <div class="result-content">
                                        <?php echo highlightText($item['content'], $search_query); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- FAQS RESULTS -->
                <?php if (count($faqs) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4">
                        <i class="fas fa-question-circle me-2 text-dark"></i>FAQs
                        <span class="badge bg-dark ms-2"><?php echo count($faqs); ?></span>
                    </h3>
                    
                    <div class="accordion" id="faqAccordion">
                        <?php foreach($faqs as $index => $item): ?>
                        <div class="accordion-item mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#faq<?php echo $index; ?>"
                                        aria-expanded="false">
                                    <span class="result-type type-faq me-3">
                                        <i class="fas fa-question-circle"></i> FAQ
                                    </span>
                                    <span class="fw-bold">
                                        <?php echo highlightText($item['title'], $search_query); ?>
                                    </span>
                                </button>
                            </h2>
                            <div id="faq<?php echo $index; ?>" 
                                 class="accordion-collapse collapse" 
                                 data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <div class="result-content">
                                        <?php echo highlightText($item['content'], $search_query); ?>
                                    </div>
                                    <?php if (!empty($item['category'])): ?>
                                    <div class="mt-3">
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-tag me-1"></i> <?php echo $item['category']; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- NO RESULTS FOUND -->
                <?php if ($total_results == 0): ?>
                <div class="no-results">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h3 class="mb-3">No Results Found</h3>
                    <p class="text-muted mb-4">
                        We couldn't find any results for "<strong><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></strong>"
                    </p>
                    
                    <div class="suggestions">
                        <a href="search.php?q=enrollment" class="suggestion-tag">enrollment</a>
                        <a href="search.php?q=admission" class="suggestion-tag">admission</a>
                        <a href="search.php?q=scholarship" class="suggestion-tag">scholarship</a>
                        <a href="search.php?q=events" class="suggestion-tag">events</a>
                        <a href="search.php?q=announcements" class="suggestion-tag">announcements</a>
                        <a href="search.php?q=BSIT" class="suggestion-tag">BSIT</a>
                        <a href="search.php?q=courses" class="suggestion-tag">courses</a>
                        <a href="search.php?q=FAQs" class="suggestion-tag">FAQs</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php else: ?>
        <!-- NO SEARCH QUERY - SHOW SUGGESTIONS -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-5">
                        <i class="fas fa-search fa-5x text-muted mb-4"></i>
                        <h2 class="mb-3">Search MSU Buug</h2>
                        <p class="text-muted mb-5">
                            Search across announcements, events, news, academic programs, courses, and FAQs
                        </p>
                    </div>
                    
                    <div class="suggestions">
                        <a href="search.php?q=enrollment" class="suggestion-tag">
                            <i class="fas fa-user-graduate me-2"></i> Enrollment
                        </a>
                        <a href="search.php?q=admission" class="suggestion-tag">
                            <i class="fas fa-file-alt me-2"></i> Admission
                        </a>
                        <a href="search.php?q=scholarship" class="suggestion-tag">
                            <i class="fas fa-award me-2"></i> Scholarship
                        </a>
                        <a href="search.php?q=events" class="suggestion-tag">
                            <i class="fas fa-calendar me-2"></i> Events
                        </a>
                        <a href="search.php?q=announcements" class="suggestion-tag">
                            <i class="fas fa-bullhorn me-2"></i> Announcements
                        </a>
                        <a href="search.php?q=BSIT" class="suggestion-tag">
                            <i class="fas fa-laptop-code me-2"></i> BSIT
                        </a>
                        <a href="search.php?q=courses" class="suggestion-tag">
                            <i class="fas fa-book me-2"></i> Courses
                        </a>
                        <a href="search.php?q=FAQs" class="suggestion-tag">
                            <i class="fas fa-question-circle me-2"></i> FAQs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">Contact Information</h5>
                    <div class="footer-contact-info">
                        <p><strong>MSU Buug Campus</strong></p>
                        <p>Datu Panas, Buug, Zamboanga Sibugay</p>
                        <p>Email: msubuug.campus@msubuug.edu.ph</p>
                        <p>Phone: (062) 955-123</p>
                        
                        <a href="https://www.facebook.com/msubuugcampus" class="fb-link" target="_blank">
                            <i class="fab fa-facebook fa-lg me-2"></i>
                            <span>Follow our Facebook Page</span>
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">MSU System Campuses</h5>
                    <ul class="systems-list">
                        <li><a href="https://www.msumain.edu.ph">MSU Main Campus - Marawi</a></li>
                        <li><a href="https://msuiit.edu.ph/">MSU-Iligan Institute of Technology</a></li>
                        <li><a href="https://msugensan.edu.ph/">MSU-General Santos</a></li>
                        <li><a href="https://msunaawan.edu.ph/">MSU-Naawan</a></li>
                        <li><a href="https://msumaguindanao.edu.ph/">MSU-Maguindanao</a></li>
                        <li><a href="https://msutcto.edu.ph/">MSU-Tawi-Tawi</a></li>
                        <li><a href="https://msusulu.edu.ph/">MSU-Sulu</a></li>
                        <li><a href="https://msubuug.edu.ph/">MSU-Buug</a></li>
                        <li><a href="https://www.msumain.edu.ph/">MSU-Lanao del Norte</a></li>
                        <li><a href="https://msumsat.edu.ph/">MSU-Maigo School of Arts and Trades</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="footer-column-title">Core Values</h5>
                    <ul class="core-values-list">
                        <li>Integrity</li>
                        <li>Culture Sensitivity</li>
                        <li>Accountability</li>
                        <li>Responsiveness</li>
                        <li>Excellence</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="row">
                    <div class="col-12">
                        <p>&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-focus on search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
            }
        });
    </script>
</body>
</html>