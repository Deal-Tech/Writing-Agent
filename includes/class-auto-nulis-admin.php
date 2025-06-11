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
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('auto_nulis_settings_nonce');
          $settings = array(
            'enabled' => isset($_POST['enabled']) ? true : false,
            'articles_per_day' => intval($_POST['articles_per_day']),
            'schedule_time' => sanitize_text_field($_POST['schedule_time']),
            'keywords' => sanitize_textarea_field($_POST['keywords']),
            'article_length' => sanitize_text_field($_POST['article_length']),
            'post_status' => sanitize_text_field($_POST['post_status']),
            'include_images' => isset($_POST['include_images']) ? true : false,
            'image_source' => sanitize_text_field($_POST['image_source']),
            'category' => intval($_POST['category']),
            'author' => intval($_POST['author']),
            'ai_provider' => sanitize_text_field($_POST['ai_provider']),
            'api_key' => sanitize_text_field($_POST['api_key']),
            'ai_model' => sanitize_text_field($_POST['ai_model']),
            'article_language' => sanitize_text_field($_POST['article_language'])
        );
        
        $validated_settings = $this->validate_settings($settings);
        update_option('auto_nulis_settings', $validated_settings);
        
        // Update cron schedule if settings changed
        $this->update_cron_schedule($validated_settings);
        
        add_settings_error('auto_nulis_settings', 'settings_updated', __('Settings saved successfully!', 'auto-nulis'), 'updated');
    }
    
    /**
     * Validate settings
     */
    public function validate_settings($input) {
        $validated = array();
        
        $validated['enabled'] = isset($input['enabled']) ? true : false;
        $validated['articles_per_day'] = max(1, min(10, intval($input['articles_per_day'])));
        $validated['schedule_time'] = sanitize_text_field($input['schedule_time']);
        $validated['keywords'] = sanitize_textarea_field($input['keywords']);
        $validated['article_length'] = in_array($input['article_length'], array('short', 'medium', 'long')) ? $input['article_length'] : 'medium';
        $validated['post_status'] = in_array($input['post_status'], array('publish', 'draft', 'pending')) ? $input['post_status'] : 'draft';
        $validated['include_images'] = isset($input['include_images']) ? true : false;
        $validated['image_source'] = in_array($input['image_source'], array('unsplash', 'pexels', 'media_library')) ? $input['image_source'] : 'unsplash';
        $validated['category'] = max(1, intval($input['category']));
        $validated['author'] = max(1, intval($input['author']));        $validated['ai_provider'] = in_array($input['ai_provider'], array('gemini', 'openai')) ? $input['ai_provider'] : 'gemini';
        $validated['api_key'] = sanitize_text_field($input['api_key']);
        $validated['ai_model'] = sanitize_text_field($input['ai_model']);
        
        // Validate article language
        $valid_languages = array_keys($this->get_language_options());
        $validated['article_language'] = in_array($input['article_language'], $valid_languages) ? $input['article_language'] : 'id';
        
        return $validated;
    }
    
    /**
     * Update cron schedule
     */
    private function update_cron_schedule($settings) {
        // Clear existing schedule
        wp_clear_scheduled_hook('auto_nulis_generate_article');
        
        // Schedule new event if enabled
        if ($settings['enabled']) {
            // Calculate next run time based on schedule_time
            $next_run = strtotime('today ' . $settings['schedule_time']);
            if ($next_run <= time()) {
                $next_run = strtotime('tomorrow ' . $settings['schedule_time']);
            }
            
            wp_schedule_event($next_run, 'auto_nulis_custom', 'auto_nulis_generate_article');
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
}
