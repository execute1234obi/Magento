define(['jquery'], function($) {
    'use strict';

    function hideGeneralTab() {
        var $tab = $('.admin__page-nav-item.item.section-general');
        if ($tab.length) {
            $tab.hide();
            return true; // tab found and hidden
        }
        return false; // tab not found yet
    }

    $(document).ready(function() {
        // Try hide immediately and keep checking until found
        var interval = setInterval(function() {
            if (hideGeneralTab()) {
                clearInterval(interval);
            }
        }, 100); // check every 100ms

        // Optional: MutationObserver (modern approach)
        var target = document.querySelector('#system_config_tabs');
        if (target) {
            var observer = new MutationObserver(function() {
                hideGeneralTab();
            });
            observer.observe(target, { childList: true, subtree: true });
        }
    });
});
