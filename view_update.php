<?php
// view_update.php - FULL ARTICLE VIEWER
require_once 'config/database.php';

// Kunin ang ID mula sa URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Kunin ang full content ng update
    $stmt = $pdo->prepare("SELECT * FROM latest_updates WHERE id = ?");
    $stmt->execute([$id]);
    $update = $stmt->fetch();
    
    if (!$update) {
        die("Update not found!");
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
    <title><?php echo htmlspecialchars($update['title']); ?> - MSU Buug</title>
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
                <i class="fas fa-newspaper me-2"></i>News Article
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
                    <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($update['title']); ?></h1>
                    <div class="meta-info">
                        <p class="mb-1"><i class="fas fa-calendar me-2"></i><strong>Posted on:</strong> <?php echo date('F j, Y', strtotime($update['date_posted'])); ?></p>
                        <?php if ($update['is_featured']): ?>
                            <p class="mb-0"><i class="fas fa-star me-2" style="color: var(--msu-gold);"></i><strong>Featured Article</strong></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if (!empty($update['image_path'])): ?>
                <div class="text-center">
                    <img src="images/<?php echo htmlspecialchars($update['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($update['title']); ?>" 
                         class="article-image">
                </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <div class="content">
                        <?php 
                        // I-display ang full content with proper formatting
                        $content = htmlspecialchars($update['content']);
                        // Palitan ang line breaks ng paragraph tags
                        $content = preg_replace('/\n\s*\n/', '</p><p>', $content);
                        echo '<p>' . $content . '</p>';
                        ?>
                    </div>
                    
                    <div class="mt-5 pt-4 border-top text-center">
                        <a href="index.php" class="back-btn me-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                        <a href="javascript:window.print()" class="back-btn" style="background: #6c757d;">
                            <i class="fas fa-print me-2"></i>Print Article
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