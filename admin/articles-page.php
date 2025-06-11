<?php
/**
 * Writing Agent Generated Articles Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get generated articles
$args = array(
    'post_type' => 'post',
    'meta_key' => '_auto_nulis_generated',
    'posts_per_page' => 20,
    'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    'orderby' => 'date',
    'order' => 'DESC'
);

$generated_posts = new WP_Query($args);
?>

<div class="wrap auto-nulis-admin">
    <h1><?php _e('Generated Articles', 'auto-nulis'); ?></h1>
    
    <div class="auto-nulis-stats-summary">
        <div class="stat-box">
            <h3><?php echo esc_html($generated_posts->found_posts); ?></h3>
            <p><?php _e('Total Generated', 'auto-nulis'); ?></p>
        </div>
        <div class="stat-box">
            <?php
            $today_count = get_posts(array(
                'post_type' => 'post',
                'meta_key' => '_auto_nulis_generated',
                'meta_value' => date('Y-m-d'),
                'meta_compare' => 'LIKE',
                'posts_per_page' => -1
            ));
            ?>
            <h3><?php echo count($today_count); ?></h3>
            <p><?php _e('Generated Today', 'auto-nulis'); ?></p>
        </div>
        <div class="stat-box">
            <?php
            $published_count = get_posts(array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'meta_key' => '_auto_nulis_generated',
                'posts_per_page' => -1
            ));
            ?>
            <h3><?php echo count($published_count); ?></h3>
            <p><?php _e('Published', 'auto-nulis'); ?></p>
        </div>
    </div>
    
    <?php if ($generated_posts->have_posts()): ?>
        <table class="auto-nulis-table widefat">
            <thead>
                <tr>
                    <th><?php _e('Title', 'auto-nulis'); ?></th>
                    <th><?php _e('Keyword', 'auto-nulis'); ?></th>
                    <th><?php _e('Status', 'auto-nulis'); ?></th>
                    <th><?php _e('Generated', 'auto-nulis'); ?></th>
                    <th><?php _e('Actions', 'auto-nulis'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($generated_posts->have_posts()): $generated_posts->the_post(); ?>
                    <?php
                    $post_id = get_the_ID();
                    $keyword = get_post_meta($post_id, '_auto_nulis_keyword', true);
                    $generated_date = get_post_meta($post_id, '_auto_nulis_generated', true);
                    $word_count = str_word_count(strip_tags(get_the_content()));
                    ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" target="_blank">
                                    <?php the_title(); ?>
                                </a>
                            </strong>
                            <div class="post-info">
                                <?php echo esc_html($word_count); ?> words
                                <?php if (has_post_thumbnail($post_id)): ?>
                                    | <span class="dashicons dashicons-format-image" title="<?php esc_attr_e('Has featured image', 'auto-nulis'); ?>"></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($keyword): ?>
                                <code><?php echo esc_html($keyword); ?></code>
                            <?php else: ?>
                                <em><?php _e('N/A', 'auto-nulis'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status = get_post_status($post_id);
                            $status_labels = array(
                                'publish' => __('Published', 'auto-nulis'),
                                'draft' => __('Draft', 'auto-nulis'),
                                'pending' => __('Pending', 'auto-nulis'),
                                'private' => __('Private', 'auto-nulis')
                            );
                            $status_classes = array(
                                'publish' => 'success',
                                'draft' => 'warning',
                                'pending' => 'info',
                                'private' => 'info'
                            );
                            ?>
                            <span class="status-badge <?php echo esc_attr($status_classes[$status] ?? ''); ?>">
                                <?php echo esc_html($status_labels[$status] ?? ucfirst($status)); ?>
                            </span>
                        </td>
                        <td>
                            <time datetime="<?php echo esc_attr($generated_date); ?>">
                                <?php echo esc_html(date_i18n('M j, Y \a\t g:i a', strtotime($generated_date))); ?>
                            </time>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" 
                                   class="button button-small" target="_blank">
                                    <?php _e('Edit', 'auto-nulis'); ?>
                                </a>
                                
                                <?php if ($status === 'publish'): ?>
                                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" 
                                       class="button button-small" target="_blank">
                                        <?php _e('View', 'auto-nulis'); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url(get_delete_post_link($post_id)); ?>" 
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this post?', 'auto-nulis'); ?>')">
                                    <?php _e('Delete', 'auto-nulis'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($generated_posts->max_num_pages > 1): ?>
            <div class="auto-nulis-pagination">
                <?php
                echo paginate_links(array(
                    'total' => $generated_posts->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => __('&laquo; Previous', 'auto-nulis'),
                    'next_text' => __('Next &raquo;', 'auto-nulis'),
                    'type' => 'plain'
                ));
                ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="auto-nulis-empty-state">
            <div class="empty-icon">
                <span class="dashicons dashicons-edit-large"></span>
            </div>
            <h3><?php _e('No Articles Generated Yet', 'auto-nulis'); ?></h3>
            <p><?php _e('Once you configure the plugin and start generating articles, they will appear here.', 'auto-nulis'); ?></p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis')); ?>" class="button button-primary">
                <?php _e('Configure Plugin', 'auto-nulis'); ?>
            </a>
        </div>
    <?php endif; ?>
    
    <?php wp_reset_postdata(); ?>
</div>

<style>
.auto-nulis-stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
}

.stat-box h3 {
    font-size: 32px;
    margin: 0 0 10px 0;
    color: #2271b1;
    font-weight: bold;
}

.stat-box p {
    margin: 0;
    color: #646970;
    font-size: 14px;
}

.post-info {
    font-size: 12px;
    color: #646970;
    margin-top: 5px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-badge.success {
    background: #d1f2d1;
    color: #2e7d32;
}

.status-badge.warning {
    background: #fff3cd;
    color: #856404;
}

.status-badge.info {
    background: #e3f2fd;
    color: #1565c0;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.action-buttons .button {
    margin: 0;
}

.auto-nulis-pagination {
    margin: 20px 0;
    text-align: center;
}

.auto-nulis-pagination .page-numbers {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 2px;
    text-decoration: none;
    border: 1px solid #c3c4c7;
    background: #fff;
    color: #2271b1;
}

.auto-nulis-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.auto-nulis-pagination .page-numbers:hover {
    background: #f6f7f7;
}

.auto-nulis-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
}

.empty-icon {
    font-size: 48px;
    color: #c3c4c7;
    margin-bottom: 20px;
}

.auto-nulis-empty-state h3 {
    margin: 0 0 15px 0;
    color: #1d2327;
}

.auto-nulis-empty-state p {
    margin: 0 0 25px 0;
    color: #646970;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
</style>
