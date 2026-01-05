<?php
// view_announcement.php - ANNOUNCEMENT VIEWER (SAME DESIGN AS VIEW_EVENT)
require_once 'config/database.php';

// Kunin ang ID mula sa URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Kunin ang full content ng announcement
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch();
    
    if (!$announcement) {
        die("Announcement not found!");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($announcement['title']); ?> - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --msu-green: #8B0000;
            --msu-gold: #FFD700;
            --msu-light: #A52A2A;
            --msu-dark: #600000;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            padding-top: 80px;
            line-height: 1.7;
        }
        
        .article-header {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        
        .article-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .article-content {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }
        
        .back-btn {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
        }
        
        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.4);
            color: white;
            background: linear-gradient(135deg, var(--msu-light), var(--msu-green));
        }
        
        .content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
        }
        
        .content p {
            margin-bottom: 1.5rem;
        }
        
        .meta-info {
            background: rgba(139, 0, 0, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid var(--msu-green);
            margin-bottom: 2rem;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .announcement-details-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 3px solid var(--msu-gold);
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: rgba(139, 0, 0, 0.03);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .detail-item:hover {
            background: rgba(139, 0, 0, 0.08);
            transform: translateX(5px);
        }
        
        .detail-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .detail-content h5 {
            color: var(--msu-green);
            margin-bottom: 0.3rem;
            font-weight: 600;
        }
        
        .detail-content p {
            margin: 0;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                <strong>MSU Buug</strong>
            </a>
            <div class="navbar-text">
                <i class="fas fa-bullhorn me-2"></i>Announcement Details
            </div>
        </div>
    </nav>

    <!-- Article Header -->
    <div class="article-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <a href="index.php" class="back-btn mb-4">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($announcement['title']); ?></h1>
                    <div class="meta-info">
                        <p class="mb-2"><i class="fas fa-calendar me-2"></i><strong>Posted Date:</strong> <?php echo date('F j, Y', strtotime($announcement['date_posted'])); ?></p>
                        <p class="mb-0"><i class="fas fa-clock me-2"></i><strong>Posted on:</strong> <?php echo date('F j, Y', strtotime($announcement['created_at'] ?? date('Y-m-d'))); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if (!empty($announcement['image_path'])): ?>
                <div class="text-center">
                    <img src="images/<?php echo htmlspecialchars($announcement['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($announcement['title']); ?>" 
                         class="article-image">
                </div>
                <?php endif; ?>
                
                <!-- Announcement Details Card -->
                <div class="announcement-details-card">
                    <h3 class="text-center mb-4" style="color: var(--msu-green);">
                        <i class="fas fa-info-circle me-2"></i>Announcement Information
                    </h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="detail-content">
                                    <h5>Posted Date</h5>
                                    <p><?php echo date('l, F j, Y', strtotime($announcement['date_posted'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="detail-content">
                                    <h5>Issued By</h5>
                                    <p>MSU Buug Campus Administration</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="detail-content">
                                    <h5>Target Audience</h5>
                                    <p>Students, Faculty, Staff & Community</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="detail-content">
                                    <h5>Category</h5>
                                    <p>Campus Announcement</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="article-content">
                    <h3 class="text-center mb-4" style="color: var(--msu-green);">
                        <i class="fas fa-file-alt me-2"></i>Announcement Content
                    </h3>
                    <div class="content">
                        <?php 
                        // I-display ang full content with proper formatting
                        $content = htmlspecialchars($announcement['content']);
                        // Palitan ang line breaks ng paragraph tags
                        $content = preg_replace('/\n\s*\n/', '</p><p>', $content);
                        echo '<p>' . $content . '</p>';
                        ?>
                    </div>
                    
                    <div class="mt-5 pt-4 border-top text-center">
                        <a href="index.php" class="back-btn me-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                        <a href="announcement.php" class="back-btn me-3" style="background: #28a745;">
                            <i class="fas fa-bullhorn me-2"></i>All Announcements
                        </a>
                        <a href="javascript:window.print()" class="back-btn" style="background: #6c757d;">
                            <i class="fas fa-print me-2"></i>Print Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>