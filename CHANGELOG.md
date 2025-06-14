# Changelog - Writing Agent Plugin

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2025-06-14

### 🐛 Critical Bug Fixes

#### **Fixed Infinite Recursion Issue** ✅
- **Problem**: Article generation was stuck in infinite loops causing repeated log entries
- **Root Cause**: Method name inconsistency (`mark_keyword_as_used()` vs `mark_keyword_used()`)
- **Solution**: 
  - Fixed method name consistency in `class-auto-nulis-generator.php`
  - Added `get_next_unused_keyword()` method to prevent infinite loops
  - Enhanced duplicate detection logic
  - Improved keyword rotation system
  - Added comprehensive logging to track keyword usage

#### **Fixed Scheduler Daily Limits** ✅
- **Problem**: Scheduler continued running after daily limits were reached
- **Root Cause**: Missing daily limit checks in scheduled execution
- **Solution**:
  - Enhanced `execute_scheduled_generation()` with comprehensive daily limit checking
  - Added `clear_remaining_schedules_for_today()` functionality
  - Improved logging when daily limits are reached
  - Automatic cleanup of remaining scheduled events

#### **Fixed Enable/Disable Toggle Issue** ✅
- **Problem**: Toggle button reverted to ON after being set to OFF and saved
- **Root Cause**: Auto-save functionality interfering with checkbox processing
- **Solution**:
  - Fixed checkbox processing with strict value checking (`=== '1'`)
  - Added `data-no-auto-save="true"` attribute to prevent auto-save interference
  - Enhanced server-side protection against AJAX overrides
  - Improved debug logging for toggle state changes
  - Added server-side validation in main plugin file

#### **Fixed Enable/Disable Toggle Persistence** ✅
- **Problem**: Toggle returns to ON state after being set to OFF and saved
- **Root Cause**: Checkbox doesn't send value when unchecked, PHP receives nothing
- **Solution**:
  - Added hidden field with value="0" before checkbox
  - Enhanced PHP processing to handle array values (hidden + checkbox)
  - Added JavaScript form handler to manage field values properly
  - Enhanced visual feedback with CSS animations
  - Added debug tools to verify toggle state persistence

#### **Fixed Scheduled Generation Keywords Issue** ✅
- **Problem**: Scheduled generation failing with "No available keywords for article generation" error
- **Root Cause**: Settings not properly loaded during WordPress cron execution
- **Solution**:
  - Enhanced generator constructor with forced settings reload during cron
  - Improved `get_next_keyword()` method with comprehensive debug logging
  - Added better error messages distinguishing between "no keywords configured" vs "no keywords available"
  - Enhanced scheduler debugging with keyword count verification
  - Fixed settings context loading for cron jobs

#### **Fixed JavaScript Error Handling** ✅
- **Problem**: JavaScript errors on non-settings pages due to missing DOM elements
- **Root Cause**: Functions trying to access elements that don't exist on all admin pages
- **Solution**:
  - Added existence checks for DOM elements before accessing them
  - Added safeguards for AJAX object availability
  - Added page detection to only initialize on appropriate pages
  - Enhanced error handling with fallback values
  - Fixed undefined `.trim()` calls by checking value types

### ✨ Enhancements

#### **Code Organization**
- **Professional Repository Structure**: Organized code for GitHub publication
- **Documentation Folder**: Created `docs/` with comprehensive guides
- **Testing Suite**: Moved all debugging tools to `tests/` folder
- **Clean File Structure**: Separated utilities from core plugin files

#### **Debugging & Monitoring**
- **Comprehensive Debug Tools**: Added complete troubleshooting suite
- **Enhanced Logging**: Detailed activity tracking and error reporting
- **Verification Scripts**: Tools to verify all fixes are working correctly
- **Professional Error Messages**: Clear, actionable error descriptions

#### **Security Improvements**
- **Enhanced Input Validation**: Strengthened sanitization and validation
- **CSRF Protection**: Improved nonce verification
- **Settings Protection**: Server-side safeguards against unauthorized changes
- **API Security**: Enhanced API key protection and validation

### 🔧 Technical Improvements

#### **Generator Class (`class-auto-nulis-generator.php`)**
- Enhanced constructor with cron context detection and forced settings reload
- Improved `get_next_keyword()` with comprehensive debug logging and settings refresh
- Better error handling with detailed keyword analysis
- Fixed method name consistency (`mark_keyword_used()`)
- Added keyword rotation debugging

#### **Scheduler Class (`class-auto-nulis-scheduler.php`)**
- Enhanced `execute_scheduled_generation()` with keyword count logging
- Added automatic clearing of remaining scheduled events when limits reached
- Improved error categorization for keyword-related issues
- Fixed syntax errors and unmatched braces
- Enhanced daily limit enforcement

#### **Admin Class (`class-auto-nulis-admin.php`)**
- Fixed checkbox processing: Enhanced boolean validation
- Added extensive debug logging to `save_settings()` method
- Enhanced validation and verification of settings save process
- Improved settings state management

#### **Settings Page (`admin/settings-page.php`)**
- Added `data-no-auto-save="true"` attribute to enabled checkbox
- Replaced `wp_parse_args()` with manual array merging to preserve boolean values
- Enhanced form validation and user feedback

#### **Admin JavaScript (`admin/js/admin.js`)**
- Excluded enabled field from auto-save functionality
- Added check for `data-no-auto-save` attribute
- Improved user interface responsiveness

#### **Main Plugin File (`auto-nulis.php`)**
- Added server-side protection against auto-save modifying enabled setting
- Enhanced auto-save function to prevent critical setting overrides
- Improved plugin initialization and validation

#### **Fixed jQuery Loading Issues** ✅
- **Problem**: JavaScript errors "$  is not a function" in admin interface
- **Root Cause**: Improper jQuery wrapper and variable conflicts
- **Solution**:
  - Wrapped admin.js in proper IIFE with jQuery parameter
  - Replaced mixed $ and jQuery usage with consistent $ within IIFE
  - Fixed event handler binding for all admin functions
  - Enhanced error handling and debugging

#### **Enhanced Enable/Disable Toggle** ✅
- **Problem**: Toggle visual feedback and state management
- **Solution**:
  - Added dedicated event handler for enable/disable toggle
  - Enhanced visual feedback with CSS styling
  - Added informational messages for state changes
  - Prevented auto-save conflicts with data attributes

### 📁 File Organization
- **Moved to `tests/` folder**:
  - `debug-enable-disable.php`
  - `test-scheduled-keywords.php`
  - `scheduler-debug.php`
  - `clear-keywords.php`
  - `fix-issues.php`
  - `troubleshoot-db.php`
  - All debugging and verification scripts

- **Moved to `docs/` folder**:
  - `ISSUE-RESOLUTION-SUMMARY.md`
  - `ENABLE-DISABLE-FIX-COMPLETE.md`
  - `SCHEDULED-KEYWORDS-FIX.md`
  - `SCHEDULING-GUIDE.md`
  - `LANGUAGE-GUIDE.md`
  - `INSTALL-PRODUCTION.md`

### 🛠️ Testing & Quality Assurance
- **Complete Fix Verification**: All reported issues tested and resolved
- **Debug Scripts**: Comprehensive testing tools for all functionality
- **Error Tracking**: Enhanced logging for troubleshooting
- **Performance Monitoring**: Optimized for better resource usage

## [1.0.1] - 2025-06-11

### 🐛 Bug Fixes
- **Database Issues**
  - Fixed missing `wp_auto_nulis_logs` table creation during activation
  - Added automatic table verification and creation system
  - Enhanced error handling for database operations
  - Added table indexes for better performance

### ✨ New Features
- **Generate Now**
  - Added immediate article generation with custom parameters
  - Real-time progress indicator with animated progress bar
  - Custom keyword, length, and status selection
  - Recent generations history display

- **Multi-Language Support**
  - Added article language selection (20+ languages supported)
  - Language-specific prompt engineering for natural content
  - Default to Indonesian with options for English, Malay, Spanish, French, German, and more
  - Language preference in both settings and immediate generation

- **System Monitoring**
  - Added system status widget in settings sidebar
  - Manual table creation button for troubleshooting
  - Database health verification tools
  - Link to standalone troubleshooting script

- **Enhanced API Support**
  - Updated default model to Gemini 1.5 Flash (free version)
  - Added safety settings for content filtering
  - Increased API timeout to 120 seconds
  - Better error messages for API connection issues

### 🔧 Improvements
- **User Experience**
  - Enhanced admin interface with better status feedback
  - Improved error messages and troubleshooting guidance
  - Added tooltips and help text throughout interface
  - Better responsive design for mobile devices

- **Code Quality**
  - Added comprehensive error handling throughout codebase
  - Improved code documentation and comments
  - Enhanced security with better input validation
  - Optimized database queries with proper indexing

### 📋 Technical Changes
- **AJAX Handlers**
  - Added `auto_nulis_generate_immediate` for custom generation
  - Added `auto_nulis_create_tables` for manual table creation
  - Enhanced existing handlers with better error handling

- **Database**
  - Added self-healing table creation system
  - Enhanced `ensure_log_table_exists()` method
  - Added proper table indexes for performance
  - Better error logging and debugging

### 🛠️ Tools Added
- **Troubleshooting Script** (`troubleshoot-db.php`)
  - Standalone database diagnostic tool
  - Automatic table creation and verification
  - Database permission testing
  - WordPress environment analysis

## [1.0.0] - 2025-06-11

### 🎉 Initial Release

#### ✨ Core Features
- **AI-Powered Article Generation**
  - Google Gemini API support (gemini-pro, gemini-pro-vision)
  - OpenAI API support (GPT-3.5, GPT-4, GPT-4 Turbo)
  - Advanced prompt engineering for human-like content
  - SEO-optimized article structure with proper headings
  - Natural keyword integration and placement

- **Comprehensive Admin Interface**
  - Intuitive settings page with real-time API testing
  - Generated articles management dashboard
  - Activity logs with filtering and export capabilities
  - Statistics tracking and performance monitoring
  - User-friendly configuration wizard

- **Advanced Content Management**
  - Multiple article lengths (300-500, 500-800, 800-1200+ words)
  - Automatic slug generation with SEO best practices
  - Meta description generation
  - Tag suggestions based on content
  - Category assignment and management

- **Image Management System**
  - Automatic featured image integration
  - Unsplash API support with proper attribution
  - Pexels API support with proper attribution
  - WordPress Media Library integration
  - Image optimization and alt text generation

- **Scheduling & Automation**
  - Flexible article frequency (1-10 articles per day)
  - Custom time scheduling with WordPress cron
  - Intelligent keyword rotation system
  - Publication status control (Draft/Published/Pending)
  - Duplicate content prevention

#### 🔒 Security & Performance
- **Robust Security**
  - Secure API key storage with encryption
  - Input validation and sanitization
  - CSRF protection with nonces
  - Capability-based permissions
  - SQL injection prevention

- **Performance Optimization**
  - Efficient database queries with proper indexing
  - Background processing for article generation
  - Image compression and optimization
  - Memory management and resource limitation
  - Caching strategies for API responses

#### 🛠️ Technical Architecture
- **WordPress Integration**
  - WordPress Coding Standards compliance
  - Hook-based architecture for extensibility
  - Translation ready (text domain: auto-nulis)
  - Multisite compatibility
  - Native WordPress API utilization

- **Object-Oriented Design**
  - Modular class structure
  - Separation of concerns
  - Error handling with exceptions
  - Extensible hook system
  - Clean, maintainable codebase

#### 📱 User Experience
- **Responsive Interface**
  - Mobile-friendly admin dashboard
  - Modern CSS with flexbox/grid layouts
  - Accessible design principles
  - Interactive form elements
  - Real-time feedback and notifications

- **Advanced Configuration**
  - Provider selection (Gemini/OpenAI)
  - Model selection and customization
  - API key management with testing
  - Custom prompt options
  - Comprehensive settings validation

#### 📊 Monitoring & Analytics
- **Comprehensive Logging**
  - Detailed activity tracking
  - Error logging and debugging
  - Performance monitoring
  - API usage statistics
  - Success rate analysis

- **Statistics Dashboard**
  - Articles generated metrics
  - Daily/weekly/monthly reports
  - Keyword usage tracking
  - Performance indicators
  - Export functionality

#### 🌐 SEO & Content Optimization
- **SEO Features**
  - Natural keyword placement
  - Semantic keyword variations
  - Proper heading structure (H1, H2, H3)
  - Meta description generation
  - Schema markup ready

- **Content Quality**
  - Human-like writing style
  - Contextual content generation
  - Duplicate detection and prevention
  - Quality scoring algorithms
  - Content optimization suggestions

#### 🔧 Development Features
- **Debugging Tools**
  - Comprehensive debug mode
  - Error tracking and reporting
  - Performance profiling
  - API response monitoring
  - Database query optimization

- **Extensibility**
  - Hook system for customization
  - Filter system for content modification
  - Action hooks for integration
  - Custom post type support
  - Third-party plugin compatibility

#### 📚 Documentation & Support
- **Complete Documentation**
  - Installation and setup guide
  - Configuration instructions
  - Usage examples and best practices
  - Troubleshooting guide
  - API reference documentation

- **Code Documentation**
  - Inline comments throughout
  - Function and class documentation
  - Hook documentation
  - Example implementations
  - Development guidelines

### 📋 System Requirements
- **WordPress**: 5.0 or newer
- **PHP**: 7.4 or newer
- **MySQL**: 5.6 or newer
- **Memory**: Minimum 128MB PHP memory limit
- **API Access**: Google AI (Gemini) or OpenAI account

### 🎯 Target Users
- **Content Creators**: Bloggers and content marketers
- **Website Owners**: Businesses needing regular content
- **SEO Professionals**: Agencies managing multiple sites
- **Developers**: Those building content-driven applications
- **Publishers**: Media companies and news sites

### 🌟 Key Benefits
- **Time Saving**: Automate content creation process
- **Consistency**: Regular, high-quality content delivery
- **SEO Optimization**: Built-in SEO best practices
- **Cost Effective**: Reduce content creation costs
- **Scalability**: Handle multiple sites and high volume

### 🔄 Future Roadmap
- Enhanced AI model support
- Advanced content templates
- Multi-language content generation
- Social media integration
- Advanced analytics and reporting

---

## Version History Summary

| Version | Release Date | Key Features |
|---------|--------------|--------------|
| 1.0.2   | 2025-06-14  | Critical bug fixes, enhanced debugging, code organization |
| 1.0.1   | 2025-06-11  | Database fixes, multi-language support, immediate generation |
| 1.0.0   | 2025-06-11  | Initial release with full feature set |

---

**Plugin Information**
- **Name**: Writing Agent
- **Description**: Professional WordPress Plugin for AI-Powered Article Generation
- **Author**: Writing Agent Team
- **License**: GPL v2 or later
- **Text Domain**: auto-nulis
- **Requires WordPress**: 5.0+
- **Tested up to**: 6.4+
- **Requires PHP**: 7.4+
- **Stable tag**: 1.0.2

**Links**
- [GitHub Repository](https://github.com/your-username/writing-agent)
- [Documentation](docs/)
- [Support Forum](https://github.com/your-username/writing-agent/issues)
- [WordPress Plugin Directory](https://wordpress.org/plugins/writing-agent/)
