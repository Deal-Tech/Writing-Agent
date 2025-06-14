<?php
/**
 * Writing Agent Form Test Page
 * Use this to test form submission functionality
 */

// Prevent direct access unless in WordPress context
if (!defined('ABSPATH')) {
    // Try to load WordPress if accessed directly
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
}

// Only allow admin users
if (!current_user_can('manage_options')) {
    die('Access denied. Administrator privileges required.');
}

echo "<h1>Writing Agent Form Test</h1>";

echo "<h2>Test Results</h2>";

// Test form submission
if (isset($_POST['test_submit'])) {
    echo "<div style='background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0;'>";
    echo "✓ Form submission works correctly!<br>";
    echo "Enable checkbox value: " . (isset($_POST['test_enabled']) ? 'Checked' : 'Not checked') . "<br>";
    echo "Test field value: " . esc_html($_POST['test_field'] ?? 'Not set');
    echo "</div>";
}

// Test current settings
$current_settings = get_option('auto_nulis_settings', array());
echo "<h3>Current Settings</h3>";
echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Setting</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Value</td></tr>";

$settings_to_show = ['enabled', 'api_key', 'keywords', 'articles_per_day'];
foreach ($settings_to_show as $setting) {
    $value = isset($current_settings[$setting]) ? $current_settings[$setting] : 'Not set';
    if ($setting === 'api_key' && !empty($value)) {
        $value = substr($value, 0, 10) . '...';
    }
    if ($setting === 'enabled') {
        $value = $value ? 'Yes' : 'No';
    }
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>" . ucfirst(str_replace('_', ' ', $setting)) . "</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . esc_html($value) . "</td></tr>";
}
echo "</table>";

?>

<h3>Test Form</h3>
<form method="post" action="">
    <?php wp_nonce_field('test_form_nonce'); ?>
    
    <p>
        <label>
            <input type="checkbox" name="test_enabled" value="1">
            Test Enable Checkbox
        </label>
    </p>
    
    <p>
        <label>
            Test Field: 
            <input type="text" name="test_field" value="test value">
        </label>
    </p>
    
    <p>
        <input type="submit" name="test_submit" value="Test Form Submission" class="button button-primary">
    </p>
</form>

<h3>Instructions</h3>
<ol>
    <li>Click "Test Form Submission" to verify basic form functionality works</li>
    <li>Check the "Test Enable Checkbox" and submit again to test checkbox handling</li>
    <li>Go to WordPress Admin → Writing Agent → Settings to test the actual plugin form</li>
    <li>Use the "Debug Form" button on the settings page to check form state</li>
</ol>

<p><a href="<?php echo admin_url('admin.php?page=auto-nulis'); ?>">← Back to Writing Agent Settings</a></p>
