define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote'
], function ($, _, quote) {
    'use strict';

    var RETRY_DELAY = 100,
        MAX_RETRIES = 20;

    function isValidQuoteId() {
        var quoteId = quote.getQuoteId ? quote.getQuoteId() : null;

        return quoteId !== undefined &&
            quoteId !== null &&
            quoteId !== '' &&
            quoteId !== 0 &&
            quoteId !== '0';
    }

    function getFallbackTotals() {
        return (window.checkoutConfig && window.checkoutConfig.totalsData) || null;
    }

    return function (originalAction) {
        return function (callbacks, deferred) {
            var attempt = 0;

            deferred = deferred || $.Deferred();

            function resolveFromFallback() {
                var fallbackTotals = getFallbackTotals();

                if (!fallbackTotals || _.isEmpty(fallbackTotals)) {
                    return false;
                }

                if (quote.setTotals) {
                    quote.setTotals(fallbackTotals);
                }

                deferred.resolve(fallbackTotals);
                return true;
            }

            function retry() {
                if (isValidQuoteId()) {
                    originalAction(callbacks, deferred);
                    return;
                }

                if (resolveFromFallback()) {
                    return;
                }

                if (attempt >= MAX_RETRIES) {
                    deferred.reject();
                    return;
                }

                attempt += 1;
                setTimeout(retry, RETRY_DELAY);
            }

            if (isValidQuoteId()) {
                return originalAction(callbacks, deferred);
            }

            retry();
            return deferred.promise();
        };
    };
});
