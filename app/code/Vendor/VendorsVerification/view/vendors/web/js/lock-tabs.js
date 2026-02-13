define(['jquery'], function ($) {
    'use strict';

    $(document).ready(function () {
        //alert("me aa gayi");
        // 1️⃣ Agar verification context hi nahi hai → kuch mat karo
        if (!window.allowedVerificationGroup) {
          //  alert("hi1");
            return;

        }

        // 2️⃣ Vendor info tabs exist karte hain?
        var $tabs = $('#vendor_info_tabs');
        if (!$tabs.length) {
            // alert("hi2");
            return;

        }

        // 3️⃣ Mapping: verification group → tab index
        var groupTabMap = {
            'business_information': 0,
            'business_address': 1,
            'business_contact': 2,
            'logo_certificates': 3
        };

        var allowedIndex = groupTabMap[window.allowedVerificationGroup];
        if (allowedIndex === undefined) {
            return;
        }

        // 4️⃣ Tabs lock logic
        $tabs.find('> ul > li').each(function (index) {
            if (index !== allowedIndex) {
                $(this)
                    .addClass('verification-tab-locked')
                    .css({
                        pointerEvents: 'none',
                        opacity: 0.4
                    });
            }
        });

        // 5️⃣ Force allowed tab open (jQuery UI safe)
        try {
            if ($tabs.hasClass('ui-tabs')) {
                $tabs.tabs('option', 'active', allowedIndex);
            }
        } catch (e) {}
    });
});
