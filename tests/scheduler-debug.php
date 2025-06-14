<?php
/**
 * Writing Agent Scheduler Test & Debug Tool
 * Use this to test and debug scheduling issues
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

echo "<h1>Writing Agent Scheduler Debug Tool</h1>";

// Load scheduler class if not loaded
if (!class_exists('Auto_Nulis_Scheduler')) {
    require_once(dirname(__FILE__) . '/includes/class-auto-nulis-scheduler.php');
}

// Actions
if (isset($_GET['action'])) {
    $action = sanitize_text_field($_GET['action']);
    
    switch ($action) {
        case 'clear_schedule':
            wp_clear_scheduled_hook('auto_nulis_generate_article');
            echo "<div style='background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0;'>";
            echo "✓ Cleared all scheduled events for auto_nulis_generate_article";
            echo "</div>";
            break;
            
        case 'force_schedule':
            $settings = get_option('auto_nulis_settings', array());
            if (class_exists('Auto_Nulis_Scheduler')) {
                $scheduler = new Auto_Nulis_Scheduler();
                $result = $scheduler->schedule_generation($settings);
                echo "<div style='background: " . ($result ? "#d1ecf1" : "#f8d7da") . "; padding: 10px; border: 1px solid " . ($result ? "#bee5eb" : "#f5c6cb") . "; border-radius: 4px; margin: 10px 0;'>";
                echo $result ? "✓ Force scheduled new event" : "✗ Failed to schedule event";
                echo "</div>";
            }
            break;
            
        case 'test_generation':
            if (class_exists('Auto_Nulis_Generator')) {
                echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0;'>";
                echo "Testing article generation...";
                echo "</div>";
                
                $generator = new Auto_Nulis_Generator();
                $result = $generator->generate_article();
                
                echo "<div style='background: " . ($result['success'] ? "#d1ecf1" : "#f8d7da") . "; padding: 10px; border: 1px solid " . ($result['success'] ? "#bee5eb" : "#f5c6cb") . "; border-radius: 4px; margin: 10px 0;'>";
                if ($result['success']) {
                    echo "✓ Test generation successful!<br>";
                    echo "Post ID: " . $result['post_id'] . "<br>";
                    echo "Keyword: " . $result['keyword'] . "<br>";
                    echo "Title: " . $result['title'];
                } else {
                    echo "✗ Test generation failed: " . $result['message'];
                }
                echo "</div>";
            }
            break;
    }
}

// Current Status
echo "<h2>Current Status</h2>";

$settings = get_option('auto_nulis_settings', array());
$next_run = wp_next_scheduled('auto_nulis_generate_article');

echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Setting</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Value</td></tr>";

echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Plugin Enabled</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . ($settings['enabled'] ? 'Yes' : 'No') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Schedule Time</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . ($settings['schedule_time'] ?? 'Not set') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Articles Per Day</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . ($settings['articles_per_day'] ?? 'Not set') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>WordPress Timezone</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . wp_timezone_string() . "</td></tr>";

if ($next_run) {
    $wp_timezone = wp_timezone();
    $next_run_local = new DateTime('@' . $next_run);
    $next_run_local->setTimezone($wp_timezone);
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Next Scheduled Run</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . $next_run_local->format('Y-m-d H:i:s T') . "</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Next Run (UTC)</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . date('Y-m-d H:i:s', $next_run) . "</td></tr>";
} else {
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Next Scheduled Run</td><td style='padding: 10px; border: 1px solid #dee2e6; color: #d63638;'>Not scheduled</td></tr>";
}

echo "</table>";

// Scheduler Status (if available)
if (class_exists('Auto_Nulis_Scheduler')) {
    $scheduler = new Auto_Nulis_Scheduler();
    $status = $scheduler->get_status();
    
    echo "<h3>Scheduler Status</h3>";
    echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Property</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Value</td></tr>";
    
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Today's Count</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . $status['today_count'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Daily Limit</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . $status['daily_limit'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Remaining Today</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . $status['remaining_today'] . "</td></tr>";
    echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Interval</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . $status['interval_formatted'] . "</td></tr>";
    
    echo "</table>";
}

// WordPress Cron Info
echo "<h3>WordPress Cron Info</h3>";
echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Property</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Value</td></tr>";

echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>WP_CRON Defined</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . (defined('WP_CRON') ? (WP_CRON ? 'Yes (enabled)' : 'Yes (disabled)') : 'No') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>DISABLE_WP_CRON</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . (defined('DISABLE_WP_CRON') ? (DISABLE_WP_CRON ? 'Yes (cron disabled)' : 'No') : 'Not defined') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Current Time (WordPress)</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . current_time('Y-m-d H:i:s T') . "</td></tr>";
echo "<tr><td style='padding: 10px; border: 1px solid #dee2e6;'>Current Time (UTC)</td><td style='padding: 10px; border: 1px solid #dee2e6;'>" . gmdate('Y-m-d H:i:s') . "</td></tr>";

echo "</table>";

// All Scheduled Events
$cron_array = _get_cron_array();
$auto_nulis_events = array();

if ($cron_array) {
    foreach ($cron_array as $timestamp => $events) {
        if (isset($events['auto_nulis_generate_article'])) {
            $auto_nulis_events[$timestamp] = $events['auto_nulis_generate_article'];
        }
    }
}

echo "<h3>All Auto Nulis Scheduled Events</h3>";
if (!empty($auto_nulis_events)) {
    echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Timestamp</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Local Time</td><td style='padding: 10px; border: 1px solid #dee2e6; font-weight: bold;'>Schedule</td></tr>";
    
    foreach ($auto_nulis_events as $timestamp => $event_data) {
        $wp_timezone = wp_timezone();
        $local_time = new DateTime('@' . $timestamp);
        $local_time->setTimezone($wp_timezone);
        
        foreach ($event_data as $event) {
            echo "<tr>";
            echo "<td style='padding: 10px; border: 1px solid #dee2e6;'>" . $timestamp . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #dee2e6;'>" . $local_time->format('Y-m-d H:i:s T') . "</td>";
            echo "<td style='padding: 10px; border: 1px solid #dee2e6;'>" . ($event['schedule'] ?? 'single') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p>No scheduled events found.</p>";
}

// Action Buttons
echo "<h2>Actions</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='?action=clear_schedule' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Clear Schedule</a>";
echo "<a href='?action=force_schedule' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Force Schedule</a>";
echo "<a href='?action=test_generation' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Test Generation</a>";
echo "<a href='?' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Refresh</a>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 4px; margin: 20px 0;'>";
echo "<strong>Note:</strong> Use these tools to diagnose and fix scheduling issues. ";
echo "If scheduling problems persist, check with your hosting provider about WordPress cron functionality.";
echo "</div>";
?>
