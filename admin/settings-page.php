<?php
/**
 * Writing Agent Settings Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = new Auto_Nulis_Admin();
$settings = get_option('auto_nulis_settings', array());

// Set default values
$defaults = array(
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

$settings = wp_parse_args($settings, $defaults);
?>

<div class="wrap auto-nulis-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('auto_nulis_settings'); ?>
    
    <div class="auto-nulis-container">
        <div class="auto-nulis-main">
            <form method="post" action="">
                <?php wp_nonce_field('auto_nulis_settings_nonce'); ?>
                
                <!-- Plugin Status Section -->
                <div class="auto-nulis-section">
                    <h2><?php _e('Plugin Status', 'auto-nulis'); ?></h2>
                    <div class="auto-nulis-field">
                        <label class="auto-nulis-toggle">
                            <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], true); ?>>
                            <span class="auto-nulis-toggle-slider"></span>
                            <?php _e('Enable Auto Article Generation', 'auto-nulis'); ?>
                        </label>
                        <p class="description"><?php _e('Toggle this to enable or disable automatic article generation.', 'auto-nulis'); ?></p>
                    </div>
                </div>
                
                <!-- Article Generation Settings -->
                <div class="auto-nulis-section">
                    <h2><?php _e('Article Generation Settings', 'auto-nulis'); ?></h2>
                    
                    <div class="auto-nulis-grid">
                        <div class="auto-nulis-field">
                            <label for="articles_per_day"><?php _e('Articles Per Day', 'auto-nulis'); ?></label>
                            <input type="number" id="articles_per_day" name="articles_per_day" value="<?php echo esc_attr($settings['articles_per_day']); ?>" min="1" max="10" class="small-text">
                            <p class="description"><?php _e('Number of articles to generate per day (1-10).', 'auto-nulis'); ?></p>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="schedule_time"><?php _e('Schedule Time', 'auto-nulis'); ?></label>
                            <input type="time" id="schedule_time" name="schedule_time" value="<?php echo esc_attr($settings['schedule_time']); ?>">
                            <p class="description"><?php _e('Time of day to start generating articles.', 'auto-nulis'); ?></p>
                        </div>
                    </div>
                    
                    <div class="auto-nulis-field">
                        <label for="keywords"><?php _e('Keywords List', 'auto-nulis'); ?></label>
                        <textarea id="keywords" name="keywords" rows="8" class="large-text" placeholder="<?php esc_attr_e('Enter one keyword per line...', 'auto-nulis'); ?>"><?php echo esc_textarea($settings['keywords']); ?></textarea>
                        <p class="description"><?php _e('Enter one keyword per line. Each keyword will be used as a topic for generating an article.', 'auto-nulis'); ?></p>
                    </div>
                    
                    <div class="auto-nulis-grid">
                        <div class="auto-nulis-field">
                            <label for="article_length"><?php _e('Article Length', 'auto-nulis'); ?></label>
                            <select id="article_length" name="article_length">
                                <?php foreach ($admin->get_article_length_options() as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['article_length'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                          <div class="auto-nulis-field">
                            <label for="post_status"><?php _e('Article Status', 'auto-nulis'); ?></label>
                            <select id="post_status" name="post_status">
                                <?php foreach ($admin->get_post_status_options() as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['post_status'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="article_language"><?php _e('Article Language', 'auto-nulis'); ?></label>
                            <select id="article_language" name="article_language">
                                <?php foreach ($admin->get_language_options() as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['article_language'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select the language for generated articles.', 'auto-nulis'); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Image Settings -->
                <div class="auto-nulis-section">
                    <h2><?php _e('Image Settings', 'auto-nulis'); ?></h2>
                    
                    <div class="auto-nulis-field">
                        <label class="auto-nulis-checkbox">
                            <input type="checkbox" name="include_images" value="1" <?php checked($settings['include_images'], true); ?>>
                            <?php _e('Include Images in Articles', 'auto-nulis'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically add relevant images to generated articles.', 'auto-nulis'); ?></p>
                    </div>
                    
                    <div class="auto-nulis-field image-source-field" <?php echo !$settings['include_images'] ? 'style="display:none;"' : ''; ?>>
                        <label for="image_source"><?php _e('Image Source', 'auto-nulis'); ?></label>
                        <select id="image_source" name="image_source">
                            <?php foreach ($admin->get_image_source_options() as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['image_source'], $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- WordPress Settings -->
                <div class="auto-nulis-section">
                    <h2><?php _e('WordPress Settings', 'auto-nulis'); ?></h2>
                    
                    <div class="auto-nulis-grid">
                        <div class="auto-nulis-field">
                            <label for="category"><?php _e('Default Category', 'auto-nulis'); ?></label>
                            <select id="category" name="category">
                                <?php foreach ($admin->get_categories() as $category): ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($settings['category'], $category->term_id); ?>>
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="author"><?php _e('Article Author', 'auto-nulis'); ?></label>
                            <select id="author" name="author">
                                <?php foreach ($admin->get_authors() as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($settings['author'], $user->ID); ?>>
                                        <?php echo esc_html($user->display_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- AI Settings -->
                <div class="auto-nulis-section">
                    <h2><?php _e('AI Configuration', 'auto-nulis'); ?></h2>
                    
                    <div class="auto-nulis-grid">
                        <div class="auto-nulis-field">
                            <label for="ai_provider"><?php _e('AI Provider', 'auto-nulis'); ?></label>
                            <select id="ai_provider" name="ai_provider">
                                <?php foreach ($admin->get_ai_provider_options() as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['ai_provider'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="ai_model"><?php _e('AI Model', 'auto-nulis'); ?></label>
                            <select id="ai_model" name="ai_model">
                                <?php foreach ($admin->get_ai_model_options($settings['ai_provider']) as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['ai_model'], $value); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="auto-nulis-field">
                        <label for="api_key"><?php _e('API Key', 'auto-nulis'); ?></label>
                        <input type="password" id="api_key" name="api_key" value="<?php echo esc_attr($settings['api_key']); ?>" class="regular-text">
                        <button type="button" id="test-api-connection" class="button button-secondary">
                            <?php _e('Test Connection', 'auto-nulis'); ?>
                        </button>
                        <p class="description"><?php _e('Enter your AI provider API key. This will be stored securely.', 'auto-nulis'); ?></p>
                        <div id="api-test-result"></div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="auto-nulis-actions">
                    <input type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'auto-nulis'); ?>">
                    <button type="button" id="generate-now" class="button button-secondary">
                        <?php _e('Generate Article Now', 'auto-nulis'); ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Sidebar -->
        <div class="auto-nulis-sidebar">
            <div class="auto-nulis-widget">
                <h3><?php _e('Quick Stats', 'auto-nulis'); ?></h3>
                <div class="auto-nulis-stats">
                    <?php
                    $generated_count = wp_count_posts('post');
                    $keywords_count = count(array_filter(explode("\n", $settings['keywords'])));
                    ?>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo esc_html($keywords_count); ?></span>
                        <span class="stat-label"><?php _e('Keywords Ready', 'auto-nulis'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo esc_html($generated_count->publish + $generated_count->draft); ?></span>
                        <span class="stat-label"><?php _e('Total Posts', 'auto-nulis'); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo esc_html($settings['articles_per_day']); ?></span>
                        <span class="stat-label"><?php _e('Daily Target', 'auto-nulis'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="auto-nulis-widget">
                <h3><?php _e('Next Scheduled Run', 'auto-nulis'); ?></h3>
                <p>
                    <?php
                    $next_run = wp_next_scheduled('auto_nulis_generate_article');
                    if ($next_run && $settings['enabled']) {
                        echo esc_html(date_i18n('Y-m-d H:i:s', $next_run));
                    } else {
                        _e('Not scheduled', 'auto-nulis');
                    }
                    ?>
                </p>
            </div>
              <div class="auto-nulis-widget">
                <h3><?php _e('System Status', 'auto-nulis'); ?></h3>
                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'auto_nulis_logs';
                $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) == $table_name;
                ?>
                <div class="system-status">
                    <div class="status-item">
                        <span class="status-label"><?php _e('Log Table:', 'auto-nulis'); ?></span>
                        <span class="status-value <?php echo $table_exists ? 'status-ok' : 'status-error'; ?>">
                            <?php echo $table_exists ? __('OK', 'auto-nulis') : __('Missing', 'auto-nulis'); ?>
                        </span>
                    </div>                    <?php if (!$table_exists): ?>
                        <div class="status-actions">
                            <button type="button" id="create-tables" class="button button-small">
                                <?php _e('Create Tables', 'auto-nulis'); ?>
                            </button>
                            <br><br>
                            <p class="description">
                                <?php _e('If the problem persists, try the', 'auto-nulis'); ?>
                                <a href="<?php echo plugin_dir_url(dirname(__FILE__)) . 'troubleshoot-db.php'; ?>" target="_blank">
                                    <?php _e('database troubleshooting tool', 'auto-nulis'); ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="auto-nulis-widget">
                <h3><?php _e('Tips', 'auto-nulis'); ?></h3>
                <ul>
                    <li><?php _e('Use specific, focused keywords for better article quality', 'auto-nulis'); ?></li>
                    <li><?php _e('Start with Draft status to review articles before publishing', 'auto-nulis'); ?></li>
                    <li><?php _e('Test your API connection before enabling auto-generation', 'auto-nulis'); ?></li>
                    <li><?php _e('Monitor the logs for any issues', 'auto-nulis'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
