<?php
/**
 * Writing Agent Uninstall
 * 
 * This file runs when the plugin is uninstalled (deleted).
 * It cleans up all plugin data from the database.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Remove plugin options
 */
delete_option('auto_nulis_settings');
delete_option('auto_nulis_used_keywords');
delete_option('auto_nulis_version');

/**
 * Remove scheduled events
 */
wp_clear_scheduled_hook('auto_nulis_generate_article');

/**
 * Remove custom database table
 */
global $wpdb;

$table_name = $wpdb->prefix . 'auto_nulis_logs';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

/**
 * Remove post meta for generated articles
 */
$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_auto_nulis_keyword'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_auto_nulis_generated'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_auto_nulis_version'
    )
);

$wpdb->delete(
    $wpdb->postmeta,
    array(
        'meta_key' => '_auto_nulis_attribution'
    )
);

/**
 * Clean up any remaining transients
 */
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_auto_nulis_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_auto_nulis_%'");

/**
 * Optional: Remove generated posts (uncomment if you want to delete all generated content)
 */
/*
$generated_posts = get_posts(array(
    'post_type' => 'post',
    'meta_key' => '_auto_nulis_generated',
    'posts_per_page' => -1,
    'post_status' => 'any'
));

foreach ($generated_posts as $post) {
    wp_delete_post($post->ID, true); // true = force delete
}
*/
