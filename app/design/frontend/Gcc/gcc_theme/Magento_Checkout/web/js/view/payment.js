define([
    'jquery',
    'underscore',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function (
    $,
    _,
    Component,
    ko,
    quote,
    stepNavigator,
    paymentService,
    methodConverter,
    getPaymentInformation,
    checkoutDataResolver,
    customerData,
    $t
) {
    'use strict';

    var countryData = customerData.get('directory-data');

    function unwrap(value) {
        return ko.unwrap(value);
    }

    function pick(source, keys) {
        var value = '';

        _.some(keys, function (key) {
            if (source && source[key] !== undefined && source[key] !== null && source[key] !== '') {
                value = unwrap(source[key]);
                return true;
            }

            return false;
        });

        return value || '';
    }

    function normalizeStreet(street) {
        var value = unwrap(street);

        if (_.isArray(value)) {
            return _.compact(_.map(value, function (line) {
                return unwrap(line);
            })).join(', ');
        }

        if (_.isObject(value)) {
            return _.compact(_.map(value, function (line) {
                return unwrap(line);
            })).join(', ');
        }

        return value || '';
    }

    function buildBillingAddressSummary() {
        var source = quote.billingAddress() ||
            (window.checkoutConfig && window.checkoutConfig.billingAddressFromData) ||
            (window.checkoutConfig && window.checkoutConfig.shippingAddressFromData) ||
            quote.shippingAddress() ||
            {};

        source = source || {};

        return {
            sameAsAccountAddress: true,
            fullName: _.compact([
                pick(source, ['firstname', 'first_name']),
                pick(source, ['lastname', 'last_name'])
            ]).join(' ') || pick(source, ['name']),
            addressLine: normalizeStreet(pick(source, ['street'])),
            country: getCountryName(pick(source, ['countryId', 'country_id'])),
            city: pick(source, ['city']),
            postcode: pick(source, ['postcode']),
            phone: pick(source, ['telephone'])
        };
    }

    function getCountryName(countryId) {
        var countries = countryData();

        return countries && countries[countryId] ? countries[countryId].name : countryId;
    }

    /** Set payment methods to collection */
    paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment',
            activeMethod: ''
        },
        isVisible: ko.observable(quote.isVirtual()),
        quoteIsVirtual: quote.isVirtual(),
        isPaymentMethodsAvailable: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length > 0;
        }),

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.billingAddress = ko.pureComputed(buildBillingAddressSummary);
            checkoutDataResolver.resolvePaymentMethod();
            stepNavigator.registerStep(
                'payment',
                null,
                $t('Review & Payments'),
                this.isVisible,
                _.bind(this.navigate, this),
                this.sortOrder
            );

            return this;
        },

        /**
         * Navigate method.
         */
        navigate: function () {
            var self = this;

            if (!self.hasShippingMethod()) {
                this.isVisible(false);
                stepNavigator.setHash('shipping');
            } else {
                getPaymentInformation().done(function () {
                    self.isVisible(true);
                });
            }
        },

        /**
         * @return {Boolean}
         */
        hasShippingMethod: function () {
            return window.checkoutConfig.selectedShippingMethod !== null;
        },

        /**
         * @return {*}
         */
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
