#!/usr/bin/env php
<?php
/**
 * Auto Nulis Plugin Installer
 * Quick setup script for Auto Nulis WordPress plugin
 */

echo "\n=== Auto Nulis Plugin Installer ===\n";
echo "Setting up your Auto Nulis plugin...\n\n";

// Check if running in WordPress directory
if (!file_exists('wp-config.php') && !file_exists('../wp-config.php')) {
    echo "❌ Error: This script must be run from your WordPress root directory.\n";
    echo "Please navigate to your WordPress installation folder and try again.\n\n";
    exit(1);
}

// Determine WordPress root
$wp_root = file_exists('wp-config.php') ? '.' : '..';
$plugins_dir = $wp_root . '/wp-content/plugins';

echo "✅ WordPress installation detected at: " . realpath($wp_root) . "\n";

// Check if plugins directory exists
if (!is_dir($plugins_dir)) {
    echo "❌ Error: Plugins directory not found at {$plugins_dir}\n";
    exit(1);
}

// Check if Auto Nulis plugin directory already exists
$plugin_dir = $plugins_dir . '/auto-nulis';
if (is_dir($plugin_dir)) {
    echo "⚠️  Warning: Auto Nulis plugin directory already exists.\n";
    echo "Do you want to overwrite it? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) !== 'y') {
        echo "Installation cancelled.\n";
        exit(0);
    }
    
    // Remove existing directory
    if (DIRECTORY_SEPARATOR === '\\') {
        // Windows
        exec("rmdir /s /q \"$plugin_dir\"");
    } else {
        // Unix/Linux
        exec("rm -rf \"$plugin_dir\"");
    }
}

// Copy plugin files
echo "📁 Creating plugin directory...\n";
if (!mkdir($plugin_dir, 0755, true)) {
    echo "❌ Error: Could not create plugin directory.\n";
    exit(1);
}

// Copy all files from current directory to plugin directory
$current_dir = __DIR__;
$files_to_copy = [
    'auto-nulis.php',
    'uninstall.php',
    'README.md',
    'CHANGELOG.md',
    'wp-config-example.php',
    'package.json'
];

$dirs_to_copy = [
    'admin',
    'includes',
    'languages'
];

echo "📄 Copying plugin files...\n";

// Copy individual files
foreach ($files_to_copy as $file) {
    if (file_exists("$current_dir/$file")) {
        if (!copy("$current_dir/$file", "$plugin_dir/$file")) {
            echo "❌ Error: Could not copy $file\n";
            exit(1);
        }
        echo "   ✓ $file\n";
    }
}

// Copy directories recursively
foreach ($dirs_to_copy as $dir) {
    if (is_dir("$current_dir/$dir")) {
        if (!copyDirectory("$current_dir/$dir", "$plugin_dir/$dir")) {
            echo "❌ Error: Could not copy directory $dir\n";
            exit(1);
        }
        echo "   ✓ $dir/\n";
    }
}

echo "\n✅ Plugin files copied successfully!\n\n";

// Create .htaccess for security
$htaccess_content = "# Auto Nulis Security\n";
$htaccess_content .= "# Deny access to PHP files in includes directory\n";
$htaccess_content .= "<Files \"*.php\">\n";
$htaccess_content .= "Order allow,deny\n";
$htaccess_content .= "Deny from all\n";
$htaccess_content .= "</Files>\n";

file_put_contents("$plugin_dir/includes/.htaccess", $htaccess_content);
echo "🔒 Security files created.\n\n";

// Check for wp-config.php to add configuration
$wp_config_path = $wp_root . '/wp-config.php';
if (is_writable($wp_config_path)) {
    echo "📝 Do you want to add API key configuration to wp-config.php? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) === 'y') {
        addApiKeysToConfig($wp_config_path);
    }
}

echo "\n🎉 Installation completed successfully!\n\n";
echo "Next steps:\n";
echo "1. Log in to your WordPress admin panel\n";
echo "2. Go to Plugins page and activate 'Auto Nulis'\n";
echo "3. Navigate to Auto Nulis > Settings\n";
echo "4. Configure your API keys and settings\n";
echo "5. Add your keywords and enable auto-generation\n\n";

echo "📚 For detailed setup instructions, see README.md\n";
echo "🐛 For issues and support, check CHANGELOG.md\n\n";

echo "Happy content generating! 🚀\n\n";

/**
 * Copy directory recursively
 */
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!$dir) return false;
    
    @mkdir($dst, 0755, true);
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir("$src/$file")) {
                copyDirectory("$src/$file", "$dst/$file");
            } else {
                copy("$src/$file", "$dst/$file");
            }
        }
    }
    
    closedir($dir);
    return true;
}

/**
 * Add API key configuration to wp-config.php
 */
function addApiKeysToConfig($wp_config_path) {
    $config_content = file_get_contents($wp_config_path);
    
    // Check if our constants already exist
    if (strpos($config_content, 'AUTO_NULIS_GEMINI_API_KEY') !== false) {
        echo "⚠️  API key constants already exist in wp-config.php\n";
        return;
    }
    
    $api_config = "\n\n// Auto Nulis Plugin Configuration\n";
    $api_config .= "// Add your AI API keys here\n";
    $api_config .= "define('AUTO_NULIS_GEMINI_API_KEY', 'your_gemini_api_key_here');\n";
    $api_config .= "define('AUTO_NULIS_OPENAI_API_KEY', 'your_openai_api_key_here');\n";
    $api_config .= "define('AUTO_NULIS_UNSPLASH_KEY', 'your_unsplash_key_here');\n";
    $api_config .= "define('AUTO_NULIS_PEXELS_KEY', 'your_pexels_key_here');\n";
    $api_config .= "// define('AUTO_NULIS_DEBUG', true); // Uncomment for debugging\n";
    
    // Insert before the "That's all" comment
    $config_content = str_replace(
        "/* That's all,",
        $api_config . "\n/* That's all,",
        $config_content
    );
    
    if (file_put_contents($wp_config_path, $config_content)) {
        echo "✅ API key configuration added to wp-config.php\n";
        echo "   Remember to replace 'your_*_api_key_here' with actual API keys!\n";
    } else {
        echo "❌ Could not write to wp-config.php\n";
    }
}
