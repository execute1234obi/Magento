define([
    'ko',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'Magento_Customer/js/customer-data'
], function (ko, _, Component, registry, quote, totals, priceUtils, customerData) {
    'use strict';

    return Component.extend({
        isLoading: totals.isLoading,
        summaryReference: ko.pureComputed(function () {
            var quoteId = quote.getQuoteId ? quote.getQuoteId() : '';

            return quoteId ? ('#' + quoteId) : '';
        }),

        getCurrentTotalsData: function () {
            var quoteTotalsObservable = quote.getTotals ? quote.getTotals() : null,
                quoteTotals = _.isFunction(quoteTotalsObservable) ? quoteTotalsObservable() : quoteTotalsObservable,
                checkoutConfigTotals = window.checkoutConfig && window.checkoutConfig.totalsData,
                modelTotals = totals && _.isFunction(totals.totals) ? totals.totals() : null,
                cartData = customerData.get('cart') ? customerData.get('cart')() : {},
                subtotalAmount = cartData && cartData.subtotalAmount,
                fallbackTotals;

            if (quoteTotals && _.isObject(quoteTotals) && !_.isEmpty(quoteTotals)) {
                return quoteTotals;
            }

            if (modelTotals && _.isObject(modelTotals) && !_.isEmpty(modelTotals)) {
                return modelTotals;
            }

            if (checkoutConfigTotals && !_.isEmpty(checkoutConfigTotals)) {
                return checkoutConfigTotals;
            }

            fallbackTotals = {};

            if (typeof subtotalAmount !== 'undefined') {
                fallbackTotals.subtotal = parseFloat(subtotalAmount) || 0;
                fallbackTotals.grand_total = parseFloat(subtotalAmount) || 0;
                fallbackTotals.total_segments = [
                    {
                        code: 'subtotal',
                        title: 'Subtotal',
                        value: parseFloat(subtotalAmount) || 0
                    },
                    {
                        code: 'grand_total',
                        title: 'Total',
                        value: parseFloat(subtotalAmount) || 0
                    }
                ];
            }

            return fallbackTotals;
        },

        initialize: function () {
            var selectedMethod = quote.paymentMethod();

            this._super();
            this.currentPaymentRenderer = ko.observable(null);
            this.rendererAvailabilitySubscription = null;
            this.isPlaceOrderActionAllowed = ko.observable(false);
            this.summaryRows = ko.pureComputed(this.getSummaryRows, this);

            this.resolvePlaceOrderState(selectedMethod);

            quote.paymentMethod.subscribe(function (method) {
                this.resolvePlaceOrderState(method);
            }, this);

            return this;
        },

        /**
         * Build the visible order summary rows directly from quote totals.
         *
         * This keeps the sidebar stable even when Magento's summary children
         * are not rendered in the custom payment flow.
         *
         * @return {Array}
         */
        getSummaryRows: function () {
            var totalsData = this.getCurrentTotalsData(),
                rows = [],
                segmentMap = {},
                segmentOrder = ['subtotal', 'tax', 'discount', 'shipping', 'grand_total'],
                fallbackTitles = {
                    subtotal: 'Subtotal',
                    tax: 'Tax',
                    discount: 'Discount',
                    shipping: 'Shipping',
                    grand_total: 'Total'
                },
                fallbackValues = {
                    subtotal: totalsData && totalsData.subtotal,
                    tax: totalsData && totalsData.tax_amount,
                    discount: totalsData && totalsData.discount_amount,
                    shipping: totalsData && totalsData.shipping_amount,
                    grand_total: totalsData && totalsData.grand_total
                };

            if (totalsData && _.isArray(totalsData.total_segments)) {
                _.each(totalsData.total_segments, function (segment) {
                    if (segment && segment.code) {
                        segmentMap[segment.code] = segment;
                    }
                });
            }

            _.each(segmentOrder, function (code) {
                var segment = segmentMap[code],
                    rawValue,
                    title;

                if (!segment && typeof fallbackValues[code] === 'undefined') {
                    return;
                }

                rawValue = segment ? segment.value : fallbackValues[code];
                title = code === 'grand_total' ? 'Total' : (segment && segment.title ? segment.title : fallbackTitles[code] || code);

                rows.push({
                    code: code,
                    title: title,
                    value: priceUtils.formatPriceLocale(parseFloat(rawValue) || 0, quote.getPriceFormat ? quote.getPriceFormat() : window.checkoutConfig.priceFormat),
                    rowClass: code === 'grand_total' ? 'grand totals' : 'totals ' + code
                });
            });

            return rows;
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
