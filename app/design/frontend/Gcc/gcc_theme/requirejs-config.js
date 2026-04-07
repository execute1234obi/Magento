var config = {
    map: {
        '*': {
            "vnecoms-vendor-contact-message": "Vnecoms_VendorsMessage/js/message",
             "mstSearchAutocomplete": "Mirasvit_SearchAutocomplete/js/autocomplete"
        }
    },

    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'js/configurable-mixin': true
            }
        }
    },

    deps: [
        'js/hide-verification-qty'
    ]
};
