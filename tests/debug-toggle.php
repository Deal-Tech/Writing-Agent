<?php
/**
 * Debug Toggle Settings
 * Run this file to check current toggle state
 */

// Include WordPress
require_once '../../../wp-config.php';

echo "<h2>Debug Toggle Settings</h2>\n";

// Get current settings
$settings = get_option('auto_nulis_settings', array());

echo "<h3>Current Settings:</h3>\n";
echo "<pre>";
print_r($settings);
echo "</pre>";

echo "<h3>Enabled Status:</h3>\n";
if (isset($settings['enabled'])) {
    echo "Value: " . var_export($settings['enabled'], true) . "\n";
    echo "Type: " . gettype($settings['enabled']) . "\n";
    echo "Is True: " . ($settings['enabled'] ? 'YES' : 'NO') . "\n";
} else {
    echo "Enabled setting not found!\n";
}

echo "<h3>Test Form Data Processing:</h3>\n";

// Simulate form data when checkbox is checked
$_POST = array(
    'enabled' => '1'
);
echo "When checkbox checked (\$_POST['enabled'] = '1'):\n";
$enabled_value = isset($_POST['enabled']) && $_POST['enabled'] === '1' ? true : false;
echo "Result: " . var_export($enabled_value, true) . "\n\n";

// Simulate form data when checkbox is unchecked (hidden field only)
$_POST = array(
    'enabled' => '0'
);
echo "When checkbox unchecked (\$_POST['enabled'] = '0'):\n";
$enabled_value = isset($_POST['enabled']) && $_POST['enabled'] === '1' ? true : false;
echo "Result: " . var_export($enabled_value, true) . "\n\n";

// Simulate form data when both hidden and checkbox are present
$_POST = array(
    'enabled' => array('0', '1')
);
echo "When both hidden and checkbox (\$_POST['enabled'] = array('0', '1')):\n";
if (is_array($_POST['enabled'])) {
    $enabled_value = end($_POST['enabled']) === '1';
} else {
    $enabled_value = $_POST['enabled'] === '1';
}
echo "Result: " . var_export($enabled_value, true) . "\n\n";

// Test manual toggle
echo "<h3>Manual Toggle Test:</h3>\n";
echo '<form method="post" action="">';
echo '<input type="hidden" name="test_mode" value="1">';
echo '<label>';
echo '<input type="hidden" name="enabled" value="0">';
echo '<input type="checkbox" name="enabled" value="1"' . (isset($settings['enabled']) && $settings['enabled'] ? ' checked' : '') . '>';
echo ' Toggle Enable/Disable';
echo '</label><br><br>';
echo '<input type="submit" value="Test Toggle">';
echo '</form>';

if (isset($_POST['test_mode']) && $_POST['test_mode'] === '1') {
    echo "<h4>Form submitted with:</h4>\n";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Process like the actual form
    $enabled_value = false;
    if (isset($_POST['enabled'])) {
        if (is_array($_POST['enabled'])) {
            $enabled_value = end($_POST['enabled']) === '1';
        } else {
            $enabled_value = $_POST['enabled'] === '1';
        }
    }
    
    echo "Processed enabled value: " . var_export($enabled_value, true) . "\n";
    
    // Update settings
    $settings['enabled'] = $enabled_value;
    $updated = update_option('auto_nulis_settings', $settings);
    
    echo "Settings updated: " . ($updated ? 'SUCCESS' : 'FAILED') . "\n";
    
    // Verify
    $new_settings = get_option('auto_nulis_settings', array());
    echo "New enabled value: " . var_export($new_settings['enabled'], true) . "\n";
}
?>
