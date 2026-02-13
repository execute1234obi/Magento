define(['jquery', 'Magento_ConfigurableProduct/js/configurable'], function($) {
    'use strict';
    alert("prise js");
    return function(config, element) {
        // This is your existing bridge code.
        $(element).on('click', '.option-btn', function() {
            var $btn = $(this);
            var optionValue = $btn.data('option-id');
            var attributeId = $btn.data('attribute-id');
            $('select[data-attribute-id="' + attributeId + '"]').val(optionValue).trigger('change');
            $btn.closest('.attribute-options').find('.option-btn').removeClass('bg-blue-100 border-blue-600');
            $btn.addClass('bg-blue-100 border-blue-600');
        });

        // Listen for the price update event.
        $(element).on('configurable.options.update', function(event, data) {
            var newPrice = data.finalPrice.amount;
            $('#product-price-update').text('$' + newPrice.toFixed(2));
            if (data.oldPrice && data.oldPrice.amount > newPrice) {
                $('#product-old-price').text('$' + data.oldPrice.amount.toFixed(2));
                $('#product-old-price').show();
            } else {
                $('#product-old-price').hide();
            }
        });
    };
});