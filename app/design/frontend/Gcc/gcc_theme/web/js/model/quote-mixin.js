define([
    'ko',
    'underscore'
], function (ko, _) {
    'use strict';

    function getCheckoutConfig() {
        return window.checkoutConfig || {};
    }

    function getQuoteData() {
        return getCheckoutConfig().quoteData || {};
    }

    function getTotalsData() {
        return getCheckoutConfig().totalsData || {};
    }

    function isValidQuoteId(value) {
        return value !== undefined &&
            value !== null &&
            value !== '' &&
            value !== 0 &&
            value !== '0';
    }

    function ensureTotalsObservable(quote) {
        var totalsObservable,
            currentTotals,
            fallbackTotals;

        if (!quote.getTotals || !_.isFunction(quote.getTotals)) {
            return;
        }

        totalsObservable = quote.getTotals();

        if (!_.isFunction(totalsObservable)) {
            return;
        }

        currentTotals = totalsObservable();
        fallbackTotals = getTotalsData();

        if ((!currentTotals || _.isEmpty(currentTotals)) && !_.isEmpty(fallbackTotals)) {
            totalsObservable(fallbackTotals);
            return;
        }

        if (!currentTotals) {
            totalsObservable({});
        }
    }

    return function (quote) {
        var originalGetQuoteId = quote.getQuoteId ? quote.getQuoteId.bind(quote) : null,
            originalIsVirtual = quote.isVirtual ? quote.isVirtual.bind(quote) : null,
            originalGetPriceFormat = quote.getPriceFormat ? quote.getPriceFormat.bind(quote) : null,
            originalGetBasePriceFormat = quote.getBasePriceFormat ? quote.getBasePriceFormat.bind(quote) : null,
            originalGetItems = quote.getItems ? quote.getItems.bind(quote) : null,
            originalGetTotals = quote.getTotals ? quote.getTotals.bind(quote) : null,
            originalSetTotals = quote.setTotals ? quote.setTotals.bind(quote) : null;

        ensureTotalsObservable(quote);

        _.extend(quote, {
            getQuoteId: function () {
                var quoteId = getQuoteData().entity_id;

                if (isValidQuoteId(quoteId)) {
                    return quoteId;
                }

                return originalGetQuoteId ? originalGetQuoteId() : quoteId;
            },

            isVirtual: function () {
                var quoteData = getQuoteData();

                if (typeof quoteData.is_virtual !== 'undefined') {
                    return !!Number(quoteData.is_virtual);
                }

                return originalIsVirtual ? originalIsVirtual() : false;
            },

            getPriceFormat: function () {
                var config = getCheckoutConfig();

                return config.priceFormat || (originalGetPriceFormat ? originalGetPriceFormat() : {});
            },

            getBasePriceFormat: function () {
                var config = getCheckoutConfig();

                return config.basePriceFormat || (originalGetBasePriceFormat ? originalGetBasePriceFormat() : {});
            },

            getItems: function () {
                var config = getCheckoutConfig();

                return config.quoteItemData || (originalGetItems ? originalGetItems() : []);
            },

            getTotals: function () {
                ensureTotalsObservable(quote);

                return originalGetTotals ? originalGetTotals() : quote.totals;
            },

            setTotals: function (data) {
                if (originalSetTotals) {
                    originalSetTotals(data);
                    return;
                }

                if (_.isFunction(quote.totals)) {
                    quote.totals(data || {});
                }
            }
        });

        return quote;
    };
});
