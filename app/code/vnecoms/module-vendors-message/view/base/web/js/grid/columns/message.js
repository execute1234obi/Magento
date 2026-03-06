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
            var content = record['content']?record['content'].replace(/<(?:.|\n)*?>/gm, ''):''; /*Strip HTML Tags*/
            var maxLength = 100;
            if (content.length > maxLength) {
    			content= content.substr(0,maxLength)+' ...'
    			
            if (record['status'] == 1) {
        		return "<strong>"+record[this.index]+'</strong><span class="text-muted"> - '+content+'</span>'; } }
            
            return record[this.index] +'<span class="text-muted"> - '+content+'</span>';
        }
    });
});
