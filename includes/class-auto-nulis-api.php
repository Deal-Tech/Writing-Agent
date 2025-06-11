<?php
/**
 * Writing Agent API Class
 * Handles communication with AI providers (Gemini & OpenAI)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Auto_Nulis_API {
    
    private $api_key;
    private $provider;
    private $model;    /**
     * Constructor
     */
    public function __construct() {
        $settings = get_option('auto_nulis_settings', array());
        $this->api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        $this->provider = isset($settings['ai_provider']) ? $settings['ai_provider'] : 'gemini';
        $this->model = isset($settings['ai_model']) ? $settings['ai_model'] : 'gemini-pro';    }
      /**
     * Test API connection
     */
    public function test_connection($provider = null, $api_key = null, $model = null) {
        $provider = $provider ?: $this->provider;
        $api_key = $api_key ?: $this->api_key;
        $model = $model ?: $this->model;
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('API key is required', 'auto-nulis')
            );
        }
        
        // Simple test prompt for connection testing
        $test_prompt = "Write a single sentence about WordPress. Keep it under 20 words.";
        
        try {
            $response = $this->make_api_request($test_prompt, $provider, $api_key, $model);
            
            if ($response && !empty($response['content'])) {
                $content_length = strlen(trim($response['content']));
                
                return array(
                    'success' => true,
                    'message' => sprintf(
                        __('API connection successful! Generated %d characters.', 'auto-nulis'), 
                        $content_length
                    ),
                    'response' => wp_trim_words($response['content'], 15)
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('API returned empty response', 'auto-nulis')
                );
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
            
            // Provide helpful error messages for common issues
            if (strpos($error_message, 'API_KEY_INVALID') !== false) {
                $error_message = __('Invalid API key. Please check your API key is correct.', 'auto-nulis');
            } elseif (strpos($error_message, 'QUOTA_EXCEEDED') !== false) {
                $error_message = __('API quota exceeded. Please check your usage limits.', 'auto-nulis');
            } elseif (strpos($error_message, 'timeout') !== false) {
                $error_message = __('Connection timeout. Please try again.', 'auto-nulis');
            }
            
            return array(
                'success' => false,
                'message' => sprintf(__('Connection failed: %s', 'auto-nulis'), $error_message)
            );
        }
    }
    
    /**
     * Generate article content using AI
     */
    public function generate_article($keyword, $length = 'medium') {
        if (empty($this->api_key)) {
            throw new Exception(__('API key not configured', 'auto-nulis'));
        }
        
        $prompt = $this->build_article_prompt($keyword, $length);
        
        try {
            $response = $this->make_api_request($prompt, $this->provider, $this->api_key, $this->model);
            
            if ($response && !empty($response['content'])) {
                return $this->parse_article_response($response['content'], $keyword);
            } else {
                throw new Exception(__('Empty or invalid API response', 'auto-nulis'));
            }
        } catch (Exception $e) {
            $this->log_error('API Generation Error', $e->getMessage(), array('keyword' => $keyword));
            throw $e;
        }
    }
      /**
     * Build comprehensive prompt for article generation
     */
    private function build_article_prompt($keyword, $length) {
        $word_counts = array(
            'short' => '300-500',
            'medium' => '500-800',
            'long' => '800-1200+'
        );
        
        $word_count = isset($word_counts[$length]) ? $word_counts[$length] : '500-800';
        
        // Get language setting
        $settings = get_option('auto_nulis_settings', array());
        $language = isset($settings['article_language']) ? $settings['article_language'] : 'id';
        
        // Language instructions
        $language_instructions = $this->get_language_instructions($language);
        
        $prompt = "You are an expert content writer specializing in creating high-quality, SEO-optimized articles that are indistinguishable from human-written content.

LANGUAGE REQUIREMENT: {$language_instructions}

TASK: Write a comprehensive, informative article about '{$keyword}' that follows these strict requirements:

## CONTENT QUALITY REQUIREMENTS:
1. **Human-like Writing Style:**
   - Use natural, conversational tone that feels authentic
   - Vary sentence length and structure to avoid monotony
   - Include personal insights, opinions, and relatable examples
   - Use transitional phrases that flow naturally
   - Avoid AI-typical phrases like 'in conclusion', 'it's worth noting', 'furthermore'
   - Write as if sharing knowledge with a friend

2. **Uniqueness & Originality:**
   - Create 100% original content with fresh perspectives
   - Provide unique insights not commonly found elsewhere
   - Include specific examples, case studies, or personal anecdotes
   - Avoid generic statements and clichés
   - Bring new angles to the topic

3. **SEO Optimization:**
   - Naturally integrate the keyword '{$keyword}' throughout the article
   - Use the keyword in the title, first paragraph, and subheadings
   - Include related keywords and semantic variations
   - Optimize for search intent behind '{$keyword}'
   - Create compelling meta-friendly content

## STRUCTURE REQUIREMENTS:
1. **Title:** Create an engaging, click-worthy title that includes '{$keyword}' naturally
2. **Introduction:** Hook the reader immediately, introduce the topic, and promise value
3. **Main Content:** Use H2 and H3 subheadings to organize content logically
4. **Conclusion:** Summarize key points and provide actionable takeaways

## TECHNICAL SPECIFICATIONS:
- **Word Count:** {$word_count} words
- **Tone:** Professional yet approachable
- **Reading Level:** Accessible to general audience
- **Format:** WordPress-ready HTML with proper heading tags

## STRICT FORMATTING:
Return ONLY a JSON object with this exact structure:
{
  \"title\": \"Your engaging article title here\",
  \"content\": \"<h2>Introduction</h2><p>Your article content with proper HTML formatting...</p>\",
  \"meta_description\": \"Compelling 155-character meta description\",
  \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"]
}

## CONTENT FOCUS:
Write about '{$keyword}' as if you're an expert with years of experience. Provide genuine value, practical advice, and insights that readers can't find elsewhere. Make it engaging, informative, and actionable.

Remember: The goal is to create content so natural and valuable that readers will want to share it and search engines will rank it highly.";

        return $prompt;
    }
    
    /**
     * Get language instructions for the prompt
     */
    private function get_language_instructions($language) {
        $language_map = array(
            'id' => 'Write the ENTIRE article in Indonesian (Bahasa Indonesia). Use natural Indonesian language, proper grammar, and culturally appropriate expressions.',
            'en' => 'Write the ENTIRE article in English. Use clear, natural English with proper grammar and vocabulary.',
            'ms' => 'Write the ENTIRE article in Malay (Bahasa Melayu). Use natural Malay language with proper grammar and Malaysian/Indonesian context.',
            'es' => 'Write the ENTIRE article in Spanish (Español). Use natural Spanish language with proper grammar and vocabulary.',
            'fr' => 'Write the ENTIRE article in French (Français). Use natural French language with proper grammar and vocabulary.',
            'de' => 'Write the ENTIRE article in German (Deutsch). Use natural German language with proper grammar and vocabulary.',
            'pt' => 'Write the ENTIRE article in Portuguese (Português). Use natural Portuguese language with proper grammar and vocabulary.',
            'it' => 'Write the ENTIRE article in Italian (Italiano). Use natural Italian language with proper grammar and vocabulary.',
            'nl' => 'Write the ENTIRE article in Dutch (Nederlands). Use natural Dutch language with proper grammar and vocabulary.',
            'ru' => 'Write the ENTIRE article in Russian (Русский). Use natural Russian language with proper grammar and vocabulary.',
            'ja' => 'Write the ENTIRE article in Japanese (日本語). Use natural Japanese language with proper grammar, kanji, hiragana, and katakana.',
            'ko' => 'Write the ENTIRE article in Korean (한국어). Use natural Korean language with proper grammar and vocabulary.',
            'zh' => 'Write the ENTIRE article in Chinese (中文). Use natural Chinese language with proper grammar and vocabulary.',
            'ar' => 'Write the ENTIRE article in Arabic (العربية). Use natural Arabic language with proper grammar and vocabulary.',
            'hi' => 'Write the ENTIRE article in Hindi (हिन्दी). Use natural Hindi language with proper grammar and vocabulary.',
            'th' => 'Write the ENTIRE article in Thai (ไทย). Use natural Thai language with proper grammar and vocabulary.',
            'vi' => 'Write the ENTIRE article in Vietnamese (Tiếng Việt). Use natural Vietnamese language with proper grammar and vocabulary.',
            'tr' => 'Write the ENTIRE article in Turkish (Türkçe). Use natural Turkish language with proper grammar and vocabulary.',
            'pl' => 'Write the ENTIRE article in Polish (Polski). Use natural Polish language with proper grammar and vocabulary.',
            'sv' => 'Write the ENTIRE article in Swedish (Svenska). Use natural Swedish language with proper grammar and vocabulary.'
        );
        
        return isset($language_map[$language]) ? $language_map[$language] : $language_map['id'];
    }
    
    /**
     * Make API request based on provider
     */
    private function make_api_request($prompt, $provider, $api_key, $model) {
        switch ($provider) {
            case 'gemini':
                return $this->make_gemini_request($prompt, $api_key, $model);
            case 'openai':
                return $this->make_openai_request($prompt, $api_key, $model);
            default:
                throw new Exception(__('Invalid AI provider', 'auto-nulis'));
        }
    }
      /**
     * Make request to Google Gemini API (Free Version)
     */
    private function make_gemini_request($prompt, $api_key, $model) {
        // Use free Gemini API endpoint
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
        
        $data = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt)
                    )
                )
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.8,
                'maxOutputTokens' => 8192, // Increased for free version
                'candidateCount' => 1
            ),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                )
            )
        );        
        $response = wp_remote_post($url, array(
            'body' => json_encode($data),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 120, // Increased timeout for free API
            'user-agent' => 'WordPress/Auto-Nulis-Plugin'
        ));
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        // Better error handling for Gemini API
        if ($response_code !== 200) {
            $error_data = json_decode($body, true);
            if (isset($error_data['error']['message'])) {
                throw new Exception('Gemini API Error: ' . $error_data['error']['message']);
            } else {
                throw new Exception('Gemini API Error: HTTP ' . $response_code);
            }
        }
        
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception('Gemini API Error: ' . $data['error']['message']);
        }
        
        // Handle safety filters and blocked content
        if (!isset($data['candidates']) || empty($data['candidates'])) {
            throw new Exception('No response generated. Content may have been filtered by safety settings.');
        }
        
        if (isset($data['candidates'][0]['finishReason']) && $data['candidates'][0]['finishReason'] !== 'STOP') {
            $reason = $data['candidates'][0]['finishReason'];
            throw new Exception("Content generation stopped due to: " . $reason);
        }
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid response format from Gemini API');
        }
        
        return array(
            'content' => $data['candidates'][0]['content']['parts'][0]['text']
        );
    }
    
    /**
     * Make request to OpenAI API
     */
    private function make_openai_request($prompt, $api_key, $model) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 2048
        );
        
        $response = wp_remote_post($url, array(
            'body' => json_encode($data),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 60
        ));
        
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['error'])) {
            throw new Exception($data['error']['message']);
        }
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception(__('Invalid response format from OpenAI API', 'auto-nulis'));
        }
        
        return array(
            'content' => $data['choices'][0]['message']['content']
        );
    }
    
    /**
     * Parse AI response and extract article components
     */
    private function parse_article_response($response, $keyword) {
        // Try to extract JSON from response
        $json_start = strpos($response, '{');
        $json_end = strrpos($response, '}');
        
        if ($json_start !== false && $json_end !== false) {
            $json_content = substr($response, $json_start, $json_end - $json_start + 1);
            $parsed = json_decode($json_content, true);
            
            if ($parsed && isset($parsed['title']) && isset($parsed['content'])) {
                return array(
                    'title' => sanitize_text_field($parsed['title']),
                    'content' => wp_kses_post($parsed['content']),
                    'meta_description' => isset($parsed['meta_description']) ? sanitize_text_field($parsed['meta_description']) : '',
                    'tags' => isset($parsed['tags']) ? array_map('sanitize_text_field', $parsed['tags']) : array()
                );
            }
        }
        
        // Fallback: parse as plain text
        $lines = explode("\n", trim($response));
        $title = '';
        $content = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (empty($title) && !empty($line)) {
                $title = strip_tags($line);
            } else {
                $content .= $line . "\n";
            }
        }
        
        // If no title found, generate one
        if (empty($title)) {
            $title = sprintf(__('Complete Guide to %s', 'auto-nulis'), ucfirst($keyword));
        }
        
        return array(
            'title' => $title,
            'content' => wpautop($content),
            'meta_description' => wp_trim_words(strip_tags($content), 20),
            'tags' => array($keyword)
        );
    }
      /**
     * Log error
     */
    private function log_error($level, $message, $context = array()) {
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
    }
}
