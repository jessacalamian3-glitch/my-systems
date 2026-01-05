<?php
echo "<h3>Step 13: Fix Carousel Only</h3>";

$index_file = __DIR__ . '/index.php';
$content = file_get_contents($index_file);

// Find the JavaScript <script> tag
$script_start = strpos($content, '<script>');
$script_end = strpos($content, '</script>', $script_start);

if ($script_start !== false && $script_end !== false) {
    echo "✅ Found JavaScript section<br>";
    
    // Get the current JavaScript
    $current_js = substr($content, $script_start, ($script_end - $script_start) + 9);
    
    // Simple check: Does it have carousel functions?
    $has_carousel = (strpos($current_js, 'function showSlide') !== false);
    
    if ($has_carousel) {
        echo "✅ Carousel functions exist<br>";
        
        // Check if DOMContentLoaded is there
        $has_dom_loaded = (strpos($current_js, 'DOMContentLoaded') !== false);
        
        if (!$has_dom_loaded) {
            echo "⚠ No DOMContentLoaded event found<br>";
            
            // Add a simple fix
            $simple_fix = <<<JS
<script>
// FIXED CAROUSEL - SIMPLE VERSION
console.log("Carousel script loading...");

// Wait for page to load
window.addEventListener('load', function() {
    console.log("Page loaded, initializing carousel...");
    
    // Carousel variables
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    
    console.log("Found " + slides.length + " slides");
    
    // Function to show slide
    function showSlide(n) {
        // Remove active from all
        for (let slide of slides) {
            slide.classList.remove('active');
        }
        for (let indicator of indicators) {
            indicator.classList.remove('active');
        }
        
        // Set current
        currentSlide = n;
        
        // Add active to current
        if (slides[currentSlide]) {
            slides[currentSlide].classList.add('active');
        }
        if (indicators[currentSlide]) {
            indicators[currentSlide].classList.add('active');
        }
    }
    
    // Next slide
    function nextSlide() {
        let next = currentSlide + 1;
        if (next >= slides.length) next = 0;
        showSlide(next);
    }
    
    // Previous slide
    function prevSlide() {
        let prev = currentSlide - 1;
        if (prev < 0) prev = slides.length - 1;
        showSlide(prev);
    }
    
    // Go to specific slide
    function goToSlide(n) {
        showSlide(n);
    }
    
    // Make functions available globally
    window.showSlide = showSlide;
    window.nextSlide = nextSlide;
    window.prevSlide = prevSlide;
    window.goToSlide = goToSlide;
    
    // Initialize
    showSlide(0);
    
    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);
    
    console.log("Carousel initialized!");
});
</script>
JS;
            
            // Replace the entire script
            $new_content = substr_replace($content, $simple_fix, $script_start, ($script_end - $script_start) + 9);
            file_put_contents($index_file, $new_content);
            
            echo "✅ Replaced with simple carousel script<br>";
            
        } else {
            echo "✅ DOMContentLoaded event exists<br>";
            echo "The issue might be something else.<br>";
            echo "Please check browser console (F12 > Console) for errors.<br>";
        }
        
    } else {
        echo "❌ No carousel functions found in JavaScript<br>";
    }
    
} else {
    echo "❌ No JavaScript section found<br>";
}

echo "<hr><h4>What to do next:</h4>";
echo "1. Refresh your website: <a href='http://localhost/msu_portal/' target='_blank'>Click here</a><br>";
echo "2. Open browser console (F12 > Console)<br>";
echo "3. Look for 'Carousel script loading...' message<br>";
echo "4. If you see that message, carousel should work<br>";
echo "5. If not, tell me what error you see in console<br>";

echo "<hr><h4>If still not working:</h4>";
echo "We can try a completely different approach - use Bootstrap Carousel instead.<br>";
echo "But first, let's see if this simple fix works.<br>";
?>