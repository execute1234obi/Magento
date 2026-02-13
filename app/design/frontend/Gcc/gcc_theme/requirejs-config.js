var config = {
    map: {
        '*': {
            "vnecoms-vendor-contact-message": "Vnecoms_VendorsMessage/js/message"
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
