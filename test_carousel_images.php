<?php
echo "<h3>Testing Carousel Images Paths</h3>";

$images = [
    'logos.jpg' => 'Slide 1',
    'femsusco.jpeg' => 'Slide 2',
    'images/54.jpg' => 'Slide 3',
    'msu.jpg' => 'Slide 4'
];

echo "<div style='display:flex; flex-wrap:wrap; gap:20px;'>";

foreach ($images as $path => $label) {
    $full_path = __DIR__ . '/' . $path;
    
    if (file_exists($full_path)) {
        $url = "http://localhost/msu_portal/$path";
        echo "<div style='border:2px solid green; padding:10px; text-align:center;'>";
        echo "<strong>$label</strong><br>";
        echo "<img src='$url' style='width:200px; height:150px; object-fit:cover;'><br>";
        echo "Path: $path<br>";
        echo "✅ EXISTS";
        echo "</div>";
    } else {
        echo "<div style='border:2px solid red; padding:10px; text-align:center;'>";
        echo "<strong>$label</strong><br>";
        echo "Path: $path<br>";
        echo "❌ NOT FOUND";
        echo "</div>";
    }
}

echo "</div>";

// Show actual folder structure
echo "<hr><h4>Actual Files in Root:</h4>";
$root_files = scandir(__DIR__);
echo "<div style='column-count:3;'>";
foreach ($root_files as $file) {
    if ($file != '.' && $file != '..') {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo "• $file<br>";
        }
    }
}
echo "</div>";
?>