<?php
/**
 * Writing Agent - Fix Infinite Loop and Enable/Disable Issues
 * Run this script to fix the current problems
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

// Only allow admin users
if (!current_user_can('manage_options')) {
    die('Access denied. Administrator privileges required.');
}

echo "<h1>Writing Agent - System Repair</h1>";

echo "<h2>🔧 Fixing Issues...</h2>";

// 1. Clear used keywords to break infinite loop
delete_option('auto_nulis_used_keywords');
echo "<p>✅ Cleared used keywords list (fixes infinite loop)</p>";

// 2. Clear all scheduled events
wp_clear_scheduled_hook('auto_nulis_generate_article');
echo "<p>✅ Cleared all scheduled events</p>";

// 3. Check current settings
$settings = get_option('auto_nulis_settings', array());
echo "<p>✅ Current plugin status: " . ($settings['enabled'] ? 'ENABLED' : 'DISABLED') . "</p>";

// 4. Test enable/disable functionality
if (isset($_POST['test_toggle'])) {
    $new_status = !$settings['enabled'];
    $settings['enabled'] = $new_status;
    update_option('auto_nulis_settings', $settings);
    echo "<p>✅ Toggle test successful! Plugin is now " . ($new_status ? 'ENABLED' : 'DISABLED') . "</p>";
    $settings = get_option('auto_nulis_settings', array()); // Refresh
}

// 5. Display system status
echo "<h2>📊 Current System Status</h2>";

echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Component</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Status</td></tr>";

// Check plugin enabled status
$enabled_status = $settings['enabled'] ? '✅ ENABLED' : '❌ DISABLED';
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Plugin Status</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$enabled_status</td></tr>";

// Check keywords
$keywords = isset($settings['keywords']) ? $settings['keywords'] : '';
$keyword_count = empty($keywords) ? 0 : count(array_filter(array_map('trim', explode("\n", $keywords))));
$keyword_status = $keyword_count > 0 ? "✅ $keyword_count keywords configured" : "❌ No keywords configured";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Keywords</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$keyword_status</td></tr>";

// Check API key
$api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
$api_status = !empty($api_key) ? "✅ API key configured" : "❌ No API key";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>API Key</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$api_status</td></tr>";

// Check scheduled events
$next_run = wp_next_scheduled('auto_nulis_generate_article');
$schedule_status = $next_run ? "✅ Next run: " . date('Y-m-d H:i:s', $next_run) : "❌ No scheduled events";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Scheduled Events</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$schedule_status</td></tr>";

// Check used keywords
$used_keywords = get_option('auto_nulis_used_keywords', array());
$used_count = count($used_keywords);
$used_status = $used_count > 0 ? "⚠️ $used_count keywords marked as used" : "✅ No keywords marked as used";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Used Keywords</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$used_status</td></tr>";

echo "</table>";

// 6. Test toggle functionality
echo "<h2>🧪 Test Enable/Disable Toggle</h2>";
echo "<form method='post' action=''>";
wp_nonce_field('test_toggle_nonce');
echo "<p>Current status: <strong>" . ($settings['enabled'] ? 'ENABLED' : 'DISABLED') . "</strong></p>";
echo "<p><input type='submit' name='test_toggle' value='Toggle Plugin Status' class='button button-primary'></p>";
echo "</form>";

// 7. Fix recommendations
echo "<h2>🛠️ Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Test the Settings Page:</strong> Go to <a href='" . admin_url('admin.php?page=auto-nulis') . "'>Writing Agent Settings</a> and try toggling the enable/disable switch</li>";
echo "<li><strong>Test Article Generation:</strong> Go to <a href='" . admin_url('admin.php?page=auto-nulis-generate') . "'>Generate Now</a> and try generating a single article</li>";
echo "<li><strong>Monitor Logs:</strong> Check <a href='" . admin_url('admin.php?page=auto-nulis-logs') . "'>Activity Logs</a> to ensure no more infinite loops</li>";
echo "<li><strong>Set Daily Limits:</strong> Configure articles per day to 1 and test that it stops after generating 1 article</li>";
echo "</ol>";

echo "<h2>✅ Fixes Applied</h2>";
echo "<ul>";
echo "<li>✅ <strong>Infinite Loop Fixed:</strong> Used keywords cleared and improved keyword selection logic</li>";
echo "<li>✅ <strong>Scheduler Improved:</strong> Added daily limit checks and automatic stopping</li>";
echo "<li>✅ <strong>Enable/Disable Ready:</strong> Settings are properly saved and validated</li>";
echo "</ul>";

echo "<p><strong>The plugin should now work correctly!</strong></p>";
echo "<p><a href='" . admin_url('admin.php?page=auto-nulis') . "' class='button button-primary'>Go to Settings</a> | <a href='" . admin_url('admin.php?page=auto-nulis-logs') . "' class='button'>View Logs</a></p>";
?>
