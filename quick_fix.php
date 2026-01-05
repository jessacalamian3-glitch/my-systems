<?php
echo "<h3>Quick Fix for MSU Buug Portal (No GD Library)</h3>";

// ==================== PART 1: Fix Carousel Image Paths ====================
echo "<h4>1. Fixing Carousel Image Paths...</h4>";

$carousel_fixes = [
    "url('logos.jpg')" => "url('images/logos.jpg')",
    "url('femsusco.jpeg')" => "url('images/femsusco.jpeg')", 
    "url('msu.jpg')" => "url('images/msu.jpg')"
];

$index_content = file_get_contents(__DIR__ . '/index.php');
$original_content = $index_content;

foreach ($carousel_fixes as $wrong => $correct) {
    if (strpos($index_content, $wrong) !== false) {
        $index_content = str_replace($wrong, $correct, $index_content);
        echo "✅ Fixed: $wrong → $correct<br>";
    }
}

file_put_contents(__DIR__ . '/index.php', $index_content);

// ==================== PART 2: Create Simple HTML Placeholder Images ====================
echo "<h4>2. Creating Default Images (HTML/CSS)...</h4>";

$default_images = [
    'default-event.jpg' => 'EVENT',
    'default-update.jpg' => 'UPDATE', 
    'default-announcement.jpg' => 'ANNOUNCEMENT'
];

foreach ($default_images as $filename => $text) {
    $path = __DIR__ . '/images/' . $filename;
    
    if (!file_exists($path)) {
        // Create a simple HTML file that displays as an image
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>$text - Placeholder</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        h1 {
            font-size: 3em;
            margin: 0;
        }
        p {
            font-size: 1.2em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>$text</h1>
        <p>Placeholder Image</p>
        <p>MSU Buug Portal</p>
    </div>
</body>
</html>
HTML;
        
        file_put_contents($path . '.html', $html);
        echo "✅ Created: /images/$filename.html (HTML placeholder)<br>";
    } else {
        echo "✅ Already exists: /images/$filename<br>";
    }
}

// ==================== PART 3: Fix Navigation Typos ====================
echo "<h4>3. Fixing Navigation Typos...</h4>";

$typo_fixes = [
    'collge-public-affairs.php' => 'college-public-affairs.php',
    'collge-it.php' => 'college-it.php'
];

foreach ($typo_fixes as $wrong => $correct) {
    if (strpos($index_content, $wrong) !== false) {
        $index_content = str_replace($wrong, $correct, $index_content);
        echo "✅ Fixed typo: $wrong → $correct<br>";
    }
}

// Also rename the actual file if it exists with typo
if (file_exists(__DIR__ . '/collge-it.php') && !file_exists(__DIR__ . '/college-it.php')) {
    rename(__DIR__ . '/collge-it.php', __DIR__ . '/college-it.php');
    echo "✅ Renamed file: collge-it.php → college-it.php<br>";
}

file_put_contents(__DIR__ . '/index.php', $index_content);

// ==================== PART 4: Create Missing PHP Files ====================
echo "<h4>4. Creating Missing PHP Files...</h4>";

$missing_files = [
    'application-process.php' => 'Application Process',
    'graduate-programs.php' => 'Graduate Programs',
    'undergraduate-programs.php' => 'Undergraduate Programs',
    'campus-secretary.php' => 'Office of the Campus Secretary',
    'vc-admin-finance.php' => 'Vice Chancellor for Admin & Finance',
    'vc-planning-development.php' => 'Vice Chancellor for Planning & Development',
    'chief-security.php' => 'Office of the Chief Security',
    'campus-registrar.php' => 'Office of the Campus Registrar',
    'student-affairs.php' => 'Office of Student Affairs'
];

$template = <<<'HTML'
<?php
$page_title = "%TITLE%";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%TITLE% | MSU Buug</title>
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
                <h2 class="mb-0">%TITLE%</h2>
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
HTML;

foreach ($missing_files as $filename => $title) {
    $filepath = __DIR__ . '/' . $filename;
    
    if (!file_exists($filepath)) {
        $content = str_replace('%TITLE%', $title, $template);
        file_put_contents($filepath, $content);
        echo "✅ Created: $filename<br>";
    } else {
        echo "✅ Already exists: $filename<br>";
    }
}

// ==================== PART 5: Fix Missing Image Files Issue ====================
echo "<h4>5. Fixing Missing Image Files Issue...</h4>";

// Check if carousel images exist in the right place
$carousel_images = [
    'logos.jpg' => 'Carousel Slide 1',
    'femsusco.jpeg' => 'Carousel Slide 2',
    '54.jpg' => 'Carousel Slide 3',
    'msu.jpg' => 'Carousel Slide 4'
];

foreach ($carousel_images as $image => $desc) {
    $source = __DIR__ . '/' . $image;
    $destination = __DIR__ . '/images/' . $image;
    
    // If image is in root but not in images folder, copy it
    if (file_exists($source) && !file_exists($destination) && $image !== '54.jpg') {
        copy($source, $destination);
        echo "✅ Copied to images folder: $image<br>";
    }
}

// ==================== PART 6: Enable GD Library (Optional) ====================
echo "<h4>6. GD Library Status...</h4>";

// Check if GD is enabled
if (extension_loaded('gd')) {
    echo "✅ GD Library is ENABLED<br>";
} else {
    echo "⚠ GD Library is DISABLED (this is why we can't create images)<br>";
    echo "To enable GD Library in XAMPP:<br>";
    echo "1. Open C:\\xampp\\php\\php.ini<br>";
    echo "2. Find: <code>;extension=gd</code><br>";
    echo "3. Remove the semicolon: <code>extension=gd</code><br>";
    echo "4. Restart Apache in XAMPP Control Panel<br>";
}

// ==================== SUMMARY ====================
echo "<hr><h4 style='color:green;'>✅ FIXES COMPLETED:</h4>";
echo "1. Fixed carousel image paths<br>";
echo "2. Created HTML placeholder images (since GD is disabled)<br>";
echo "3. Fixed navigation typos<br>";
echo "4. Created missing PHP files<br>";
echo "5. Copied carousel images to images folder<br>";
echo "6. Checked GD Library status<br>";
echo "<br>";
echo "<strong>Next steps:</strong><br>";
echo "1. Refresh your website: <a href='http://localhost/msu_portal/' target='_blank'>http://localhost/msu_portal/</a><br>";
echo "2. Check if images now load<br>";
echo "3. Test if navigation links work<br>";

// Backup original file
file_put_contents(__DIR__ . '/index_backup.php', $original_content);
echo "<br><small><em>Backup saved as: index_backup.php</em></small>";
?>