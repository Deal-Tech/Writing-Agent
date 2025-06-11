<?php
/**
 * Writing Agent Image Handler Class
 * Handles image fetching and processing
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Nulis_Image {
    
    private $unsplash_access_key = 'YOUR_UNSPLASH_ACCESS_KEY'; // Users need to add their own key
    private $pexels_api_key = 'YOUR_PEXELS_API_KEY'; // Users need to add their own key
    
    /**
     * Constructor
     */
    public function __construct() {
        // Keys should be configured in settings or wp-config.php
        if (defined('AUTO_NULIS_UNSPLASH_KEY')) {
            $this->unsplash_access_key = AUTO_NULIS_UNSPLASH_KEY;
        }
        
        if (defined('AUTO_NULIS_PEXELS_KEY')) {
            $this->pexels_api_key = AUTO_NULIS_PEXELS_KEY;
        }
    }
    
    /**
     * Get relevant image for keyword
     */
    public function get_relevant_image($keyword, $source = 'unsplash') {
        switch ($source) {
            case 'unsplash':
                return $this->get_unsplash_image($keyword);
            case 'pexels':
                return $this->get_pexels_image($keyword);
            case 'media_library':
                return $this->get_media_library_image($keyword);
            default:
                return false;
        }
    }
    
    /**
     * Get image from Unsplash
     */
    private function get_unsplash_image($keyword) {
        if (empty($this->unsplash_access_key) || $this->unsplash_access_key === 'YOUR_UNSPLASH_ACCESS_KEY') {
            return false;
        }
        
        $url = 'https://api.unsplash.com/search/photos';
        $params = array(
            'query' => $keyword,
            'per_page' => 1,
            'orientation' => 'landscape',
            'content_filter' => 'high'
        );
        
        $response = wp_remote_get($url . '?' . http_build_query($params), array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $this->unsplash_access_key
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['results'][0]['urls']['regular'])) {
            return array(
                'url' => $data['results'][0]['urls']['regular'],
                'alt_text' => $data['results'][0]['alt_description'] ?: $keyword,
                'attribution' => array(
                    'author' => $data['results'][0]['user']['name'],
                    'author_url' => $data['results'][0]['user']['links']['html'],
                    'source' => 'Unsplash',
                    'source_url' => $data['results'][0]['links']['html']
                )
            );
        }
        
        return false;
    }
    
    /**
     * Get image from Pexels
     */
    private function get_pexels_image($keyword) {
        if (empty($this->pexels_api_key) || $this->pexels_api_key === 'YOUR_PEXELS_API_KEY') {
            return false;
        }
        
        $url = 'https://api.pexels.com/v1/search';
        $params = array(
            'query' => $keyword,
            'per_page' => 1,
            'orientation' => 'landscape'
        );
        
        $response = wp_remote_get($url . '?' . http_build_query($params), array(
            'headers' => array(
                'Authorization' => $this->pexels_api_key
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['photos'][0]['src']['large'])) {
            return array(
                'url' => $data['photos'][0]['src']['large'],
                'alt_text' => $keyword,
                'attribution' => array(
                    'author' => $data['photos'][0]['photographer'],
                    'author_url' => $data['photos'][0]['photographer_url'],
                    'source' => 'Pexels',
                    'source_url' => $data['photos'][0]['url']
                )
            );
        }
        
        return false;
    }
    
    /**
     * Get image from WordPress Media Library
     */
    private function get_media_library_image($keyword) {
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 1,
            's' => $keyword,
            'orderby' => 'rand'
        ));
        
        if (!empty($attachments)) {
            $attachment = $attachments[0];
            return array(
                'url' => wp_get_attachment_url($attachment->ID),
                'attachment_id' => $attachment->ID,
                'alt_text' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ?: $keyword
            );
        }
        
        // If no specific match, get any random image
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => 1,
            'orderby' => 'rand'
        ));
        
        if (!empty($attachments)) {
            $attachment = $attachments[0];
            return array(
                'url' => wp_get_attachment_url($attachment->ID),
                'attachment_id' => $attachment->ID,
                'alt_text' => $keyword
            );
        }
        
        return false;
    }
    
    /**
     * Download and attach image to post
     */
    public function download_and_attach_image($image_data, $post_id, $keyword) {
        // If it's already a media library image
        if (isset($image_data['attachment_id'])) {
            return $image_data['attachment_id'];
        }
        
        if (!isset($image_data['url'])) {
            return false;
        }
        
        $image_url = $image_data['url'];
        $alt_text = isset($image_data['alt_text']) ? $image_data['alt_text'] : $keyword;
        
        // Download image
        $temp_file = download_url($image_url);
        
        if (is_wp_error($temp_file)) {
            return false;
        }
        
        // Get file info
        $file_array = array(
            'name' => $this->generate_filename($keyword, $image_url),
            'tmp_name' => $temp_file
        );
        
        // Import image to media library
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        // Clean up temp file
        @unlink($temp_file);
        
        if (is_wp_error($attachment_id)) {
            return false;
        }
        
        // Set alt text
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        
        // Add attribution if available
        if (isset($image_data['attribution'])) {
            update_post_meta($attachment_id, '_auto_nulis_attribution', $image_data['attribution']);
        }
        
        // Mark as auto-generated
        update_post_meta($attachment_id, '_auto_nulis_generated', true);
        
        return $attachment_id;
    }
    
    /**
     * Generate filename for downloaded image
     */
    private function generate_filename($keyword, $image_url) {
        $keyword_slug = sanitize_title($keyword);
        $extension = $this->get_image_extension($image_url);
        $timestamp = time();
        
        return "auto-nulis-{$keyword_slug}-{$timestamp}.{$extension}";
    }
    
    /**
     * Get image extension from URL
     */
    private function get_image_extension($url) {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        // Default to jpg if no extension found
        if (empty($extension) || !in_array(strtolower($extension), array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
            $extension = 'jpg';
        }
        
        return strtolower($extension);
    }
    
    /**
     * Add image attribution to post content
     */
    public function add_attribution_to_content($content, $post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return $content;
        }
        
        $attribution = get_post_meta($thumbnail_id, '_auto_nulis_attribution', true);
        
        if (!$attribution) {
            return $content;
        }
        
        $attribution_html = '<div class="auto-nulis-image-attribution" style="font-size: 12px; color: #666; margin-top: 10px;">';
        $attribution_html .= sprintf(
            __('Photo by <a href="%s" target="_blank" rel="noopener">%s</a> on <a href="%s" target="_blank" rel="noopener">%s</a>', 'auto-nulis'),
            esc_url($attribution['author_url']),
            esc_html($attribution['author']),
            esc_url($attribution['source_url']),
            esc_html($attribution['source'])
        );
        $attribution_html .= '</div>';
        
        return $content . $attribution_html;
    }
    
    /**
     * Optimize image for web
     */
    public function optimize_image($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        $image_info = getimagesize($file_path);
        
        if (!$image_info) {
            return false;
        }
        
        $mime_type = $image_info['mime'];
        $max_width = 1200;
        $max_height = 800;
        $quality = 85;
        
        // Only process if image is too large
        if ($image_info[0] <= $max_width && $image_info[1] <= $max_height) {
            return true;
        }
        
        // Load image based on type
        switch ($mime_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file_path);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        // Calculate new dimensions
        $ratio = min($max_width / $image_info[0], $max_height / $image_info[1]);
        $new_width = intval($image_info[0] * $ratio);
        $new_height = intval($image_info[1] * $ratio);
        
        // Create new image
        $new_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        if ($mime_type === 'image/png') {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
        }
        
        // Resize image
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $image_info[0], $image_info[1]);
        
        // Save optimized image
        switch ($mime_type) {
            case 'image/jpeg':
                imagejpeg($new_image, $file_path, $quality);
                break;
            case 'image/png':
                imagepng($new_image, $file_path, 9);
                break;
            case 'image/gif':
                imagegif($new_image, $file_path);
                break;
        }
        
        // Clean up
        imagedestroy($image);
        imagedestroy($new_image);
        
        // Update attachment metadata
        $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $metadata);
        
        return true;
    }
}

// Hook to add attribution to content
add_filter('the_content', function($content) {
    if (is_singular('post') && in_the_loop() && is_main_query()) {
        $image_handler = new Auto_Nulis_Image();
        return $image_handler->add_attribution_to_content($content, get_the_ID());
    }
    return $content;
});
