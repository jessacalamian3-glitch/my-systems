<?php
echo "<h3>STEP 4: Checking Image Files</h3>";
echo "Current directory: " . __DIR__ . "<br><hr>";

// Check common image locations
$images_to_check = [
    // Logo
    'msulogo.png' => 'Logo sa header',
    'msulogo.png' => 'Logo (alternative)',
    
    // Carousel images
    'logos.jpg' => 'Carousel slide 1',
    'femsusco.jpeg' => 'Carousel slide 2',
    'images/54.jpg' => 'Carousel slide 3',
    'msu.jpg' => 'Carousel slide 4',
    
    // College logos
    'images/cit.png' => 'College of IT',
    'images/agriculture.webp' => 'College of Agriculture',
    'images/cas.webp' => 'College of Arts & Sciences',
    'images/education.png' => 'College of Education',
    'images/cpa.webp' => 'College of Public Affairs',
    'images/hm.png' => 'Hospitality Management',
    'images/envi.webp' => 'Forestry & Environmental',
    'images/nursing.jpg' => 'College of Nursing',
    'images/fisheries.jpg' => 'College of Fisheries',
    
    // Full width section
    'transparencie.png' => 'Transparency image',
    'dataprivacy.png' => 'Data Privacy image',
    'electron_files/electronic.png' => 'Electronic FOI image',
    
    // SDG images
    'images/s1.png' => 'SDG 1',
    'images/s2.png' => 'SDG 2',
    'images/s3.png' => 'SDG 3',
    'images/s4.png' => 'SDG 4',
    'images/s5.png' => 'SDG 5',
    'images/s6.png' => 'SDG 6',
    'images/s7.png' => 'SDG 7',
    'images/s8.png' => 'SDG 8',
    'images/s9.png' => 'SDG 9',
    'images/s10.png' => 'SDG 10',
    'images/s11.png' => 'SDG 11',
    'images/s12.png' => 'SDG 12',
    'images/s13.png' => 'SDG 13',
    'images/s14.png' => 'SDG 14',
    'images/s15.png' => 'SDG 15',
    'images/s16.png' => 'SDG 16',
    'images/s17.png' => 'SDG 17',
    'images/sustainable_goals.png' => 'SDG Banner',
];

echo "<h4>Checking Image Files:</h4>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Image</th><th>Description</th><th>Status</th><th>Full Path</th></tr>";

foreach ($images_to_check as $path => $description) {
    $full_path = __DIR__ . '/' . $path;
    
    if (file_exists($full_path)) {
        $status = "<span style='color:green;'>✅ EXISTS</span>";
    } else {
        $status = "<span style='color:red;'>❌ MISSING</span>";
    }
    
    echo "<tr>";
    echo "<td>$path</td>";
    echo "<td>$description</td>";
    echo "<td>$status</td>";
    echo "<td>$full_path</td>";
    echo "</tr>";
}

echo "</table><hr>";

// Check folder structure
echo "<h4>Folder Structure Check:</h4>";
$folders = ['', 'images/', 'electron_files/'];

foreach ($folders as $folder) {
    $folder_path = __DIR__ . '/' . $folder;
    
    if (is_dir($folder_path)) {
        echo "✅ Folder: $folder_path<br>";
        $files = scandir($folder_path);
        echo "Files: " . count($files) . " items<br>";
        
        // List first 10 files
        $count = 0;
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;• $file<br>";
                $count++;
                if ($count >= 10) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;... and more<br>";
                    break;
                }
            }
        }
        echo "<br>";
    } else {
        echo "❌ Folder missing: $folder_path<br><br>";
    }
}

// Test URL access
echo "<h4>Testing URL Access to Images:</h4>";
$test_images = ['msulogo.png', 'images/cit.png', 'transparencie.png'];

foreach ($test_images as $image) {
    $url = "http://localhost/msu_portal/$image";
    echo "Testing: <a href='$url' target='_blank'>$image</a><br>";
}
?>