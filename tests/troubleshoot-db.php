<?php
/**
 * Writing Agent Database Troubleshooting Script
 * Run this file directly to check and fix database issues
 */

// Define WordPress path if not already defined
if (!defined('ABSPATH')) {
    // Try to find wp-config.php
    $config_file = '';
    $current_dir = dirname(__FILE__);
    
    // Look for wp-config.php in common locations
    $possible_paths = [
        $current_dir . '/../../../../wp-config.php',  // If in wp-content/plugins/auto-nulis/
        $current_dir . '/../../../wp-config.php',     // Alternative path
        $current_dir . '/wp-config.php',              // In same directory
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $config_file = $path;
            break;
        }
    }
    
    if ($config_file) {
        require_once($config_file);
    } else {
        die('WordPress configuration file not found. Please run this from your WordPress installation.');
    }
}

if (!defined('WPINC')) {
    require_once(ABSPATH . 'wp-includes/wp-db.php');
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}

echo "<h1>Writing Agent Database Troubleshooting</h1>";

// Get WordPress database connection
global $wpdb;
if (!$wpdb) {
    $wpdb = new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
}

$table_name = $wpdb->prefix . 'auto_nulis_logs';

echo "<h2>Database Information</h2>";
echo "<p><strong>Database Name:</strong> " . DB_NAME . "</p>";
echo "<p><strong>Table Name:</strong> " . $table_name . "</p>";

// Check if table exists
echo "<h2>Table Status Check</h2>";
$table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

if ($table_exists == $table_name) {
    echo "<p style='color: green;'>✓ Table '{$table_name}' exists.</p>";
    
    // Check table structure
    $columns = $wpdb->get_results("DESCRIBE {$table_name}");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column->Field}</td>";
        echo "<td>{$column->Type}</td>";
        echo "<td>{$column->Null}</td>";
        echo "<td>{$column->Key}</td>";
        echo "<td>{$column->Default}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check record count
    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    echo "<p><strong>Records in table:</strong> {$count}</p>";
    
} else {
    echo "<p style='color: red;'>✗ Table '{$table_name}' does not exist.</p>";
    echo "<h2>Creating Table...</h2>";
    
    // Create table
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        level varchar(20) DEFAULT '' NOT NULL,
        message text NOT NULL,
        context text,
        PRIMARY KEY (id),
        KEY level (level),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    $result = dbDelta($sql);
    
    if ($wpdb->last_error) {
        echo "<p style='color: red;'>Error creating table: " . $wpdb->last_error . "</p>";
        echo "<p><strong>SQL Query:</strong> " . $sql . "</p>";
    } else {
        echo "<p style='color: green;'>✓ Table created successfully!</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        // Verify table was created
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if ($table_exists == $table_name) {
            echo "<p style='color: green;'>✓ Table verification successful.</p>";
        } else {
            echo "<p style='color: red;'>✗ Table verification failed.</p>";
        }
    }
}

// Test database permissions
echo "<h2>Database Permissions Test</h2>";
try {
    // Test INSERT
    $test_insert = $wpdb->insert(
        $table_name,
        array(
            'level' => 'info',
            'message' => 'Database troubleshooting test',
            'context' => '{"test": true}',
            'timestamp' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s')
    );
    
    if ($test_insert) {
        echo "<p style='color: green;'>✓ INSERT permission: OK</p>";
        
        // Test SELECT
        $test_select = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE message = 'Database troubleshooting test'");
        if ($test_select > 0) {
            echo "<p style='color: green;'>✓ SELECT permission: OK</p>";
            
            // Test DELETE (cleanup)
            $test_delete = $wpdb->delete($table_name, array('message' => 'Database troubleshooting test'), array('%s'));
            if ($test_delete) {
                echo "<p style='color: green;'>✓ DELETE permission: OK</p>";
            } else {
                echo "<p style='color: orange;'>⚠ DELETE permission: Limited (but not critical)</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ SELECT permission: Failed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ INSERT permission: Failed</p>";
        if ($wpdb->last_error) {
            echo "<p>Error: " . $wpdb->last_error . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database permission test failed: " . $e->getMessage() . "</p>";
}

echo "<h2>WordPress Environment</h2>";
echo "<p><strong>WordPress Version:</strong> " . (defined('WP_DEBUG') ? get_bloginfo('version') : 'Unknown') . "</p>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>MySQL Version:</strong> " . $wpdb->db_version() . "</p>";
echo "<p><strong>WordPress DB Version:</strong> " . get_option('db_version') . "</p>";

// Check Writing Agent plugin status
echo "<h2>Writing Agent Plugin Status</h2>";
if (function_exists('get_option')) {
    $auto_nulis_settings = get_option('auto_nulis_settings');
    if ($auto_nulis_settings) {
        echo "<p style='color: green;'>✓ Plugin settings found.</p>";
        echo "<p><strong>API Provider:</strong> " . ($auto_nulis_settings['ai_provider'] ?? 'Not set') . "</p>";
        echo "<p><strong>API Key:</strong> " . (empty($auto_nulis_settings['api_key']) ? 'Not set' : 'Set (hidden)') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Plugin settings not found. Plugin may not be activated.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ WordPress functions not available.</p>";
}

echo "<h2>Recommendations</h2>";
if ($table_exists == $table_name) {
    echo "<p style='color: green;'>✓ Database setup is complete. The plugin should work correctly now.</p>";
} else {
    echo "<p style='color: red;'>Database issues detected. Please:</p>";
    echo "<ul>";
    echo "<li>Check database user permissions</li>";
    echo "<li>Ensure WordPress database connection is working</li>";
    echo "<li>Try deactivating and reactivating the plugin</li>";
    echo "<li>Contact your hosting provider if issues persist</li>";
    echo "</ul>";
}

echo "<p><em>Troubleshooting completed on " . date('Y-m-d H:i:s') . "</em></p>";
?>
