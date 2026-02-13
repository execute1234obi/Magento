require(['jquery'], function ($) {
    $(document).ready(function () {

        let hasVerificationProduct = false;

        $('.cart.item').each(function () {
            let productName = $(this).find('.product-item-name').text().trim();

            if (productName.includes('Seller Verification Fees')) {
                hasVerificationProduct = true;

                // hide qty input
                $(this).find('.field.qty').hide();

                // optional: hide update button
                $(this).find('.actions-toolbar').hide();
            }
        });

        // hide qty column header ONLY if verification product exists
        if (hasVerificationProduct) {
            $('th.col.qty').hide();
        }
    });
});
