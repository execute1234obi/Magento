define([
    'ko',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals'
], function (ko, _, Component, registry, quote, totals) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,

        initialize: function () {
            var selectedMethod = quote.paymentMethod();

            this._super();
            this.currentPaymentRenderer = ko.observable(null);
            this.rendererAvailabilitySubscription = null;
            this.isPlaceOrderActionAllowed = ko.observable(false);

            this.resolvePlaceOrderState(selectedMethod);

            quote.paymentMethod.subscribe(function (method) {
                this.resolvePlaceOrderState(method);
            }, this);

            return this;
        },

        /**
         * Update the active payment renderer and its place-order availability.
         *
         * @param {Object} selectedMethod
         */
        resolvePlaceOrderState: function (selectedMethod) {
            var methodCode,
                self = this;

            if (this.rendererAvailabilitySubscription && _.isFunction(this.rendererAvailabilitySubscription.dispose)) {
                this.rendererAvailabilitySubscription.dispose();
            }

            this.rendererAvailabilitySubscription = null;
            this.currentPaymentRenderer(null);
            this.isPlaceOrderActionAllowed(false);

            if (!selectedMethod || !selectedMethod.method) {
                return;
            }

            methodCode = selectedMethod.method;

            registry.get(function (item) {
                return item &&
                    item.item &&
                    item.item.method === methodCode &&
                    _.isFunction(item.placeOrder);
            }, function (renderer) {
                var availability;

                if (!quote.paymentMethod() || quote.paymentMethod().method !== methodCode) {
                    return;
                }

                self.currentPaymentRenderer(renderer);

                if (_.isFunction(renderer.isPlaceOrderActionAllowed)) {
                    availability = renderer.isPlaceOrderActionAllowed();
                    self.isPlaceOrderActionAllowed(!!availability);

                    if (_.isFunction(renderer.isPlaceOrderActionAllowed.subscribe)) {
                        self.rendererAvailabilitySubscription = renderer.isPlaceOrderActionAllowed.subscribe(function (value) {
                            self.isPlaceOrderActionAllowed(!!value);
                        });
                    }

                    return;
                }

                if (_.isFunction(renderer.isButtonActive)) {
                    self.isPlaceOrderActionAllowed(!!renderer.isButtonActive());
                    return;
                }

                self.isPlaceOrderActionAllowed(true);
            });
        },

        /**
         * Find the currently selected payment renderer in the UI registry.
         *
         * @return {Object|null}
         */
        getSelectedPaymentRenderer: function () {
            var selectedMethod = quote.paymentMethod(),
                renderers;

            if (!selectedMethod || !selectedMethod.method) {
                return null;
            }

            renderers = registry.filter(function (item) {
                return item &&
                    item.item &&
                    item.item.method === selectedMethod.method &&
                    _.isFunction(item.placeOrder);
            });

            return renderers.length ? renderers[0] : null;
        },

        /**
         * Delegate the place order action to the active payment method renderer.
         *
         * @param {*} data
         * @param {Event} event
         * @return {Boolean}
         */
        placeOrder: function (data, event) {
            var renderer = this.currentPaymentRenderer() || this.getSelectedPaymentRenderer();

            if (event) {
                event.preventDefault();
            }

            if (renderer && _.isFunction(renderer.placeOrder)) {
                return renderer.placeOrder(data, event);
            }

            return false;
        }
    });
});
