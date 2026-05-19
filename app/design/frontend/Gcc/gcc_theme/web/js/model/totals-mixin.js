define([
    'ko',
    'underscore',
    'Magento_Checkout/js/model/quote'
], function (ko, _, quote) {
    'use strict';

    function getInitialItems(totalsModule) {
        var totalsValue = _.isFunction(totalsModule.totals) ? totalsModule.totals() : null,
            totalsItems = totalsValue && _.isArray(totalsValue.items) ? totalsValue.items : null,
            quoteItems = _.isFunction(quote.getItems) ? quote.getItems() : null;

        if (totalsItems && totalsItems.length) {
            return totalsItems;
        }

        if (_.isArray(quoteItems) && quoteItems.length) {
            return quoteItems;
        }

        return [];
    }

    return function (totalsModule) {
        var itemsObservable = ko.observable(getInitialItems(totalsModule));

        if (_.isFunction(totalsModule.totals) && _.isFunction(totalsModule.totals.subscribe)) {
            totalsModule.totals.subscribe(function (newTotals) {
                if (newTotals && _.isArray(newTotals.items)) {
                    itemsObservable(newTotals.items);
                    return;
                }

                if (_.isFunction(quote.getItems)) {
                    itemsObservable(quote.getItems() || []);
                    return;
                }

                itemsObservable([]);
            });
        }

        return _.extend(totalsModule, {
            getItems: function () {
                return itemsObservable;
            }
        });
    };
});
