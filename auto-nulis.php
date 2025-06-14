<?php
/**
 * Plugin Name: Writing Agent
 * Plugin URI: https://github.com/Deal-Tech/Writing-Agent
 * Description: Professional WordPress plugin for automated AI-powered article generation using Google Gemini & OpenAI with SEO optimization and human-like content quality.
 * Version: 1.0.2
 * Author: DealTech
 * Author URI: https://tech.mudahdeal.com
 * License: GPL v2 or later
 * Text Domain: auto-nulis
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AUTO_NULIS_VERSION', '1.0.2');
define('AUTO_NULIS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AUTO_NULIS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AUTO_NULIS_PLUGIN_FILE', __FILE__);

/**
 * Main Writing Agent Class
 */
class Auto_Nulis {
      /**
     * Constructor
     */
    public function __construct() {
        // Load dependencies first
        if (!$this->load_dependencies()) {
            return; // Stop if dependencies can't be loaded
        }
        
        // Initialize hooks only after dependencies are loaded
        $this->init_hooks();
    }    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Ensure log table exists on admin pages
        add_action('admin_init', array($this, 'ensure_tables_exist'));
        
        // Initialize scheduler
        if (class_exists('Auto_Nulis_Scheduler')) {
            $this->scheduler = new Auto_Nulis_Scheduler();
        }
    }
    
    /**
     * Ensure database tables exist
     */
    public function ensure_tables_exist() {
        self::ensure_log_table_exists();
    }    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        $files = array(
            'includes/class-auto-nulis-admin.php',
            'includes/class-auto-nulis-api.php',
            'includes/class-auto-nulis-generator.php',
            'includes/class-auto-nulis-image.php',
            'includes/class-auto-nulis-scheduler.php'
        );
        
        foreach ($files as $file) {
            $file_path = AUTO_NULIS_PLUGIN_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {                add_action('admin_notices', function() use ($file) {
                    echo '<div class="notice notice-error"><p>';
                    echo sprintf(__('Writing Agent: Required file %s not found.', 'auto-nulis'), $file);
                    echo '</p></div>';
                });
                return false;
            }
        }
        return true;
    }
    
    /**
     * Plugin activation
     */
    public function activate() {        // Create default options
        $default_options = array(
            'enabled' => false,
            'articles_per_day' => 1,
            'schedule_time' => '09:00',
            'keywords' => '',
            'article_length' => 'medium',
            'post_status' => 'draft',
            'include_images' => true,
            'image_source' => 'unsplash',
            'category' => 1,
            'author' => 1,
            'ai_provider' => 'gemini',
            'api_key' => '',
            'ai_model' => 'gemini-1.5-flash', // Default to free model
            'article_language' => 'id' // Default to Indonesian
        );
        
        add_option('auto_nulis_settings', $default_options);
        
        // Schedule initial cron if enabled
        if (!wp_next_scheduled('auto_nulis_generate_article')) {
            wp_schedule_event(time(), 'auto_nulis_custom', 'auto_nulis_generate_article');
        }        // Create log table
        $this->create_log_table();
        
        // Ensure log table exists
        self::ensure_log_table_exists();
        
        // Set up initial cron schedule
        $this->setup_initial_cron();
    }
    
    /**
     * Setup initial cron schedule
     */
    private function setup_initial_cron() {
        // Don't schedule during activation, let settings update handle it
        // This prevents conflicts with timezone calculations
    }
      /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('auto_nulis_generate_article');
        
        // Also clear any other potential hooks
        wp_clear_scheduled_hook('auto_nulis_custom');
        
        // Log deactivation
        if (class_exists('Auto_Nulis_Generator')) {
            $generator = new Auto_Nulis_Generator();
            $generator->log_message('info', 'Plugin deactivated - all scheduled events cleared');
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('auto-nulis', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
      /**
     * Add admin menu
     */
    public function add_admin_menu() {        add_menu_page(
            __('Writing Agent Settings', 'auto-nulis'),
            __('Writing Agent', 'auto-nulis'),
            'manage_options',
            'auto-nulis',
            array($this, 'admin_page'),
            'dashicons-edit-large',
            30
        );
        
        add_submenu_page(
            'auto-nulis',
            __('Settings', 'auto-nulis'),
            __('Settings', 'auto-nulis'),
            'manage_options',
            'auto-nulis',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'auto-nulis',
            __('Generate Article', 'auto-nulis'),
            __('Generate Now', 'auto-nulis'),
            'manage_options',
            'auto-nulis-generate',
            array($this, 'generate_page')
        );
        
        add_submenu_page(
            'auto-nulis',
            __('Generated Articles', 'auto-nulis'),
            __('All Articles', 'auto-nulis'),
            'manage_options',
            'auto-nulis-articles',
            array($this, 'articles_page')
        );
        
        add_submenu_page(
            'auto-nulis',
            __('Logs', 'auto-nulis'),
            __('Activity Logs', 'auto-nulis'),
            'manage_options',
            'auto-nulis-logs',
            array($this, 'logs_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'auto-nulis') === false) {
            return;
        }
        
        wp_enqueue_style('auto-nulis-admin', AUTO_NULIS_PLUGIN_URL . 'admin/css/admin.css', array(), AUTO_NULIS_VERSION);
        wp_enqueue_script('auto-nulis-admin', AUTO_NULIS_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), AUTO_NULIS_VERSION, true);
        
        wp_localize_script('auto-nulis-admin', 'autoNulisAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('auto_nulis_nonce'),
            'strings' => array(
                'testing' => __('Testing connection...', 'auto-nulis'),
                'success' => __('Connection successful!', 'auto-nulis'),
                'error' => __('Connection failed!', 'auto-nulis')
            )
        ));
    }
      /**
     * Add custom cron schedule
     */
    public function add_custom_cron_schedule($schedules) {
        $settings = get_option('auto_nulis_settings', array());
        $articles_per_day = isset($settings['articles_per_day']) ? intval($settings['articles_per_day']) : 1;
        
        // Calculate interval based on articles per day
        $interval = floor(24 * 60 * 60 / $articles_per_day);
        
        // Ensure minimum interval of 1 hour
        $interval = max($interval, 3600);
        
        $schedules['auto_nulis_custom'] = array(
            'interval' => $interval,
            'display' => sprintf(__('Every %s for Auto Nulis', 'auto-nulis'), $this->format_interval($interval))
        );
        
        return $schedules;
    }
    
    /**
     * Format interval for display
     */
    private function format_interval($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remaining_hours = $hours % 24;
            if ($remaining_hours > 0) {
                return sprintf(__('%d days %d hours', 'auto-nulis'), $days, $remaining_hours);
            } else {
                return sprintf(__('%d days', 'auto-nulis'), $days);
            }
        } elseif ($hours > 0) {
            if ($minutes > 0) {
                return sprintf(__('%d hours %d minutes', 'auto-nulis'), $hours, $minutes);
            } else {
                return sprintf(__('%d hours', 'auto-nulis'), $hours);
            }
        } else {
            return sprintf(__('%d minutes', 'auto-nulis'), $minutes);
        }
    }    /**
     * Generate scheduled article
     */
    public function generate_scheduled_article() {
        $settings = get_option('auto_nulis_settings', array());
        
        if (!isset($settings['enabled']) || !$settings['enabled']) {
            return;
        }
        
        // Check daily limit
        $today_count = $this->get_today_generated_count();
        $daily_limit = isset($settings['articles_per_day']) ? intval($settings['articles_per_day']) : 1;
        
        if ($today_count >= $daily_limit) {
            return; // Daily limit reached
        }
        
        if (class_exists('Auto_Nulis_Generator')) {
            $generator = new Auto_Nulis_Generator();
            
            // Log the scheduled generation attempt
            $wp_timezone = wp_timezone();
            $current_time = new DateTime('now', $wp_timezone);
            
            $generator->log_message('info', 'Scheduled article generation started', array(
                'current_time' => $current_time->format('Y-m-d H:i:s T'),
                'timezone' => $wp_timezone->getName(),
                'today_count' => $today_count,
                'daily_limit' => $daily_limit
            ));
            
            $result = $generator->generate_article();
            
            // Log the result
            if ($result['success']) {
                $generator->log_message('success', 'Scheduled article generation completed successfully', array(
                    'post_id' => $result['post_id'],
                    'keyword' => $result['keyword']
                ));
            } else {
                $generator->log_message('error', 'Scheduled article generation failed', array(
                    'error' => $result['message']
                ));
            }
        }
    }
    
    /**
     * Get count of articles generated today
     */
    private function get_today_generated_count() {
        global $wpdb;
        
        // Get WordPress timezone
        $wp_timezone = wp_timezone();
        $today_start = new DateTime('today', $wp_timezone);
        $today_start_utc = $today_start->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_auto_nulis_generated'
                AND p.post_date >= %s",
                $today_start_utc
            )
        );
        
        return intval($count);
    }
      /**
     * Admin page callback
     */
    public function admin_page() {
        if (class_exists('Auto_Nulis_Admin')) {
            $admin = new Auto_Nulis_Admin();
            $admin->display_settings_page();        } else {
            echo '<div class="wrap"><h1>Writing Agent</h1><div class="notice notice-error"><p>';
            _e('Plugin dependencies not loaded properly. Please deactivate and reactivate the plugin.', 'auto-nulis');
            echo '</p></div></div>';
        }
    }
    
    /**
     * Articles page callback
     */
    public function articles_page() {
        include AUTO_NULIS_PLUGIN_PATH . 'admin/articles-page.php';
    }
      /**
     * Generate page callback
     */
    public function generate_page() {
        include AUTO_NULIS_PLUGIN_PATH . 'admin/generate-page.php';
    }
    
    /**
     * Logs page callback
     */
    public function logs_page() {
        include AUTO_NULIS_PLUGIN_PATH . 'admin/logs-page.php';
    }
      /**
     * Ensure log table exists
     */
    public static function ensure_log_table_exists() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'auto_nulis_logs';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        
        if ($table_exists != $table_name) {
            // Create table if it doesn't exist
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
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $result = dbDelta($sql);
              // Log any errors during table creation
            if (defined('WP_DEBUG') && WP_DEBUG && $wpdb->last_error) {
                error_log('Writing Agent table creation error: ' . $wpdb->last_error);
            }
            
            return $result;
        }
        
        return true;
    }
    
    /**
     * Create log table
     */
    private function create_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'auto_nulis_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            level varchar(20) DEFAULT '' NOT NULL,
            message text NOT NULL,
            context text,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Initialize the plugin
new Auto_Nulis();

// AJAX handlers
add_action('wp_ajax_auto_nulis_test_api', 'auto_nulis_test_api_connection');
add_action('wp_ajax_auto_nulis_generate_now', 'auto_nulis_generate_article_now');
add_action('wp_ajax_auto_nulis_generate_immediate', 'auto_nulis_generate_article_immediate');
add_action('wp_ajax_auto_nulis_get_stats', 'auto_nulis_get_stats');
add_action('wp_ajax_auto_nulis_export_logs', 'auto_nulis_export_logs');
add_action('wp_ajax_auto_nulis_auto_save', 'auto_nulis_auto_save_setting');
add_action('wp_ajax_auto_nulis_create_tables', 'auto_nulis_create_tables');

/**
 * Test API connection via AJAX
 */
function auto_nulis_test_api_connection() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    if (!class_exists('Auto_Nulis_API')) {
        wp_send_json(array(
            'success' => false,
            'message' => __('API class not available', 'auto-nulis')
        ));
        return;
    }
    
    $provider = sanitize_text_field($_POST['provider']);
    $api_key = sanitize_text_field($_POST['api_key']);
    $model = sanitize_text_field($_POST['model']);
    
    $api = new Auto_Nulis_API();
    $result = $api->test_connection($provider, $api_key, $model);
    
    wp_send_json($result);
}

/**
 * Generate article now via AJAX
 */
function auto_nulis_generate_article_now() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    if (!class_exists('Auto_Nulis_Generator')) {
        wp_send_json(array(
            'success' => false,
            'message' => __('Generator class not available', 'auto-nulis')
        ));
        return;
    }
    
    $generator = new Auto_Nulis_Generator();
    $result = $generator->generate_article();
    
    wp_send_json($result);
}

/**
 * Generate article immediate with custom parameters via AJAX
 */
function auto_nulis_generate_article_immediate() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    if (!class_exists('Auto_Nulis_Generator')) {
        wp_send_json_error(array(
            'message' => __('Generator class not available', 'auto-nulis')
        ));
        return;
    }
      // Get custom parameters
    $keyword = !empty($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
    $length = !empty($_POST['length']) ? sanitize_text_field($_POST['length']) : 'medium';
    $status = !empty($_POST['status']) ? sanitize_text_field($_POST['status']) : 'draft';
    $include_image = !empty($_POST['include_image']) ? true : false;
    $language = !empty($_POST['language']) ? sanitize_text_field($_POST['language']) : 'id';
    
    try {
        $generator = new Auto_Nulis_Generator();
        $result = $generator->generate_article_with_params($keyword, $length, $status, $include_image, $language);
        
        if ($result && isset($result['post_id'])) {
            wp_send_json_success(array(
                'title' => get_the_title($result['post_id']),
                'edit_link' => get_edit_post_link($result['post_id']),
                'post_id' => $result['post_id'],
                'message' => __('Article generated successfully!', 'auto-nulis')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to generate article', 'auto-nulis')
            ));
        }
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}

/**
 * Get plugin statistics via AJAX
 */
function auto_nulis_get_stats() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    $generator = new Auto_Nulis_Generator();
    $stats = $generator->get_statistics();
    
    wp_send_json_success($stats);
}

/**
 * Export logs via AJAX
 */
function auto_nulis_export_logs() {
    check_ajax_referer('export_logs', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'auto_nulis_logs';
    
    // Build query based on filters
    $where_conditions = array('1=1');
    $where_values = array();
    
    if (!empty($_POST['level'])) {
        $where_conditions[] = 'level = %s';
        $where_values[] = sanitize_text_field($_POST['level']);
    }
    
    if (!empty($_POST['date'])) {
        $where_conditions[] = 'DATE(timestamp) = %s';
        $where_values[] = sanitize_text_field($_POST['date']);
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    $query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY timestamp DESC";
    
    if (!empty($where_values)) {
        $logs = $wpdb->get_results($wpdb->prepare($query, $where_values));
    } else {
        $logs = $wpdb->get_results($query);
    }
    
    // Generate CSV
    $filename = 'auto-nulis-logs-' . date('Y-m-d-H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, array('Timestamp', 'Level', 'Message', 'Context'));
    
    // CSV data
    foreach ($logs as $log) {
        fputcsv($output, array(
            $log->timestamp,
            $log->level,
            $log->message,
            $log->context
        ));
    }
    
    fclose($output);
    exit;
}

/**
 * Auto-save setting via AJAX
 */
function auto_nulis_auto_save_setting() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    $field = sanitize_text_field($_POST['field']);
    $value = sanitize_textarea_field($_POST['value']);
    
    // Don't allow auto-save for critical settings like 'enabled'
    if ($field === 'enabled') {
        wp_send_json_error(array('message' => __('Enabled setting can only be changed via form submission', 'auto-nulis')));
        return;
    }
    
    $settings = get_option('auto_nulis_settings', array());
    $settings[$field] = $value;
    
    $updated = update_option('auto_nulis_settings', $settings);
    
    if ($updated) {
        wp_send_json_success(array('message' => __('Setting saved', 'auto-nulis')));
    } else {
        wp_send_json_error(array('message' => __('Failed to save setting', 'auto-nulis')));
    }
}

/**
 * Create database tables via AJAX
 */
function auto_nulis_create_tables() {
    check_ajax_referer('auto_nulis_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized access', 'auto-nulis'));
    }
    
    try {
        // Force create log table
        Auto_Nulis::ensure_log_table_exists();
        
        // Verify table was created
        global $wpdb;
        $table_name = $wpdb->prefix . 'auto_nulis_logs';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
        
        if ($table_exists) {
            wp_send_json_success(array(
                'message' => __('Database tables created successfully!', 'auto-nulis')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to create database tables', 'auto-nulis')
            ));
        }
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}
