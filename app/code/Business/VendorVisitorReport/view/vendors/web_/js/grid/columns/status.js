/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/grid/columns/column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        /*eslint-disable eqeqeq*/
        /**
         * Retrieves label associated with a provided value.
         *
         * @returns {String}
         */
        getLabel: function (record) {
            var value = record[this.index];
            var statusClass = ['label'];
            if (value == 3  || value == 99) {
                statusClass.push('label-warning');
                statusClass.push('adbooking-status-inactive');
                
            } else if (value == 0 || value == 7) {             
				statusClass.push('label-default');   
                statusClass.push('adbooking-status-unpaid');
            } else if ( value == 5 || value == 6) {
                statusClass.push('label-danger');                
			} else if ( value==2 ) {
				statusClass.push('label-primary');
                statusClass.push('adbooking-status-active');
            } else if (value == 1 || value==2 || value == 4) {
                statusClass.push('label-success');
                statusClass.push('adbooking-status-paid');
            } 
            
            var options = this.options || [],
                values = this._super(),
                label = [];

            if (!Array.isArray(values)) {
                values = [values];
            }

            values = values.map(function (value) {
                return value + '';
            });

            options.forEach(function (item) {
                if (_.contains(values, item.value + '')) {
                    label.push(item.label);
                }
            });

            return '<div class="' + statusClass.join(" ") + '">' + label.join(', ') + '</div>';
        }

        /*eslint-enable eqeqeq*/
    });
});
