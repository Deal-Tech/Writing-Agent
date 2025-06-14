# 🤖 Writing Agent

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2+-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.2-orange.svg)](https://github.com/Deal-Tech/Writing-Agent)

**Professional WordPress Plugin for Automated AI-Powered Article Generation**

An advanced WordPress plugin that leverages cutting-edge AI technology (Google Gemini & OpenAI) to automatically generate high-quality, SEO-optimized articles with human-like writing quality.

## 🚀 Key Features

### ✨ **AI-Powered Content Generation**
- **Multi-AI Support**: Compatible with Google Gemini and OpenAI APIs
- **SEO-Optimized**: Articles structured for search engine optimization
- **Human-Like Quality**: Advanced prompt engineering for natural content
- **Multiple Lengths**: Support for short (300-500), medium (500-800), and long (800-1200+) articles
- **Keyword Integration**: Natural keyword placement for organic SEO

### 🎛️ **Comprehensive Admin Control**
- **Intuitive Dashboard**: Clean, user-friendly interface
- **Flexible Scheduling**: Configure generation frequency and timing
- **Keyword Management**: Easy bulk keyword input and management
- **Status Control**: Choose publication status (Published/Draft/Pending)
- **Real-time Monitoring**: Live activity logs and statistics

### 🖼️ **Advanced Image Management**
- **Multiple Sources**: Support for Unsplash, Pexels, and WordPress Media Library
- **Automatic Attribution**: Proper image credits and licensing
- **SEO-Friendly Alt Text**: Descriptive alt text for accessibility and SEO
- **Smart Image Selection**: Context-aware image matching

### 📊 **Professional Monitoring & Analytics**
- **Comprehensive Logging**: Detailed activity tracking
- **Statistics Dashboard**: Article generation metrics
- **Error Handling**: Advanced debugging and troubleshooting
- **Performance Monitoring**: API usage and generation time tracking

## 📋 Requirements

- **WordPress**: 5.0 or newer
- **PHP**: 7.4 or newer  
- **MySQL**: 5.6 or newer
- **API Key**: Google AI (Gemini) or OpenAI
- **Internet**: Stable connection required
- **Memory**: Minimum 128MB PHP memory limit recommended

## 🔧 Installation

### Method 1: WordPress Admin Upload

1. **Download Plugin**
   ```powershell
   # Download the latest release
-  git clone https://github.com/your-username/writing-agent.git
+  git clone https://github.com/Deal-Tech/Writing-Agent.git
   ```

2. **Prepare Plugin**
   ```powershell
   # Create ZIP file for upload
   Compress-Archive -Path "auto-nulis" -DestinationPath "writing-agent.zip"
   ```

3. **Upload via WordPress Admin**
   - Go to `Plugins > Add New > Upload Plugin`
   - Select the ZIP file and install
   - Activate the plugin

### Method 2: FTP/Direct Upload

1. **Upload Files**
   ```powershell
   # Upload the entire folder to:
   # /wp-content/plugins/auto-nulis/
   ```

2. **Set Permissions** (if needed)
   ```bash
   chmod 755 /wp-content/plugins/auto-nulis/
   chmod 644 /wp-content/plugins/auto-nulis/*.php
   ```

3. **Activate Plugin**
   - Go to WordPress Admin > Plugins
   - Find "Writing Agent" and click Activate

## ⚙️ Configuration

### 1. Get API Keys

#### Google AI (Gemini) - Recommended
1. Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Create a new API key
3. Copy the API key for plugin configuration

#### OpenAI (Alternative)
1. Visit [OpenAI Platform](https://platform.openai.com/api-keys)
2. Create a new API key
3. Copy the API key for plugin configuration

### 2. Plugin Setup

1. **Access Settings**
   - Navigate to `Writing Agent > Settings` in WordPress Admin

2. **Basic Configuration**
   - ✅ Enable the plugin
   - 🔑 Enter your API Key
   - 🤖 Select AI Provider (Gemini/OpenAI)
   - 🔧 Test API connection

3. **Article Settings**
   ```
   📊 Articles Per Day: 1-10
   ⏰ Schedule Time: 09:00 (example)
   📝 Keywords: (one per line)
        AI technology trends
        digital marketing strategies
        web development best practices
   📏 Article Length: Medium (500-800 words)
   📊 Post Status: Draft
   ```

4. **Image Configuration** (Optional)
   ```
   🖼️ Include Images: ✅ Enable
   🌐 Image Source: Unsplash API
   ```

5. **WordPress Integration**
   ```
   📁 Category: Select default category
   👤 Author: Choose article author
   ```

### 3. Image API Setup (Optional)

#### For Unsplash
1. Register at [Unsplash Developers](https://unsplash.com/developers)
2. Add to `wp-config.php`:
   ```php
   define('AUTO_NULIS_UNSPLASH_KEY', 'your_unsplash_access_key');
   ```

#### For Pexels
1. Register at [Pexels API](https://www.pexels.com/api/)
2. Add to `wp-config.php`:
   ```php
   define('AUTO_NULIS_PEXELS_KEY', 'your_pexels_api_key');
   ```

## 🚀 Usage

### Quick Start Guide

1. **Complete Configuration**
   - Ensure API key is set and tested
   - Add relevant keywords (one per line)
   - Configure article settings

2. **Enable Auto-Generation**
   - Toggle "Enable Auto Article Generation" to ON
   - Save settings
   - Plugin will start generating according to schedule

3. **Manual Generation**
   - Click "Generate Article Now" for immediate testing
   - Monitor in `Writing Agent > Generated Articles`

### Monitoring Your Content

1. **Generated Articles**
   - Navigate to `Writing Agent > Generated Articles`
   - Review, edit, and publish articles
   - Manage article status and categories

2. **Activity Logs**
   - Check `Writing Agent > Logs` for detailed activity
   - Monitor API usage and errors
   - Debug any generation issues

## 🎯 Best Practices

### Effective Keywords
```
✅ GOOD:
how to build responsive websites
digital marketing automation tools
SEO optimization techniques 2025
mobile app development trends

❌ AVOID:
website
marketing
SEO
development
```

### Optimization Tips

1. **Start Conservative**
   - Begin with 1 article per day
   - Use "Draft" status initially
   - Review generated content quality

2. **Quality Over Quantity**
   - Use specific, long-tail keywords
   - Focus on niche topics
   - Avoid overly broad subjects

3. **Regular Monitoring**
   - Check logs weekly
   - Review article quality
   - Adjust settings based on results

## 🔧 Troubleshooting

### Common Issues

#### ❌ API Connection Failed
**Problem**: Test API connection fails  
**Solutions**:
- ✅ Verify API key is correct
- ✅ Check internet connectivity
- ✅ Verify API quota/billing
- ✅ Try different AI provider

#### ❌ No Articles Generated
**Problem**: Scheduled generation not working  
**Solutions**:
- ✅ Ensure plugin is enabled
- ✅ Verify keywords are configured
- ✅ Check WordPress cron functionality
- ✅ Review activity logs for errors

#### ❌ Enable/Disable Toggle Issues
**Problem**: Settings not saving properly  
**Solutions**:
- ✅ Clear browser cache
- ✅ Disable conflicting plugins temporarily
- ✅ Check for JavaScript errors in browser console
- ✅ Verify WordPress nonce functionality

#### ❌ Scheduled Generation Failing
**Problem**: "No available keywords" error  
**Solutions**:
- ✅ Add more keywords to the list
- ✅ Reset keyword usage counters
- ✅ Check keyword rotation settings
- ✅ Monitor daily generation limits

#### ❌ Infinite Recursion Issues
**Problem**: Plugin generates duplicate content repeatedly  
**Solutions**:
- ✅ Plugin automatically prevents this with keyword rotation
- ✅ Check logs for duplicate detection
- ✅ Verify keyword usage tracking
- ✅ Reset generation counters if needed

### Debug Mode

Enable debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Debug logs will be saved to `/wp-content/debug.log`

## 🔒 Security

### API Key Protection
- API keys stored encrypted in database
- No API keys exposed in frontend
- NONCE validation for all actions
- Secure AJAX endpoints

### Content Safety
- Complete input sanitization
- API response validation
- Output escaping
- SQL injection prevention

## 📈 Performance

### Optimization Features
- Automatic image optimization
- API response caching
- Efficient database queries
- Background processing for generation
- Memory usage optimization

### Monitoring Capabilities
- API usage tracking
- Generation time monitoring
- Database performance logging
- Error rate analysis

## 🛠️ Development

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Testing
```powershell
# Run debugging scripts
php tests/debug-enable-disable.php
php tests/test-scheduled-keywords.php
```

### File Structure
```
auto-nulis/
├── auto-nulis.php          # Main plugin file
├── includes/               # Core classes
├── admin/                  # Admin interface
├── tests/                  # Debug & test scripts
├── docs/                   # Documentation
└── languages/              # Translation files
```

## 📚 Documentation

- [Installation Guide](docs/INSTALL-PRODUCTION.md)
- [Scheduling Guide](docs/SCHEDULING-GUIDE.md)
- [Language Support](docs/LANGUAGE-GUIDE.md)
- [Issue Resolution](docs/ISSUE-RESOLUTION-SUMMARY.md)

## 🆘 Support

### Self-Help Resources
1. Check logs at `Writing Agent > Logs`
2. Review settings configuration
3. Test API connection
4. Check WordPress debug logs
5. Review [troubleshooting documentation](docs/)

### Getting Help
- Open an issue on GitHub
- Check existing issues and discussions
- Review documentation thoroughly before asking

### Backup Recommendations
- Backup database before installation
- Regular site backups
- Export logs before clearing

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed release notes.

## 📄 License

This project is licensed under the GPL v2 or later - same as WordPress core.

See [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- WordPress community for the robust foundation
- Google AI and OpenAI for powerful API access
- Contributors and testers who helped improve the plugin
- Open source community for inspiration and tools

---

**Writing Agent v1.0.2** - Professional WordPress Plugin for AI-Powered Article Generation

Built with ❤️ for the WordPress community worldwide
