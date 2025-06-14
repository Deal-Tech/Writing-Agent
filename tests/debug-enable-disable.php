<?php
/**
 * Debug Enable/Disable Settings Issue
 * Check what's happening with the enabled setting
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

echo "<h1>Writing Agent - Enable/Disable Debug</h1>";

// Handle test form submission
if (isset($_POST['test_disable'])) {
    echo "<h2>🧪 Testing Disable Functionality</h2>";
    
    // Get current settings
    $current_settings = get_option('auto_nulis_settings', array());
    echo "<p><strong>Before change:</strong> enabled = " . ($current_settings['enabled'] ? 'true' : 'false') . "</p>";
    
    // Manually set enabled to false
    $current_settings['enabled'] = false;
    $result = update_option('auto_nulis_settings', $current_settings);
    
    echo "<p><strong>Update result:</strong> " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    
    // Re-read settings to verify
    $updated_settings = get_option('auto_nulis_settings', array());
    echo "<p><strong>After change:</strong> enabled = " . ($updated_settings['enabled'] ? 'true' : 'false') . "</p>";
    
    if (!$updated_settings['enabled']) {
        echo "<p style='color: green;'>✅ Manual disable test PASSED - settings can be disabled</p>";
    } else {
        echo "<p style='color: red;'>❌ Manual disable test FAILED - settings are not being saved properly</p>";
    }
}

if (isset($_POST['test_enable'])) {
    echo "<h2>🧪 Testing Enable Functionality</h2>";
    
    // Get current settings
    $current_settings = get_option('auto_nulis_settings', array());
    echo "<p><strong>Before change:</strong> enabled = " . ($current_settings['enabled'] ? 'true' : 'false') . "</p>";
    
    // Manually set enabled to true
    $current_settings['enabled'] = true;
    $result = update_option('auto_nulis_settings', $current_settings);
    
    echo "<p><strong>Update result:</strong> " . ($result ? 'SUCCESS' : 'FAILED') . "</p>";
    
    // Re-read settings to verify
    $updated_settings = get_option('auto_nulis_settings', array());
    echo "<p><strong>After change:</strong> enabled = " . ($updated_settings['enabled'] ? 'true' : 'false') . "</p>";
    
    if ($updated_settings['enabled']) {
        echo "<p style='color: green;'>✅ Manual enable test PASSED - settings can be enabled</p>";
    } else {
        echo "<p style='color: red;'>❌ Manual enable test FAILED - settings are not being saved properly</p>";
    }
}

// Display current settings
echo "<h2>📊 Current Settings Status</h2>";
$settings = get_option('auto_nulis_settings', array());

echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Setting</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Value</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Type</td></tr>";

$enabled_value = isset($settings['enabled']) ? $settings['enabled'] : 'NOT SET';
$enabled_type = isset($settings['enabled']) ? gettype($settings['enabled']) : 'undefined';
$enabled_display = is_bool($enabled_value) ? ($enabled_value ? 'true' : 'false') : $enabled_value;

echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>enabled</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$enabled_display</td><td style='padding: 10px; border: 1px solid #dee2e6;'>$enabled_type</td></tr>";

echo "</table>";

// Check if there are any filters or actions that might be interfering
echo "<h2>🔍 WordPress Hooks Check</h2>";

global $wp_filter;
$auto_nulis_hooks = array();

foreach ($wp_filter as $hook_name => $hook_callbacks) {
    if (strpos($hook_name, 'auto_nulis') !== false) {
        $auto_nulis_hooks[] = $hook_name;
    }
}

if (!empty($auto_nulis_hooks)) {
    echo "<p><strong>Active Auto Nulis hooks:</strong></p>";
    echo "<ul>";
    foreach ($auto_nulis_hooks as $hook) {
        echo "<li>$hook</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No Auto Nulis hooks found.</p>";
}

// Test forms
echo "<h2>🧪 Manual Tests</h2>";

echo "<form method='post' action='' style='margin: 10px 0;'>";
wp_nonce_field('test_disable_nonce');
echo "<input type='submit' name='test_disable' value='Test Manual DISABLE' class='button button-secondary' style='margin-right: 10px;'>";
echo "</form>";

echo "<form method='post' action='' style='margin: 10px 0;'>";
wp_nonce_field('test_enable_nonce');
echo "<input type='submit' name='test_enable' value='Test Manual ENABLE' class='button button-primary'>";
echo "</form>";

echo "<h2>💡 Debugging Steps</h2>";
echo "<ol>";
echo "<li><strong>Check Database:</strong> Verify the wp_options table has the auto_nulis_settings option</li>";
echo "<li><strong>Check Form Submission:</strong> Use browser dev tools to see what POST data is sent</li>";
echo "<li><strong>Check PHP Processing:</strong> Add debugging to the save_settings() method</li>";
echo "<li><strong>Check Default Override:</strong> Ensure wp_parse_args isn't overriding saved values</li>";
echo "</ol>";

echo "<p><a href='" . admin_url('admin.php?page=auto-nulis') . "'>← Back to Settings Page</a></p>";
?>
