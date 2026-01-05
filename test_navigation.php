<?php
echo "<h3>Step 7: Testing Navigation Links</h3>";

// Check mga common na PHP files para sa navigation
$nav_links = [
    // Transparency
    'university-profile.php' => 'University Profile',
    'mission-vision.php' => 'Mission & Vision',
    'campus-officials.php' => 'Campus Officials',
    
    // Admission
    'admission-requirements.php' => 'Admission Requirements',
    'application-process.php' => 'Application Process',
    'graduate-programs.php' => 'Graduate Programs',
    'undergraduate-programs.php' => 'Undergraduate Programs',
    
    // Academics - Basic Education
    'senior-high.php' => 'Senior High School',
    'junior-high.php' => 'Junior High School',
    
    // Academics - Colleges
    'college-agriculture.php' => 'College of Agriculture',
    'college-arts.php' => 'College of Arts and Sciences',
    'college-education.php' => 'College of Education',
    'college-public-affairs.php' => 'College of Public Affairs',
    'college-fisheries.php' => 'College of Fisheries',
    'college-forestry.php' => 'College of Forestry',
    'college-hospitality.php' => 'College of Hospitality',
    'college-it.php' => 'College of IT',
    'college-nursing.php' => 'College of Nursing',
    
    // Research & Extension
    'stream-publication.php' => 'Stream Publication',
    'phanalam-publication.php' => 'Phanalam Publication',
    'research-colloquium.php' => 'Research Colloquium',
    
    // Administration
    'vc-research-extensions.php' => 'VC Research & Extensions',
    'campus-secretary.php' => 'Campus Secretary',
    'vc-admin-finance.php' => 'VC Admin & Finance',
    'vc-planning-development.php' => 'VC Planning & Development',
    
    // Offices
    'property-custodian.php' => 'Property Custodian',
    'chief-security.php' => 'Chief Security',
    'campus-registrar.php' => 'Campus Registrar',
    'student-affairs.php' => 'Student Affairs',
    
    // Portals
    'faculty_login.php' => 'Faculty Login',
    'student_login.php' => 'Student Login',
    
    // Others
    'sustainability.php' => 'Sustainability',
    'transparency.php' => 'Transparency',
    'dataprivacy.php' => 'Data Privacy'
];

echo "<table border='1' cellpadding='5' style='width:100%;'>";
echo "<tr><th>PHP File</th><th>Description</th><th>Exists?</th><th>Test Link</th></tr>";

foreach ($nav_links as $file => $desc) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✅ EXISTS' : '❌ MISSING';
    $color = $exists ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>$desc</td>";
    echo "<td style='color:$color;'>$status</td>";
    echo "<td>";
    
    if ($exists) {
        echo "<a href='$file' target='_blank'>Test Link</a>";
    } else {
        // Check for typo or alternative name
        $files_in_dir = scandir(__DIR__);
        $matches = [];
        foreach ($files_in_dir as $f) {
            if (strpos(strtolower($f), strtolower(str_replace(['-', '.php'], ['', ''], $file))) !== false) {
                $matches[] = $f;
            }
        }
        
        if (!empty($matches)) {
            echo "Possible matches: " . implode(', ', $matches);
        }
    }
    
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Check for any PHP errors
echo "<hr><h4>Checking for Common Issues:</h4>";

// 1. Check if there's a header.php file (commonly used)
echo "1. Header.php: " . (file_exists(__DIR__ . '/header.php') ? '✅ EXISTS' : '❌ MISSING') . "<br>";

// 2. Check if there's a footer.php file
echo "2. Footer.php: " . (file_exists(__DIR__ . '/footer.php') ? '✅ EXISTS' : '❌ MISSING') . "<br>";

// 3. Check if there's a config folder
echo "3. Config folder: " . (is_dir(__DIR__ . '/config') ? '✅ EXISTS' : '❌ MISSING') . "<br>";

// 4. Check .htaccess file
echo "4. .htaccess file: " . (file_exists(__DIR__ . '/.htaccess') ? '✅ EXISTS' : '❌ MISSING') . "<br>";

// 5. Check for session start
session_start();
echo "5. PHP Sessions: " . (session_status() === PHP_SESSION_ACTIVE ? '✅ ACTIVE' : '❌ NOT ACTIVE') . "<br>";

// List all PHP files in root for reference
echo "<hr><h4>All PHP Files in Root:</h4>";
$php_files = glob(__DIR__ . '/*.php');
echo "<div style='column-count:3;'>";
foreach ($php_files as $php_file) {
    $filename = basename($php_file);
    echo "• $filename<br>";
}
echo "</div>";
?>