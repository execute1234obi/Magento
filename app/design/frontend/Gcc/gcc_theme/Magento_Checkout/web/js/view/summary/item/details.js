define([
    'uiComponent',
    'escaper'
], function (Component, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details',
            allowedTags: ['b', 'strong', 'i', 'em', 'u']
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getNameUnsanitizedHtml: function (quoteItem) {
            var txt = document.createElement('textarea');

            txt.innerHTML = quoteItem.name;

            return escaper.escapeHtml(txt.value, this.allowedTags);
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getDescription: function (quoteItem) {
            var description = quoteItem && (
                    quoteItem.description ||
                    quoteItem.short_description ||
                    quoteItem.subtitle
                ) ? String(
                    quoteItem.description ||
                    quoteItem.short_description ||
                    quoteItem.subtitle
                ) : '',
                wrapper;

            if (!description) {
                return '';
            }

            wrapper = document.createElement('div');
            wrapper.innerHTML = description;

            return (wrapper.textContent || wrapper.innerText || '').replace(/\s+/g, ' ').trim();
        },

        /**
         * @param {Object} quoteItem
         * @return {Boolean}
         */
        hasDescription: function (quoteItem) {
            return this.getDescription(quoteItem) !== '';
        }
    });
});
