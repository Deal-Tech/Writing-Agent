<?php
/**
 * Test Scheduled Generation Keywords
 * Quick test to verify keyword access during scheduled generation
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

echo "<h1>Test Scheduled Generation Keywords</h1>";

// Simulate scheduled generation context
define('DOING_CRON', true);

// Load required classes
if (!class_exists('Auto_Nulis_Generator')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-generator.php');
}

if (!class_exists('Auto_Nulis_API')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-api.php');
}

if (!class_exists('Auto_Nulis_Image')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-image.php');
}

if (!class_exists('Auto_Nulis_Scheduler')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-scheduler.php');
}

echo "<h2>🔍 Simulating Scheduled Execution</h2>";

echo "<p><strong>DOING_CRON defined:</strong> " . (defined('DOING_CRON') ? 'YES' : 'NO') . "</p>";

// Test 1: Direct settings access
echo "<h3>Test 1: Direct Settings Access</h3>";
$settings = get_option('auto_nulis_settings', array());
$keywords = isset($settings['keywords']) ? $settings['keywords'] : '';
$keyword_count = empty($keywords) ? 0 : count(array_filter(array_map('trim', explode("\n", $keywords))));

echo "<p>✓ Settings loaded: " . (empty($settings) ? 'NO' : 'YES') . "</p>";
echo "<p>✓ Keywords found: " . $keyword_count . " keywords</p>";

// Test 2: Generator initialization
echo "<h3>Test 2: Generator Initialization</h3>";
try {
    $generator = new Auto_Nulis_Generator();
    echo "<p>✅ Generator created successfully</p>";
    
    // Use reflection to test get_next_keyword
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('get_next_keyword');
    $method->setAccessible(true);
    
    $keyword = $method->invoke($generator);
    
    if ($keyword) {
        echo "<p>✅ Keyword retrieved: <strong>" . esc_html($keyword) . "</strong></p>";
    } else {
        echo "<p>❌ No keyword retrieved</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Generator error: " . esc_html($e->getMessage()) . "</p>";
}

// Test 3: Full generation simulation
echo "<h3>Test 3: Full Generation Simulation</h3>";

if (isset($_POST['test_generation'])) {
    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007cba; margin: 20px 0;'>";
    echo "<h4>Generation Test Results:</h4>";
    
    try {
        $generator = new Auto_Nulis_Generator();
        
        // Note: We won't actually generate an article to avoid API costs
        // Just test the keyword retrieval part
        echo "<p>📝 Testing keyword retrieval only (not full generation)...</p>";
        
        $reflection = new ReflectionClass($generator);
        $method = $reflection->getMethod('get_next_keyword');
        $method->setAccessible(true);
        
        $keyword = $method->invoke($generator);
        
        if ($keyword) {
            echo "<p style='color: green;'>✅ SUCCESS: Keyword '<strong>" . esc_html($keyword) . "</strong>' would be used for generation</p>";
            echo "<p>This confirms that scheduled generation should work properly.</p>";
        } else {
            echo "<p style='color: red;'>❌ FAILED: No keyword available for generation</p>";
            echo "<p>This explains why scheduled generation is failing.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ ERROR: " . esc_html($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Test 4: Scheduler test
echo "<h3>Test 4: Scheduler Context</h3>";
try {
    $scheduler = new Auto_Nulis_Scheduler();
    echo "<p>✅ Scheduler created successfully</p>";
    
    // Test scheduler status
    $status = $scheduler->get_status();
    if ($status) {
        echo "<p>✓ Scheduler status available</p>";
        echo "<p>✓ Enabled: " . ($status['enabled'] ? 'YES' : 'NO') . "</p>";
        echo "<p>✓ Daily limit: " . $status['daily_limit'] . "</p>";
        echo "<p>✓ Today count: " . $status['today_count'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Scheduler error: " . esc_html($e->getMessage()) . "</p>";
}

// Action buttons
echo "<h2>🧪 Test Actions</h2>";

echo "<form method='post' style='margin: 20px 0;'>";
echo "<input type='submit' name='test_generation' value='Test Keyword Retrieval' class='button button-primary'>";
echo "</form>";

echo "<h2>🔧 Solutions Applied</h2>";
echo "<ul>";
echo "<li>✅ Enhanced debug logging in generator constructor</li>";
echo "<li>✅ Improved get_next_keyword() method with detailed logging</li>";
echo "<li>✅ Better error messages for keyword issues</li>";
echo "<li>✅ Force settings reload in get_next_keyword()</li>";
echo "<li>✅ Enhanced scheduler error handling</li>";
echo "</ul>";

echo "<h2>💡 Next Steps</h2>";
echo "<ol>";
echo "<li>Run this test to confirm keyword retrieval works</li>";
echo "<li>Check WordPress debug logs for detailed scheduling information</li>";
echo "<li>Verify that scheduled generation runs without errors</li>";
echo "<li>Monitor logs during actual scheduled execution</li>";
echo "</ol>";

?>

<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 20px; }
.button { padding: 8px 16px; background: #0073aa; color: white; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; display: inline-block; }
.button-primary { background: #0073aa; }
</style>
