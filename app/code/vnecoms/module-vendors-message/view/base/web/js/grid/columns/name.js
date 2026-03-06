/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mageUtils',
    'moment',
    'Magento_Ui/js/grid/columns/column'
], function (utils, moment, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
        },
        /**
         * Ment to preprocess data associated with a current columns' field.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {String}
         */
        getLabel: function (record) {
            var name = record[this.index];
            if (parseInt(record['msg_count']) > 1) {
name +=' <span class="msg_count">('+record['msg_count']+')</span>'
            if (record['status'] == 1) {
return "<strong>"+name+"</strong>"; } }
            
            return name;
        }
    });
});
