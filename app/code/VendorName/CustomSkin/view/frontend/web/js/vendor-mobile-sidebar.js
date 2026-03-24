(function () {
    'use strict';

    function isVendorPage() {
        return Array.prototype.some.call(document.body.classList, function (className) {
            return className.indexOf('page-vendor') !== -1;
        });
    }

    function createToggleButton() {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'vendor-mobile-sidebar-toggle';
        button.setAttribute('aria-label', 'Open sidebar menu');
        button.setAttribute('aria-expanded', 'false');
        button.innerHTML = '<span class="vendor-mobile-sidebar-toggle-bar"></span>';
        return button;
    }

    function createOverlay() {
        var overlay = document.createElement('div');
        overlay.className = 'vendor-mobile-sidebar-overlay';
        return overlay;
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
        var topbarInner = document.querySelector('.vendor-topbar__inner');
        var navbar = document.querySelector('.main-header .navbar');
        var buttonHost = topbarInner || navbar;

        if (!sidebar || !buttonHost || document.querySelector('.vendor-mobile-sidebar-toggle')) {
            return;
        }

        var toggleButton = createToggleButton();
        var overlay = createOverlay();

        buttonHost.insertBefore(toggleButton, buttonHost.firstChild);
        document.body.appendChild(overlay);

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
