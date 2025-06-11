<?php
/**
 * Writing Agent Generate Article Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('auto_nulis_settings', array());
$api_configured = !empty($settings['api_key']);
$keywords_available = !empty(trim($settings['keywords']));
$keywords_list = array_filter(explode("\n", trim($settings['keywords'])));
?>

<div class="wrap auto-nulis-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="auto-nulis-container">
        <div class="auto-nulis-main">
            <!-- Generate Article Section -->
            <div class="auto-nulis-section">
                <h2><?php _e('Generate Article Now', 'auto-nulis'); ?></h2>
                
                <?php if (!$api_configured): ?>
                    <div class="notice notice-warning">
                        <p>
                            <?php _e('API key is not configured. Please configure your AI provider settings first.', 'auto-nulis'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis')); ?>" class="button button-secondary">
                                <?php _e('Go to Settings', 'auto-nulis'); ?>
                            </a>
                        </p>
                    </div>
                <?php elseif (!$keywords_available): ?>
                    <div class="notice notice-warning">
                        <p>
                            <?php _e('No keywords configured. Please add keywords to generate articles.', 'auto-nulis'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis')); ?>" class="button button-secondary">
                                <?php _e('Add Keywords', 'auto-nulis'); ?>
                            </a>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="auto-nulis-generate-form">
                        <div class="auto-nulis-field">
                            <label for="generate_keyword"><?php _e('Select Keyword', 'auto-nulis'); ?></label>
                            <select id="generate_keyword" name="generate_keyword">
                                <option value=""><?php _e('Random from list', 'auto-nulis'); ?></option>
                                <?php foreach ($keywords_list as $keyword): ?>
                                    <option value="<?php echo esc_attr(trim($keyword)); ?>">
                                        <?php echo esc_html(trim($keyword)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select a specific keyword or leave blank to use a random one.', 'auto-nulis'); ?></p>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="generate_length"><?php _e('Article Length', 'auto-nulis'); ?></label>
                            <select id="generate_length" name="generate_length">
                                <option value="short" <?php selected($settings['article_length'], 'short'); ?>><?php _e('Short (300-500 words)', 'auto-nulis'); ?></option>
                                <option value="medium" <?php selected($settings['article_length'], 'medium'); ?>><?php _e('Medium (500-800 words)', 'auto-nulis'); ?></option>
                                <option value="long" <?php selected($settings['article_length'], 'long'); ?>><?php _e('Long (800+ words)', 'auto-nulis'); ?></option>
                            </select>
                        </div>
                          <div class="auto-nulis-field">
                            <label for="generate_status"><?php _e('Post Status', 'auto-nulis'); ?></label>
                            <select id="generate_status" name="generate_status">
                                <option value="draft" <?php selected($settings['post_status'], 'draft'); ?>><?php _e('Draft', 'auto-nulis'); ?></option>
                                <option value="pending" <?php selected($settings['post_status'], 'pending'); ?>><?php _e('Pending Review', 'auto-nulis'); ?></option>
                                <option value="publish" <?php selected($settings['post_status'], 'publish'); ?>><?php _e('Publish', 'auto-nulis'); ?></option>
                            </select>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label for="generate_language"><?php _e('Article Language', 'auto-nulis'); ?></label>
                            <select id="generate_language" name="generate_language">
                                <?php 
                                $admin = new Auto_Nulis_Admin();
                                foreach ($admin->get_language_options() as $value => $label): 
                                ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['article_language'], $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="auto-nulis-field">
                            <label class="auto-nulis-checkbox">
                                <input type="checkbox" id="generate_include_image" name="generate_include_image" value="1" <?php checked($settings['include_images'], true); ?>>
                                <?php _e('Include Featured Image', 'auto-nulis'); ?>
                            </label>
                        </div>
                        
                        <div class="auto-nulis-actions">
                            <button type="button" id="generate-article-btn" class="button button-primary button-large">
                                <?php _e('Generate Article', 'auto-nulis'); ?>
                            </button>
                            <div id="generation-progress" style="display:none;">
                                <div class="auto-nulis-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill"></div>
                                    </div>
                                    <p class="progress-text"><?php _e('Generating article...', 'auto-nulis'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div id="generation-result" style="display:none;"></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Generation History -->
            <div class="auto-nulis-section">
                <h2><?php _e('Recent Generations', 'auto-nulis'); ?></h2>
                
                <?php
                $recent_posts = new WP_Query(array(
                    'meta_key' => '_auto_nulis_generated',
                    'meta_value' => '1',
                    'posts_per_page' => 5,
                    'post_status' => array('publish', 'draft', 'pending'),
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                ?>
                
                <?php if ($recent_posts->have_posts()): ?>
                    <div class="auto-nulis-table-wrapper">
                        <table class="auto-nulis-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Title', 'auto-nulis'); ?></th>
                                    <th><?php _e('Status', 'auto-nulis'); ?></th>
                                    <th><?php _e('Generated', 'auto-nulis'); ?></th>
                                    <th><?php _e('Actions', 'auto-nulis'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($recent_posts->have_posts()): $recent_posts->the_post(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php the_title(); ?></strong>
                                            <div class="row-actions">
                                                <span class="keyword">
                                                    <?php 
                                                    $keyword = get_post_meta(get_the_ID(), '_auto_nulis_keyword', true);
                                                    if ($keyword) {
                                                        echo sprintf(__('Keyword: %s', 'auto-nulis'), esc_html($keyword));
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $status = get_post_status();
                                            $status_labels = array(
                                                'publish' => __('Published', 'auto-nulis'),
                                                'draft' => __('Draft', 'auto-nulis'),
                                                'pending' => __('Pending', 'auto-nulis'),
                                                'private' => __('Private', 'auto-nulis')
                                            );
                                            ?>
                                            <span class="status-badge <?php echo esc_attr($status); ?>">
                                                <?php echo esc_html($status_labels[$status] ?? ucfirst($status)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                <?php echo esc_html(get_the_date('M j, Y g:i a')); ?>
                                            </time>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?php echo esc_url(get_edit_post_link()); ?>" 
                                                   class="button button-small" target="_blank">
                                                    <?php _e('Edit', 'auto-nulis'); ?>
                                                </a>
                                                
                                                <?php if (get_post_status() === 'publish'): ?>
                                                    <a href="<?php echo esc_url(get_permalink()); ?>" 
                                                       class="button button-small" target="_blank">
                                                        <?php _e('View', 'auto-nulis'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="auto-nulis-empty-state">
                        <div class="empty-icon">
                            <span class="dashicons dashicons-edit-large"></span>
                        </div>
                        <h3><?php _e('No Recent Generations', 'auto-nulis'); ?></h3>
                        <p><?php _e('Generated articles will appear here.', 'auto-nulis'); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="auto-nulis-sidebar">
            <div class="auto-nulis-widget">
                <h3><?php _e('Current Configuration', 'auto-nulis'); ?></h3>
                <div class="config-summary">
                    <div class="config-item">
                        <span class="config-label"><?php _e('AI Provider:', 'auto-nulis'); ?></span>
                        <span class="config-value">
                            <?php 
                            $providers = array('gemini' => 'Google Gemini', 'openai' => 'OpenAI');
                            echo esc_html($providers[$settings['ai_provider']] ?? $settings['ai_provider']); 
                            ?>
                        </span>
                    </div>
                    <div class="config-item">
                        <span class="config-label"><?php _e('Model:', 'auto-nulis'); ?></span>
                        <span class="config-value"><?php echo esc_html($settings['ai_model'] ?? 'Not set'); ?></span>
                    </div>
                    <div class="config-item">
                        <span class="config-label"><?php _e('Available Keywords:', 'auto-nulis'); ?></span>
                        <span class="config-value"><?php echo count($keywords_list); ?></span>
                    </div>                    <div class="config-item">
                        <span class="config-label"><?php _e('Default Length:', 'auto-nulis'); ?></span>
                        <span class="config-value">
                            <?php 
                            $lengths = array(
                                'short' => __('Short', 'auto-nulis'),
                                'medium' => __('Medium', 'auto-nulis'),
                                'long' => __('Long', 'auto-nulis')
                            );
                            echo esc_html($lengths[$settings['article_length']] ?? $settings['article_length']); 
                            ?>
                        </span>
                    </div>
                    <div class="config-item">
                        <span class="config-label"><?php _e('Default Language:', 'auto-nulis'); ?></span>
                        <span class="config-value">
                            <?php 
                            $admin = new Auto_Nulis_Admin();
                            $languages = $admin->get_language_options();
                            echo esc_html($languages[$settings['article_language']] ?? $settings['article_language']); 
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="auto-nulis-widget">
                <h3><?php _e('Generation Tips', 'auto-nulis'); ?></h3>                <ul>
                    <li><?php _e('Choose specific keywords for better results', 'auto-nulis'); ?></li>
                    <li><?php _e('Use Draft status to review before publishing', 'auto-nulis'); ?></li>
                    <li><?php _e('Longer articles typically perform better for SEO', 'auto-nulis'); ?></li>
                    <li><?php _e('Select the appropriate language for your target audience', 'auto-nulis'); ?></li>
                    <li><?php _e('Check the generated content for accuracy', 'auto-nulis'); ?></li>
                </ul>
            </div>
            
            <div class="auto-nulis-widget">
                <h3><?php _e('Quick Actions', 'auto-nulis'); ?></h3>
                <div class="widget-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis')); ?>" class="button button-secondary">
                        <?php _e('Plugin Settings', 'auto-nulis'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis-articles')); ?>" class="button button-secondary">
                        <?php _e('All Articles', 'auto-nulis'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis-logs')); ?>" class="button button-secondary">
                        <?php _e('View Logs', 'auto-nulis'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#generate-article-btn').on('click', function() {
        var $button = $(this);
        var $progress = $('#generation-progress');
        var $result = $('#generation-result');
          // Get form values
        var keyword = $('#generate_keyword').val();
        var length = $('#generate_length').val();
        var status = $('#generate_status').val();
        var language = $('#generate_language').val();
        var includeImage = $('#generate_include_image').is(':checked');
        
        if (!confirm('<?php esc_js(_e('Are you sure you want to generate an article now? This will use your API quota.', 'auto-nulis')); ?>')) {
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true);
        $progress.show();
        $result.hide();
        
        // Animate progress bar
        var progress = 0;
        var progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            $('.progress-fill').css('width', progress + '%');
        }, 500);
        
        // Make AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',            data: {
                action: 'auto_nulis_generate_immediate',
                keyword: keyword,
                length: length,
                status: status,
                language: language,
                include_image: includeImage ? 1 : 0,
                nonce: '<?php echo wp_create_nonce('auto_nulis_nonce'); ?>'
            },
            success: function(response) {
                clearInterval(progressInterval);
                $('.progress-fill').css('width', '100%');
                
                setTimeout(function() {
                    $progress.hide();
                    
                    if (response.success) {
                        $result.html(
                            '<div class="notice notice-success">' +
                            '<p><strong><?php esc_js(_e('Article generated successfully!', 'auto-nulis')); ?></strong></p>' +
                            '<p><?php esc_js(_e('Title:', 'auto-nulis')); ?> ' + response.data.title + '</p>' +
                            '<p><a href="' + response.data.edit_link + '" target="_blank" class="button button-primary">' +
                            '<?php esc_js(_e('Edit Article', 'auto-nulis')); ?></a></p>' +
                            '</div>'
                        ).show();
                        
                        // Reload recent generations after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        $result.html(
                            '<div class="notice notice-error">' +
                            '<p><strong><?php esc_js(_e('Failed to generate article:', 'auto-nulis')); ?></strong></p>' +
                            '<p>' + (response.data ? response.data.message : '<?php esc_js(_e('Unknown error occurred', 'auto-nulis')); ?>') + '</p>' +
                            '</div>'
                        ).show();
                    }
                }, 1000);
            },
            error: function(xhr, status, error) {
                clearInterval(progressInterval);
                $progress.hide();
                $result.html(
                    '<div class="notice notice-error">' +
                    '<p><strong><?php esc_js(_e('Connection error:', 'auto-nulis')); ?></strong></p>' +
                    '<p>' + error + '</p>' +
                    '</div>'
                ).show();
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});
</script>

<style>
.auto-nulis-generate-form {
    max-width: 600px;
}

.auto-nulis-progress {
    margin: 20px 0;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background-color: #f1f1f1;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2271b1, #72aee6);
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    margin: 10px 0;
    font-weight: 600;
}

.config-summary {
    space-y: 8px;
}

.config-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.config-item:last-child {
    border-bottom: none;
}

.config-label {
    font-weight: 600;
}

.config-value {
    color: #666;
}

.widget-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.widget-actions .button {
    text-align: center;
}
</style>
