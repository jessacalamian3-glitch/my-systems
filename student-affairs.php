<?php
$page_title = "Office of Student Affairs";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office of Student Affairs | MSU Buug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 800px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #8B0000, #A52A2A);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-university me-2"></i>MSU Buug
            </a>
            <a href="index.php" class="btn btn-light">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header" style="background: #8B0000; color: white;">
                <h2 class="mb-0">Office of Student Affairs</h2>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>This page is currently under development.</strong>
                    <p class="mb-0 mt-2">Content will be added soon. Thank you for your patience.</p>
                </div>
                
                <div class="mt-4">
                    <h4><i class="fas fa-clock me-2"></i>Coming Soon</h4>
                    <p>We are working hard to bring you the best content. Please check back later.</p>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="mt-5 py-3 text-center" style="background: #f8f9fa; border-top: 3px solid #dee2e6;">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Mindanao State University - Buug Campus</p>
        </div>
    </footer>
</body>
</html>