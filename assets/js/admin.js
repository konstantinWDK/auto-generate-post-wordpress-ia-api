/**
 * Admin JavaScript for Auto Post Generator
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Tab functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var target = $this.attr('href').split('tab=')[1];
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active');
        
        // Show/hide tab content
        $('.apg-tab-content').removeClass('active');
        $('.apg-tab-content[data-tab="' + target + '"]').addClass('active');
        
        // Update URL without reload
        if (history.pushState) {
            history.pushState(null, null, $this.attr('href'));
        }
    });
    
    // Initialize active tab from URL
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab') || 'general';
    $('.nav-tab[href*="tab=' + activeTab + '"]').addClass('nav-tab-active');
    $('.apg-tab-content[data-tab="' + activeTab + '"]').addClass('active');
    
    // Form submission with loading state
    $('form').on('submit', function() {
        var $form = $(this);
        var $submitBtn = $form.find('input[type="submit"]');
        
        if ($submitBtn.length) {
            $submitBtn.prop('disabled', true);
            $submitBtn.val(autoPostGenerator.strings.generating);
            $form.addClass('apg-loading');
        }
    });
    
    // Ideas generation with AJAX
    $('#generate-ideas-btn').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var $form = $btn.closest('form');
        var formData = $form.serialize();
        
        // Show loading state
        $btn.prop('disabled', true);
        $btn.html('<span class="apg-spinner"></span>' + autoPostGenerator.strings.generating);
        
        // Make AJAX request
        $.ajax({
            url: autoPostGenerator.ajaxurl,
            type: 'POST',
            data: {
                action: 'generate_post_ideas',
                nonce: autoPostGenerator.nonce,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showMessage(response.data.message, 'success');
                    
                    // Reset form
                    $form[0].reset();
                    
                    // Refresh ideas list if present
                    refreshIdeasList();
                } else {
                    showMessage(response.data.message || autoPostGenerator.strings.error, 'error');
                }
            },
            error: function() {
                showMessage(autoPostGenerator.strings.error, 'error');
            },
            complete: function() {
                // Reset button state
                $btn.prop('disabled', false);
                $btn.html('🚀 Generar y Guardar Ideas');
            }
        });
    });
    
    // Delete idea confirmation
    $(document).on('click', '.delete-idea', function(e) {
        var confirmDelete = confirm(autoPostGenerator.strings.confirm_delete);
        if (!confirmDelete) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-save form fields
    $('.auto-save').on('change', function() {
        var $field = $(this);
        var fieldName = $field.attr('name');
        var fieldValue = $field.val();
        
        $.ajax({
            url: autoPostGenerator.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_setting',
                nonce: autoPostGenerator.nonce,
                setting: fieldName,
                value: fieldValue
            },
            success: function(response) {
                if (response.success) {
                    $field.addClass('saved');
                    setTimeout(function() {
                        $field.removeClass('saved');
                    }, 2000);
                }
            }
        });
    });
    
    // Character counter for text inputs
    $('input[maxlength], textarea[maxlength]').each(function() {
        var $input = $(this);
        var maxLength = $input.attr('maxlength');
        var $counter = $('<div class="char-counter"></div>');
        
        $input.after($counter);
        
        function updateCounter() {
            var remaining = maxLength - $input.val().length;
            $counter.text(remaining + ' characters remaining');
            
            if (remaining < 10) {
                $counter.addClass('warning');
            } else {
                $counter.removeClass('warning');
            }
        }
        
        $input.on('input', updateCounter);
        updateCounter();
    });
    
    // Collapsible sections
    $('.apg-collapsible-trigger').on('click', function() {
        var $trigger = $(this);
        var $content = $trigger.next('.apg-collapsible-content');
        
        $content.slideToggle();
        $trigger.toggleClass('collapsed');
    });
    
    // Copy to clipboard functionality
    $('.copy-to-clipboard').on('click', function() {
        var $btn = $(this);
        var targetSelector = $btn.data('target');
        var $target = $(targetSelector);
        
        if ($target.length) {
            $target.select();
            document.execCommand('copy');
            
            // Show feedback
            var originalText = $btn.text();
            $btn.text('Copied!');
            setTimeout(function() {
                $btn.text(originalText);
            }, 2000);
        }
    });
    
    // Tooltip functionality
    $('.apg-tooltip').hover(
        function() {
            $(this).find('.tooltiptext').fadeIn();
        },
        function() {
            $(this).find('.tooltiptext').fadeOut();
        }
    );
    
    // Progress bar animation
    function animateProgressBar($bar, targetPercent) {
        var $fill = $bar.find('.apg-progress-fill');
        $fill.animate({
            width: targetPercent + '%'
        }, 1000);
    }
    
    // Initialize progress bars
    $('.apg-progress-bar').each(function() {
        var $bar = $(this);
        var percent = $bar.data('percent') || 0;
        setTimeout(function() {
            animateProgressBar($bar, percent);
        }, 500);
    });
    
    // Refresh ideas list
    function refreshIdeasList() {
        var $ideasList = $('#recent-ideas-list');
        if ($ideasList.length) {
            $.ajax({
                url: autoPostGenerator.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_recent_ideas',
                    nonce: autoPostGenerator.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $ideasList.html(response.data.html);
                    }
                }
            });
        }
    }
    
    // Show message helper
    function showMessage(message, type) {
        var $message = $('<div class="apg-message ' + type + '">' + message + '</div>');
        
        // Find the best place to insert the message
        var $target = $('.wrap h1').first();
        if ($target.length) {
            $target.after($message);
        } else {
            $('body').prepend($message);
        }
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $message.remove();
            });
        }, 5000);
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $message.offset().top - 50
        }, 500);
    }
    
    // Handle API key validation
    $('input[name="openai_api_key"], input[name="deepseek_api_key"]').on('blur', function() {
        var $input = $(this);
        var apiKey = $input.val().trim();
        
        if (apiKey && apiKey.length > 20) {
            $input.addClass('validating');
            
            $.ajax({
                url: autoPostGenerator.ajaxurl,
                type: 'POST',
                data: {
                    action: 'validate_api_key',
                    nonce: autoPostGenerator.nonce,
                    api_key: apiKey,
                    provider: $input.attr('name').replace('_api_key', '')
                },
                success: function(response) {
                    $input.removeClass('validating');
                    
                    if (response.success) {
                        $input.addClass('valid');
                        $input.removeClass('invalid');
                    } else {
                        $input.addClass('invalid');
                        $input.removeClass('valid');
                    }
                },
                error: function() {
                    $input.removeClass('validating');
                    $input.addClass('invalid');
                    $input.removeClass('valid');
                }
            });
        }
    });
    
    // Search functionality for ideas
    $('#ideas-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.post-idea-item').each(function() {
            var $item = $(this);
            var title = $item.find('h4').text().toLowerCase();
            var keyword = $item.find('.keyword-highlight').text().toLowerCase();
            
            if (title.includes(searchTerm) || keyword.includes(searchTerm)) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 83) {
            e.preventDefault();
            $('.button-primary[type="submit"]').first().click();
        }
        
        // Ctrl/Cmd + G to generate
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 71) {
            e.preventDefault();
            $('#generate-ideas-btn').click();
        }
    });
    
    // Initialize components
    initializeComponents();
    
    function initializeComponents() {
        // Auto-focus first input in active tab
        var $activeTab = $('.apg-tab-content.active');
        if ($activeTab.length) {
            $activeTab.find('input[type="text"], textarea').first().focus();
        }
        
        // Initialize select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('select.enhanced').select2({
                width: '100%'
            });
        }
        
        // Initialize date pickers if available
        if (typeof $.fn.datepicker !== 'undefined') {
            $('input[type="date"]').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
    }
});

// Utility functions for external use
window.AutoPostGenerator = {
    showMessage: function(message, type) {
        jQuery(document).trigger('apg-show-message', [message, type]);
    },
    
    refreshIdeasList: function() {
        jQuery(document).trigger('apg-refresh-ideas');
    },
    
    validateApiKey: function(apiKey, provider) {
        return jQuery.ajax({
            url: autoPostGenerator.ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_api_key',
                nonce: autoPostGenerator.nonce,
                api_key: apiKey,
                provider: provider
            }
        });
    }
};