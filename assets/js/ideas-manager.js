/**
 * Ideas Manager JavaScript
 * Handles idea keyword saving functionality
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Save idea keyword functionality
    $('.save-keyword-btn').click(function() {
        var ideaId = $(this).data('idea-id');
        var keyword = $(this).siblings('.idea-keyword-input').val();
        var button = $(this);
        
        $.ajax({
            url: miapgAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_idea_keyword',
                idea_id: ideaId,
                keyword: keyword,
                nonce: miapgAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    button.html('✅');
                    setTimeout(function() {
                        button.html('💾');
                    }, 2000);
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred while saving the keyword.');
            }
        });
    });
});