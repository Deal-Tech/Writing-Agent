<?php
/**
 * Writing Agent Generator Class
 * Handles the article generation process
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Nulis_Generator {
    
    private $settings;
    private $api;
    private $image_handler;
      /**
     * Constructor
     */
    public function __construct() {
        // Force reload settings to ensure we have the latest data
        $this->settings = get_option('auto_nulis_settings', array());
        
        // Add debug logging for cron context
        if (defined('DOING_CRON') && DOING_CRON) {
            $this->log_message('debug', 'Generator initialized in CRON context', array(
                'settings_loaded' => !empty($this->settings),
                'keywords_present' => !empty($this->settings['keywords'] ?? ''),
                'plugin_enabled' => $this->settings['enabled'] ?? false
            ));
        }
        
        $this->api = new Auto_Nulis_API();
        $this->image_handler = new Auto_Nulis_Image();
    }
    
    /**
     * Generate a new article
     */
    public function generate_article() {
        try {
            // Check if plugin is enabled
            if (!isset($this->settings['enabled']) || !$this->settings['enabled']) {
                $this->log_message('info', 'Article generation skipped - plugin disabled');
                return array(
                    'success' => false,
                    'message' => __('Plugin is disabled', 'auto-nulis')
                );
            }
              // Get next keyword
            $keyword = $this->get_next_keyword();
            if (!$keyword) {
                // More detailed error message
                $keywords_raw = isset($this->settings['keywords']) ? $this->settings['keywords'] : '';
                $keywords_count = empty($keywords_raw) ? 0 : count(array_filter(array_map('trim', explode("\n", $keywords_raw))));
                
                $this->log_message('error', 'No keywords available for article generation', array(
                    'keywords_configured' => $keywords_count,
                    'keywords_raw_empty' => empty($keywords_raw),
                    'settings_enabled' => $this->settings['enabled'] ?? false
                ));
                
                $error_message = $keywords_count === 0 
                    ? __('No keywords configured. Please add keywords in the plugin settings.', 'auto-nulis')
                    : __('No available keywords for article generation', 'auto-nulis');
                
                return array(
                    'success' => false,
                    'message' => $error_message
                );
            }
              $this->log_message('info', "Starting article generation for keyword: {$keyword}");            // Check for duplicate titles
            if ($this->is_duplicate_topic($keyword)) {
                $this->log_message('info', "Skipping keyword '{$keyword}' - similar article already exists");
                
                // Mark this keyword as used to avoid selecting it again
                $this->mark_keyword_used($keyword);
                
                // Try to get another keyword instead of recursion
                $next_keyword = $this->get_next_unused_keyword();
                if (!$next_keyword) {
                    $this->log_message('warning', 'No unused keywords available - all keywords have been used or have duplicates');
                    return array(
                        'success' => false,
                        'message' => __('No available keywords for article generation', 'auto-nulis')
                    );
                }
                
                // Generate with the new keyword (without recursion)
                return $this->generate_article_for_keyword($next_keyword);
            }
            
            // Generate article content
            return $this->generate_article_for_keyword($keyword);
        } catch (Exception $e) {
            $this->log_message('error', 'Article generation failed: ' . $e->getMessage(), array(
                'keyword' => $keyword ?? 'unknown'
            ));
            
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Generate article for a specific keyword (helper method)
     */
    private function generate_article_for_keyword($keyword) {
        try {
            // Generate article content
            $article_data = $this->api->generate_article(
                $keyword,
                $this->settings['article_length']
            );
            
            if (!$article_data) {
                throw new Exception(__('Failed to generate article content', 'auto-nulis'));
            }
            
            // Create WordPress post
            $post_id = $this->create_wordpress_post($article_data, $keyword);
            
            if (!$post_id) {
                throw new Exception(__('Failed to create WordPress post', 'auto-nulis'));
            }
            
            // Add featured image if enabled
            if (isset($this->settings['include_images']) && $this->settings['include_images']) {
                $this->add_featured_image($post_id, $keyword);
            }
            
            // Mark keyword as used
            $this->mark_keyword_used($keyword);
            
            $this->log_message('success', "Article generated successfully for keyword: {$keyword}", array(
                'post_id' => $post_id,
                'title' => $article_data['title']
            ));
            
            return array(
                'success' => true,
                'message' => __('Article generated successfully!', 'auto-nulis'),
                'post_id' => $post_id,
                'keyword' => $keyword,
                'title' => $article_data['title']
            );
            
        } catch (Exception $e) {
            $this->log_message('error', 'Article generation failed: ' . $e->getMessage(), array(
                'keyword' => isset($keyword) ? $keyword : 'unknown'
            ));
            
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
      /**
     * Get next keyword from the list
     */
    private function get_next_keyword() {
        // Force refresh settings in case they changed
        $this->settings = get_option('auto_nulis_settings', array());
        
        $keywords = isset($this->settings['keywords']) ? $this->settings['keywords'] : '';
        
        // Enhanced logging for debugging
        $this->log_message('debug', 'Getting next keyword', array(
            'keywords_raw_length' => strlen($keywords),
            'keywords_empty' => empty($keywords),
            'settings_loaded' => !empty($this->settings)
        ));
        
        if (empty($keywords)) {
            $this->log_message('warning', 'No keywords configured in settings');
            return false;
        }
        
        $keyword_list = array_filter(array_map('trim', explode("\n", $keywords)));
        $used_keywords = get_option('auto_nulis_used_keywords', array());
        
        $this->log_message('debug', 'Keyword analysis', array(
            'total_keywords' => count($keyword_list),
            'used_keywords' => count($used_keywords),
            'keywords_sample' => array_slice($keyword_list, 0, 3) // Show first 3 keywords
        ));
        
        // Find unused keywords
        $available_keywords = array_diff($keyword_list, $used_keywords);
        
        // If all keywords used, reset the list
        if (empty($available_keywords)) {
            $this->log_message('info', 'All keywords used, resetting used keywords list', array(
                'total_keywords' => count($keyword_list),
                'used_keywords' => count($used_keywords)
            ));
            
            delete_option('auto_nulis_used_keywords');
            $available_keywords = $keyword_list;
        }
        
        if (empty($available_keywords)) {
            $this->log_message('error', 'No keywords available after processing', array(
                'keyword_list_count' => count($keyword_list),
                'keyword_list_sample' => array_slice($keyword_list, 0, 5)
            ));
            return false;
        }
        
        // Return random keyword
        $selected_keyword = $available_keywords[array_rand($available_keywords)];
        
        $this->log_message('debug', 'Keyword selected', array(
            'selected_keyword' => $selected_keyword,
            'available_count' => count($available_keywords)
        ));
        
        return $selected_keyword;
    }
    
    /**
     * Get next unused keyword without resetting used keywords list
     * This prevents infinite loops when all keywords have duplicates
     */
    private function get_next_unused_keyword() {
        $keywords = isset($this->settings['keywords']) ? $this->settings['keywords'] : '';
        
        if (empty($keywords)) {
            return false;
        }
        
        $keyword_list = array_filter(array_map('trim', explode("\n", $keywords)));
        $used_keywords = get_option('auto_nulis_used_keywords', array());
        
        // Find unused keywords
        $available_keywords = array_diff($keyword_list, $used_keywords);
        
        // Don't reset if all keywords used - this prevents infinite loops
        if (empty($available_keywords)) {
            return false;
        }
        
        // Return random keyword
        return $available_keywords[array_rand($available_keywords)];
    }
    
    /**
     * Check if a similar article already exists
     */
    private function is_duplicate_topic($keyword) {
        $existing_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'pending'),
            'meta_query' => array(
                array(
                    'key' => '_auto_nulis_keyword',
                    'value' => $keyword,
                    'compare' => '='
                )
            ),
            'numberposts' => 1
        ));
        
        if (!empty($existing_posts)) {
            return true;
        }
        
        // Also check by title similarity
        $similar_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'pending'),
            's' => $keyword,
            'numberposts' => 1
        ));
        
        return !empty($similar_posts);
    }
    
    /**
     * Create WordPress post from article data
     */
    private function create_wordpress_post($article_data, $keyword) {
        // Generate SEO-friendly slug
        $slug = $this->generate_seo_slug($article_data['title'], $keyword);
        
        $post_data = array(
            'post_title' => $article_data['title'],
            'post_content' => $article_data['content'],
            'post_status' => $this->settings['post_status'],
            'post_author' => $this->settings['author'],
            'post_category' => array($this->settings['category']),
            'post_name' => $slug,
            'post_type' => 'post'
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }
        
        // Add meta data
        update_post_meta($post_id, '_auto_nulis_keyword', $keyword);
        update_post_meta($post_id, '_auto_nulis_generated', current_time('mysql'));
        update_post_meta($post_id, '_auto_nulis_version', AUTO_NULIS_VERSION);
        
        // Add meta description if available
        if (!empty($article_data['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $article_data['meta_description']);
            update_post_meta($post_id, '_aioseop_description', $article_data['meta_description']);
        }
        
        // Add tags
        if (!empty($article_data['tags'])) {
            wp_set_post_tags($post_id, $article_data['tags']);
        }
        
        // Set category
        wp_set_post_categories($post_id, array($this->settings['category']));
        
        return $post_id;
    }
    
    /**
     * Create WordPress post with custom status
     */
    private function create_wordpress_post_with_status($article_data, $keyword, $status) {
        // Generate SEO-friendly slug
        $slug = $this->generate_seo_slug($article_data['title'], $keyword);
        
        // Get settings with defaults
        $default_category = isset($this->settings['category']) ? $this->settings['category'] : 1;
        $default_author = isset($this->settings['author']) ? $this->settings['author'] : 1;
        
        $post_data = array(
            'post_title' => $article_data['title'],
            'post_content' => $article_data['content'],
            'post_status' => $status, // Use custom status
            'post_author' => $default_author,
            'post_category' => array($default_category),
            'post_name' => $slug,
            'post_type' => 'post'
        );
        
        // Insert the post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            throw new Exception($post_id->get_error_message());
        }
          // Add meta data
        update_post_meta($post_id, '_auto_nulis_keyword', $keyword);
        update_post_meta($post_id, '_auto_nulis_generated', '1');
        update_post_meta($post_id, '_auto_nulis_generation_time', current_time('mysql'));
        update_post_meta($post_id, '_auto_nulis_version', AUTO_NULIS_VERSION);
        update_post_meta($post_id, '_auto_nulis_timezone', wp_timezone_string());
        
        // Add meta description if available
        if (!empty($article_data['meta_description'])) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $article_data['meta_description']);
        }
        
        // Add tags if provided
        if (!empty($article_data['tags'])) {
            wp_set_post_tags($post_id, $article_data['tags']);
        }
        
        return $post_id;
    }
    
    /**
     * Generate SEO-friendly slug
     */
    private function generate_seo_slug($title, $keyword) {
        // Start with the title
        $slug = sanitize_title($title);
        
        // Ensure keyword is in slug
        $keyword_slug = sanitize_title($keyword);
        if (strpos($slug, $keyword_slug) === false) {
            $slug = $keyword_slug . '-' . $slug;
        }
        
        // Limit length to 50 characters for SEO
        if (strlen($slug) > 50) {
            $words = explode('-', $slug);
            $new_slug = '';
            foreach ($words as $word) {
                if (strlen($new_slug . '-' . $word) <= 50) {
                    $new_slug .= ($new_slug ? '-' : '') . $word;
                } else {
                    break;
                }
            }
            $slug = $new_slug;
        }
        
        // Ensure uniqueness
        $original_slug = $slug;
        $counter = 1;
        while (get_page_by_path($slug, OBJECT, 'post')) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Add featured image to post
     */
    private function add_featured_image($post_id, $keyword) {
        try {
            $image_url = $this->image_handler->get_relevant_image($keyword, $this->settings['image_source']);
            
            if ($image_url) {
                $attachment_id = $this->image_handler->download_and_attach_image($image_url, $post_id, $keyword);
                
                if ($attachment_id) {
                    set_post_thumbnail($post_id, $attachment_id);
                    $this->log_message('info', "Featured image added to post {$post_id}");
                }
            }
        } catch (Exception $e) {
            $this->log_message('warning', "Failed to add featured image: " . $e->getMessage(), array(
                'post_id' => $post_id,
                'keyword' => $keyword
            ));
        }
    }
    
    /**
     * Mark keyword as used
     */
    private function mark_keyword_used($keyword) {
        $used_keywords = get_option('auto_nulis_used_keywords', array());
        $used_keywords[] = $keyword;
        update_option('auto_nulis_used_keywords', array_unique($used_keywords));
    }
    
    /**
     * Get generation statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $total_generated = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_auto_nulis_generated'"
        );
        
        $today_generated = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} 
                WHERE meta_key = '_auto_nulis_generated' 
                AND meta_value >= %s",
                date('Y-m-d 00:00:00')
            )
        );
        
        $keywords = isset($this->settings['keywords']) ? $this->settings['keywords'] : '';
        $total_keywords = count(array_filter(array_map('trim', explode("\n", $keywords))));
        
        $used_keywords = get_option('auto_nulis_used_keywords', array());
        $remaining_keywords = $total_keywords - count($used_keywords);
        
        return array(
            'total_generated' => intval($total_generated),
            'today_generated' => intval($today_generated),
            'total_keywords' => $total_keywords,
            'remaining_keywords' => max(0, $remaining_keywords),
            'daily_target' => intval($this->settings['articles_per_day'])
        );
    }    /**
     * Log message
     */
    public function log_message($level, $message, $context = array()) {
        global $wpdb;
        
        // Ensure log table exists
        if (class_exists('Auto_Nulis')) {
            Auto_Nulis::ensure_log_table_exists();
        }
        
        $table_name = $wpdb->prefix . 'auto_nulis_logs';
          $wpdb->insert(
            $table_name,
            array(
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'timestamp' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
          // Also log to WordPress debug log if enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $wp_timezone = wp_timezone_string();
            error_log("Writing Agent [{$level}] [{$wp_timezone}]: {$message}");
        }
    }
    
    /**
     * Get recent logs
     */
    public function get_recent_logs($limit = 50) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'auto_nulis_logs';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
                $limit
            )
        );
    }
      /**
     * Generate article with custom parameters
     */
    public function generate_article_with_params($keyword = '', $length = 'medium', $status = 'draft', $include_image = true, $language = '') {
        try {
            // Set language in settings temporarily if provided
            if (!empty($language)) {
                $this->settings['article_language'] = $language;
            }
            
            // Use provided keyword or get next from list
            if (empty($keyword)) {
                $keyword = $this->get_next_keyword();
                if (!$keyword) {
                    throw new Exception(__('No keywords available', 'auto-nulis'));
                }
            }
            
            $this->log_message('info', "Starting immediate article generation for keyword: {$keyword} in language: " . ($language ?: 'default'));
            
            // Check for duplicate titles
            if ($this->is_duplicate_topic($keyword)) {
                $this->log_message('info', "Warning: Similar article may already exist for keyword '{$keyword}'");
                // Continue anyway for immediate generation
            }
            
            // Generate article content with custom length
            $article_data = $this->api->generate_article($keyword, $length);
            
            if (!$article_data) {
                throw new Exception(__('Failed to generate article content', 'auto-nulis'));
            }
            
            // Create WordPress post with custom status
            $post_id = $this->create_wordpress_post_with_status($article_data, $keyword, $status);
            
            if (!$post_id) {
                throw new Exception(__('Failed to create WordPress post', 'auto-nulis'));
            }
            
            // Add featured image if requested
            if ($include_image) {
                $this->add_featured_image($post_id, $keyword);
            }
            
            // Don't mark keyword as used for immediate generation to allow reuse
            
            $this->log_message('success', "Immediate article generated successfully for keyword: {$keyword}", array(
                'post_id' => $post_id,
                'title' => $article_data['title'],
                'status' => $status
            ));
            
            return array(
                'success' => true,
                'message' => __('Article generated successfully!', 'auto-nulis'),
                'post_id' => $post_id,
                'keyword' => $keyword,
                'title' => $article_data['title'],
                'status' => $status
            );
            
        } catch (Exception $e) {
            $this->log_message('error', 'Immediate article generation failed: ' . $e->getMessage(), array(
                'keyword' => $keyword,
                'length' => $length,
                'status' => $status
            ));
            
            throw $e; // Re-throw for AJAX handler
        }
    }
}
