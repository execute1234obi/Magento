define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        /**
         * @param {*} price
         * @return {*|String}
         */
        getFormattedPrice: function (price) {
            return priceUtils.formatPriceLocale(price, quote.getPriceFormat());
        },

        /**
         * @return {*}
         */
        getTotals: function () {
            return totals.totals();
        },

        /**
         * Show checkout totals as soon as quote totals exist.
         *
         * The default Magento implementation waits for the shipping step to be
         * marked processed, but our custom checkout flow can skip that flag.
         *
         * @return {Boolean}
         */
        isFullMode: function () {
            return !!this.getTotals();
        }
    });
});
