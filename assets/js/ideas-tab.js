/**
 * Ideas Tab JavaScript
 * Handles ideas tab functionality including delete and bulk actions
 */

// Global function for deleting individual ideas
function deleteIdea(ideaId, ideaTitle) {
    if (!confirm(miapgIdeasTab.strings.confirmDelete + ' "' + ideaTitle + '"')) {
        return;
    }
    
    jQuery.ajax({
        url: miapgAdmin.ajaxurl,
        type: 'POST',
        data: {
            action: 'delete_idea',
            idea_id: ideaId,
            nonce: miapgAdmin.nonce
        },
        success: function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload(); // Refresh the page to show updated list
            } else {
                alert('Error: ' + response.data.message);
            }
        },
        error: function() {
            alert(miapgIdeasTab.strings.errorDeleting);
        }
    });
}

// Document ready functions
jQuery(document).ready(function($) {
    'use strict';
    
    // Handle bulk actions
    $('#doaction').click(function(e) {
        e.preventDefault();
        
        var action = $('#bulk-action-selector-top').val();
        var selectedIds = [];
        
        $('input[name="idea_ids[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (action === '-1') {
            alert(miapgIdeasTab.strings.selectAction);
            return;
        }
        
        if (selectedIds.length === 0) {
            alert(miapgIdeasTab.strings.selectIdeas);
            return;
        }
        
        var keyword = '';
        if (action === 'bulk_add_keyword') {
            keyword = prompt(miapgIdeasTab.strings.enterKeyword);
            if (!keyword) {
                return;
            }
        }
        
        if (action === 'bulk_delete_selected' && !confirm(miapgIdeasTab.strings.confirmBulkDelete)) {
            return;
        }
        
        $.ajax({
            url: miapgAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'bulk_ideas_action',
                bulk_action: action,
                idea_ids: selectedIds,
                keyword: keyword,
                nonce: miapgAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert(miapgIdeasTab.strings.errorBulkAction);
            }
        });
    });
    
    // Select all checkbox
    $('#cb-select-all-1').change(function() {
        $('input[name="idea_ids[]"]').prop('checked', this.checked);
    });
});