/**
 * Page Redirecter JavaScript
 * Handles page redirections after successful operations
 */

(function() {
    'use strict';
    
    // Check if there's a redirect URL provided
    if (typeof miapgPageRedirect !== 'undefined' && miapgPageRedirect.redirectUrl) {
        window.history.replaceState({}, document.title, miapgPageRedirect.redirectUrl);
    }
})();