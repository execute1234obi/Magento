(function () {
    'use strict';

    function closeDropdowns() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-dd].is-open'), function (item) {
            item.classList.remove('is-open');

            Array.prototype.forEach.call(item.querySelectorAll('[data-dd-btn]'), function (button) {
                button.setAttribute('aria-expanded', 'false');
            });
        });
    }

    function setSidebarState(isOpen) {
        var toggleButton = document.querySelector('.vendor-mobile-nav-toggle');

        document.body.classList.toggle('vendor-mobile-sidebar-open', isOpen);

        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    }

    function initVendorMobileLayout() {
        var sidebar = document.querySelector('.main-sidebar, .left-side');
        var toggleButton = document.querySelector('.vendor-mobile-nav-toggle');
        var closeButton = document.querySelector('.vendor-mobile-sidebar-close');
        var overlay = document.querySelector('.vendor-mobile-sidebar-overlay');

        if (!sidebar) {
            return;
        }

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                setSidebarState(!document.body.classList.contains('vendor-mobile-sidebar-open'));
            });
        }

        if (closeButton) {
            closeButton.addEventListener('click', function () {
                setSidebarState(false);
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                setSidebarState(false);
                closeDropdowns();
            });
        }

        document.addEventListener('click', function (event) {
            var toggle = event.target.closest('[data-dd-btn]');

            if (toggle) {
                var item = toggle.closest('[data-dd]');
                var shouldOpen = item && !item.classList.contains('is-open');

                closeDropdowns();

                if (item && shouldOpen) {
                    item.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                }

                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (!event.target.closest('[data-dd]')) {
                closeDropdowns();
            }

            if (window.innerWidth <= 767 && event.target.closest('.main-sidebar a')) {
                setSidebarState(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDropdowns();
                setSidebarState(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 767) {
                setSidebarState(false);
                closeDropdowns();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVendorMobileLayout);
    } else {
        initVendorMobileLayout();
    }
}());
