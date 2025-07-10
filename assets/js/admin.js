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
            var originalText = $submitBtn.val();
            var loadingText = 'Saving...';
            
            // Check if this is a content generation form
            if ($form.find('#generate-ideas-btn').length || 
                $form.find('input[name="create_now"]').length ||
                $form.find('textarea[name="custom_prompt"]').length) {
                loadingText = autoPostGenerator.strings.generating || 'Generating...';
            }
            
            $submitBtn.prop('disabled', true);
            $submitBtn.val(loadingText);
            $submitBtn.data('original-text', originalText);
            $form.addClass('apg-loading');
            
            // Remove language change notification if present
            $form.find('.language-change-notice').remove();
            $submitBtn.removeClass('button-primary-changed');
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
    
    // Delete idea with AJAX
    $(document).on('click', '.idea-delete-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var ideaId = $btn.data('idea-id');
        var ideaTitle = $btn.data('idea-title');
        var $row = $btn.closest('tr');
        
        // Show confirmation dialog
        var confirmMessage = autoPostGenerator.strings.confirm_delete_idea || 
                            'Are you sure you want to delete the idea "' + ideaTitle + '"?';
        
        if (!confirm(confirmMessage)) {
            return false;
        }
        
        // Show loading state
        $btn.prop('disabled', true);
        $btn.html('<span class="spinner is-active" style="float: none; margin: 0;"></span>');
        
        // Make AJAX request
        $.ajax({
            url: autoPostGenerator.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_idea',
                nonce: autoPostGenerator.nonce,
                idea_id: ideaId
            },
            success: function(response) {
                if (response.success) {
                    // Remove row with animation
                    $row.fadeOut(300, function() {
                        $row.remove();
                        
                        // Update ideas count if visible
                        var $ideasCount = $('.ideas-count');
                        if ($ideasCount.length) {
                            var currentCount = parseInt($ideasCount.text()) || 0;
                            $ideasCount.text(Math.max(0, currentCount - 1));
                        }
                        
                        // Check if no ideas left
                        var $table = $('.ideas-list table tbody');
                        if ($table.find('tr').length === 0) {
                            $('.ideas-list').html('<div class="notice notice-info"><p>' + 
                                (autoPostGenerator.strings.no_ideas || 'No ideas found. Generate some ideas using the form above.') + 
                                '</p></div>');
                        }
                    });
                    
                    // Show success message
                    showMessage(response.data.message, 'success');
                } else {
                    // Show error message
                    showMessage(response.data.message || autoPostGenerator.strings.error, 'error');
                    
                    // Reset button state
                    $btn.prop('disabled', false);
                    $btn.html('🗑️ ' + (autoPostGenerator.strings.delete || 'Delete'));
                }
            },
            error: function() {
                showMessage(autoPostGenerator.strings.error || 'An error occurred', 'error');
                
                // Reset button state
                $btn.prop('disabled', false);
                $btn.html('🗑️ ' + (autoPostGenerator.strings.delete || 'Delete'));
            }
        });
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
    
    // Language selector change handler - no auto-submit
    $('select[name="interface_language"]').on('change', function() {
        var $select = $(this);
        var selectedLanguage = $select.val();
        
        // Just show a visual indicator that language has changed
        $select.addClass('changed');
        
        // Show a message to remind user to save
        var $form = $select.closest('form');
        var $submitBtn = $form.find('input[type="submit"]');
        if ($submitBtn.length) {
            $submitBtn.addClass('button-primary-changed');
            
            // Add a small notification
            var $notification = $('<span class="language-change-notice" style="color: #d54e21; font-size: 12px; margin-left: 10px;">Language changed - click Save to apply</span>');
            $select.parent().find('.language-change-notice').remove();
            $select.parent().append($notification);
        }
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
    
    // Bulk actions confirmation
    $('#doaction, #doaction2').on('click', function(e) {
        var $form = $(this).closest('form');
        var action = $form.find('select[name="action"]').val() || $form.find('select[name="action2"]').val();
        var $checkedBoxes = $form.find('input[name="post[]"]:checked');
        
        if (action === 'bulk_delete_ideas' && $checkedBoxes.length > 0) {
            var count = $checkedBoxes.length;
            var confirmMessage = count === 1 ? 
                'Are you sure you want to delete this idea?' : 
                'Are you sure you want to delete these ' + count + ' ideas?';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        }
        
        if (action === 'generate_posts' && $checkedBoxes.length > 0) {
            var count = $checkedBoxes.length;
            var confirmMessage = count === 1 ? 
                'Generate 1 post from this idea?' : 
                'Generate ' + count + ' posts from these ideas?';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Delete all ideas confirmation
    $(document).on('click', '.delete-all-ideas', function(e) {
        var $link = $(this);
        var ideaCount = $link.text().match(/\((\d+)\)/);
        var count = ideaCount ? ideaCount[1] : 'all';
        
        var confirmMessage = 'Are you ABSOLUTELY sure you want to delete ALL ' + count + ' ideas?\n\n' +
                            'This action cannot be undone!\n\n' +
                            'Type "DELETE ALL" to confirm:';
        
        var userInput = prompt(confirmMessage);
        if (userInput !== 'DELETE ALL') {
            e.preventDefault();
            return false;
        }
    });
    
    // Select all checkbox functionality
    $('#cb-select-all-1').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('input[name="idea_ids[]"]').prop('checked', isChecked);
    });
    
    // Individual checkbox change
    $(document).on('change', 'input[name="idea_ids[]"]', function() {
        var totalCheckboxes = $('input[name="idea_ids[]"]').length;
        var checkedCheckboxes = $('input[name="idea_ids[]"]:checked').length;
        $('#cb-select-all-1').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Bulk actions form submission
    $('#doaction').on('click', function(e) {
        e.preventDefault();
        
        var action = $('#bulk-action-selector-top').val();
        var $checkedBoxes = $('input[name="idea_ids[]"]:checked');
        
        if (action === '-1') {
            alert('Please select an action.');
            return false;
        }
        
        if ($checkedBoxes.length === 0) {
            alert('Please select at least one idea.');
            return false;
        }
        
        var count = $checkedBoxes.length;
        var confirmMessage = '';
        
        switch(action) {
            case 'bulk_delete_selected':
                confirmMessage = count === 1 ? 
                    'Are you sure you want to delete this idea?' : 
                    'Are you sure you want to delete these ' + count + ' ideas?';
                break;
            case 'bulk_generate_posts':
                confirmMessage = count === 1 ? 
                    'Generate 1 post from this idea?' : 
                    'Generate ' + count + ' posts from these ideas?';
                break;
            case 'bulk_add_keyword':
                var keyword = prompt('Enter keyword to add to selected ideas:');
                if (!keyword) return false;
                performBulkAction(action, $checkedBoxes, keyword);
                return;
        }
        
        if (confirmMessage && confirm(confirmMessage)) {
            performBulkAction(action, $checkedBoxes);
        }
    });
    
    // Perform bulk action
    function performBulkAction(action, $checkedBoxes, keyword) {
        var ideaIds = [];
        $checkedBoxes.each(function() {
            ideaIds.push($(this).val());
        });
        
        var data = {
            action: 'bulk_ideas_action',
            nonce: autoPostGenerator.nonce,
            bulk_action: action,
            idea_ids: ideaIds
        };
        
        if (keyword) {
            data.keyword = keyword;
        }
        
        // Show loading state
        $('#doaction').prop('disabled', true).val('Processing...');
        
        $.ajax({
            url: autoPostGenerator.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    
                    // If delete action, remove rows
                    if (action === 'bulk_delete_selected') {
                        $checkedBoxes.each(function() {
                            $(this).closest('tr').fadeOut(300, function() {
                                $(this).remove();
                                updateIdeasCount();
                            });
                        });
                    }
                    
                    // Reset form
                    $('#cb-select-all-1').prop('checked', false);
                    $('#bulk-action-selector-top').val('-1');
                } else {
                    showMessage(response.data.message || 'An error occurred', 'error');
                }
            },
            error: function() {
                showMessage('An error occurred while processing the request', 'error');
            },
            complete: function() {
                $('#doaction').prop('disabled', false).val('Apply');
            }
        });
    }
    
    // Update ideas count
    function updateIdeasCount() {
        var remainingRows = $('#ideas-bulk-form tbody tr').length;
        $('.ideas-count').text(remainingRows);
        $('.displaying-num').text(remainingRows + (remainingRows === 1 ? ' item' : ' items'));
        
        if (remainingRows === 0) {
            $('.ideas-list').html('<div class="notice notice-info"><p>No ideas found. Generate some ideas using the form above.</p></div>');
        }
    }
    
    // Handle page reload after settings are saved (language change)
    $(window).on('load', function() {
        // Check if we're coming back from a settings save
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('settings-updated') === 'true') {
            // Remove any loading states that might be stuck
            $('input[type="submit"]').prop('disabled', false);
            $('.apg-loading').removeClass('apg-loading');
            
            // Restore button text
            $('input[type="submit"]').each(function() {
                var $btn = $(this);
                var originalText = $btn.data('original-text');
                if (originalText) {
                    $btn.val(originalText);
                } else {
                    // Default text based on context
                    if ($btn.closest('.general-tab, .ai-tab, .content-tab, .scheduling-tab').length) {
                        $btn.val('Save Changes');
                    }
                }
            });
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