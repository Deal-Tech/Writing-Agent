<?php
/**
 * Simple Toggle Test Script
 * Test the enable/disable functionality without WordPress
 */

// Simulate form submission
if ($_POST) {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h2>Checkbox Processing:</h2>";
    echo "enabled isset: " . (isset($_POST['enabled']) ? 'true' : 'false') . "<br>";
    echo "enabled value: " . (isset($_POST['enabled']) ? $_POST['enabled'] : 'not set') . "<br>";
    echo "Final enabled: " . (isset($_POST['enabled']) ? 'true' : 'false') . "<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Toggle Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-form { border: 1px solid #ccc; padding: 20px; margin: 20px 0; }
        .checkbox-wrapper { margin: 10px 0; }
        .submit-btn { background: #0073aa; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Toggle Functionality Test</h1>
    
    <div class="test-form">
        <h3>Test 1: Regular Checkbox (Current Implementation)</h3>
        <form method="post" action="">
            <div class="checkbox-wrapper">
                <label>
                    <input type="checkbox" name="enabled" value="1" <?php echo isset($_POST['enabled']) ? 'checked' : ''; ?>>
                    Enable Feature
                </label>
            </div>
            <input type="submit" value="Submit Test 1" class="submit-btn">
        </form>
    </div>
    
    <div class="test-form">
        <h3>Test 2: Checkbox with Hidden Field</h3>
        <form method="post" action="">
            <div class="checkbox-wrapper">
                <label>
                    <input type="hidden" name="enabled_test2" value="0">
                    <input type="checkbox" name="enabled_test2" value="1" <?php echo (isset($_POST['enabled_test2']) && $_POST['enabled_test2'] == '1') ? 'checked' : ''; ?>>
                    Enable Feature (with hidden field)
                </label>
            </div>
            <input type="submit" value="Submit Test 2" class="submit-btn">
        </form>
    </div>
    
    <div class="test-form">
        <h3>Test 3: Radio Buttons</h3>
        <form method="post" action="">
            <div class="checkbox-wrapper">
                <label>
                    <input type="radio" name="enabled_test3" value="1" <?php echo (isset($_POST['enabled_test3']) && $_POST['enabled_test3'] == '1') ? 'checked' : ''; ?>>
                    Enable
                </label>
                <label>
                    <input type="radio" name="enabled_test3" value="0" <?php echo (isset($_POST['enabled_test3']) && $_POST['enabled_test3'] == '0') ? 'checked' : (!isset($_POST['enabled_test3']) ? 'checked' : ''); ?>>
                    Disable
                </label>
            </div>
            <input type="submit" value="Submit Test 3" class="submit-btn">
        </form>
    </div>
    
    <div class="test-form">
        <h3>Current Settings Simulation</h3>
        <?php
        // Simulate current settings
        $settings = array(
            'enabled' => isset($_POST['enabled']) ? true : false,
            'enabled_test2' => isset($_POST['enabled_test2']) && $_POST['enabled_test2'] == '1' ? true : false,
            'enabled_test3' => isset($_POST['enabled_test3']) ? ($_POST['enabled_test3'] == '1' ? true : false) : false
        );
        
        echo "<pre>";
        print_r($settings);
        echo "</pre>";
        ?>
    </div>
    
    <script>
        // Debug form submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    console.log('Form submission:', this);
                    const formData = new FormData(this);
                    console.log('FormData entries:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, value);
                    }
                });
            });
        });
    </script>
</body>
</html>
