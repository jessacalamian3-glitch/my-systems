<?php
// all_updates.php - VIEW ALL LATEST UPDATES
require_once 'config/database.php';

// Kunin ang lahat ng updates
try {
    $updates_query = "SELECT * FROM latest_updates ORDER BY date_posted DESC";
    $updates = $pdo->query($updates_query)->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Pagination variables
$items_per_page = 6;
$total_items = count($updates);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
$start_index = ($current_page - 1) * $items_per_page;
$paginated_updates = array_slice($updates, $start_index, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Latest Updates - MSU Buug</title>
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
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
        }
        
        .update-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            border: none;
        }
        
        .update-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .update-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .update-content {
            padding: 1.5rem;
        }
        
        .update-date {
            color: var(--msu-green);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .update-title {
            color: var(--msu-dark);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .update-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .read-more-btn {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .read-more-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
            color: white;
        }
        
        .back-btn {
            background: transparent;
            color: white;
            border: 2px solid white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: white;
            color: var(--msu-green);
        }
        
        .featured-badge {
            background: var(--msu-gold);
            color: var(--msu-dark);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 700;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .pagination .page-link {
            color: var(--msu-green);
            border: 1px solid #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            background: var(--msu-green);
            border-color: var(--msu-green);
            color: white;
        }
        
        .pagination .page-link:hover {
            background: rgba(139, 0, 0, 0.1);
            color: var(--msu-dark);
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light)) !important;
        }
        
        .no-updates {
            text-align: center;
            padding: 3rem;
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
                <i class="fas fa-newspaper me-2"></i>All Latest Updates
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <a href="index.php" class="back-btn mb-4">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                    <h1 class="display-5 fw-bold mb-3">All Latest Updates</h1>
                    <p class="lead mb-0">Stay informed with the latest news and announcements from MSU Buug</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Updates Grid -->
    <div class="container">
        <?php if (!empty($paginated_updates)): ?>
            <div class="row">
                <?php foreach($paginated_updates as $update): ?>
                <div class="col-lg-6 mb-4">
                    <div class="update-card">
                        <div class="position-relative">
                            <?php if (!empty($update['image_path'])): ?>
                            <img src="images/<?php echo htmlspecialchars($update['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($update['title']); ?>" 
                                 class="update-image">
                            <?php else: ?>
                            <div class="update-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-newspaper fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($update['is_featured']): ?>
                            <span class="featured-badge">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="update-content">
                            <div class="update-date">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('F j, Y', strtotime($update['date_posted'])); ?>
                            </div>
                            
                            <h3 class="update-title">
                                <?php echo htmlspecialchars($update['title']); ?>
                            </h3>
                            
                            <p class="update-excerpt">
                                <?php 
                                $excerpt = strip_tags($update['content']);
                                echo strlen($excerpt) > 150 ? substr($excerpt, 0, 150) . '...' : $excerpt;
                                ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="view_update.php?id=<?php echo $update['id']; ?>" class="read-more-btn">
                                    Read Full Story <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                                
                                <?php if ($update['is_featured']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>Featured
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-updates">
                <i class="fas fa-newspaper fa-4x mb-3 text-muted"></i>
                <h3>No Updates Available</h3>
                <p>Check back later for the latest news and announcements.</p>
                <a href="index.php" class="read-more-btn mt-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 Mindanao State University - Buug Campus. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>