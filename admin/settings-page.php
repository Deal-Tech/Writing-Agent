<?php
/**
 * Writing Agent Settings Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$admin = new Auto_Nulis_Admin();
$settings = get_option('auto_nulis_settings', array());

// Set default values - but preserve false values that are explicitly set
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

// Manually merge settings to preserve boolean false values
foreach ($defaults as $key => $default_value) {
    if (!array_key_exists($key, $settings)) {
        $settings[$key] = $default_value;
    }
}

// Debug: Log current settings if debug is enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Auto Nulis Settings Page - Current settings: ' . print_r($settings, true));
    error_log('Auto Nulis Settings Page - Enabled value: ' . (isset($settings['enabled']) ? ($settings['enabled'] ? 'true' : 'false') : 'not set'));
}
?>

<style>
/* WordPress-style toggle switch */
.toggle-switch {
    position: relative;
    display: inline-block;
}

.toggle-switch-checkbox {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}

.toggle-switch-slider {
    position: relative;
    display: inline-block;
    width: 36px;
    height: 18px;
    background-color: #8c8f94;
    border-radius: 9px;
    transition: background-color 0.2s ease-in-out;
    vertical-align: middle;
    cursor: pointer;
}

.toggle-switch-slider:before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 14px;
    height: 14px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.2s ease-in-out;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
}

.toggle-switch-checkbox:checked + .toggle-switch-slider {
    background-color: #00a32a;
}

.toggle-switch-checkbox:checked + .toggle-switch-slider:before {
    transform: translateX(18px);
}

.toggle-switch-checkbox:focus + .toggle-switch-slider {
    box-shadow: 0 0 0 2px #2271b1;
    outline: 2px solid transparent;
}

.toggle-switch-slider:hover {
    background-color: #646970;
}

.toggle-switch-checkbox:checked + .toggle-switch-slider:hover {
    background-color: #008a00;
}

.toggle-switch-label {
    font-weight: 600;
    color: #1d2327;
    user-select: none;
    cursor: pointer;
    display: inline-block;
}

/* Disabled state */
.toggle-switch-checkbox:disabled + .toggle-switch-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

.toggle-switch-checkbox:disabled ~ .toggle-switch-label {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>

<div class="wrap auto-nulis-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('auto_nulis_settings'); ?>
    
    <div class="auto-nulis-container">
        <div class="auto-nulis-main">
            <form method="post" action="">
                <?php wp_nonce_field('auto_nulis_settings_nonce'); ?>                <!-- Plugin Status Section -->
                <div class="auto-nulis-section">
                    <h2><?php _e('Plugin Status', 'auto-nulis'); ?></h2>
                    <div style="display: flex; flex-direction: column; align-items: flex-start; padding: 0 20px;">
                        <div id="plugin-status-indicator" style="margin-bottom: 8px; font-weight: 600; font-size: 14px;">
                            <?php if ($settings['enabled']): ?>
                                <span style="color: #00a32a;">● Active</span>
                            <?php else: ?>
                                <span style="color: #d63638;">○ Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label class="toggle-switch" style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled'], true); ?> id="enabled" class="toggle-switch-checkbox">
                                <span class="toggle-switch-slider"></span>
                            </label>
                            <span class="toggle-switch-label" style="font-weight: 600; color: #1d2327; margin: 0;"><?php _e('Enable Auto Article Generation', 'auto-nulis'); ?></span>
                        </div>
                        <p class="description" style="margin-top: 8px;"><?php _e('Toggle this to enable or disable automatic article generation.', 'auto-nulis'); ?></p>
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
                    <button type="button" id="debug-form" class="button button-secondary" style="margin-left: 10px;">
                        <?php _e('Debug Form', 'auto-nulis'); ?>
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
            </div>            <div class="auto-nulis-widget">
                <h3><?php _e('Scheduling Status', 'auto-nulis'); ?></h3>
                <?php
                // Get scheduling status
                $scheduler_status = null;
                if (class_exists('Auto_Nulis_Scheduler')) {
                    $scheduler = new Auto_Nulis_Scheduler();
                    $scheduler_status = $scheduler->get_status();
                }
                
                if ($scheduler_status): ?>
                    <div class="auto-nulis-schedule-status">
                        <p><strong><?php _e('Status:', 'auto-nulis'); ?></strong>
                            <span class="status-<?php echo $scheduler_status['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo $scheduler_status['enabled'] ? __('Enabled', 'auto-nulis') : __('Disabled', 'auto-nulis'); ?>
                            </span>
                        </p>
                        
                        <?php if ($scheduler_status['enabled']): ?>
                            <p><strong><?php _e('Next Run:', 'auto-nulis'); ?></strong>
                                <?php if ($scheduler_status['scheduled']): ?>
                                    <time datetime="<?php echo esc_attr($scheduler_status['next_run']['next_run']); ?>">
                                        <?php echo esc_html($scheduler_status['next_run']['next_run_formatted']); ?>
                                    </time>
                                <?php else: ?>
                                    <span class="status-error"><?php _e('Not scheduled', 'auto-nulis'); ?></span>
                                <?php endif; ?>
                            </p>
                            
                            <p><strong><?php _e('Today\'s Progress:', 'auto-nulis'); ?></strong>
                                <?php echo esc_html($scheduler_status['today_count']); ?> / <?php echo esc_html($scheduler_status['daily_limit']); ?>
                                (<?php echo esc_html($scheduler_status['remaining_today']); ?> <?php _e('remaining', 'auto-nulis'); ?>)
                            </p>
                            
                            <p><strong><?php _e('Interval:', 'auto-nulis'); ?></strong>
                                <?php echo esc_html($scheduler_status['interval_formatted']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="description">
                            <strong><?php _e('Timezone:', 'auto-nulis'); ?></strong> <?php echo esc_html(wp_timezone_string()); ?><br>
                            <strong><?php _e('Schedule Time:', 'auto-nulis'); ?></strong> <?php echo esc_html($settings['schedule_time']); ?><br>
                            <strong><?php _e('Articles Per Day:', 'auto-nulis'); ?></strong> <?php echo esc_html($settings['articles_per_day']); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <p>
                        <?php
                        $next_run = wp_next_scheduled('auto_nulis_generate_article');
                        if ($next_run && $settings['enabled']) {
                            $wp_timezone = wp_timezone();
                            $next_run_local = new DateTime('@' . $next_run);
                            $next_run_local->setTimezone($wp_timezone);
                            echo esc_html($next_run_local->format('Y-m-d H:i:s T'));
                        } else {
                            _e('Not scheduled', 'auto-nulis');
                        }
                        ?>
                    </p>
                    
                    <?php if ($settings['enabled']): ?>
                        <p class="description">
                            <strong><?php _e('Timezone:', 'auto-nulis'); ?></strong> <?php echo esc_html(wp_timezone_string()); ?><br>
                            <strong><?php _e('Schedule Time:', 'auto-nulis'); ?></strong> <?php echo esc_html($settings['schedule_time']); ?><br>
                            <strong><?php _e('Articles Per Day:', 'auto-nulis'); ?></strong> <?php echo esc_html($settings['articles_per_day']); ?>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
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
        </div>    </div>
    <script>
jQuery(document).ready(function($) {
    // Update status indicator
    function updateStatusIndicator(isEnabled) {
        var $indicator = $('#plugin-status-indicator');
        if (isEnabled) {
            $indicator.html('<span style="color: #00a32a;">● Active</span>');
        } else {
            $indicator.html('<span style="color: #d63638;">○ Inactive</span>');
        }
    }
    
    // Handle direct checkbox clicks
    $('.toggle-switch-checkbox').on('change', function() {
        var $checkbox = $(this);
        var isEnabled = $checkbox.is(':checked');
        
        console.log('Toggle changed:', isEnabled);
        
        // Update hidden field
        var $hiddenField = $('input[name="enabled"][type="hidden"]');
        if ($hiddenField.length > 0) {
            $hiddenField.val(isEnabled ? '1' : '0');
        }
        
        // Update status indicator
        updateStatusIndicator(isEnabled);
        
        // Visual feedback on label
        var $label = $('.toggle-switch-label');
        if (isEnabled) {
            $label.css('color', '#00a32a');
        } else {
            $label.css('color', '#1d2327');
        }
    });
    
    // Handle clicks on toggle area (slider)
    $('.toggle-switch-slider').on('click', function(e) {
        e.preventDefault();
        var $checkbox = $(this).siblings('.toggle-switch-checkbox');
        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    });
    
    // Handle clicks on label
    $('.toggle-switch-label').on('click', function(e) {
        e.preventDefault();
        var $checkbox = $('#enabled');
        $checkbox.prop('checked', !$checkbox.is(':checked')).trigger('change');
    });
    
    // Initialize state on page load
    var $checkbox = $('.toggle-switch-checkbox');
    if ($checkbox.length > 0) {
        var isEnabled = $checkbox.is(':checked');
        
        // Set initial status
        updateStatusIndicator(isEnabled);
        
        // Set initial label color
        var $label = $('.toggle-switch-label');
        if (isEnabled) {
            $label.css('color', '#00a32a');
        }
        
        console.log('Initial state:', isEnabled);
    }
});
</script>
</div>
