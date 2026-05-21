define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    function isValidQuoteId() {
        var quoteId = quote.getQuoteId ? quote.getQuoteId() : null;

        return quoteId !== undefined &&
            quoteId !== null &&
            quoteId !== '' &&
            quoteId !== 0 &&
            quoteId !== '0';
    }

    return function (originalAction) {
        return function (messageContainer) {
            var deferred = $.Deferred();

            if (!isValidQuoteId()) {
                deferred.resolve();
                return deferred.promise();
            }

            return originalAction(messageContainer);
        };
    };
});
