<?php
/**
 * Debug script to check plugin files and syntax
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin_dir = plugin_dir_path(__FILE__);
$files_to_check = array(
    'auto-post-generator.php',
    'includes/class-settings.php',
    'includes/class-post-generator.php',
    'includes/class-ideas-generator.php',
    'includes/class-post-ideas-cpt.php',
    'includes/class-scheduler.php',
    'admin/class-admin.php',
    'admin/class-admin-pages.php',
    'admin/class-admin-ajax.php',
);

echo "<h2>Auto Post Generator - Debug Information</h2>";
echo "<h3>PHP Version: " . PHP_VERSION . "</h3>";
echo "<h3>WordPress Version: " . get_bloginfo('version') . "</h3>";

echo "<h3>File Checks:</h3>";
foreach ($files_to_check as $file) {
    $file_path = $plugin_dir . $file;
    echo "<strong>$file:</strong> ";
    
    if (file_exists($file_path)) {
        echo "✓ Exists ";
        
        // Check if file is readable
        if (is_readable($file_path)) {
            echo "✓ Readable ";
            
            // Check basic syntax by including the file
            try {
                ob_start();
                include_once($file_path);
                ob_end_clean();
                echo "✓ Syntax OK";
            } catch (Exception $e) {
                echo "✗ Syntax Error: " . $e->getMessage();
            }
        } else {
            echo "✗ Not readable";
        }
    } else {
        echo "✗ Missing";
    }
    echo "<br>";
}

echo "<h3>Class Existence Check:</h3>";
$classes_to_check = array(
    'Auto_Post_Generator',
    'Auto_Post_Generator_Settings',
    'Auto_Post_Generator_Post_Generator',
    'Auto_Post_Generator_Ideas_Generator',
    'Auto_Post_Generator_Post_Ideas_CPT',
    'Auto_Post_Generator_Scheduler',
    'Auto_Post_Generator_Admin',
    'Auto_Post_Generator_Admin_Pages',
    'Auto_Post_Generator_Admin_Ajax',
);

foreach ($classes_to_check as $class) {
    echo "<strong>$class:</strong> ";
    if (class_exists($class)) {
        echo "✓ Exists<br>";
    } else {
        echo "✗ Missing<br>";
    }
}

echo "<h3>Constants Check:</h3>";
$constants_to_check = array(
    'AUTO_POST_GENERATOR_VERSION',
    'AUTO_POST_GENERATOR_PLUGIN_URL',
    'AUTO_POST_GENERATOR_PLUGIN_PATH',
    'AUTO_POST_GENERATOR_PLUGIN_FILE',
    'AUTO_POST_GENERATOR_TEXT_DOMAIN',
);

foreach ($constants_to_check as $constant) {
    echo "<strong>$constant:</strong> ";
    if (defined($constant)) {
        echo "✓ Defined: " . constant($constant) . "<br>";
    } else {
        echo "✗ Not defined<br>";
    }
}