<?php
/**
 * Quick Toggle Test
 * Simple test to verify toggle functionality
 */

// Include WordPress
require_once '../../../wp-config.php';

if (isset($_POST['action']) && $_POST['action'] === 'test_toggle') {
    echo "<h2>Toggle Test Results</h2>\n";
    
    echo "<h3>POST Data Received:</h3>\n";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Process enabled value exactly like the plugin does
    $enabled_value = false;
    if (isset($_POST['enabled'])) {
        if (is_array($_POST['enabled'])) {
            $enabled_value = end($_POST['enabled']) === '1';
            echo "<p>Enabled is array, last value: " . end($_POST['enabled']) . "</p>";
        } else {
            $enabled_value = $_POST['enabled'] === '1';
            echo "<p>Enabled is single value: " . $_POST['enabled'] . "</p>";
        }
    }
    
    echo "<h3>Processed Result:</h3>\n";
    echo "<p><strong>Enabled Value: </strong>" . var_export($enabled_value, true) . "</p>";
    echo "<p><strong>Will be saved as: </strong>" . ($enabled_value ? 'TRUE (enabled)' : 'FALSE (disabled)') . "</p>";
    
    // Update the actual setting
    $current_settings = get_option('auto_nulis_settings', array());
    $current_settings['enabled'] = $enabled_value;
    $updated = update_option('auto_nulis_settings', $current_settings);
    
    echo "<h3>Database Update:</h3>\n";
    echo "<p><strong>Update Result: </strong>" . ($updated ? 'SUCCESS' : 'FAILED') . "</p>";
    
    // Verify what was saved
    $saved_settings = get_option('auto_nulis_settings', array());
    echo "<p><strong>Actual Saved Value: </strong>" . var_export($saved_settings['enabled'], true) . "</p>";
    
    echo "<hr>";
}

// Get current setting
$current_settings = get_option('auto_nulis_settings', array());
$current_enabled = isset($current_settings['enabled']) ? $current_settings['enabled'] : false;

echo "<h2>Current Plugin State</h2>\n";
echo "<p><strong>Currently Enabled: </strong>" . ($current_enabled ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Raw Value: </strong>" . var_export($current_enabled, true) . "</p>";

?>

<h2>Test Toggle</h2>
<form method="post">
    <input type="hidden" name="action" value="test_toggle">
    
    <h3>Method 1: Hidden + Checkbox (like plugin form)</h3>
    <label>
        <input type="hidden" name="enabled" value="0">
        <input type="checkbox" name="enabled" value="1" <?php echo $current_enabled ? 'checked' : ''; ?>>
        Enable Plugin
    </label>
    <br><br>
    
    <input type="submit" value="Test Save" class="button">
</form>

<h3>Manual Tests:</h3>
<form method="post" style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;">
    <input type="hidden" name="action" value="test_toggle">
    <input type="hidden" name="enabled" value="0">
    <input type="submit" value="Force Disable (hidden only)" class="button">
</form>

<form method="post" style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;">
    <input type="hidden" name="action" value="test_toggle">
    <input type="checkbox" name="enabled" value="1" checked>
    <input type="submit" value="Force Enable (checkbox only)" class="button">
</form>
