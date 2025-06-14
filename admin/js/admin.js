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
        
        // Generate article now
        $(document).on('click', '#generate-now', this.generateArticleNow);
        
        // AI provider change
        $(document).on('change', '#ai_provider', this.onProviderChange);
        
        // Include images toggle
        $(document).on('change', 'input[name="include_images"]', this.toggleImageSource);        // Form validation (only for settings form)
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
    },    /**
     * Validate form before submission
     */
    validateForm: function(e) {
        var $form = $(e.target);
        
        // Only validate settings form, not other forms like log filters
        if (!$form.find('#articles_per_day').length) {
            return true; // Skip validation for non-settings forms (like logs filter)
        }
        
        var isValid = true;
        var errors = [];
        
        // Only validate required fields for warnings, but allow form submission
        // This allows users to enable the plugin and configure it step by step
        var warnings = [];
        
        if ($('input[name="enabled"]').is(':checked')) {
            if (!$('#api_key').val().trim()) {
                warnings.push('API key should be configured for article generation to work.');
            }
            
            if (!$('#keywords').val().trim()) {
                warnings.push('Keywords should be added for article generation to work.');
            }
        }
        
        // Validate articles per day field only if it exists
        var $articlesField = $('#articles_per_day');
        if ($articlesField.length > 0) {
            var articlesPerDay = parseInt($articlesField.val());
            if (isNaN(articlesPerDay) || articlesPerDay < 1 || articlesPerDay > 10) {
                errors.push('Articles per day must be between 1 and 10.');
                AutoNulisAdmin.markFieldError('#articles_per_day');
                isValid = false;
            } else {
                // Remove error state if value is valid
                $articlesField.closest('.auto-nulis-field').removeClass('has-error');
                $articlesField.closest('.auto-nulis-field').find('.field-error').remove();
            }
        }
        
        // Show warnings but don't prevent form submission
        if (warnings.length > 0) {
            AutoNulisAdmin.showNotice('warning', 'Configuration recommendations:<br>' + warnings.join('<br>'));
        }
        
        // Only prevent submission for critical errors
        if (!isValid) {
            e.preventDefault();
            AutoNulisAdmin.showNotice('error', 'Please fix the following errors:<br>' + errors.join('<br>'));
        }
        
        return isValid;
    },
    
    /**
     * Mark field as having error
     */
    markFieldError: function(selector) {
        $(selector).closest('.auto-nulis-field').addClass('has-error');
          // Remove error state on focus
        $(selector).one('focus', function() {
            $(this).closest('.auto-nulis-field').removeClass('has-error');
        });
    },

    /**
     * Auto-save functionality
     */
    autoSave: function() {
        var $field = $(this);
        var fieldName = $field.attr('name');
        var fieldValue = $field.val();
        
        // Skip auto-save for certain fields that should only be saved via form submission
        if ($field.data('no-auto-save') || fieldName === 'enabled' || $field.attr('type') === 'checkbox') {
            return;
        }
        
        // Show saving indicator
        AutoNulisAdmin.showSavingIndicator($field);
        
        // Debounce the save
        clearTimeout($field.data('autoSaveTimeout'));
        $field.data('autoSaveTimeout', setTimeout(function() {
            AutoNulisAdmin.performAutoSave(fieldName, fieldValue);
        }, 1000));
    },
    
    /**
     * Show saving indicator
     */
    showSavingIndicator: function($field) {
        var $indicator = $field.siblings('.auto-save-indicator');
        if ($indicator.length === 0) {
            $indicator = $('<span class="auto-save-indicator">Saving...</span>');
            $field.after($indicator);
        }
        $indicator.show();
    },
    
    /**
     * Perform auto-save
     */
    performAutoSave: function(fieldName, fieldValue) {
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
                $('.auto-save-indicator').hide();
                if (response.success) {
                    AutoNulisAdmin.showTempMessage('Saved', 'success');
                }
            },
            error: function() {
                $('.auto-save-indicator').hide();
                AutoNulisAdmin.showTempMessage('Save failed', 'error');
            }
        });
    },
    
    /**
     * Show temporary message
     */
    showTempMessage: function(message, type) {
        var $message = $('<div class="auto-nulis-temp-message ' + type + '">' + message + '</div>');
        $('body').append($message);
        
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 2000);
    },
    
    /**
     * Show admin notice
     */
    showNotice: function(type, message) {
        var $notice = $('<div class="auto-nulis-notice ' + type + '">' + message + '</div>');
        $('.auto-nulis-admin h1').after($notice);
        
        // Auto-hide success notices
        if (type === 'success') {
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        }
        
        // Scroll to notice
        $('html, body').animate({
            scrollTop: $notice.offset().top - 100
        }, 500);
    },
      /**
     * Initialize progress tracking
     */
    initProgressTracking: function() {
        // Only initialize if we have the necessary AJAX data
        if (typeof autoNulisAjax === 'undefined') {
            return; // Exit if AJAX object not available
        }
        
        // Update progress bars based on current stats
        this.updateProgressBars();
        
        // Set up periodic updates
        setInterval(this.updateProgressBars, 30000); // Every 30 seconds
    },
    
    /**
     * Update progress bars
     */
    updateProgressBars: function() {
        // Check if AJAX object is available
        if (typeof autoNulisAjax === 'undefined') {
            return;
        }
        
        $.ajax({
            url: autoNulisAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'auto_nulis_get_stats',
                nonce: autoNulisAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    AutoNulisAdmin.updateStatsDisplay(response.data);
                }
            },
            error: function() {
                // Silently fail if AJAX request fails
            }
        });
    },
    
    /**
     * Update stats display
     */
    updateStatsDisplay: function(stats) {
        $('.stat-item').each(function() {
            var $item = $(this);
            var statType = $item.data('stat-type');
            
            if (stats[statType] !== undefined) {
                $item.find('.stat-number').text(stats[statType]);
            }
        });
    },
      /**
     * Initialize keyword management
     */
    initKeywordManagement: function() {
        // Only initialize if keywords field exists (on settings page)
        if ($('#keywords').length > 0) {
            // Add keyword counter
            this.updateKeywordCounter();
            $('#keywords').on('input', this.updateKeywordCounter);
            
            // Add keyword validation
            $('#keywords').on('blur', this.validateKeywords);
        }
    },
    
    /**
     * Update keyword counter
     */
    updateKeywordCounter: function() {
        var $keywordsField = $('#keywords');
        if ($keywordsField.length === 0) {
            return; // Exit if keywords field doesn't exist
        }
        
        var keywords = $keywordsField.val();
        if (typeof keywords !== 'string') {
            keywords = '';
        }
        
        var trimmedKeywords = keywords.trim();
        var count = trimmedKeywords ? trimmedKeywords.split('\n').filter(function(k) { return k.trim(); }).length : 0;
        
        var $counter = $('#keyword-counter');
        if ($counter.length === 0) {
            $counter = $('<div id="keyword-counter" class="description"></div>');
            $('#keywords').after($counter);
        }
        
        $counter.text(count + ' keywords entered');
    },
      /**
     * Validate keywords
     */
    validateKeywords: function() {
        var $field = $(this);
        var keywords = $field.val();
        
        if (typeof keywords !== 'string') {
            return; // Exit if no valid keywords
        }
        
        var trimmedKeywords = keywords.trim();
        if (!trimmedKeywords) {
            return; // Exit if empty
        }
        
        var lines = trimmedKeywords.split('\n');
        var duplicates = [];
        var seen = {};
        
        lines.forEach(function(line, index) {
            var keyword = line.trim().toLowerCase();
            if (keyword && seen[keyword]) {
                duplicates.push(keyword);
            }
            seen[keyword] = true;
        });
        
        if (duplicates.length > 0) {
            AutoNulisAdmin.showNotice('warning', 'Duplicate keywords found: ' + duplicates.join(', '));
        }
    },
    
    /**
     * Create database tables
     */
    createTables: function(e) {
        e.preventDefault();
        
        var $button = $(this);
        
        if (!confirm('Are you sure you want to create the database tables?')) {
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true).text('Creating...');
        
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
                    AutoNulisAdmin.showNotice('success', 'Database tables created successfully!');
                    // Reload page to update status
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    AutoNulisAdmin.showNotice('error', 'Failed to create tables: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showNotice('error', 'Connection error: ' + error);
            },
            complete: function() {
                $button.prop('disabled', false).text('Create Tables');
            }
        });
    },
    
    /**
     * Debug form state and validation
     */    debugForm: function(e) {
        e.preventDefault();
        
        console.log('=== Auto Nulis Form Debug ===');
        
        var $enabledCheckbox = $('input[name="enabled"][type="checkbox"]');
        var $enabledHidden = $('input[name="enabled"][type="hidden"]');
        
        var formData = {
            enabled_checkbox: $enabledCheckbox.is(':checked'),
            enabled_hidden_value: $enabledHidden.val(),
            api_key: $('#api_key').val(),
            keywords: $('#keywords').val(),
            articles_per_day: $('#articles_per_day').val()
        };
        
        console.log('Form Data:', formData);
        console.log('Enabled checkbox element:', $enabledCheckbox[0]);
        console.log('Enabled hidden element:', $enabledHidden[0]);
        
        // Determine final enabled value (same logic as form submission)
        var finalEnabledValue = $enabledCheckbox.is(':checked');
        
        var debugInfo = 'Form Debug Information:\n\n' +
                       'Plugin Enabled (Checkbox): ' + (formData.enabled_checkbox ? 'YES' : 'NO') + '\n' +
                       'Hidden Field Value: ' + formData.enabled_hidden_value + '\n' +
                       'Final Enabled Value: ' + (finalEnabledValue ? 'YES' : 'NO') + '\n' +
                       'API Key Set: ' + (formData.api_key.trim() ? 'YES' : 'NO') + '\n' +
                       'Keywords Set: ' + (formData.keywords.trim() ? 'YES' : 'NO') + '\n' +
                       'Articles Per Day: ' + formData.articles_per_day + '\n\n' +
                       'Check the browser console for detailed form data.';
        
        AutoNulisAdmin.showNotice('info', debugInfo.replace(/\n/g, '<br>'));
    },    /**
     * Handle form submission
     */
    handleFormSubmit: function(e) {
        var $form = $(this);
        
        // Only handle settings form submission, not other forms like log filters
        if (!$form.find('input[name="enabled"]').length) {
            return true; // Skip handling for non-settings forms
        }
        
        // Ensure enabled checkbox value is properly set
        var $enabledCheckbox = $form.find('input[name="enabled"][type="checkbox"]');
        var $enabledHidden = $form.find('input[name="enabled"][type="hidden"]');
        
        if (typeof console !== 'undefined') {
            console.log('Form submission - Checkbox checked:', $enabledCheckbox.is(':checked'));
            console.log('Form submission - Hidden value:', $enabledHidden.val());
        }
        
        if ($enabledCheckbox.length > 0 && $enabledHidden.length > 0) {
            if ($enabledCheckbox.is(':checked')) {
                // When checkbox is checked, remove hidden field so only checkbox value (1) is sent
                $enabledHidden.remove();
                if (typeof console !== 'undefined') {
                    console.log('Checkbox checked - removed hidden field');
                }
            } else {
                // When checkbox is unchecked, ensure hidden field value is 0
                $enabledHidden.val('0');
                if (typeof console !== 'undefined') {
                    console.log('Checkbox unchecked - set hidden field to 0');
                }
            }
        }
        
        // Log what will be submitted (only in debug mode)
        if (typeof console !== 'undefined') {
            var formData = new FormData($form[0]);
            console.log('Form data being submitted:');
            for (var pair of formData.entries()) {
                if (pair[0] === 'enabled') {
                    console.log('  enabled:', pair[1]);
                }
            }
        }
        
        // Continue with form submission
        return true;    }

};

})(jQuery);
