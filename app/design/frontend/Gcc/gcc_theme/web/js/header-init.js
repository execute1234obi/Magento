define(['jquery'], function ($) {
    'use strict';

    return function () {
        // --- 1. Desktop Dropdown Logic ---
        function toggleDropdown(id) {
            const all = ['vendorMenu', 'customerMenu'];
            all.forEach(menuId => {
                const el = document.getElementById(menuId);
                if (!el) return;
                (menuId === id) ? el.classList.toggle('hidden') : el.classList.add('hidden');
            });
            const v = document.getElementById('vendorBtn'), c = document.getElementById('customerBtn');
            if (id === 'vendorMenu') { v?.classList.toggle('dropdown-active'); c?.classList.remove('dropdown-active'); }
            if (id === 'customerMenu') { c?.classList.toggle('dropdown-active'); v?.classList.remove('dropdown-active'); }
        }

        // --- 2. Mobile Menu Logic ---
        function toggleMobileMenu() {
            const m = document.getElementById('mobileMenu');
            const h = document.getElementById('hamburgerIcon');
            const c = document.getElementById('closeIcon');
            m?.classList.toggle('hidden');
            h?.classList.toggle('hidden');
            c?.classList.toggle('hidden');
        }

        function toggleMobileDropdown(id) {
            document.getElementById(id)?.classList.toggle('hidden');
        }

        // --- 3. Language Dropdown Logic ---
        const langToggle = document.getElementById('langToggle');
        const languageMenu = document.getElementById('languageMenu');
        if (langToggle && languageMenu) {
            langToggle.addEventListener('click', e => { e.stopPropagation(); languageMenu.classList.toggle('hidden'); });
            document.addEventListener('click', e => {
                if (!langToggle.contains(e.target) && !languageMenu.contains(e.target)) {
                    languageMenu.classList.add('hidden');
                }
            });
        }
        
        // --- 4. Attach Listeners (using jQuery for simplicity) ---
        // Attaching click listeners to buttons using the functions above
        $('#vendorBtn').on('click', function() { toggleDropdown('vendorMenu'); });
        $('#customerBtn').on('click', function() { toggleDropdown('customerMenu'); });
        $('[data-role="mobile-menu-toggle"]').on('click', toggleMobileMenu);

        // Click outside handler for desktop dropdowns
        $(window).on('click', function(e) {
            if (!$(e.target).closest('.group').length) {
                $('#vendorMenu')?.addClass('hidden');
                $('#customerMenu')?.addClass('hidden');
                $('#vendorBtn')?.removeClass('dropdown-active');
                $('#customerBtn')?.removeClass('dropdown-active');
            }
        });

    };
});