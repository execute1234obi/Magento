require(['jquery'], function ($) {
    $(document).ready(function () {

        let hasVerificationProduct = false;

        $('.cart.item').each(function () {
            let productName = $(this).find('.product-item-name').text().trim();

            if (productName.includes('Seller Verification Fees')) {
                hasVerificationProduct = true;

                // ✅ ONLY hide qty input
                $(this).find('.field.qty').hide();

                // ❌ actions-toolbar ko touch mat karo
                // delete button visible rahega
            }
        });

        // hide qty column header ONLY if verification product exists
        if (hasVerificationProduct) {
            $('th.col.qty').hide();
        }
    });
});
