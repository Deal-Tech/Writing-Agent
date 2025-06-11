/**
 * Auto Nulis Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin interface
    AutoNulisAdmin.init();
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
        $(document).on('change', 'input[name="include_images"]', this.toggleImageSource);
        
        // Form validation
        $(document).on('submit', 'form', this.validateForm);
        
        // Create tables button
        $(document).on('click', '#create-tables', this.createTables);
        
        // Auto-save functionality
        $(document).on('change input', '.auto-save', this.autoSave);
    },
    
    /**
     * Initialize toggle fields
     */
    initToggleFields: function() {
        // Show/hide image source field based on include images checkbox
        this.toggleImageSource();
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
        var provider = $('#ai_provider').val();
        var apiKey = $('#api_key').val();
        var model = $('#ai_model').val();
        
        if (!apiKey.trim()) {
            AutoNulisAdmin.showApiResult('error', autoNulisAjax.strings.error + ' API key is required.');
            return;
        }
        
        // Show loading state
        $button.prop('disabled', true);
        $result.removeClass('success error').addClass('loading').show();
        $result.html('<span class="auto-nulis-loading"></span>' + autoNulisAjax.strings.testing);
        
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
                if (response.success) {
                    AutoNulisAdmin.showApiResult('success', autoNulisAjax.strings.success);
                } else {
                    AutoNulisAdmin.showApiResult('error', autoNulisAjax.strings.error + ' ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                AutoNulisAdmin.showApiResult('error', autoNulisAjax.strings.error + ' ' + error);
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
            alert('Please enable the plugin first before generating articles.');
            return;
        }
        
        // Check if API key is set
        if (!$('#api_key').val().trim()) {
            alert('Please configure your API key first.');
            return;
        }
        
        // Check if keywords are set
        if (!$('#keywords').val().trim()) {
            alert('Please add some keywords first.');
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
    },
    
    /**
     * Validate form before submission
     */
    validateForm: function(e) {
        var isValid = true;
        var errors = [];
        
        // Check if enabled but no API key
        if ($('input[name="enabled"]').is(':checked')) {
            if (!$('#api_key').val().trim()) {
                errors.push('API key is required when plugin is enabled.');
                AutoNulisAdmin.markFieldError('#api_key');
                isValid = false;
            }
            
            if (!$('#keywords').val().trim()) {
                errors.push('Keywords are required when plugin is enabled.');
                AutoNulisAdmin.markFieldError('#keywords');
                isValid = false;
            }
        }
        
        // Validate articles per day
        var articlesPerDay = parseInt($('#articles_per_day').val());
        if (articlesPerDay < 1 || articlesPerDay > 10) {
            errors.push('Articles per day must be between 1 and 10.');
            AutoNulisAdmin.markFieldError('#articles_per_day');
            isValid = false;
        }
        
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
        // Update progress bars based on current stats
        this.updateProgressBars();
        
        // Set up periodic updates
        setInterval(this.updateProgressBars, 30000); // Every 30 seconds
    },
    
    /**
     * Update progress bars
     */
    updateProgressBars: function() {
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
        // Add keyword counter
        this.updateKeywordCounter();
        $('#keywords').on('input', this.updateKeywordCounter);
        
        // Add keyword validation
        $('#keywords').on('blur', this.validateKeywords);
    },
    
    /**
     * Update keyword counter
     */
    updateKeywordCounter: function() {
        var keywords = $('#keywords').val().trim();
        var count = keywords ? keywords.split('\n').filter(function(k) { return k.trim(); }).length : 0;
        
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
        var keywords = $(this).val().trim();
        var lines = keywords.split('\n');
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
    }
};

// Initialize when DOM is ready
$(document).ready(function() {
    AutoNulisAdmin.initProgressTracking();
    AutoNulisAdmin.initKeywordManagement();
});
