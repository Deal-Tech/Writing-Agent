<?php
/**
 * Auto Nulis Image Debug Functions
 * Tambahkan code ini ke functions.php tema atau plugin untuk debugging
 */

// Debug function untuk memeriksa image settings
function auto_nulis_debug_image_settings() {
    $settings = get_option('auto_nulis_settings', array());
    
    echo '<div style="background: #fff; border: 1px solid #ccc; padding: 20px; margin: 20px;">';
    echo '<h3>Auto Nulis Image Debug Info</h3>';
    
    echo '<h4>Settings:</h4>';
    echo '<ul>';
    echo '<li>Include Images: ' . (isset($settings['include_images']) && $settings['include_images'] ? 'Yes' : 'No') . '</li>';
    echo '<li>Image Source: ' . ($settings['image_source'] ?? 'Not set') . '</li>';
    echo '<li>Unsplash API Key: ' . (empty($settings['unsplash_api_key']) ? 'Not set' : 'Set (' . strlen($settings['unsplash_api_key']) . ' chars)') . '</li>';
    echo '<li>Pexels API Key: ' . (empty($settings['pexels_api_key']) ? 'Not set' : 'Set (' . strlen($settings['pexels_api_key']) . ' chars)') . '</li>';
    echo '</ul>';
    
    if (class_exists('Auto_Nulis_Image')) {
        echo '<h4>Testing Image Handler:</h4>';
        try {
            $image_handler = new Auto_Nulis_Image();
            $test_result = $image_handler->get_relevant_image('technology', $settings['image_source'] ?? 'unsplash');
            
            if ($test_result) {
                echo '<p style="color: green;">✅ Image found successfully!</p>';
                echo '<pre>' . print_r($test_result, true) . '</pre>';
            } else {
                echo '<p style="color: red;">❌ No image found. Check API key and settings.</p>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Error: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p style="color: red;">❌ Auto_Nulis_Image class not found</p>';
    }
    
    echo '</div>';
}

// Shortcode untuk menampilkan debug info
function auto_nulis_debug_shortcode() {
    ob_start();
    auto_nulis_debug_image_settings();
    return ob_get_clean();
}
add_shortcode('auto_nulis_debug', 'auto_nulis_debug_shortcode');

// Admin notice untuk debug (hanya untuk admin)
function auto_nulis_debug_admin_notice() {
    if (current_user_can('manage_options') && isset($_GET['auto_nulis_debug'])) {
        auto_nulis_debug_image_settings();
    }
}
add_action('admin_notices', 'auto_nulis_debug_admin_notice');

// Test function untuk generate artikel dengan debug
function auto_nulis_test_article_with_image() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_GET['test_auto_nulis_article'])) {
        echo '<div style="background: #fff; border: 1px solid #ccc; padding: 20px; margin: 20px;">';
        echo '<h3>Testing Article Generation with Image</h3>';
        
        if (class_exists('Auto_Nulis_Generator')) {
            try {
                $generator = new Auto_Nulis_Generator();
                $result = $generator->generate_article();
                
                echo '<h4>Generation Result:</h4>';
                echo '<pre>' . print_r($result, true) . '</pre>';
                
                if (isset($result['post_id'])) {
                    $post_id = $result['post_id'];
                    $featured_image_id = get_post_thumbnail_id($post_id);
                    
                    if ($featured_image_id) {
                        echo '<p style="color: green;">✅ Featured image set successfully!</p>';
                        echo '<p>Image ID: ' . $featured_image_id . '</p>';
                        echo '<p>Image URL: ' . wp_get_attachment_url($featured_image_id) . '</p>';
                    } else {
                        echo '<p style="color: red;">❌ No featured image set</p>';
                    }
                }
                
            } catch (Exception $e) {
                echo '<p style="color: red;">❌ Error: ' . $e->getMessage() . '</p>';
            }
        } else {
            echo '<p style="color: red;">❌ Auto_Nulis_Generator class not found</p>';
        }
        
        echo '</div>';
    }
}
add_action('admin_notices', 'auto_nulis_test_article_with_image');

/**
 * HOW TO USE:
 * 
 * 1. Debug Settings:
 *    Add ?auto_nulis_debug=1 to any admin page URL
 *    
 * 2. Test Article Generation:
 *    Add ?test_auto_nulis_article=1 to any admin page URL
 *    
 * 3. Use Shortcode in any post/page:
 *    [auto_nulis_debug]
 */
?>
