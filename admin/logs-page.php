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
$search_filter = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
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

if (!empty($search_filter)) {
    $where_conditions[] = '(message LIKE %s OR context LIKE %s)';
    $search_term = '%' . $wpdb->esc_like($search_filter) . '%';
    $where_values[] = $search_term;
    $where_values[] = $search_term;
}

$where_clause = implode(' AND ', $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_clause";
if (!empty($where_values)) {
    $total_logs = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
} else {
    $total_logs = $wpdb->get_var($count_query);
}

// Debug: Log query if debug is enabled
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Auto Nulis Logs Query: ' . $count_query);
    error_log('Auto Nulis Logs Where Values: ' . print_r($where_values, true));
    error_log('Auto Nulis Logs Total Found: ' . $total_logs);
}

// Get logs
$logs_query = "SELECT * FROM $table_name WHERE $where_clause ORDER BY timestamp DESC LIMIT %d OFFSET %d";

if (!empty($where_values)) {
    // Prepare the query with where values + limit and offset
    $query_values = array_merge($where_values, array($per_page, $offset));
    $logs = $wpdb->get_results($wpdb->prepare($logs_query, $query_values));
} else {
    // No where conditions, just prepare with limit and offset
    $logs = $wpdb->get_results($wpdb->prepare($logs_query, $per_page, $offset));
}

$total_pages = ceil($total_logs / $per_page);
?>

<div class="wrap auto-nulis-admin">
    <h1><?php _e('Activity Logs', 'auto-nulis'); ?></h1>
      <?php if (!empty($level_filter) || !empty($date_filter) || !empty($search_filter)): ?>
        <div class="notice notice-info">
            <p>
                <?php _e('Filters applied:', 'auto-nulis'); ?>
                <?php if (!empty($level_filter)): ?>
                    <strong><?php _e('Level:', 'auto-nulis'); ?></strong> <?php echo esc_html(ucfirst($level_filter)); ?>
                <?php endif; ?>
                <?php if (!empty($level_filter) && (!empty($date_filter) || !empty($search_filter))): ?> | <?php endif; ?>
                <?php if (!empty($date_filter)): ?>
                    <strong><?php _e('Date:', 'auto-nulis'); ?></strong> <?php echo esc_html($date_filter); ?>
                <?php endif; ?>
                <?php if (!empty($date_filter) && !empty($search_filter)): ?> | <?php endif; ?>
                <?php if (!empty($search_filter)): ?>
                    <strong><?php _e('Search:', 'auto-nulis'); ?></strong> "<?php echo esc_html($search_filter); ?>"
                <?php endif; ?>
                &nbsp;
                <a href="<?php echo esc_url(admin_url('admin.php?page=auto-nulis-logs')); ?>" class="button button-small">
                    <?php _e('Clear Filters', 'auto-nulis'); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="auto-nulis-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="auto-nulis-logs">
              <div class="auto-nulis-grid" style="grid-template-columns: 200px 200px 300px auto;">
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
                
                <div class="auto-nulis-field">
                    <label for="search"><?php _e('Search', 'auto-nulis'); ?></label>
                    <input type="text" id="search" name="search" value="<?php echo esc_attr($search_filter); ?>" placeholder="<?php esc_attr_e('Search in messages...', 'auto-nulis'); ?>">
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
    });    // Auto-submit filter form when values change
    $('#level, #date').on('change', function() {
        var $form = $(this).closest('form');
        
        // Only auto-submit for select and date changes, not search input
        if ($(this).is('select') || $(this).attr('type') === 'date') {
            // Show loading indicator
            var $button = $form.find('input[type="submit"]');
            var originalText = $button.val();
            $button.val('<?php esc_js(_e('Filtering...', 'auto-nulis')); ?>').prop('disabled', true);
            
            // Submit form
            $form.submit();
        }
    });
    
    // For search input, submit on Enter key or after a short delay
    var searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        var $form = $(this).closest('form');
        
        // Clear any previous timeout
        searchTimeout = setTimeout(function() {
            // Only submit if search has some content or is being cleared
            var searchValue = $('#search').val().trim();
            if (searchValue.length >= 2 || searchValue.length === 0) {
                $form.submit();
            }
        }, 1000); // Wait 1 second after user stops typing
    });
    
    $('#search').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            clearTimeout(searchTimeout);
            $(this).closest('form').submit();
        }
    });
    
    // Export logs
    $('#export-logs').on('click', function() {
        var url = '<?php echo admin_url('admin-ajax.php'); ?>';        var data = {
            action: 'auto_nulis_export_logs',
            nonce: '<?php echo wp_create_nonce('export_logs'); ?>',
            level: '<?php echo esc_js($level_filter); ?>',
            date: '<?php echo esc_js($date_filter); ?>',
            search: '<?php echo esc_js($search_filter); ?>'
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

/* Filter improvements */
.auto-nulis-section form {
    background: #fff;
    padding: 20px;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    margin-bottom: 20px;
}

.auto-nulis-grid {
    display: grid;
    gap: 15px;
    align-items: end;
}

.auto-nulis-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.auto-nulis-field select,
.auto-nulis-field input[type="date"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    font-size: 14px;
}

.auto-nulis-field select:focus,
.auto-nulis-field input[type="date"]:focus {
    border-color: #2271b1;
    outline: none;
    box-shadow: 0 0 0 1px #2271b1;
}

/* Loading state for filter */
.filtering {
    opacity: 0.6;
    pointer-events: none;
}
</style>
