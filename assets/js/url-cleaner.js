/**
 * URL Cleaner JavaScript
 * Handles URL cleaning functionality for ideas manager
 */

(function() {
    'use strict';
    
    // Clean URL after showing the message
    if (window.location.href.indexOf('miapg_message=') > -1) {
        var cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?page=miapg-ideas-manager';
        window.history.replaceState({}, document.title, cleanUrl);
    }
})();