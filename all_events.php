<?php
// all_events.php - VIEW ALL EVENTS
require_once 'config/database.php';

// Kunin ang lahat ng events
try {
    $events_query = "SELECT * FROM upcoming_events ORDER BY event_date ASC";
    $events = $pdo->query($events_query)->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Pagination variables
$items_per_page = 6;
$total_items = count($events);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, min($total_pages, intval($_GET['page']))) : 1;
$start_index = ($current_page - 1) * $items_per_page;
$paginated_events = array_slice($events, $start_index, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events - MSU Buug</title>
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
        
        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            border: none;
            height: 100%;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .event-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }
        
        .event-date {
            background: linear-gradient(135deg, var(--msu-green), var(--msu-light));
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            display: inline-block;
            align-self: flex-start;
        }
        
        .event-title {
            color: var(--msu-dark);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        
        .event-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
            flex-grow: 1;
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
        
        .no-events {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .event-meta {
            background: rgba(139, 0, 0, 0.05);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: var(--msu-dark);
        }
        
        .meta-item i {
            color: var(--msu-green);
            margin-right: 8px;
            width: 20px;
        }
        
        .event-type {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .upcoming-badge {
            background: var(--msu-gold);
            color: var(--msu-dark);
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .past-event {
            opacity: 0.8;
        }
        
        .past-event .event-date {
            background: #6c757d;
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
                <i class="fas fa-calendar-alt me-2"></i>All Events
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
                    <h1 class="display-5 fw-bold mb-3">All Events</h1>
                    <p class="lead mb-0">Stay updated with campus events and activities at MSU Buug</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="container">
        <?php if (!empty($paginated_events)): ?>
            <div class="row">
                <?php foreach($paginated_events as $event): 
                    $is_upcoming = strtotime($event['event_date']) >= strtotime('today');
                    $is_past = strtotime($event['event_date']) < strtotime('today');
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="event-card <?php echo $is_past ? 'past-event' : ''; ?>">
                        <div class="position-relative">
                            <?php if (!empty($event['image_path'])): ?>
                            <img src="images/<?php echo htmlspecialchars($event['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                 class="event-image">
                            <?php else: ?>
                            <div class="event-image bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($is_upcoming): ?>
                            <span class="upcoming-badge">
                                <i class="fas fa-clock me-1"></i>Upcoming
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="event-content">
                            <span class="event-type">
                                <i class="fas fa-calendar-check me-1"></i> Campus Event
                            </span>
                            
                            <div class="event-date">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                            </div>
                            
                            <h3 class="event-title">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </h3>
                            
                            <div class="event-meta">
                                <?php if (!empty($event['location'])): ?>
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($is_past): ?>
                                <div class="meta-item">
                                    <i class="fas fa-history"></i>
                                    <em>This event has passed</em>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <p class="event-excerpt">
                                <?php 
                                $excerpt = strip_tags($event['description']);
                                echo strlen($excerpt) > 120 ? substr($excerpt, 0, 120) . '...' : $excerpt;
                                ?>
                            </p>
                            
                            <div class="mt-auto">
                                <a href="view_event.php?id=<?php echo $event['id']; ?>" class="read-more-btn">
                                    View Event Details <i class="fas fa-arrow-right ms-2"></i>
                                </a>
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
            <div class="no-events">
                <i class="fas fa-calendar-times fa-4x mb-3 text-muted"></i>
                <h3>No Events Scheduled</h3>
                <p>There are no upcoming events at the moment. Please check back later for updates.</p>
                <a href="index.php" class="read-more-btn mt-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 Mindanao State University - Buug Campus. All Rights Reserved.</p>
            <p class="mb-0 small mt-2">
                <a href="search.php?q=events" class="text-white text-decoration-underline">Search Events</a> | 
                <a href="all_announcements.php" class="text-white text-decoration-underline">View Announcements</a> | 
                <a href="all_updates.php" class="text-white text-decoration-underline">View Updates</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto card height adjustment
        document.addEventListener('DOMContentLoaded', function() {
            // Set equal height for event cards
            const cards = document.querySelectorAll('.event-card');
            let maxHeight = 0;
            
            cards.forEach(card => {
                const height = card.offsetHeight;
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });
            
            // Apply max height to all cards
            cards.forEach(card => {
                card.style.minHeight = maxHeight + 'px';
            });
        });
    </script>
</body>
</html>