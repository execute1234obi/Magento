(function () {
    'use strict';

    function isVendorPage() {
        return Array.prototype.some.call(document.body.classList, function (className) {
            return className.indexOf('page-vendor') !== -1;
        });
    }

    function closeSidebar(toggleButton) {
        document.body.classList.remove('vendor-mobile-sidebar-open');
        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', 'false');
        }
    }

    function openSidebar(toggleButton) {
        document.body.classList.add('vendor-mobile-sidebar-open');
        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', 'true');
        }
    }

    function initMobileSidebar() {
        if (!isVendorPage()) {
            return;
        }

        var sidebar = document.querySelector('.main-sidebar, .left-side');
        var toggleButton = document.querySelector('.vendor-mobile-sidebar-toggle');
        var overlay = document.querySelector('.vendor-mobile-sidebar-overlay');

        if (!sidebar || !toggleButton || !overlay) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            if (document.body.classList.contains('vendor-mobile-sidebar-open')) {
                closeSidebar(toggleButton);
                return;
            }

            openSidebar(toggleButton);
        });

        overlay.addEventListener('click', function () {
            closeSidebar(toggleButton);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar(toggleButton);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 767) {
                closeSidebar(toggleButton);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileSidebar);
    } else {
        initMobileSidebar();
    }
}());
