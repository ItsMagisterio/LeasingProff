<?php
// Theme Viewer - A simple script to preview the car-leasing theme files

// Basic HTML header
function output_header($title = 'Car Leasing Theme Viewer') {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            /* Include custom CSS from the theme */
            ' . file_get_contents('./wp-content/themes/car-leasing/css/main.css') . '
            
            /* Additional viewer styles */
            pre {
                background-color: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 0.25rem;
                padding: 1rem;
                overflow: auto;
            }
            .file-list {
                list-style-type: none;
                padding-left: 0;
            }
            .file-list li {
                padding: 0.5rem;
                border-bottom: 1px solid #e9ecef;
            }
            .file-list li:last-child {
                border-bottom: none;
            }
            .directory-link {
                font-weight: bold;
            }
            .theme-preview {
                border: 1px solid #dee2e6;
                border-radius: 0.25rem;
                padding: 2rem;
                margin-bottom: 2rem;
            }
        </style>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="theme-viewer.php">Car Leasing Theme Viewer</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="theme-viewer.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="theme-viewer.php?action=preview_front_page">Preview Front Page</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="theme-viewer.php?action=preview_dashboard">Preview Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="theme-viewer.php?action=preview_marketplace">Preview Marketplace</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">';
}

// Basic HTML footer
function output_footer() {
    echo '</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}

// List directory contents
function list_directory($dir, $base_path = '') {
    if (empty($base_path)) {
        $base_path = $dir;
    }
    
    $items = scandir($dir);
    echo '<ul class="file-list">';
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        $relative_path = substr($path, strlen($base_path) + 1);
        
        if (is_dir($path)) {
            echo '<li><i class="fas fa-folder text-warning"></i> <a class="directory-link" href="?dir=' . urlencode($path) . '">' . htmlspecialchars($item) . '/</a></li>';
        } else {
            echo '<li><i class="fas fa-file text-secondary"></i> <a href="?file=' . urlencode($path) . '">' . htmlspecialchars($item) . '</a></li>';
        }
    }
    
    echo '</ul>';
}

// Display file contents
function display_file($file) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $content = file_get_contents($file);
    
    echo '<h2>' . htmlspecialchars(basename($file)) . '</h2>';
    echo '<div><strong>Path:</strong> ' . htmlspecialchars($file) . '</div>';
    
    if ($extension === 'php' || $extension === 'css' || $extension === 'js' || $extension === 'html') {
        echo '<pre><code>' . htmlspecialchars($content) . '</code></pre>';
    } else if ($extension === 'svg') {
        echo '<div class="p-3 bg-light rounded">' . $content . '</div>';
    } else {
        echo '<p>This file type cannot be previewed directly.</p>';
    }
}

// Mock WordPress functions and include template files safely
function include_template($file) {
    // This is a simplified version for preview purposes only
    ob_start();
    
    // Define some basic WordPress functions that might be used in the templates
    if (!function_exists('get_header')) {
        function get_header() { echo '<header class="site-header mock"><div class="container"><h1>Mock WordPress Header</h1></div></header>'; }
        function get_footer() { echo '<footer class="site-footer mock"><div class="container"><p>Mock WordPress Footer</p></div></footer>'; }
        function get_template_part($slug, $name = null) { 
            echo "<div class=\"alert alert-info\">Template part would be included here: $slug";
            if ($name) echo "-$name";
            echo ".php</div>";
        }
        function esc_html_e($text, $domain = '') { echo htmlspecialchars($text); }
        function esc_html__($text, $domain = '') { return htmlspecialchars($text); }
        function esc_attr_e($text, $domain = '') { echo htmlspecialchars($text); }
        function esc_attr__($text, $domain = '') { return htmlspecialchars($text); }
        function esc_url($url) { return $url; }
        function home_url($path = '') { return '/' . ltrim($path, '/'); }
        function wp_get_current_user() { return (object)['display_name' => 'Demo User']; }
        function is_user_logged_in() { return true; }
    }
    
    // Include the file, capturing any errors
    try {
        include($file);
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
    
    $output = ob_get_clean();
    return $output;
}

// Main logic
output_header();

$action = $_GET['action'] ?? '';
$dir = $_GET['dir'] ?? '';
$file = $_GET['file'] ?? '';

if ($action === 'preview_front_page') {
    echo '<h1 class="mb-4">Front Page Preview</h1>';
    echo '<div class="theme-preview">';
    echo include_template('./wp-content/themes/car-leasing/front-page.php');
    echo '</div>';
} else if ($action === 'preview_dashboard') {
    echo '<h1 class="mb-4">Dashboard Preview</h1>';
    echo '<div class="theme-preview">';
    echo include_template('./wp-content/themes/car-leasing/page-templates/dashboard-client.php');
    echo '</div>';
} else if ($action === 'preview_marketplace') {
    echo '<h1 class="mb-4">Marketplace Preview</h1>';
    echo '<div class="theme-preview">';
    echo include_template('./wp-content/themes/car-leasing/page-templates/vehicle-marketplace.php');
    echo '</div>';
} else if (!empty($file)) {
    display_file($file);
} else {
    echo '<h1 class="mb-4">Car Leasing Theme Explorer</h1>';
    
    if (empty($dir)) {
        $dir = './wp-content/themes/car-leasing';
    }
    
    echo '<nav aria-label="breadcrumb">';
    echo '<ol class="breadcrumb">';
    
    $parts = explode('/', $dir);
    $path = '';
    
    foreach ($parts as $i => $part) {
        $path .= ($i > 0 ? '/' : '') . $part;
        if (empty($part)) continue;
        
        if ($i === count($parts) - 1) {
            echo '<li class="breadcrumb-item active">' . htmlspecialchars($part) . '</li>';
        } else {
            echo '<li class="breadcrumb-item"><a href="?dir=' . urlencode($path) . '">' . htmlspecialchars($part) . '</a></li>';
        }
    }
    
    echo '</ol>';
    echo '</nav>';
    
    echo '<div class="card">';
    echo '<div class="card-header"><strong>Directory:</strong> ' . htmlspecialchars($dir) . '</div>';
    echo '<div class="card-body">';
    list_directory($dir, './wp-content/themes');
    echo '</div></div>';
}

output_footer();
?>