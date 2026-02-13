define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function () {

        var hash = window.location.hash;
        if (!hash) {
            return;
        }

        function activateTab() {
            var $tabs = $('#vendor_info_tabs');

            if (
                $tabs.length &&
                typeof $tabs.tabs === 'function' &&
                $tabs.hasClass('ui-tabs')
            ) {
                var $link = $tabs.find('a[href="' + hash + '"]');

                if ($link.length) {
                    var index = $link.parent().index();
                    $tabs.tabs('option', 'active', index);
                    return true;
                }
            }
            return false;
        }

        // 🔹 Try immediately
        if (activateTab()) {
            return;
        }

        // 🔹 Observe DOM changes (Vnecoms async load)
        var observer = new MutationObserver(function () {
            if (activateTab()) {
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    };
});
