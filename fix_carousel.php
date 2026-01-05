<?php
echo "<h3>Step 11: Fixing Carousel</h3>";

// Read the index.php file
$index_file = __DIR__ . '/index.php';
$content = file_get_contents($index_file);

// Find and fix the carousel section
echo "Looking for carousel section...<br>";

// The carousel section starts with: <section class="welcome-carousel">
$carousel_start = strpos($content, '<section class="welcome-carousel">');

if ($carousel_start !== false) {
    // Find the end of the carousel section
    $carousel_end = strpos($content, '</section>', $carousel_start);
    $carousel_length = $carousel_end - $carousel_start + 9; // 9 = length of '</section>'
    
    // Extract the carousel section
    $carousel_html = substr($content, $carousel_start, $carousel_length);
    
    echo "✅ Found carousel section<br>";
    
    // Check current structure
    if (strpos($carousel_html, '<div class="carousel-btns">') !== false) {
        echo "⚠ Found empty carousel-btns div (this might be the problem)<br>";
    }
    
    // Count closing divs vs opening divs
    $opening_divs = substr_count($carousel_html, '<div');
    $closing_divs = substr_count($carousel_html, '</div>');
    
    echo "Opening divs: $opening_divs, Closing divs: $closing_divs<br>";
    
    if ($closing_divs > $opening_divs) {
        echo "❌ PROBLEM: More closing divs than opening divs!<br>";
    }
    
    // Create the corrected carousel HTML
    $correct_carousel = <<<HTML
<!-- WELCOME CAROUSEL - FIXED VERSION -->
<section class="welcome-carousel">
    <div class="carousel-slide slide-1 active"></div>
    <div class="carousel-slide slide-2"></div>
    <div class="carousel-slide slide-3"></div>
    <div class="carousel-slide slide-4"></div>

    <div class="carousel-indicators">
        <div class="indicator active" onclick="goToSlide(0)"></div>
        <div class="indicator" onclick="goToSlide(1)"></div>
        <div class="indicator" onclick="goToSlide(2)"></div>
        <div class="indicator" onclick="goToSlide(3)"></div>
    </div>
</section>
HTML;
    
    // Replace the old carousel with the fixed one
    $new_content = substr_replace($content, $correct_carousel, $carousel_start, $carousel_length);
    
    // Save the file
    file_put_contents($index_file, $new_content);
    
    echo "✅ Carousel HTML structure fixed<br>";
    
} else {
    echo "❌ Could not find carousel section<br>";
}

// ==================== PART 2: Check JavaScript ====================
echo "<h4>Checking JavaScript...</h4>";

// Check if JavaScript functions exist
$js_functions = ['showSlide', 'nextSlide', 'prevSlide', 'goToSlide'];

foreach ($js_functions as $function) {
    if (strpos($content, "function $function") !== false) {
        echo "✅ Function '$function' found<br>";
    } else {
        echo "❌ Function '$function' NOT found<br>";
    }
}

// ==================== PART 3: Create Test Page ====================
echo "<h4>Creating Carousel Test Page...</h4>";

$test_page = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carousel Test</title>
    <style>
        .test-carousel {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .test-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }
        
        .test-slide.active {
            opacity: 1;
        }
        
        .test-slide-1 { background-image: url('images/logos.jpg'); }
        .test-slide-2 { background-image: url('images/femsusco.jpeg'); }
        .test-slide-3 { background-image: url('images/54.jpg'); }
        .test-slide-4 { background-image: url('images/msu.jpg'); }
        
        .test-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .test-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            border: 2px solid white;
        }
        
        .test-indicator.active {
            background: #FFD700;
        }
        
        .test-controls {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 10;
        }
        
        .test-btn {
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <h2>Carousel Test Page</h2>
    <p>Testing if basic carousel works without your main site's CSS/JS conflicts.</p>
    
    <div class="test-carousel">
        <div class="test-slide test-slide-1 active"></div>
        <div class="test-slide test-slide-2"></div>
        <div class="test-slide test-slide-3"></div>
        <div class="test-slide test-slide-4"></div>
        
        <div class="test-controls">
            <button class="test-btn" onclick="testPrevSlide()">❮</button>
            <button class="test-btn" onclick="testNextSlide()">❯</button>
        </div>
        
        <div class="test-indicators">
            <div class="test-indicator active" onclick="testGoToSlide(0)"></div>
            <div class="test-indicator" onclick="testGoToSlide(1)"></div>
            <div class="test-indicator" onclick="testGoToSlide(2)"></div>
            <div class="test-indicator" onclick="testGoToSlide(3)"></div>
        </div>
    </div>
    
    <div style="margin-top: 20px; padding: 10px; background: #f0f0f0;">
        <h3>Test Results:</h3>
        <p id="test-result">Carousel should auto-slide every 5 seconds</p>
        <p>Current Slide: <span id="current-slide">1</span>/4</p>
    </div>
    
    <script>
        // Simple test carousel
        let testCurrentSlide = 0;
        const testSlides = document.querySelectorAll('.test-slide');
        const testIndicators = document.querySelectorAll('.test-indicator');
        const totalTestSlides = testSlides.length;
        
        function testShowSlide(n) {
            testSlides.forEach(slide => slide.classList.remove('active'));
            testIndicators.forEach(indicator => indicator.classList.remove('active'));
            
            testCurrentSlide = n;
            
            testSlides[testCurrentSlide].classList.add('active');
            testIndicators[testCurrentSlide].classList.add('active');
            
            document.getElementById('current-slide').textContent = testCurrentSlide + 1;
        }
        
        function testNextSlide() {
            testCurrentSlide = (testCurrentSlide + 1) % totalTestSlides;
            testShowSlide(testCurrentSlide);
        }
        
        function testPrevSlide() {
            testCurrentSlide = (testCurrentSlide - 1 + totalTestSlides) % totalTestSlides;
            testShowSlide(testCurrentSlide);
        }
        
        function testGoToSlide(n) {
            testShowSlide(n);
        }
        
        // Auto slide every 5 seconds
        setInterval(testNextSlide, 5000);
        
        // Initialize
        testShowSlide(0);
        
        document.getElementById('test-result').textContent = "Carousel working! Auto-sliding every 5 seconds.";
    </script>
    
    <div style="margin-top: 20px;">
        <a href="index.php" style="display: inline-block; padding: 10px 20px; background: #8B0000; color: white; text-decoration: none;">Back to Main Site</a>
    </div>
</body>
</html>
HTML;

file_put_contents(__DIR__ . '/test_carousel.html', $test_page);
echo "✅ Created test page: <a href='test_carousel.html' target='_blank'>test_carousel.html</a><br>";

// ==================== PART 4: Quick JavaScript Check ====================
echo "<h4>Quick JavaScript Check...</h4>";

// Check if there are JavaScript errors in the main page
echo "To check for JavaScript errors:<br>";
echo "1. Open your website (F12 for Developer Tools)<br>";
echo "2. Go to Console tab<br>";
echo "3. Refresh the page<br>";
echo "4. Look for red error messages<br>";

echo "<hr><h4>Common Carousel Issues:</h4>";
echo "1. ❌ JavaScript errors (check browser console)<br>";
echo "2. ❌ Missing CSS classes (.carousel-slide.active)<br>";
echo "3. ❌ Incorrect image paths<br>";
echo "4. ❌ Conflicting JavaScript libraries<br>";

echo "<hr><h4>Next Steps:</h4>";
echo "1. Test the carousel: <a href='http://localhost/msu_portal/' target='_blank'>Main Site</a><br>";
echo "2. Test simple carousel: <a href='test_carousel.html' target='_blank'>Test Page</a><br>";
echo "3. If test page works but main site doesn't → JavaScript conflict<br>";
echo "4. If neither works → CSS/HTML issue<br>";
?>