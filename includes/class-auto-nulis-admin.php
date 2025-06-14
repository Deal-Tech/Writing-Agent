<?php
/**
 * Writing Agent Admin Class
 * Handles the admin interface and settings page
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Nulis_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('auto_nulis_settings', 'auto_nulis_settings', array($this, 'validate_settings'));
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings = get_option('auto_nulis_settings', array());
        
        include AUTO_NULIS_PLUGIN_PATH . 'admin/settings-page.php';
    }    /**
     * Save settings
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('auto_nulis_settings_nonce');        // Debug: Log what we received in POST
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Auto Nulis Settings POST data: ' . print_r($_POST, true));
            error_log('Auto Nulis enabled checkbox: ' . (isset($_POST['enabled']) ? 'SET' : 'NOT SET'));
            error_log('Auto Nulis enabled value: ' . (isset($_POST['enabled']) ? $_POST['enabled'] : 'none'));
            
            // Check if we received multiple values for enabled (hidden + checkbox)
            if (isset($_POST['enabled']) && is_array($_POST['enabled'])) {
                error_log('Auto Nulis enabled is array: ' . print_r($_POST['enabled'], true));
            }
        }
        
        // Handle checkbox properly - if it's an array, take the last value (checkbox overrides hidden)
        $enabled_value = false;
        if (isset($_POST['enabled'])) {
            if (is_array($_POST['enabled'])) {
                $enabled_value = end($_POST['enabled']) === '1';
            } else {
                $enabled_value = $_POST['enabled'] === '1';
            }
        }
          $settings = array(
            'enabled' => $enabled_value,
            'articles_per_day' => intval($_POST['articles_per_day']),
            'schedule_time' => sanitize_text_field($_POST['schedule_time']),
            'keywords' => sanitize_textarea_field($_POST['keywords']),
            'article_length' => sanitize_text_field($_POST['article_length']),
            'post_status' => sanitize_text_field($_POST['post_status']),
            'include_images' => isset($_POST['include_images']) ? true : false,
            'image_source' => sanitize_text_field($_POST['image_source']),
            'unsplash_api_key' => sanitize_text_field($_POST['unsplash_api_key'] ?? ''),
            'pexels_api_key' => sanitize_text_field($_POST['pexels_api_key'] ?? ''),
            'category' => intval($_POST['category']),
            'author' => intval($_POST['author']),
            'ai_provider' => sanitize_text_field($_POST['ai_provider']),
            'api_key' => sanitize_text_field($_POST['api_key']),
            'ai_model' => sanitize_text_field($_POST['ai_model']),
            'article_language' => sanitize_text_field($_POST['article_language'])
        );
        
        // Debug: Log what we're about to save
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Auto Nulis Settings before validation: ' . print_r($settings, true));
        }
        
        $validated_settings = $this->validate_settings($settings);
        
        // Debug: Log validated settings
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Auto Nulis Settings after validation: ' . print_r($validated_settings, true));
        }
        
        $update_result = update_option('auto_nulis_settings', $validated_settings);
        
        // Debug: Log update result
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Auto Nulis Settings update result: ' . ($update_result ? 'SUCCESS' : 'FAILED'));
            
            // Verify what was actually saved
            $saved_settings = get_option('auto_nulis_settings', array());
            error_log('Auto Nulis Settings actually saved: ' . print_r($saved_settings, true));
        }
        
        // Update cron schedule if settings changed
        $this->update_cron_schedule($validated_settings);
        
        add_settings_error('auto_nulis_settings', 'settings_updated', __('Settings saved successfully!', 'auto-nulis'), 'updated');
    }
      /**
     * Validate settings
     */
    public function validate_settings($input) {
        $validated = array();
          // Preserve the enabled value exactly as processed in save_settings
        $validated['enabled'] = isset($input['enabled']) ? $input['enabled'] : false;
        $validated['articles_per_day'] = max(1, min(10, intval($input['articles_per_day'])));
        $validated['schedule_time'] = sanitize_text_field($input['schedule_time']);
        $validated['keywords'] = sanitize_textarea_field($input['keywords']);
        $validated['article_length'] = in_array($input['article_length'], array('short', 'medium', 'long')) ? $input['article_length'] : 'medium';
        $validated['post_status'] = in_array($input['post_status'], array('publish', 'draft', 'pending')) ? $input['post_status'] : 'draft';
        $validated['include_images'] = isset($input['include_images']) ? true : false;
        $validated['image_source'] = in_array($input['image_source'], array('unsplash', 'pexels', 'media_library')) ? $input['image_source'] : 'unsplash';
        $validated['unsplash_api_key'] = sanitize_text_field($input['unsplash_api_key'] ?? '');
        $validated['pexels_api_key'] = sanitize_text_field($input['pexels_api_key'] ?? '');
        $validated['category'] = max(1, intval($input['category']));
        $validated['author'] = max(1, intval($input['author']));$validated['ai_provider'] = in_array($input['ai_provider'], array('gemini', 'openai')) ? $input['ai_provider'] : 'gemini';
        $validated['api_key'] = sanitize_text_field($input['api_key']);
        $validated['ai_model'] = sanitize_text_field($input['ai_model']);
        
        // Validate article language
        $valid_languages = array_keys($this->get_language_options());
        $validated['article_language'] = in_array($input['article_language'], $valid_languages) ? $input['article_language'] : 'id';
        
        // Debug: Log validation input and output
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Auto Nulis Validation - Input enabled: ' . var_export($input['enabled'], true));
            error_log('Auto Nulis Validation - Validated enabled: ' . var_export($validated['enabled'], true));
        }
        
        return $validated;
    }    /**
     * Update cron schedule
     */
    private function update_cron_schedule($settings) {
        // Use the scheduler class if available
        if (class_exists('Auto_Nulis_Scheduler')) {
            $scheduler = new Auto_Nulis_Scheduler();
            $scheduler->schedule_generation($settings);
        } else {
            // Fallback to old method
            wp_clear_scheduled_hook('auto_nulis_generate_article');
            
            if ($settings['enabled']) {
                $wp_timezone = wp_timezone();
                $current_time = new DateTime('now', $wp_timezone);
                
                $schedule_time_parts = explode(':', $settings['schedule_time']);
                $schedule_hour = intval($schedule_time_parts[0]);
                $schedule_minute = intval($schedule_time_parts[1]);
                
                $next_run_time = new DateTime('today', $wp_timezone);
                $next_run_time->setTime($schedule_hour, $schedule_minute, 0);
                
                if ($next_run_time <= $current_time) {
                    $next_run_time->add(new DateInterval('P1D'));
                }
                
                $next_run_utc = $next_run_time->getTimestamp();
                wp_schedule_event($next_run_utc, 'auto_nulis_custom', 'auto_nulis_generate_article');
            }
        }
    }
    
    /**
     * Display admin notices
     */
    public function admin_notices() {
        settings_errors('auto_nulis_settings');
    }
    
    /**
     * Get available categories
     */
    public function get_categories() {
        $categories = get_categories(array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        return $categories;
    }
    
    /**
     * Get available users/authors
     */
    public function get_authors() {
        $users = get_users(array(
            'who' => 'authors',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));
        
        return $users;
    }
    
    /**
     * Get article length options
     */
    public function get_article_length_options() {
        return array(
            'short' => __('Short (300-500 words)', 'auto-nulis'),
            'medium' => __('Medium (500-800 words)', 'auto-nulis'),
            'long' => __('Long (800-1200+ words)', 'auto-nulis')
        );
    }
    
    /**
     * Get post status options
     */
    public function get_post_status_options() {
        return array(
            'publish' => __('Published', 'auto-nulis'),
            'draft' => __('Draft', 'auto-nulis'),
            'pending' => __('Pending Review', 'auto-nulis')
        );
    }
    
    /**
     * Get AI provider options
     */
    public function get_ai_provider_options() {
        return array(
            'gemini' => __('Google AI (Gemini)', 'auto-nulis'),
            'openai' => __('OpenAI', 'auto-nulis')
        );
    }
      /**
     * Get AI model options
     */
    public function get_ai_model_options($provider) {
        $models = array();
        
        switch ($provider) {
            case 'gemini':
                $models = array(
                    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Fast & Free)',
                    'gemini-1.5-pro' => 'Gemini 1.5 Pro (Advanced)',
                    'gemini-pro' => 'Gemini Pro (Legacy)'
                );
                break;
            case 'openai':
                $models = array(
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                    'gpt-4' => 'GPT-4',
                    'gpt-4-turbo' => 'GPT-4 Turbo'
                );
                break;
        }
        
        return $models;
    }
    
    /**
     * Get language options
     */
    public function get_language_options() {
        return array(
            'id' => __('Indonesian (Bahasa Indonesia)', 'auto-nulis'),
            'en' => __('English', 'auto-nulis'),
            'ms' => __('Malay (Bahasa Melayu)', 'auto-nulis'),
            'es' => __('Spanish (Español)', 'auto-nulis'),
            'fr' => __('French (Français)', 'auto-nulis'),
            'de' => __('German (Deutsch)', 'auto-nulis'),
            'pt' => __('Portuguese (Português)', 'auto-nulis'),
            'it' => __('Italian (Italiano)', 'auto-nulis'),
            'nl' => __('Dutch (Nederlands)', 'auto-nulis'),
            'ru' => __('Russian (Русский)', 'auto-nulis'),
            'ja' => __('Japanese (日本語)', 'auto-nulis'),
            'ko' => __('Korean (한국어)', 'auto-nulis'),
            'zh' => __('Chinese (中文)', 'auto-nulis'),
            'ar' => __('Arabic (العربية)', 'auto-nulis'),
            'hi' => __('Hindi (हिन्दी)', 'auto-nulis'),
            'th' => __('Thai (ไทย)', 'auto-nulis'),
            'vi' => __('Vietnamese (Tiếng Việt)', 'auto-nulis'),
            'tr' => __('Turkish (Türkçe)', 'auto-nulis'),
            'pl' => __('Polish (Polski)', 'auto-nulis'),
            'sv' => __('Swedish (Svenska)', 'auto-nulis')
        );
    }
      /**
     * Get image source options
     */
    public function get_image_source_options() {
        return array(
            'unsplash' => __('Unsplash API', 'auto-nulis'),
            'pexels' => __('Pexels API', 'auto-nulis'),
            'media_library' => __('WordPress Media Library', 'auto-nulis')
        );
    }
    
    /**
     * Test Unsplash API connection
     */
    public function test_unsplash_api($api_key) {
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is required', 'auto-nulis')
            );
        }
        
        $url = 'https://api.unsplash.com/search/photos';
        $params = array(
            'query' => 'test',
            'per_page' => 1
        );
        
        $response = wp_remote_get($url . '?' . http_build_query($params), array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $api_key,
                'Accept-Version' => 'v1'
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Connection failed: ', 'auto-nulis') . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            $data = json_decode($body, true);
            if (isset($data['results'])) {
                return array(
                    'success' => true,
                    'message' => __('Unsplash API connection successful!', 'auto-nulis'),
                    'data' => array(
                        'total_results' => $data['total'] ?? 0,
                        'rate_limit' => wp_remote_retrieve_header($response, 'X-Ratelimit-Remaining')
                    )
                );
            }
        } elseif ($response_code === 401) {
            return array(
                'success' => false,
                'message' => __('Invalid API key or unauthorized access', 'auto-nulis')
            );
        } elseif ($response_code === 403) {
            return array(
                'success' => false,
                'message' => __('API rate limit exceeded or access forbidden', 'auto-nulis')
            );
        }
        
        return array(
            'success' => false,
            'message' => __('API test failed with response code: ', 'auto-nulis') . $response_code
        );
    }
    
    /**
     * Test Pexels API connection
     */
    public function test_pexels_api($api_key) {
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is required', 'auto-nulis')
            );
        }
        
        $url = 'https://api.pexels.com/v1/search';
        $params = array(
            'query' => 'test',
            'per_page' => 1
        );
        
        $response = wp_remote_get($url . '?' . http_build_query($params), array(
            'headers' => array(
                'Authorization' => $api_key
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Connection failed: ', 'auto-nulis') . $response->get_error_message()
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($response_code === 200) {
            $data = json_decode($body, true);
            if (isset($data['photos'])) {
                return array(
                    'success' => true,
                    'message' => __('Pexels API connection successful!', 'auto-nulis'),
                    'data' => array(
                        'total_results' => $data['total_results'] ?? 0,
                        'rate_limit' => wp_remote_retrieve_header($response, 'X-Ratelimit-Remaining')
                    )
                );
            }
        } elseif ($response_code === 401) {
            return array(
                'success' => false,
                'message' => __('Invalid API key or unauthorized access', 'auto-nulis')
            );
        } elseif ($response_code === 429) {
            return array(
                'success' => false,
                'message' => __('API rate limit exceeded', 'auto-nulis')
            );
        }
        
        return array(
            'success' => false,
            'message' => __('API test failed with response code: ', 'auto-nulis') . $response_code
        );
    }
}
