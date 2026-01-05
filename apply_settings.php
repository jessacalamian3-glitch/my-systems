<?php
// config/apply_settings.php

function applyUserSettings() {
    // Check if session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // If not logged in, return defaults
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return ['theme' => 'maroon', 'language' => 'en'];
    }
    
    $faculty_id = $_SESSION['username'];
    
    // If already in session, use it
    if (isset($_SESSION['theme_color']) && isset($_SESSION['language'])) {
        return [
            'theme' => $_SESSION['theme_color'],
            'language' => $_SESSION['language']
        ];
    }
    
    // If not in session, get from database
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT theme_color, language FROM faculty_settings WHERE faculty_id = ?");
        $stmt->execute([$faculty_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings) {
            $_SESSION['theme_color'] = $settings['theme_color'];
            $_SESSION['language'] = $settings['language'];
            return [
                'theme' => $settings['theme_color'],
                'language' => $settings['language']
            ];
        }
    } catch(PDOException $e) {
        // If error, use defaults
    }
    
    // Default values
    $_SESSION['theme_color'] = 'maroon';
    $_SESSION['language'] = 'en';
    return ['theme' => 'maroon', 'language' => 'en'];
}

// Function to get theme CSS
function getThemeCSS($theme) {
    $colors = [
        'maroon' => ['primary' => '#800000', 'secondary' => '#5a0000'],
        'blue' => ['primary' => '#007bff', 'secondary' => '#0056b3'],
        'green' => ['primary' => '#28a745', 'secondary' => '#1e7e34'],
        'purple' => ['primary' => '#6f42c1', 'secondary' => '#5a36a8'],
        'dark' => ['primary' => '#343a40', 'secondary' => '#23272b']
    ];
    
    $color = $colors[$theme] ?? $colors['maroon'];
    
    return "
    <style>
        :root {
            --maroon: {$color['primary']};
            --maroon-dark: {$color['secondary']};
        }
    </style>
    ";
}
?>
