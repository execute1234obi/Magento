define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/payment-service'
], function ($, _, quote, methodConverter, paymentService) {
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

    function getCheckoutConfig() {
        return window.checkoutConfig || {};
    }

    function applyFallbackData() {
        var config = getCheckoutConfig();

        if (config.totalsData && !_.isEmpty(config.totalsData) && quote.setTotals) {
            quote.setTotals(config.totalsData);
        }

        if (config.paymentMethods && config.paymentMethods.length) {
            paymentService.setPaymentMethods(methodConverter(config.paymentMethods));
        }
    }

    return function (originalAction) {
        return function (deferred, messageContainer) {
            var attempt = 0;

            deferred = deferred || $.Deferred();

            function retry() {
                if (isValidQuoteId()) {
                    originalAction(deferred, messageContainer);
                    return;
                }

                applyFallbackData();

                if (isValidQuoteId() || (_.isArray(getCheckoutConfig().paymentMethods) && getCheckoutConfig().paymentMethods.length > 0) || !_.isEmpty(getCheckoutConfig().totalsData || {})) {
                    deferred.resolve();
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
                return originalAction(deferred, messageContainer);
            }

            retry();
            return deferred.promise();
        };
    };
});
