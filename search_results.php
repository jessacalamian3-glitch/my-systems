<?php
// search.php
require_once 'config/database.php';

$search_query = $_GET['q'] ?? '';
$search_query = trim($search_query);
$show_results = !empty($search_query);

// Function to highlight search terms
function highlightText($text, $search) {
    if (empty($search) || empty($text)) return htmlspecialchars($text);
    
    $search_terms = explode(' ', $search);
    $highlighted = htmlspecialchars($text);
    
    foreach ($search_terms as $term) {
        $term = trim($term);
        if (strlen($term) > 2) {
            $pattern = '/(' . preg_quote($term, '/') . ')/i';
            $highlighted = preg_replace($pattern, '<mark class="bg-warning">$1</mark>', $highlighted);
        }
    }
    
    return $highlighted;
}

// Perform search if there's a query
if ($show_results) {
    $search_term = '%' . $search_query . '%';
    
    // SEARCH ANNOUNCEMENTS (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            content,
            date_posted,
            'announcement' as type,
            image_path
        FROM announcements 
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY date_posted DESC 
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $announcements = $stmt->fetchAll();
    
    // SEARCH EVENTS (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            description as content,
            event_date,
            'event' as type,
            image_path,
            location
        FROM upcoming_events 
        WHERE title LIKE ? OR description LIKE ? OR location LIKE ?
        ORDER BY event_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $events = $stmt->fetchAll();
    
    // SEARCH UPDATES (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            title,
            content,
            date_posted,
            'update' as type,
            image_path
        FROM latest_updates 
        WHERE title LIKE ? OR content LIKE ?
        ORDER BY date_posted DESC 
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $updates = $stmt->fetchAll();
    
    // SEARCH PROGRAMS (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            program_code as id,
            program_name as title,
            department as content,
            'program' as type,
            NULL as image_path
        FROM programs 
        WHERE program_name LIKE ? OR department LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term]);
    $programs = $stmt->fetchAll();
    
    // SEARCH COURSES (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            course_id as id,
            course_name as title,
            CONCAT('Code: ', course_code, ' | Department: ', department) as content,
            'course' as type,
            NULL as image_path
        FROM courses 
        WHERE course_name LIKE ? OR course_code LIKE ? OR department LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $courses = $stmt->fetchAll();
    
    // SEARCH FAQs (limit 5)
    $stmt = $pdo->prepare("
        SELECT 
            faq_id as id,
            question as title,
            answer as content,
            'faq' as type,
            NULL as image_path
        FROM faqs 
        WHERE question LIKE ? OR answer LIKE ? OR category LIKE ?
        LIMIT 5
    ");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $faqs = $stmt->fetchAll();
    
    // Count total results
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
    
    <!-- CSS LINKS (SAME AS YOUR INDEX.PHP) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* SEARCH PAGE SPECIFIC STYLES */
        .search-hero {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 80px 0;
            margin-bottom: 50px;
        }
        
        .search-box-lg {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .search-input-lg {
            height: 60px;
            font-size: 1.2rem;
            border-radius: 30px 0 0 30px;
            border: 3px solid var(--msu-green);
            padding-left: 25px;
        }
        
        .search-btn-lg {
            height: 60px;
            background: var(--msu-gold);
            color: var(--msu-green);
            border: 3px solid var(--msu-green);
            border-left: none;
            border-radius: 0 30px 30px 0;
            padding: 0 35px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .search-btn-lg:hover {
            background: #ffed4e;
            transform: translateY(-2px);
        }
        
        .result-card {
            border-left: 4px solid var(--msu-green);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .result-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .result-type {
            background: var(--msu-gold);
            color: var(--msu-green);
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .type-announcement { background: #d1ecf1; color: #0c5460; }
        .type-event { background: #d4edda; color: #155724; }
        .type-update { background: #fff3cd; color: #856404; }
        .type-program { background: #cce5ff; color: #004085; }
        .type-course { background: #d6d8db; color: #383d41; }
        .type-faq { background: #e2e3e5; color: #383d41; }
        
        .no-results {
            text-align: center;
            padding: 50px 0;
        }
        
        .no-results-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .search-suggestions {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
        }
        
        .suggestion-item {
            display: inline-block;
            background: white;
            border: 1px solid #ddd;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            text-decoration: none;
            color: var(--msu-green);
            transition: all 0.3s ease;
        }
        
        .suggestion-item:hover {
            background: var(--msu-green);
            color: white;
            transform: translateY(-2px);
        }
        
        .see-more-btn {
            background: transparent;
            color: var(--msu-green);
            border: 2px solid var(--msu-green);
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }
        
        .see-more-btn:hover {
            background: var(--msu-green);
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .search-hero {
                padding: 50px 0;
            }
            
            .search-input-lg {
                height: 50px;
                font-size: 1rem;
            }
            
            .search-btn-lg {
                height: 50px;
                padding: 0 25px;
            }
        }
    </style>
</head>
<body>
    <!-- TOP BAR (COPY FROM YOUR INDEX.PHP) -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6"></div>
                <div class="col-md-6 text-md-end">
                    <div class="top-bar-buttons">
                        <a href="search.php" class="top-bar-search-icon" title="Search">
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

    <!-- HEADER (COPY FROM YOUR INDEX.PHP) -->
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
        
        <!-- NAVIGATION (COPY FROM YOUR INDEX.PHP) -->
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
                        <!-- COPY THE REST OF YOUR NAVIGATION HERE -->
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
                    <h1 class="display-4 fw-bold mb-4">
                        <?php echo $show_results ? 'Search Results' : 'Search MSU Buug'; ?>
                    </h1>
                    
                    <!-- SEARCH FORM -->
                    <form action="search.php" method="GET" class="search-box-lg">
                        <div class="input-group">
                            <input type="text" 
                                   name="q" 
                                   class="form-control search-input-lg" 
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   placeholder="Search announcements, events, news, programs..."
                                   autocomplete="off"
                                   autofocus>
                            <button class="btn search-btn-lg" type="submit">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!$show_results): ?>
                    <p class="mt-4 opacity-75">
                        Search across announcements, events, news, and academic programs
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <main class="container">
        <?php if ($show_results): ?>
        <!-- ====================== -->
        <!-- VIEW 2: WITH RESULTS -->
        <!-- ====================== -->
        
        <div class="row mb-4">
            <div class="col-12">
                <p class="lead">
                    Found <strong><?php echo $total_results; ?></strong> results for 
                    "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                </p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <!-- ANNOUNCEMENTS RESULTS -->
                <?php if (count($announcements) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-primary">
                        <i class="fas fa-bullhorn me-2"></i>Announcements
                        <span class="badge bg-primary ms-2"><?php echo count($announcements); ?></span>
                    </h3>
                    
                    <?php foreach($announcements as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="result-type type-announcement">Announcement</span>
                                    <span class="text-muted ms-2">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($item['date_posted'])); ?>
                                    </span>
                                </div>
                                <a href="view_announcement.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                $content = substr($item['content'], 0, 150);
                                echo highlightText($content, $search_query);
                                if (strlen($item['content']) > 150) echo '...';
                                ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($announcements) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=announcements&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More Announcements
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- EVENTS RESULTS -->
                <?php if (count($events) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-success">
                        <i class="fas fa-calendar-alt me-2"></i>Events
                        <span class="badge bg-success ms-2"><?php echo count($events); ?></span>
                    </h3>
                    
                    <?php foreach($events as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="result-type type-event">Event</span>
                                    <span class="text-muted ms-2">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($item['event_date'])); ?>
                                    </span>
                                </div>
                                <a href="view_event.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm btn-outline-success">
                                    View Details
                                </a>
                            </div>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                $desc = substr($item['content'], 0, 150);
                                echo highlightText($desc, $search_query);
                                if (strlen($item['content']) > 150) echo '...';
                                ?>
                            </p>
                            <?php if (!empty($item['location'])): ?>
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                <small><?php echo $item['location']; ?></small>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($events) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=events&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More Events
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- UPDATES RESULTS -->
                <?php if (count($updates) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-warning">
                        <i class="fas fa-newspaper me-2"></i>News & Updates
                        <span class="badge bg-warning ms-2"><?php echo count($updates); ?></span>
                    </h3>
                    
                    <?php foreach($updates as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="result-type type-update">Update</span>
                                    <span class="text-muted ms-2">
                                        <i class="far fa-calendar me-1"></i>
                                        <?php echo date('M d, Y', strtotime($item['date_posted'])); ?>
                                    </span>
                                </div>
                                <a href="view_update.php?id=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm btn-outline-warning">
                                    View Details
                                </a>
                            </div>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                $content = substr($item['content'], 0, 150);
                                echo highlightText($content, $search_query);
                                if (strlen($item['content']) > 150) echo '...';
                                ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($updates) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=updates&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More Updates
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- PROGRAMS RESULTS -->
                <?php if (count($programs) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-info">
                        <i class="fas fa-graduation-cap me-2"></i>Academic Programs
                        <span class="badge bg-info ms-2"><?php echo count($programs); ?></span>
                    </h3>
                    
                    <?php foreach($programs as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <span class="result-type type-program">Program</span>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                echo highlightText($item['content'], $search_query);
                                ?>
                            </p>
                            <p class="mb-0">
                                <small>Code: <strong><?php echo $item['id']; ?></strong></small>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($programs) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=programs&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More Programs
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- COURSES RESULTS -->
                <?php if (count($courses) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-secondary">
                        <i class="fas fa-book me-2"></i>Courses
                        <span class="badge bg-secondary ms-2"><?php echo count($courses); ?></span>
                    </h3>
                    
                    <?php foreach($courses as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <span class="result-type type-course">Course</span>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                echo highlightText($item['content'], $search_query);
                                ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($courses) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=courses&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More Courses
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- FAQS RESULTS -->
                <?php if (count($faqs) > 0): ?>
                <div class="mb-5">
                    <h3 class="mb-4 text-dark">
                        <i class="fas fa-question-circle me-2"></i>FAQs
                        <span class="badge bg-dark ms-2"><?php echo count($faqs); ?></span>
                    </h3>
                    
                    <?php foreach($faqs as $item): ?>
                    <div class="card result-card">
                        <div class="card-body">
                            <span class="result-type type-faq">FAQ</span>
                            <h5 class="card-title mt-2">
                                <?php echo highlightText($item['title'], $search_query); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php 
                                $content = substr($item['content'], 0, 150);
                                echo highlightText($content, $search_query);
                                if (strlen($item['content']) > 150) echo '...';
                                ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($faqs) >= 5): ?>
                    <div class="text-center">
                        <a href="view_all.php?category=faqs&q=<?php echo urlencode($search_query); ?>" 
                           class="see-more-btn">
                            <i class="fas fa-arrow-right me-1"></i> See More FAQs
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- NO RESULTS FOUND -->
                <?php if ($total_results == 0): ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="mb-3">No Results Found</h3>
                    <p class="text-muted mb-4">
                        We couldn't find any results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    </p>
                    
                    <div class="search-suggestions">
                        <h5 class="mb-3">Try searching for:</h5>
                        <div>
                            <a href="search.php?q=enrollment" class="suggestion-item">enrollment</a>
                            <a href="search.php?q=admission" class="suggestion-item">admission</a>
                            <a href="search.php?q=scholarship" class="suggestion-item">scholarship</a>
                            <a href="search.php?q=BSIT" class="suggestion-item">BSIT</a>
                            <a href="search.php?q=events" class="suggestion-item">events</a>
                            <a href="search.php?q=announcements" class="suggestion-item">announcements</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <?php else: ?>
        <!-- ====================== -->
        <!-- VIEW 1: SEARCH BAR ONLY -->
        <!-- ====================== -->
        
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-5">
                        <i class="fas fa-search fa-4x text-muted mb-4"></i>
                        <h2 class="mb-3">Search MSU Buug</h2>
                        <p class="lead text-muted mb-5">
                            Find announcements, events, academic programs, courses, and more
                        </p>
                    </div>
                    
                    <div class="search-suggestions">
                        <h4 class="mb-4">What are you looking for?</h4>
                        <div class="mb-4">
                            <a href="search.php?q=enrollment" class="suggestion-item">
                                <i class="fas fa-user-graduate me-1"></i> Enrollment
                            </a>
                            <a href="search.php?q=admission+requirements" class="suggestion-item">
                                <i class="fas fa-file-alt me-1"></i> Admission Requirements
                            </a>
                            <a href="search.php?q=BSIT" class="suggestion-item">
                                <i class="fas fa-laptop-code me-1"></i> BS Information Technology
                            </a>
                            <a href="search.php?q=BSN" class="suggestion-item">
                                <i class="fas fa-user-nurse me-1"></i> BS Nursing
                            </a>
                            <a href="search.php?q=events" class="suggestion-item">
                                <i class="fas fa-calendar me-1"></i> Events
                            </a>
                            <a href="search.php?q=scholarship" class="suggestion-item">
                                <i class="fas fa-award me-1"></i> Scholarship
                            </a>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-bullhorn fa-2x text-primary mb-3"></i>
                                        <h5>Announcements</h5>
                                        <p class="text-muted small">Official campus announcements</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-calendar-alt fa-2x text-success mb-3"></i>
                                        <h5>Events</h5>
                                        <p class="text-muted small">Campus activities & programs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <i class="fas fa-graduation-cap fa-2x text-info mb-3"></i>
                                        <h5>Programs</h5>
                                        <p class="text-muted small">Academic degrees offered</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER (COPY FROM YOUR INDEX.PHP) -->
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
            const searchInput = document.querySelector('.search-input-lg');
            if (searchInput) {
                searchInput.focus();
                // Move cursor to end
                searchInput.selectionStart = searchInput.selectionEnd = searchInput.value.length;
            }
        });
    </script>
</body>
</html>