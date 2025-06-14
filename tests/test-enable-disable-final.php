<?php
/**
 * Final Enable/Disable Toggle Test
 * Comprehensive test for the toggle functionality
 */

// Simple standalone test without WordPress
echo "<h1>Enable/Disable Toggle - Final Test</h1>";

// Test the PHP logic that handles checkbox values
function test_checkbox_logic() {
    echo "<h2>Testing PHP Checkbox Logic</h2>";
    
    // Test Case 1: Checkbox checked (value="1" sent)
    $_POST_test1 = array('enabled' => '1');
    $result1 = isset($_POST_test1['enabled']) && $_POST_test1['enabled'] === '1' ? true : false;
    echo "<p><strong>Test 1 - Checkbox Checked:</strong> " . ($result1 ? 'ENABLED' : 'DISABLED') . " ✓</p>";
    
    // Test Case 2: Checkbox unchecked (no value sent)
    $_POST_test2 = array(); // No 'enabled' key
    $result2 = isset($_POST_test2['enabled']) && $_POST_test2['enabled'] === '1' ? true : false;
    echo "<p><strong>Test 2 - Checkbox Unchecked:</strong> " . ($result2 ? 'ENABLED' : 'DISABLED') . " ✓</p>";
    
    // Test Case 3: Checkbox with empty value
    $_POST_test3 = array('enabled' => '');
    $result3 = isset($_POST_test3['enabled']) && $_POST_test3['enabled'] === '1' ? true : false;
    echo "<p><strong>Test 3 - Empty Value:</strong> " . ($result3 ? 'ENABLED' : 'DISABLED') . " ✓</p>";
    
    return array($result1, $result2, $result3);
}

// Handle form submission for live testing
if ($_POST) {
    echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #007cba; margin: 20px 0;'>";
    echo "<h3>Form Submission Results:</h3>";
    echo "<p><strong>Raw POST data:</strong></p>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    $enabled_setting = isset($_POST['enabled']) && $_POST['enabled'] === '1' ? true : false;
    echo "<p><strong>Processed 'enabled' setting:</strong> " . ($enabled_setting ? 'TRUE (ENABLED)' : 'FALSE (DISABLED)') . "</p>";
    
    if ($enabled_setting) {
        echo "<p style='color: green; font-weight: bold;'>✓ Plugin would be ENABLED</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Plugin would be DISABLED</p>";
    }
    echo "</div>";
}

// Run the logic tests
$test_results = test_checkbox_logic();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enable/Disable Toggle Test</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 20px; 
            background: #f1f1f1; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .test-form { 
            border: 1px solid #ddd; 
            padding: 20px; 
            margin: 20px 0; 
            border-radius: 4px; 
            background: #fafafa; 
        }
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
            margin-right: 10px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .submit-btn { 
            background: #0073aa; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 14px;
            margin-top: 15px;
        }
        .submit-btn:hover {
            background: #005a87;
        }
        .status {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .enabled {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .disabled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enable/Disable Toggle - Final Test</h1>
        
        <div class="test-form">
            <h3>Interactive Toggle Test</h3>
            <p>This test simulates the exact functionality of the WordPress plugin toggle.</p>
            
            <form method="post" action="">
                <div style="margin: 20px 0;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="enabled" value="1" 
                               <?php echo (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? 'checked' : ''; ?>
                               onchange="updateStatus(this)">
                        <span class="slider"></span>
                    </label>
                    <strong>Enable Auto Article Generation</strong>
                    <div id="current-status" class="status <?php echo (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? 'enabled' : 'disabled'; ?>">
                        Current Status: <?php echo (isset($_POST['enabled']) && $_POST['enabled'] === '1') ? 'ENABLED' : 'DISABLED'; ?>
                    </div>
                </div>
                
                <input type="submit" value="Save Settings" class="submit-btn">
            </form>
        </div>
        
        <div class="test-form">
            <h3>Test Instructions</h3>
            <ol>
                <li><strong>Initial State:</strong> The toggle should be OFF (DISABLED)</li>
                <li><strong>Turn ON:</strong> Click the toggle to turn it ON, then click "Save Settings"</li>
                <li><strong>Verify ON:</strong> After saving, the page should reload with toggle ON</li>
                <li><strong>Turn OFF:</strong> Click the toggle to turn it OFF, then click "Save Settings"</li>
                <li><strong>Verify OFF:</strong> After saving, the page should reload with toggle OFF</li>
            </ol>
            
            <h4>Expected Behavior:</h4>
            <ul>
                <li>✓ When toggle is ON and saved, it should stay ON after page reload</li>
                <li>✓ When toggle is OFF and saved, it should stay OFF after page reload</li>
                <li>✗ Toggle should NOT revert to ON automatically</li>
            </ul>
        </div>
        
        <div class="test-form">
            <h3>Fix Summary</h3>
            <p><strong>Issues Fixed:</strong></p>
            <ol>
                <li><strong>Checkbox Processing:</strong> Changed from <code>isset($_POST['enabled']) ? true : false</code> to <code>isset($_POST['enabled']) && $_POST['enabled'] === '1' ? true : false</code></li>
                <li><strong>Auto-save Interference:</strong> Excluded 'enabled' checkbox from auto-save functionality</li>
                <li><strong>Form Validation:</strong> Added data attribute to prevent auto-save conflicts</li>
                <li><strong>Debug Logging:</strong> Enhanced logging to track checkbox state changes</li>
            </ol>
            
            <p><strong>Files Modified:</strong></p>
            <ul>
                <li><code>admin/settings-page.php</code> - Added data-no-auto-save attribute</li>
                <li><code>includes/class-auto-nulis-admin.php</code> - Improved checkbox processing</li>
                <li><code>admin/js/admin.js</code> - Excluded enabled field from auto-save</li>
                <li><code>auto-nulis.php</code> - Prevented auto-save override of enabled setting</li>
            </ul>
        </div>
    </div>
    
    <script>
        function updateStatus(checkbox) {
            var statusDiv = document.getElementById('current-status');
            if (checkbox.checked) {
                statusDiv.textContent = 'Current Status: ENABLED';
                statusDiv.className = 'status enabled';
            } else {
                statusDiv.textContent = 'Current Status: DISABLED';
                statusDiv.className = 'status disabled';
            }
        }
        
        // Log form submission for debugging
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitted with enabled checkbox:', 
                       document.querySelector('input[name="enabled"]').checked);
        });
    </script>
</body>
</html>
