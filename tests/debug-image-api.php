<?php
/**
 * Debug Image API Test
 * Test script untuk memeriksa konfigurasi dan fungsi Image API
 */

// Ensure this is being run from WordPress context
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

echo "<h1>Auto Nulis Image API Debug Test</h1>";

// 1. Test Settings
echo "<h2>1. Checking Settings</h2>";
$settings = get_option('auto_nulis_settings', array());

echo "<p><strong>Include Images:</strong> " . (isset($settings['include_images']) && $settings['include_images'] ? 'Yes' : 'No') . "</p>";
echo "<p><strong>Image Source:</strong> " . ($settings['image_source'] ?? 'Not set') . "</p>";
echo "<p><strong>Unsplash API Key:</strong> " . (empty($settings['unsplash_api_key']) ? 'Not set' : 'Set (' . strlen($settings['unsplash_api_key']) . ' characters)') . "</p>";
echo "<p><strong>Pexels API Key:</strong> " . (empty($settings['pexels_api_key']) ? 'Not set' : 'Set (' . strlen($settings['pexels_api_key']) . ' characters)') . "</p>";

// 2. Test Image Handler Class
echo "<h2>2. Testing Image Handler Class</h2>";

if (!class_exists('Auto_Nulis_Image')) {
    echo "<p style='color: red;'>❌ Auto_Nulis_Image class not found!</p>";
} else {
    echo "<p style='color: green;'>✅ Auto_Nulis_Image class found</p>";
    
    try {
        $image_handler = new Auto_Nulis_Image();
        echo "<p style='color: green;'>✅ Image handler instantiated successfully</p>";
        
        // Test get_relevant_image method
        echo "<h3>Testing get_relevant_image method</h3>";
        $test_keyword = 'technology';
        $image_source = $settings['image_source'] ?? 'unsplash';
        
        echo "<p>Testing keyword: <strong>{$test_keyword}</strong></p>";
        echo "<p>Image source: <strong>{$image_source}</strong></p>";
        
        $result = $image_handler->get_relevant_image($test_keyword, $image_source);
        
        if ($result) {
            echo "<p style='color: green;'>✅ Image found successfully!</p>";
            echo "<pre>" . print_r($result, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ No image found or API error</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// 3. Test Generator Class
echo "<h2>3. Testing Generator Integration</h2>";

if (!class_exists('Auto_Nulis_Generator')) {
    echo "<p style='color: red;'>❌ Auto_Nulis_Generator class not found!</p>";
} else {
    echo "<p style='color: green;'>✅ Auto_Nulis_Generator class found</p>";
    
    try {
        $generator = new Auto_Nulis_Generator();
        echo "<p style='color: green;'>✅ Generator instantiated successfully</p>";
        
        // Check if image handler is initialized in generator
        $reflection = new ReflectionClass($generator);
        $image_handler_property = $reflection->getProperty('image_handler');
        $image_handler_property->setAccessible(true);
        $image_handler_in_generator = $image_handler_property->getValue($generator);
        
        if ($image_handler_in_generator) {
            echo "<p style='color: green;'>✅ Image handler is initialized in generator</p>";
        } else {
            echo "<p style='color: red;'>❌ Image handler not initialized in generator</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// 4. Check WordPress functions
echo "<h2>4. Checking WordPress Functions</h2>";

if (function_exists('wp_remote_get')) {
    echo "<p style='color: green;'>✅ wp_remote_get function available</p>";
} else {
    echo "<p style='color: red;'>❌ wp_remote_get function not available</p>";
}

if (function_exists('download_url')) {
    echo "<p style='color: green;'>✅ download_url function available</p>";
} else {
    echo "<p style='color: red;'>❌ download_url function not available</p>";
}

if (function_exists('media_handle_sideload')) {
    echo "<p style='color: green;'>✅ media_handle_sideload function available</p>";
} else {
    echo "<p style='color: red;'>❌ media_handle_sideload function not available</p>";
}

// 5. Check recent logs
echo "<h2>5. Recent Auto Nulis Logs</h2>";

global $wpdb;
$table_name = $wpdb->prefix . 'auto_nulis_logs';
$table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;

if ($table_exists) {
    $recent_logs = $wpdb->get_results(
        "SELECT * FROM {$table_name} WHERE message LIKE '%image%' OR message LIKE '%Image%' ORDER BY created_at DESC LIMIT 10"
    );
    
    if ($recent_logs) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Time</th><th>Type</th><th>Message</th></tr>";
        foreach ($recent_logs as $log) {
            echo "<tr>";
            echo "<td>" . $log->created_at . "</td>";
            echo "<td>" . $log->type . "</td>";
            echo "<td>" . htmlspecialchars($log->message) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No image-related logs found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Logs table does not exist</p>";
}

echo "<hr>";
echo "<p><strong>Recommendation:</strong></p>";
echo "<ul>";
if (empty($settings['unsplash_api_key']) && empty($settings['pexels_api_key'])) {
    echo "<li>Configure API keys in WordPress Admin → Auto Nulis → Settings → Image Settings</li>";
}
if (!isset($settings['include_images']) || !$settings['include_images']) {
    echo "<li>Enable 'Include Images in Articles' in settings</li>";
}
echo "<li>Try generating a test article to see detailed logs</li>";
echo "<li>Check WordPress error logs for any PHP errors</li>";
echo "</ul>";
?>
