<?php
/**
 * Debug Scheduled Generation Issue
 * Test script to debug why scheduled generation can't find keywords
 */

// Try to load WordPress
$wp_load_paths = [
    dirname(__FILE__) . '/../../../../wp-load.php',
    dirname(__FILE__) . '/../../../wp-load.php', 
    dirname(__FILE__) . '/../../wp-load.php',
    dirname(__FILE__) . '/wp-load.php'
];

$wp_loaded = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('WordPress not found. Please run this from your WordPress installation.');
}

echo "<h1>Debug Scheduled Generation Issue</h1>";

// Load the generator class
if (!class_exists('Auto_Nulis_Generator')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-generator.php');
}

if (!class_exists('Auto_Nulis_API')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-api.php');
}

if (!class_exists('Auto_Nulis_Image')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-image.php');
}

echo "<h2>🔍 Settings Analysis</h2>";

// Get settings directly
$settings = get_option('auto_nulis_settings', array());
echo "<p><strong>Settings found:</strong> " . (empty($settings) ? 'NO' : 'YES') . "</p>";

if (!empty($settings)) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th style='padding: 8px;'>Setting</th><th style='padding: 8px;'>Value</th></tr>";
    
    foreach ($settings as $key => $value) {
        $display_value = $value;
        if (is_bool($value)) {
            $display_value = $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            $display_value = 'Array(' . count($value) . ' items)';
        } elseif (strlen($value) > 100) {
            $display_value = substr($value, 0, 100) . '...';
        }
        
        echo "<tr><td style='padding: 8px;'>$key</td><td style='padding: 8px;'>" . esc_html($display_value) . "</td></tr>";
    }
    echo "</table>";
}

echo "<h2>📝 Keywords Analysis</h2>";

$keywords = isset($settings['keywords']) ? $settings['keywords'] : '';
echo "<p><strong>Keywords raw:</strong> " . (empty($keywords) ? 'EMPTY' : 'FOUND') . "</p>";

if (!empty($keywords)) {
    echo "<p><strong>Keywords length:</strong> " . strlen($keywords) . " characters</p>";
    
    $keyword_list = array_filter(array_map('trim', explode("\n", $keywords)));
    echo "<p><strong>Parsed keywords count:</strong> " . count($keyword_list) . "</p>";
    
    if (!empty($keyword_list)) {
        echo "<p><strong>Keywords list:</strong></p>";
        echo "<ol>";
        foreach ($keyword_list as $keyword) {
            echo "<li>" . esc_html($keyword) . "</li>";
        }
        echo "</ol>";
    }
}

echo "<h2>🔄 Used Keywords Analysis</h2>";

$used_keywords = get_option('auto_nulis_used_keywords', array());
echo "<p><strong>Used keywords:</strong> " . count($used_keywords) . " items</p>";

if (!empty($used_keywords)) {
    echo "<ul>";
    foreach ($used_keywords as $used) {
        echo "<li>" . esc_html($used) . "</li>";
    }
    echo "</ul>";
}

// Calculate available keywords
if (!empty($keyword_list)) {
    $available_keywords = array_diff($keyword_list, $used_keywords);
    echo "<p><strong>Available keywords:</strong> " . count($available_keywords) . " items</p>";
    
    if (!empty($available_keywords)) {
        echo "<ul>";
        foreach ($available_keywords as $available) {
            echo "<li style='color: green;'>" . esc_html($available) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>All keywords have been used. System should reset used keywords list.</p>";
    }
}

echo "<h2>🧪 Generator Test</h2>";

if (isset($_POST['test_generator'])) {
    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007cba; margin: 20px 0;'>";
    echo "<h3>Generator Test Results</h3>";
    
    try {
        // Create generator instance
        $generator = new Auto_Nulis_Generator();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($generator);
        $get_next_keyword_method = $reflection->getMethod('get_next_keyword');
        $get_next_keyword_method->setAccessible(true);
        
        $keyword = $get_next_keyword_method->invoke($generator);
        
        echo "<p><strong>get_next_keyword() result:</strong> " . ($keyword ? esc_html($keyword) : 'NULL/FALSE') . "</p>";
        
        if ($keyword) {
            echo "<p style='color: green;'>✅ Keyword retrieval SUCCESSFUL</p>";
        } else {
            echo "<p style='color: red;'>❌ Keyword retrieval FAILED</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Generator test error: " . esc_html($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

echo "<h2>🔧 Fix Actions</h2>";

echo "<form method='post' style='margin: 20px 0;'>";
echo "<input type='submit' name='test_generator' value='Test Generator Keyword Retrieval' class='button button-primary' style='margin-right: 10px;'>";
echo "</form>";

if (isset($_POST['reset_used_keywords'])) {
    delete_option('auto_nulis_used_keywords');
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
    echo "<p style='color: #155724;'>✅ Used keywords list has been reset!</p>";
    echo "</div>";
    echo "<script>window.location.reload();</script>";
}

echo "<form method='post' style='margin: 20px 0;'>";
echo "<input type='submit' name='reset_used_keywords' value='Reset Used Keywords' class='button button-secondary' style='margin-right: 10px;'>";
echo "</form>";

echo "<h2>💡 Possible Solutions</h2>";
echo "<ol>";
echo "<li><strong>Settings Not Loading:</strong> Generator constructor might not be loading settings properly during cron</li>";
echo "<li><strong>Empty Keywords:</strong> Keywords field might be empty or contain only whitespace</li>";
echo "<li><strong>All Keywords Used:</strong> All keywords marked as used, reset mechanism not working</li>";
echo "<li><strong>Different Context:</strong> Cron runs in different context than admin, settings might not be accessible</li>";
echo "</ol>";

echo "<h2>🔍 Debugging Steps</h2>";
echo "<ol>";
echo "<li>Check if settings are properly loaded in constructor</li>";
echo "<li>Add logging to get_next_keyword() method</li>";
echo "<li>Verify keywords are not empty during scheduled execution</li>";
echo "<li>Ensure used keywords reset is working properly</li>";
echo "</ol>";

?>
