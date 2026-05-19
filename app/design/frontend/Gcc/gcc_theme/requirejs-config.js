var config = {
    map: {
        '*': {
            "vnecoms-vendor-contact-message": "Vnecoms_VendorsMessage/js/message",
             "mstSearchAutocomplete": "Mirasvit_SearchAutocomplete/js/autocomplete"
        }
    },

    config: {
        mixins: {
            'Magento_Checkout/js/model/quote': {
                'js/model/quote-mixin': true
            },
            'Magento_Checkout/js/action/get-totals': {
                'js/action/get-totals-mixin': true
            },
            'Magento_Checkout/js/action/get-payment-information': {
                'js/action/get-payment-information-mixin': true
            },
            'Magento_Checkout/js/action/set-billing-address': {
                'js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/model/totals': {
                'js/model/totals-mixin': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'js/configurable-mixin': true
            }
        }
    },

    deps: [
        'js/hide-verification-qty'
    ]
};
