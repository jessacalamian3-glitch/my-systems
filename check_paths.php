<?php
echo "<h3>STEP 5: Checking Image Paths in HTML/CSS</h3>";

// Read the index.php file
$content = file_get_contents(__DIR__ . '/index.php');

// Find all image references in the file
echo "<h4>Image Paths Found in index.php:</h4>";

// Patterns to find image paths
$patterns = [
    '/src=["\']([^"\']+\.(?:png|jpg|jpeg|gif|webp))["\']/i',
    '/background-image:\s*url\(["\']?([^"\'\)]+\.(?:png|jpg|jpeg|gif|webp))["\']?\)/i',
    '/url\(["\']?([^"\'\)]+\.(?:png|jpg|jpeg|gif|webp))["\']?\)/i'
];

$all_matches = [];

foreach ($patterns as $pattern) {
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $all_matches[] = $match[1];
    }
}

// Remove duplicates
$all_matches = array_unique($all_matches);

// Check each path
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Path in Code</th><th>File Exists?</th><th>Correct Path?</th><th>Suggested Fix</th></tr>";

foreach ($all_matches as $path) {
    $full_path = __DIR__ . '/' . $path;
    $exists = file_exists($full_path);
    
    // Check if path is correct
    $is_correct = true;
    $suggestion = '';
    
    // Fix common path issues
    if (strpos($path, 'images/') === 0) {
        // Path starts with images/
        if (!file_exists(__DIR__ . '/' . $path)) {
            $is_correct = false;
            $suggestion = "Check if file exists at: " . $path;
        }
    } elseif (strpos($path, 'electron_files/') === 0) {
        // Path starts with electron_files/
        if (!file_exists(__DIR__ . '/' . $path)) {
            $is_correct = false;
            $suggestion = "Check electron_files folder";
        }
    } elseif (!strpos($path, '/') && !file_exists(__DIR__ . '/' . $path)) {
        // File in root but doesn't exist
        $is_correct = false;
        $suggestion = "Try: images/" . $path;
    }
    
    echo "<tr>";
    echo "<td>$path</td>";
    echo "<td>" . ($exists ? '✅' : '❌') . "</td>";
    echo "<td>" . ($is_correct ? '✅' : '❌') . "</td>";
    echo "<td>$suggestion</td>";
    echo "</tr>";
}

echo "</table><hr>";

// Check specific problematic areas from the code
echo "<h4>Problematic Areas to Fix:</h4>";

// 1. Carousel images in CSS
echo "<h5>1. Carousel Images (CSS lines 100-130):</h5>";
$carousel_images = [
    'logos.jpg',
    'femsusco.jpeg', 
    'images/54.jpg',
    'msu.jpg'
];

foreach ($carousel_images as $img) {
    $full = __DIR__ . '/' . $img;
    $exists = file_exists($full) ? '✅' : '❌';
    echo "$img: $exists<br>";
}

echo "<strong>Issue:</strong> Some are in root, some are in images/ folder<br>";
echo "<strong>Fix:</strong> Move all to images/ folder and update CSS<br><br>";

// 2. Check if files are actually accessible via URL
echo "<h5>2. Test Image URLs:</h5>";
echo "<div style='display:flex; flex-wrap:wrap; gap:10px;'>";

$test_urls = [
    'http://localhost/msu_portal/logos.jpg',
    'http://localhost/msu_portal/msulogo.png',
    'http://localhost/msu_portal/images/cit.png',
    'http://localhost/msu_portal/images/54.jpg',
    'http://localhost/msu_portal/transparencie.png',
    'http://localhost/msu_portal/dataprivacy.png',
    'http://localhost/msu_portal/electron_files/electronic.png'
];

foreach ($test_urls as $url) {
    echo "<div style='border:1px solid #ccc; padding:10px; text-align:center;'>";
    echo "<div style='height:100px; width:100px; background:url($url) center/contain no-repeat; margin:0 auto;'></div>";
    echo "<small><a href='$url' target='_blank'>Test</a></small>";
    echo "</div>";
}

echo "</div>";

// Check for missing PHP files for buttons/links
echo "<hr><h4>3. Check Missing PHP Files (for buttons/links):</h4>";

$php_files = [
    'faculty_login.php',
    'student_login.php',
    'university-profile.php',
    'mission-vision.php',
    'campus-officials.php',
    'admission-requirements.php',
    'application-process.php',
    'graduate-programs.php',
    'undergraduate-programs.php',
    'senior-high.php',
    'junior-high.php',
    'college-agriculture.php',
    'college-arts.php',
    'college-education.php',
    'collge-public-affairs.php',
    'college-fisheries.php',
    'college-forestry.php',
    'college-hospitality.php',
    'collge-it.php',
    'college-nursing.php'
];

foreach ($php_files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file) ? '✅' : '❌';
    echo "$file: $exists<br>";
}
?>