<?php
/**
 * Clear used keywords list
 * Run this from WordPress admin or via WordPress CLI
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
    die('WordPress not found. Please run this from your WordPress installation or copy to WordPress root.');
}

// Clear used keywords
delete_option('auto_nulis_used_keywords');

// Clear any remaining scheduled events
wp_clear_scheduled_hook('auto_nulis_generate_article');

echo "<h1>Writing Agent - Reset Complete</h1>";
echo "<p>✅ Used keywords list cleared</p>";
echo "<p>✅ Scheduled events cleared</p>";
echo "<p><a href='" . admin_url('admin.php?page=auto-nulis') . "'>← Back to Writing Agent Settings</a></p>";
echo "<p><strong>You can now:</strong></p>";
echo "<ul>";
echo "<li>Enable/disable the plugin using the toggle</li>";
echo "<li>Set up new schedules without conflicts</li>";
echo "<li>Generate articles without infinite loops</li>";
echo "</ul>";
