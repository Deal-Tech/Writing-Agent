/**
 * Auto Nulis Admin JavaScript
 */

;(function($) {
    'use strict';    // Initialize all components on DOM ready
    $(document).ready(function(){
        // Check if we're on the right page before initializing
        if ($('.auto-nulis-admin').length > 0 || $('#auto-nulis-page').length > 0) {
            AutoNulisAdmin.init();
            AutoNulisAdmin.initProgressTracking();
            AutoNulisAdmin.initKeywordManagement();
        }
    });

var AutoNulisAdmin = {
    
    /**
     * Initialize admin functions
     */
    init: function() {
        this.bindEvents();
        this.initToggleFields();
        this.initTooltips();
    },
    
    /**
     * Bind event handlers
     */
    bindEvents: function() {
        // Test API connection
        $(document).on('click', '#test-api-connection', this.testApiConnection);
        
        // Test image API connections
        $(document).on('click', '#test-unsplash-api', this.testUnsplashApi);
        $(document).on('click', '#test-pexels-api', this.testPexelsApi);
        
        // Generate article now
        $(document).on('click', '#generate-now', this.generateArticleNow);
        
        // AI provider change
        $(document).on('change', '#ai_provider', this.onProviderChange);
        
        // Include images toggle
        $(document).on('change', 'input[name="include_images"]', this.toggleImageSource);
        
        // Image source change
        $(document).on('change', '#image_source', this.toggleImageApiFields);        // Form validation (only for settings form)
        $(document).on('submit', '.auto-nulis-admin form', this.validateForm);
        
        // Real-time validation for articles per day (only on settings page)
        $(document).on('input blur', '#articles_per_day', function() {
            // Only run validation if we're on the settings page
            if ($(this).closest('form').find('input[name="enabled"]').length === 0) {
                return; // Skip if not on settings form
            }
            
            var value = parseInt($(this).val());
            var $field = $(this).closest('.auto-nulis-field');
            
            // Clear any existing errors first
            $field.removeClass('has-error');
            $field.find('.field-error').remove();
            
            if ($(this).val() === '' || isNaN(value) || value < 1 || value > 10) {
                $field.addClass('has-error');
                // Show inline error message
                if ($field.find('.field-error').length === 0) {
                    $field.append('<p class="field-error" style="color: #d63638; font-size: 12px; margin-top: 5px;">Articles per day must be between 1 and 10.</p>');
                }
            }
        });
          // Create tables button
        $(document).on('click', '#create-tables', this.createTables);
        
        // Debug form button
        $(document).on('click', '#debug-form', this.debugForm);
        
        // Auto-save functionality
        $(document).on('change input', '.auto-save', this.autoSave);        // Form submission handler
        $(document).on('submit', '.auto-nulis-admin form', this.handleFormSubmit);
    },
      /**
     * Initialize toggle fields
     */
    initToggleFields: function() {
        // Show/hide image source field based on include images checkbox
        this.toggleImageSource();
        
        // Initialize enable toggle state
        this.initEnableToggleState();
    },
      /**
     * Initialize enable toggle state
     */
    initEnableToggleState: function() {
        var $enableToggle = $('input[name="enabled"][type="checkbox"]');
        if ($enableToggle.length > 0) {
            var isEnabled = $enableToggle.is(':checked');
            var $field = $enableToggle.closest('.auto-nulis-field');
            var $toggleElement = $enableToggle.closest('.auto-nulis-toggle');
            
            // Set initial state
            if (isEnabled) {
                $field.addClass('enabled');
                $toggleElement.addClass('active');
            } else {
                $field.removeClass('enabled');
                $toggleElement.removeClass('active');
            }
              // Update hidden field to match
            var $hiddenField = $('input[name="enabled"][type="hidden"]');
            if ($hiddenField.length > 0) {
                $hiddenField.val(isEnabled ? '1' : '0');
            }
        }
    },
    
    /**
     * Initialize tooltips
     */
    initTooltips: function() {
        $('.auto-nulis-tooltip').each(function() {
            $(this).attr('title', $(this).data('tooltip'));
        });
    },
      /**
     * Test API connection
     */
    testApiConnection: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#api-test-result');
        var $providerField = $('#ai_provider');
        var $apiKeyField = $('#api_key');
        var $modelField = $('#ai_model');
        
        // Check if required fields exist
        if ($providerField.length === 0 || $apiKeyField.length === 0) {
            AutoNulisAdmin.showApiResult('error', 'Required form fields not found');
            return;
        }
        
        var provider = $providerField.val();
        var apiKey = $apiKeyField.val();
        var model = $modelField.length > 0 ? $modelField.val() : '';
        
        if (!apiKey || !apiKey.trim()) {
            AutoNulisAdmin.showApiResult('error', 'API key is required');
            return;
        }
        
        // Check if AJAX object is available
        if (typeof autoNulisAjax === 'undefined') {
            AutoNulisAdmin.showApiResult('error', 'AJAX configuration not available');
            return;
        }
          // Show loading state
        $button.prop('disabled', true);
        $result.removeClass('success error').addClass('loading').show();
        
        var loadingText = (autoNulisAjax.strings && autoNulisAjax.strings.testing) ? 
                         autoNulisAjax.strings.testing : 'Testing connection...';
        $result.html('<span class="auto-nulis-loading"></span>' + loadingText);
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_test_api',
                provider: provider,
                api_key: apiKey,
                model: model,
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                var successText = (autoNulisAjax.strings && autoNulisAjax.strings.success) ? 
                                 autoNulisAjax.strings.success : 'Connection successful!';
                var errorText = (autoNulisAjax.strings && autoNulisAjax.strings.error) ? 
                               autoNulisAjax.strings.error : 'Connection failed!';
                               
                if (response.success) {
                    AutoNulisAdmin.showApiResult('success', successText);
                } else {
                    AutoNulisAdmin.showApiResult('error', errorText + ' ' + (response.message || ''));
                }
            },
            error: function(xhr, status, error) {
                var errorText = (autoNulisAjax.strings && autoNulisAjax.strings.error) ? 
                               autoNulisAjax.strings.error : 'Connection failed!';
                AutoNulisAdmin.showApiResult('error', errorText + ' ' + error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    },
    
    /**
     * Show API test result
     */
    showApiResult: function(type, message) {
        var $result = $('#api-test-result');
        $result.removeClass('loading success error').addClass(type).html(message).show();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $result.fadeOut();
        }, 5000);
    },
      /**
     * Generate article now
     */
    generateArticleNow: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Check if plugin is enabled
        if (!$('input[name="enabled"]').is(':checked')) {
            AutoNulisAdmin.showNotice('error', 'Please enable the plugin first before generating articles.');
            return;
        }
        
        // Check if API key is set
        if (!$('#api_key').val().trim()) {
            AutoNulisAdmin.showNotice('error', 'Please configure your API key first in the settings above.');
            return;
        }
        
        // Check if keywords are set
        if (!$('#keywords').val().trim()) {
            AutoNulisAdmin.showNotice('error', 'Please add some keywords first in the settings above.');
            return;
        }
        
        if (!confirm('Are you sure you want to generate an article now? This will use your API quota.')) {
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true).html('<span class="auto-nulis-loading"></span>Generating...');
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_generate_now',
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    AutoNulisAdmin.showNotice('success', 'Article generated successfully! ' + 
                        '<a href="/wp-admin/post.php?post=' + response.post_id + '&action=edit" target="_blank">Edit Article</a>');
                } else {
                    AutoNulisAdmin.showNotice('error', 'Failed to generate article: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showNotice('error', 'Generation failed: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    },
    
    /**
     * Handle AI provider change
     */
    onProviderChange: function() {
        var provider = $(this).val();
        var $modelSelect = $('#ai_model');
        
        // Clear existing options
        $modelSelect.empty();
        
        // Add new options based on provider
        var models = AutoNulisAdmin.getModelsForProvider(provider);
        $.each(models, function(value, label) {
            $modelSelect.append(new Option(label, value));
        });
    },
      /**
     * Get models for AI provider
     */
    getModelsForProvider: function(provider) {
        var models = {};
        
        switch (provider) {
            case 'gemini':
                models = {
                    'gemini-1.5-flash': 'Gemini 1.5 Flash (Fast & Free)',
                    'gemini-1.5-pro': 'Gemini 1.5 Pro (Advanced)',
                    'gemini-pro': 'Gemini Pro (Legacy)'
                };
                break;
            case 'openai':
                models = {
                    'gpt-3.5-turbo': 'GPT-3.5 Turbo',
                    'gpt-4': 'GPT-4',
                    'gpt-4-turbo': 'GPT-4 Turbo'
                };
                break;
        }
        
        return models;
    },
    
    /**
     * Toggle image source field
     */
    toggleImageSource: function() {
        var includeImages = $('input[name="include_images"]').is(':checked');
        $('.image-source-field').toggle(includeImages);
        $('.image-api-field').toggle(includeImages);
        
        // Also trigger the API fields toggle
        AutoNulisAdmin.toggleImageApiFields();
    },
    
    /**
     * Toggle image API fields based on source selection
     */
    toggleImageApiFields: function() {
        var includeImages = $('input[name="include_images"]').is(':checked');
        var imageSource = $('#image_source').val();
        
        // Hide all API fields first
        $('.unsplash-api-field, .pexels-api-field').hide();
        
        // Show appropriate API field only if images are enabled
        if (includeImages && imageSource !== 'media_library') {
            $('.image-api-field').show();
            
            if (imageSource === 'unsplash') {
                $('.unsplash-api-field').show();
            } else if (imageSource === 'pexels') {
                $('.pexels-api-field').show();
            }
        } else {
            $('.image-api-field').hide();
        }
    },
    
    /**
     * Test Unsplash API connection
     */
    testUnsplashApi: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#unsplash-api-test-result');
        var $apiKeyField = $('#unsplash_api_key');
        
        var apiKey = $apiKeyField.val();
        
        if (!apiKey || !apiKey.trim()) {
            AutoNulisAdmin.showImageApiResult($result, 'error', 'Unsplash API key is required');
            return;
        }
        
        // Check if AJAX object is available
        if (typeof autoNulisAjax === 'undefined') {
            AutoNulisAdmin.showImageApiResult($result, 'error', 'AJAX configuration not available');
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true);
        $result.removeClass('success error').addClass('loading').show();
        $result.html('<span class="auto-nulis-loading"></span>Testing Unsplash API...');
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_test_unsplash_api',
                api_key: apiKey,
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var message = response.data.message;
                    if (response.data.data && response.data.data.rate_limit) {
                        message += ' (Rate limit remaining: ' + response.data.data.rate_limit + ')';
                    }
                    AutoNulisAdmin.showImageApiResult($result, 'success', message);
                } else {
                    AutoNulisAdmin.showImageApiResult($result, 'error', response.data.message || 'Connection failed');
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showImageApiResult($result, 'error', 'Connection failed: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    },
    
    /**
     * Test Pexels API connection
     */
    testPexelsApi: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#pexels-api-test-result');
        var $apiKeyField = $('#pexels_api_key');
        
        var apiKey = $apiKeyField.val();
        
        if (!apiKey || !apiKey.trim()) {
            AutoNulisAdmin.showImageApiResult($result, 'error', 'Pexels API key is required');
            return;
        }
        
        // Check if AJAX object is available
        if (typeof autoNulisAjax === 'undefined') {
            AutoNulisAdmin.showImageApiResult($result, 'error', 'AJAX configuration not available');
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true);
        $result.removeClass('success error').addClass('loading').show();
        $result.html('<span class="auto-nulis-loading"></span>Testing Pexels API...');
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_test_pexels_api',
                api_key: apiKey,
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var message = response.data.message;
                    if (response.data.data && response.data.data.rate_limit) {
                        message += ' (Rate limit remaining: ' + response.data.data.rate_limit + ')';
                    }
                    AutoNulisAdmin.showImageApiResult($result, 'success', message);
                } else {
                    AutoNulisAdmin.showImageApiResult($result, 'error', response.data.message || 'Connection failed');
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showImageApiResult($result, 'error', 'Connection failed: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    },
    
    /**
     * Show image API test result
     */
    showImageApiResult: function($result, type, message) {
        $result.removeClass('loading success error').addClass(type).html(message).show();
        
        // Auto-hide after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(function() {
                $result.fadeOut();
            }, 5000);
        }
    },
    
    /**
     * Create tables
     */
    createTables: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Show loading state
        $button.prop('disabled', true).text('Creating tables...');
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_create_tables',
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    AutoNulisAdmin.showNotice('success', 'Tables created successfully!');
                } else {
                    AutoNulisAdmin.showNotice('error', 'Failed to create tables: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showNotice('error', 'Table creation failed: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    },
    
    /**
     * Debug form
     */
    debugForm: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var originalText = $button.text();
        
        // Show loading state
        $button.prop('disabled', true).text('Debugging...');
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_debug_form',
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    AutoNulisAdmin.showNotice('success', 'Debug information sent successfully!');
                } else {
                    AutoNulisAdmin.showNotice('error', 'Failed to send debug information: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showNotice('error', 'Debugging failed: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    },
    
    /**
     * Auto-save settings
     */
    autoSave: function() {
        var $field = $(this);
        var fieldName = $field.attr('name');
        var fieldValue = $field.is(':checkbox') ? ($field.is(':checked') ? 1 : 0) : $field.val();
        
        // Make AJAX request
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_auto_save',
                field: fieldName,
                value: fieldValue,
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Optionally show a success message or update UI
                } else {
                    // Handle error
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX error
            }
        });
    },
    
    /**
     * Show notice
     */
    showNotice: function(type, message) {
        var $notice = $('#auto-nulis-notice');
        $notice.removeClass('success error').addClass(type).html(message).show();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    }
};

// Expose AutoNulisAdmin to global scope
window.AutoNulisAdmin = AutoNulisAdmin;

})(jQuery);