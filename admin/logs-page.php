<?php
/**
 * Writing Agent Logs Page
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Ensure log table exists
if (class_exists('Auto_Nulis')) {
    Auto_Nulis::ensure_log_table_exists();
}

// Handle log clearing
if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'clear_logs')) {
    $table_name = $wpdb->prefix . 'auto_nulis_logs';
    $wpdb->query("TRUNCATE TABLE $table_name");
    echo '<div class="notice notice-success"><p>' . __('Logs cleared successfully.', 'auto-nulis') . '</p></div>';
}

// Get filter parameters
$level_filter = isset($_GET['level']) ? sanitize_text_field($_GET['level']) : '';
$date_filter = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
$per_page = 50;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Build query
$table_name = $wpdb->prefix . 'auto_nulis_logs';
$where_conditions = array('1=1');
$where_values = array();

if (!empty($level_filter)) {
    $where_conditions[] = 'level = %s';
    $where_values[] = $level_filter;
}

if (!empty($date_filter)) {
    $where_conditions[] = 'DATE(timestamp) = %s';
    $where_values[] = $date_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
if (!empty($where_values)) {
    $total_logs = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
} else {
    $total_logs = $wpdb->get_var($count_query);
}

// Get logs
$logs_query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY timestamp DESC LIMIT %d OFFSET %d";
$query_values = array_merge($where_values, array($per_page, $offset));

if (!empty($where_values)) {
    $logs = $wpdb->get_results($wpdb->prepare($logs_query, $query_values));
} else {
    $logs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT %d OFFSET %d", $per_page, $offset));
}

$total_pages = ceil($total_logs / $per_page);
?>

<div class="wrap auto-nulis-admin">
    <h1><?php _e('Activity Logs', 'auto-nulis'); ?></h1>
    
    <!-- Filters -->
    <div class="auto-nulis-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="auto-nulis-logs">
            
            <div class="auto-nulis-grid" style="grid-template-columns: 200px 200px auto;">
                <div class="auto-nulis-field">
                    <label for="level"><?php _e('Log Level', 'auto-nulis'); ?></label>
                    <select id="level" name="level">
                        <option value=""><?php _e('All Levels', 'auto-nulis'); ?></option>
                        <option value="success" <?php selected($level_filter, 'success'); ?>><?php _e('Success', 'auto-nulis'); ?></option>
                        <option value="info" <?php selected($level_filter, 'info'); ?>><?php _e('Info', 'auto-nulis'); ?></option>
                        <option value="warning" <?php selected($level_filter, 'warning'); ?>><?php _e('Warning', 'auto-nulis'); ?></option>
                        <option value="error" <?php selected($level_filter, 'error'); ?>><?php _e('Error', 'auto-nulis'); ?></option>
                    </select>
                </div>
                
                <div class="auto-nulis-field">
                    <label for="date"><?php _e('Date', 'auto-nulis'); ?></label>
                    <input type="date" id="date" name="date" value="<?php echo esc_attr($date_filter); ?>">
                </div>
                
                <div class="auto-nulis-field" style="display: flex; align-items: end; gap: 10px;">
                    <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'auto-nulis'); ?>">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis-logs')); ?>" class="button">
                        <?php _e('Clear Filters', 'auto-nulis'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Stats -->
    <div class="auto-nulis-logs-stats">
        <?php
        $stats = $wpdb->get_results("
            SELECT level, COUNT(*) as count 
            FROM $table_name 
            GROUP BY level
        ");
        ?>
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
                <div class="stat-item <?php echo esc_attr($stat->level); ?>">
                    <span class="stat-number"><?php echo esc_html($stat->count); ?></span>
                    <span class="stat-label"><?php echo esc_html(ucfirst($stat->level)); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Logs Table -->
    <?php if (!empty($logs)): ?>
        <table class="auto-nulis-table widefat">
            <thead>
                <tr>
                    <th width="120"><?php _e('Timestamp', 'auto-nulis'); ?></th>
                    <th width="80"><?php _e('Level', 'auto-nulis'); ?></th>
                    <th><?php _e('Message', 'auto-nulis'); ?></th>
                    <th width="100"><?php _e('Details', 'auto-nulis'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr class="log-row log-level-<?php echo esc_attr($log->level); ?>">
                        <td>
                            <time datetime="<?php echo esc_attr($log->timestamp); ?>">
                                <?php echo esc_html(date_i18n('M j, g:i a', strtotime($log->timestamp))); ?>
                            </time>
                        </td>
                        <td>
                            <span class="log-level <?php echo esc_attr($log->level); ?>">
                                <?php echo esc_html(ucfirst($log->level)); ?>
                            </span>
                        </td>
                        <td class="log-message">
                            <?php echo esc_html($log->message); ?>
                        </td>
                        <td>
                            <?php if (!empty($log->context)): ?>
                                <button type="button" class="button button-small toggle-context" data-context="<?php echo esc_attr($log->context); ?>">
                                    <?php _e('View', 'auto-nulis'); ?>
                                </button>
                            <?php else: ?>
                                <em><?php _e('None', 'auto-nulis'); ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($log->context)): ?>
                        <tr class="context-row" style="display: none;" data-log-id="<?php echo esc_attr($log->id); ?>">
                            <td colspan="4">
                                <div class="context-content">
                                    <strong><?php _e('Context:', 'auto-nulis'); ?></strong>
                                    <pre><?php echo esc_html(json_encode(json_decode($log->context), JSON_PRETTY_PRINT)); ?></pre>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="auto-nulis-pagination">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'total' => $total_pages,
                    'current' => $current_page,
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => __('&laquo; Previous', 'auto-nulis'),
                    'next_text' => __('Next &raquo;', 'auto-nulis'),
                    'type' => 'plain'
                );
                
                echo paginate_links($pagination_args);
                ?>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="auto-nulis-empty-state">
            <div class="empty-icon">
                <span class="dashicons dashicons-list-view"></span>
            </div>
            <h3><?php _e('No Logs Found', 'auto-nulis'); ?></h3>
            <p><?php _e('No activity logs match your current filters.', 'auto-nulis'); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Actions -->
    <div class="auto-nulis-actions">
        <form method="POST" action="" style="display: inline;">
            <?php wp_nonce_field('clear_logs'); ?>
            <input type="submit" name="clear_logs" class="button button-secondary" 
                   value="<?php esc_attr_e('Clear All Logs', 'auto-nulis'); ?>"
                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to clear all logs? This action cannot be undone.', 'auto-nulis'); ?>')">
        </form>
        
        <button type="button" id="refresh-logs" class="button">
            <?php _e('Refresh', 'auto-nulis'); ?>
        </button>
        
        <button type="button" id="export-logs" class="button">
            <?php _e('Export Logs', 'auto-nulis'); ?>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle context details
    $('.toggle-context').on('click', function() {
        var $button = $(this);
        var $row = $button.closest('tr');
        var $contextRow = $row.next('.context-row');
        
        if ($contextRow.is(':visible')) {
            $contextRow.hide();
            $button.text('<?php esc_js(_e('View', 'auto-nulis')); ?>');
        } else {
            $contextRow.show();
            $button.text('<?php esc_js(_e('Hide', 'auto-nulis')); ?>');
        }
    });
    
    // Refresh logs
    $('#refresh-logs').on('click', function() {
        location.reload();
    });
    
    // Export logs
    $('#export-logs').on('click', function() {
        var url = '<?php echo admin_url('admin-ajax.php'); ?>';
        var data = {
            action: 'auto_nulis_export_logs',
            nonce: '<?php echo wp_create_nonce('export_logs'); ?>',
            level: '<?php echo esc_js($level_filter); ?>',
            date: '<?php echo esc_js($date_filter); ?>'
        };
        
        // Create form and submit
        var form = $('<form>', {
            method: 'POST',
            action: url
        });
        
        $.each(data, function(key, value) {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: value
            }));
        });
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
});
</script>

<style>
.auto-nulis-logs-stats {
    margin-bottom: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stats-grid .stat-item {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    text-align: center;
}

.stats-grid .stat-item.success {
    border-left: 4px solid #4caf50;
}

.stats-grid .stat-item.info {
    border-left: 4px solid #2196f3;
}

.stats-grid .stat-item.warning {
    border-left: 4px solid #ff9800;
}

.stats-grid .stat-item.error {
    border-left: 4px solid #f44336;
}

.log-message {
    max-width: 400px;
    word-break: break-word;
}

.context-content {
    background: #f6f7f7;
    padding: 15px;
    border-radius: 4px;
    margin: 10px 0;
}

.context-content pre {
    background: #fff;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow-x: auto;
    font-size: 12px;
}

.log-row:hover {
    background: #f9f9f9;
}

.context-row {
    background: #f6f7f7 !important;
}

.context-row:hover {
    background: #f6f7f7 !important;
}
</style>
