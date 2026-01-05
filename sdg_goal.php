<?php
// config/database.php - DATABASE CONFIGURATION
$host = 'localhost';
$dbname = 'msubuug_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// SDG MODULE CLASS
class SustainableGoals {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllSDGs() {
        $stmt = $this->pdo->query("SELECT * FROM sustainable_goals ORDER BY sdg_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSDGByNumber($number) {
        $stmt = $this->pdo->prepare("SELECT * FROM sustainable_goals WHERE sdg_number = ?");
        $stmt->execute([$number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateSDG($number, $title, $description, $content, $image_path = null) {
        $sql = "UPDATE sustainable_goals SET title = ?, description = ?, content = ?";
        $params = [$title, $description, $content];
        
        if ($image_path) {
            $sql .= ", image_path = ?";
            $params[] = $image_path;
        }
        
        $sql .= " WHERE sdg_number = ?";
        $params[] = $number;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function initializeSDGs() {
        // Check if SDGs already exist
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM sustainable_goals");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $sdgs = [
                [1, 'No Poverty', 'End poverty in all its forms everywhere', 'Content for SDG 1...'],
                [2, 'Zero Hunger', 'End hunger, achieve food security and improved nutrition...', 'Content for SDG 2...'],
                [3, 'Good Health and Well-being', 'Ensure healthy lives and promote well-being for all...', 'Content for SDG 3...'],
                [4, 'Quality Education', 'Ensure inclusive and equitable quality education...', 'Content for SDG 4...'],
                [5, 'Gender Equality', 'Achieve gender equality and empower all women and girls...', 'Content for SDG 5...'],
                [6, 'Clean Water and Sanitation', 'Ensure availability and sustainable management of water...', 'Content for SDG 6...'],
                [7, 'Affordable and Clean Energy', 'Ensure access to affordable, reliable, sustainable energy...', 'Content for SDG 7...'],
                [8, 'Decent Work and Economic Growth', 'Promote sustained, inclusive and sustainable economic growth...', 'Content for SDG 8...'],
                [9, 'Industry, Innovation and Infrastructure', 'Build resilient infrastructure, promote sustainable industrialization...', 'Content for SDG 9...'],
                [10, 'Reduced Inequality', 'Reduce inequality within and among countries...', 'Content for SDG 10...'],
                [11, 'Sustainable Cities and Communities', 'Make cities and human settlements inclusive, safe, resilient and sustainable...', 'Content for SDG 11...'],
                [12, 'Responsible Consumption and Production', 'Ensure sustainable consumption and production patterns...', 'Content for SDG 12...'],
                [13, 'Climate Action', 'Take urgent action to combat climate change and its impacts...', 'Content for SDG 13...'],
                [14, 'Life Below Water', 'Conserve and sustainably use the oceans, seas and marine resources...', 'Content for SDG 14...'],
                [15, 'Life on Land', 'Protect, restore and promote sustainable use of terrestrial ecosystems...', 'Content for SDG 15...'],
                [16, 'Peace, Justice and Strong Institutions', 'Promote peaceful and inclusive societies for sustainable development...', 'Content for SDG 16...'],
                [17, 'Partnerships for the Goals', 'Strengthen the means of implementation and revitalize the global partnership...', 'Content for SDG 17...']
            ];
            
            $stmt = $this->pdo->prepare("INSERT INTO sustainable_goals (sdg_number, title, description, content) VALUES (?, ?, ?, ?)");
            
            foreach ($sdgs as $sdg) {
                $stmt->execute($sdg);
            }
            
            return "17 SDGs initialized successfully!";
        }
        
        return "SDGs already exist in database.";
    }
}

// Initialize SDG Module
$sdgModule = new SustainableGoals($pdo);

// Handle form submissions
if ($_POST['action'] ?? '' == 'update_sdg') {
    $sdg_number = $_POST['sdg_number'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content = $_POST['content'];
    
    // Handle image upload
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "images/sdg/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $filename = "sdg_" . $sdg_number . "." . $imageFileType;
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $filename;
        }
    }
    
    $sdgModule->updateSDG($sdg_number, $title, $description, $content, $image_path);
    $success_message = "SDG $sdg_number updated successfully!";
}

// Handle initialization
if ($_GET['action'] ?? '' == 'initialize_sdgs') {
    $init_message = $sdgModule->initializeSDGs();
}

// Get all SDGs for display
$all_sdgs = $sdgModule->getAllSDGs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sustainable Development Goals - MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sdg-admin-header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 80px 0 50px;
            margin-top: 120px;
        }
        .sdg-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .sdg-item {
            display: block;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .sdg-item:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .sdg-image {
            width: 100%;
            height: auto;
            display: block;
        }
        .sdg-main-image {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto 50px;
            display: block;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .sdg-detail-header {
            background: linear-gradient(135deg, var(--sdg-color1), var(--sdg-color2));
            color: white;
            padding: 100px 0 50px;
            margin-top: 120px;
        }
        .sdg-content {
            padding: 50px 0;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .sdg-card {
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
        }
        .sdg-card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .sdg-grid { grid-template-columns: repeat(4, 1fr); max-width: 800px; }
        }
        @media (max-width: 768px) {
            .sdg-grid { grid-template-columns: repeat(3, 1fr); max-width: 600px; }
        }
        @media (max-width: 576px) {
            .sdg-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        }
    </style>
</head>
<body>
    <!-- Your existing header code here -->
    <header class="header">
        <!-- Your header content from original code -->
    </header>

    <?php if (!isset($_GET['sdg']) && !isset($_GET['admin'])): ?>
        <!-- SUSTAINABLE GOALS LANDING PAGE -->
        <section class="sustainable-goals-section">
            <div class="container">
                <img src="sustainable.png" alt="Sustainable Development Goals" class="sdg-main-image">
                
                <div class="sdg-grid">
                    <?php foreach($all_sdgs as $sdg): ?>
                    <a href="?sdg=<?= $sdg['sdg_number'] ?>" class="sdg-item">
                        <img src="images/sdg/s<?= $sdg['sdg_number'] ?>.png" alt="SDG <?= $sdg['sdg_number'] ?>: <?= $sdg['title'] ?>" class="sdg-image">
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Admin Link -->
                <div class="text-center mt-5">
                    <a href="?admin=1" class="btn btn-primary">
                        <i class="fas fa-cog me-2"></i>Manage SDGs (Admin)
                    </a>
                    <?php if (!count($all_sdgs)): ?>
                        <a href="?action=initialize_sdgs" class="btn btn-warning ms-2">
                            <i class="fas fa-database me-2"></i>Initialize SDGs
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    <?php elseif (isset($_GET['sdg'])): ?>
        <!-- INDIVIDUAL SDG DETAIL PAGE -->
        <?php
        $sdg_number = $_GET['sdg'];
        $sdg = $sdgModule->getSDGByNumber($sdg_number);
        
        if (!$sdg) {
            echo "<div class='container' style='margin-top: 150px; padding: 50px 0;'>
                    <div class='alert alert-danger text-center'>
                        <h4>SDG not found!</h4>
                        <a href='?' class='btn btn-primary mt-3'>Back to SDGs</a>
                    </div>
                  </div>";
            exit;
        }
        
        $colors = [
            1 => ['E5243B', 'B51D2F'], 2 => ['DDA63A', 'A8822E'], 
            3 => ['4C9F38', '3A7C2B'], 4 => ['C5192D', '9D1424'],
            5 => ['FF3A21', 'CC2E1A'], 6 => ['26BDE2', '1E97B6'],
            7 => ['FCC30B', 'CA9C09'], 8 => ['A21942', '821434'],
            9 => ['FD6925', 'CA541D'], 10 => ['DD1367', 'B10F52'],
            11 => ['FD9D24', 'CA7E1D'], 12 => ['BF8B2E', '996F25'],
            13 => ['3F7E44', '326536'], 14 => ['0A97D9', '0879AD'],
            15 => ['56C02B', '459A22'], 16 => ['00689D', '00537D'],
            17 => ['19486A', '143A54']
        ];
        ?>
        
        <style>
            :root {
                --sdg-color1: #<?= $colors[$sdg_number][0] ?>;
                --sdg-color2: #<?= $colors[$sdg_number][1] ?>;
            }
        </style>

        <div class="sdg-detail-header">
            <div class="container text-center">
                <img src="images/sdg/s<?= $sdg_number ?>.png" alt="SDG <?= $sdg_number ?>" 
                     style="max-width: 200px; margin: 0 auto 30px; display: block;">
                <h1>SDG <?= $sdg_number ?>: <?= $sdg['title'] ?></h1>
                <p class="lead"><?= $sdg['description'] ?></p>
            </div>
        </div>
        
        <div class="sdg-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <?= nl2br(htmlspecialchars($sdg['content'])) ?>
                                
                                <div class="mt-5 text-center">
                                    <a href="?" class="btn btn-primary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to All SDGs
                                    </a>
                                    <a href="?admin=1" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-cog me-2"></i>Admin Panel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif (isset($_GET['admin'])): ?>
        <!-- ADMIN PANEL -->
        <div class="sdg-admin-header">
            <div class="container text-center">
                <h1><i class="fas fa-cogs me-3"></i>SDG Management Panel</h1>
                <p class="lead">Manage all 17 Sustainable Development Goals content</p>
            </div>
        </div>

        <div class="admin-container">
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?= $success_message ?></div>
            <?php endif; ?>
            
            <?php if (isset($init_message)): ?>
                <div class="alert alert-info"><?= $init_message ?></div>
            <?php endif; ?>

            <div class="text-end mb-4">
                <a href="?" class="btn btn-outline-primary">
                    <i class="fas fa-eye me-2"></i>View SDG Pages
                </a>
                <?php if (!count($all_sdgs)): ?>
                    <a href="?admin=1&action=initialize_sdgs" class="btn btn-warning ms-2">
                        <i class="fas fa-database me-2"></i>Initialize SDGs
                    </a>
                <?php endif; ?>
            </div>

            <?php foreach($all_sdgs as $sdg): ?>
            <div class="card sdg-card">
                <div class="sdg-card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bullseye me-2"></i>
                        SDG <?= $sdg['sdg_number'] ?>: <?= $sdg['title'] ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_sdg">
                        <input type="hidden" name="sdg_number" value="<?= $sdg['sdg_number'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control" 
                                           value="<?= htmlspecialchars($sdg['title']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Short Description</label>
                                    <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($sdg['description']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <?php if($sdg['image_path']): ?>
                                        <small class="text-muted">Current: <?= $sdg['image_path'] ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Detailed Content</label>
                                    <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($sdg['content']) ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update SDG <?= $sdg['sdg_number'] ?>
                            </button>
                            <a href="?sdg=<?= $sdg['sdg_number'] ?>" class="btn btn-outline-primary ms-2" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>View Page
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (!count($all_sdgs)): ?>
                <div class="alert alert-warning text-center">
                    <h4>No SDGs Found in Database</h4>
                    <p>Click the "Initialize SDGs" button to create the 17 Sustainable Development Goals entries.</p>
                    <a href="?admin=1&action=initialize_sdgs" class="btn btn-warning">
                        <i class="fas fa-database me-2"></i>Initialize SDGs
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Your existing footer code here -->
    <footer class="footer">
        <!-- Your footer content from original code -->
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>