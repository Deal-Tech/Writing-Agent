<?php
/**
 * Writing Agent Scheduler Class
 * Handles cron scheduling with proper timezone support
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Nulis_Scheduler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init_scheduler'));
        add_filter('cron_schedules', array($this, 'add_custom_intervals'));
    }
    
    /**
     * Initialize scheduler
     */
    public function init_scheduler() {
        // Add action for our cron hook
        add_action('auto_nulis_generate_article', array($this, 'execute_scheduled_generation'));
        
        // Check and fix broken schedules on admin pages
        if (is_admin()) {
            add_action('admin_init', array($this, 'verify_and_fix_schedule'));
        }
    }
    
    /**
     * Add custom cron intervals
     */
    public function add_custom_intervals($schedules) {
        $settings = get_option('auto_nulis_settings', array());
        $articles_per_day = isset($settings['articles_per_day']) ? max(1, intval($settings['articles_per_day'])) : 1;
        
        // Calculate interval (minimum 1 hour)
        $interval = max(3600, floor(24 * 3600 / $articles_per_day));
        
        $schedules['auto_nulis_interval'] = array(
            'interval' => $interval,
            'display' => sprintf(__('Auto Nulis: Every %s', 'auto-nulis'), $this->format_duration($interval))
        );
        
        return $schedules;
    }
    
    /**
     * Schedule article generation
     */
    public function schedule_generation($settings) {
        // Clear existing schedules
        $this->clear_scheduled_generation();
        
        // Only schedule if enabled
        if (empty($settings['enabled'])) {
            return false;
        }
        
        // Get WordPress timezone
        $wp_timezone = wp_timezone();
        $current_time = new DateTime('now', $wp_timezone);
        
        // Parse schedule time
        $schedule_time_parts = explode(':', $settings['schedule_time']);
        $schedule_hour = intval($schedule_time_parts[0]);
        $schedule_minute = isset($schedule_time_parts[1]) ? intval($schedule_time_parts[1]) : 0;
        
        // Create next run time in WordPress timezone
        $next_run = new DateTime('today', $wp_timezone);
        $next_run->setTime($schedule_hour, $schedule_minute, 0);
        
        // If time has passed today, schedule for tomorrow
        if ($next_run <= $current_time) {
            $next_run->add(new DateInterval('P1D'));
        }
        
        // Convert to UTC timestamp
        $next_run_utc = $next_run->getTimestamp();
        
        // Schedule the event
        $scheduled = wp_schedule_event($next_run_utc, 'auto_nulis_interval', 'auto_nulis_generate_article');
        
        // Log scheduling attempt
        if (class_exists('Auto_Nulis_Generator')) {
            $generator = new Auto_Nulis_Generator();
            $generator->log_message('info', 'Cron scheduling attempt', array(
                'scheduled' => $scheduled !== false,
                'next_run_local' => $next_run->format('Y-m-d H:i:s T'),
                'next_run_utc' => date('Y-m-d H:i:s', $next_run_utc),
                'timezone' => $wp_timezone->getName(),
                'interval_seconds' => $this->get_interval_seconds($settings),
                'articles_per_day' => $settings['articles_per_day']
            ));
        }
        
        return $scheduled !== false;
    }
    
    /**
     * Clear scheduled generation
     */
    public function clear_scheduled_generation() {
        wp_clear_scheduled_hook('auto_nulis_generate_article');
    }
      /**
     * Execute scheduled generation
     */
    public function execute_scheduled_generation() {
        $settings = get_option('auto_nulis_settings', array());
        
        // Check if plugin is enabled
        if (empty($settings['enabled'])) {
            $this->log_generation_message('info', 'Scheduled generation skipped - plugin disabled');
            return;
        }
        
        // Check daily limit
        $today_count = $this->get_today_count();
        $daily_limit = max(1, intval($settings['articles_per_day']));
        
        if ($today_count >= $daily_limit) {
            // Log that daily limit reached and stop further execution
            $this->log_generation_message('info', 'Daily article limit reached - stopping scheduled generation', array(
                'today_count' => $today_count,
                'daily_limit' => $daily_limit
            ));
            
            // Clear any remaining scheduled events for today to prevent more execution
            $this->clear_remaining_schedules_for_today();
            return;
        }
          // Generate article
        if (class_exists('Auto_Nulis_Generator')) {
            $generator = new Auto_Nulis_Generator();
            
            // Log generation start with more details
            $wp_timezone = wp_timezone();
            $current_time = new DateTime('now', $wp_timezone);
            
            // Get settings for debugging
            $current_settings = get_option('auto_nulis_settings', array());
            $keywords_configured = !empty($current_settings['keywords']) ? count(array_filter(array_map('trim', explode("\n", $current_settings['keywords'])))) : 0;
            
            $generator->log_message('info', 'Scheduled generation started', array(
                'execution_time' => $current_time->format('Y-m-d H:i:s T'),
                'timezone' => $wp_timezone->getName(),
                'today_count' => $today_count,
                'daily_limit' => $daily_limit,
                'keywords_configured' => $keywords_configured,
                'plugin_enabled' => $current_settings['enabled'] ?? false
            ));
            
            // Execute generation
            $result = $generator->generate_article();
            
            // Log result with enhanced debugging
            if ($result && $result['success']) {
                $generator->log_message('success', 'Scheduled generation completed', array(
                    'post_id' => $result['post_id'],
                    'keyword' => $result['keyword'],
                    'title' => $result['title']
                ));
                
                // Check if daily limit reached after this generation
                $new_today_count = $this->get_today_count();
                if ($new_today_count >= $daily_limit) {
                    $generator->log_message('info', 'Daily limit reached after generation - no more articles will be generated today', array(
                        'today_count' => $new_today_count,
                        'daily_limit' => $daily_limit
                    ));
                    $this->clear_remaining_schedules_for_today();
                }
            } else {
                $error_message = $result ? $result['message'] : 'Unknown error';
                $generator->log_message('error', 'Scheduled generation failed', array(
                    'error' => $error_message,
                    'keywords_configured' => $keywords_configured,
                    'plugin_enabled' => $current_settings['enabled'] ?? false
                ));
                  // If generation failed due to no keywords, stop scheduling for today
                if ($result && (strpos($result['message'], 'No available keywords') !== false || strpos($result['message'], 'No keywords configured') !== false)) {
                    $generator->log_message('warning', 'Keyword issue detected - stopping scheduled generation for today', array(
                        'error_message' => $error_message,
                        'keywords_configured' => $keywords_configured
                    ));
                    $this->clear_remaining_schedules_for_today();
                }
            }
        } else {
            // Log if generator class not available
            if (function_exists('error_log')) {
                error_log('Auto Nulis: Generator class not available during scheduled execution');
            }
        }
    }
    
    /**
     * Get today's article count
     */
    private function get_today_count() {
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
     * Get interval in seconds
     */
    private function get_interval_seconds($settings) {
        $articles_per_day = max(1, intval($settings['articles_per_day']));
        return max(3600, floor(24 * 3600 / $articles_per_day));
    }
    
    /**
     * Format duration for display
     */
    private function format_duration($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours >= 24) {
            $days = floor($hours / 24);
            $remaining_hours = $hours % 24;
            if ($remaining_hours > 0) {
                return sprintf(__('%d days %d hours', 'auto-nulis'), $days, $remaining_hours);
            }
            return sprintf(__('%d days', 'auto-nulis'), $days);
        }
        
        if ($hours > 0) {
            if ($minutes > 0) {
                return sprintf(__('%d hours %d minutes', 'auto-nulis'), $hours, $minutes);
            }
            return sprintf(__('%d hours', 'auto-nulis'), $hours);
        }
        
        return sprintf(__('%d minutes', 'auto-nulis'), $minutes);
    }
    
    /**
     * Get next scheduled run info
     */
    public function get_next_run_info() {
        $next_run_timestamp = wp_next_scheduled('auto_nulis_generate_article');
        
        if (!$next_run_timestamp) {
            return array(
                'scheduled' => false,
                'next_run' => null,
                'next_run_local' => null,
                'timezone' => wp_timezone_string()
            );
        }
        
        // Convert to WordPress timezone
        $wp_timezone = wp_timezone();
        $next_run_local = new DateTime('@' . $next_run_timestamp);
        $next_run_local->setTimezone($wp_timezone);
        
        return array(
            'scheduled' => true,
            'next_run' => $next_run_timestamp,
            'next_run_local' => $next_run_local,
            'next_run_formatted' => $next_run_local->format('Y-m-d H:i:s T'),
            'timezone' => $wp_timezone->getName()
        );
    }
    
    /**
     * Verify and fix broken schedules
     */
    public function verify_and_fix_schedule() {
        $settings = get_option('auto_nulis_settings', array());
        
        // Only check if plugin is enabled
        if (empty($settings['enabled'])) {
            return;
        }
        
        $next_run = wp_next_scheduled('auto_nulis_generate_article');
        
        // If no schedule exists but should, create it
        if (!$next_run) {
            $this->schedule_generation($settings);
        }
    }
    
    /**
     * Get scheduling status for admin display
     */
    public function get_status() {
        $settings = get_option('auto_nulis_settings', array());
        $next_run_info = $this->get_next_run_info();
        $today_count = $this->get_today_count();
        $daily_limit = max(1, intval($settings['articles_per_day']));
        
        return array(
            'enabled' => !empty($settings['enabled']),
            'scheduled' => $next_run_info['scheduled'],
            'next_run' => $next_run_info,
            'today_count' => $today_count,
            'daily_limit' => $daily_limit,
            'remaining_today' => max(0, $daily_limit - $today_count),
            'schedule_time' => $settings['schedule_time'] ?? '09:00',
            'articles_per_day' => $daily_limit,
            'interval_formatted' => $this->format_duration($this->get_interval_seconds($settings))
        );
    }
    
    /**
     * Clear remaining scheduled events for today to prevent over-generation
     */
    private function clear_remaining_schedules_for_today() {
        // Get all scheduled events for our hook
        $timestamp = wp_next_scheduled('auto_nulis_generate_article');
        
        if ($timestamp) {
            // Get WordPress timezone
            $wp_timezone = wp_timezone();
            $scheduled_time = new DateTime('@' . $timestamp);
            $scheduled_time->setTimezone($wp_timezone);
            $today_end = new DateTime('tomorrow', $wp_timezone);
            
            // If the next scheduled event is today, clear it
            if ($scheduled_time < $today_end) {
                wp_unschedule_event($timestamp, 'auto_nulis_generate_article');
                $this->log_generation_message('info', 'Cleared remaining scheduled events for today');
            }
        }
    }
    
    /**
     * Helper method to log generation messages
     */
    private function log_generation_message($level, $message, $context = array()) {
        if (class_exists('Auto_Nulis_Generator')) {
            $generator = new Auto_Nulis_Generator();
            $generator->log_message($level, $message, $context);
        }
    }
}
