<?php
/**
 * Writing Agent Configuration Example
 * Add these lines to your wp-config.php file
 */

// Google AI (Gemini) API Key
// Get your key from: https://makersuite.google.com/app/apikey
define('AUTO_NULIS_GEMINI_API_KEY', 'your_gemini_api_key_here');

// OpenAI API Key
// Get your key from: https://platform.openai.com/api-keys
define('AUTO_NULIS_OPENAI_API_KEY', 'your_openai_api_key_here');

// Unsplash API Key (Optional - for images)
// Get your key from: https://unsplash.com/developers
define('AUTO_NULIS_UNSPLASH_KEY', 'your_unsplash_access_key_here');

// Pexels API Key (Optional - for images)
// Get your key from: https://www.pexels.com/api/
define('AUTO_NULIS_PEXELS_KEY', 'your_pexels_api_key_here');

// Plugin Debug Mode (Optional)
// Set to true for debugging
define('AUTO_NULIS_DEBUG', false);

// Max articles per day limit (Optional)
// Default is 10, you can increase if needed
define('AUTO_NULIS_MAX_DAILY_ARTICLES', 10);

// Custom prompt suffix (Optional)
// Add custom instructions to AI prompts
define('AUTO_NULIS_CUSTOM_PROMPT_SUFFIX', 'Always write in Indonesian language.');

/**
 * SECURITY NOTES:
 * 
 * 1. Never commit API keys to version control
 * 2. Use environment variables in production
 * 3. Regularly rotate your API keys
 * 4. Monitor API usage and quotas
 * 
 * PRODUCTION EXAMPLE:
 * define('AUTO_NULIS_GEMINI_API_KEY', $_ENV['GEMINI_API_KEY']);
 */
