/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'moment',
    'Magento_Ui/js/grid/columns/date',
    'mage/translate'
], function ($, utils, moment, Column, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        /**
         * Formats incoming date based on the 'dateFormat' property.
         *
         * @returns {String} Formatted date.
         */
        getLabel: function (value, format) {
            var date = moment(this._super());
            var now = new Date().getTime();
            var createdAt = new Date(this._super()).getTime();
            var $differentTime = now - createdAt;
            $differentTime = Math.round($differentTime/1000);

            var $minutes = Math.round($differentTime / 60);
            if ($minutes == 0) {
                return $t("Now");
            } else if ($minutes < 60 && $minutes > 0) {
                return $.mage.__("%1 minutes").replace('%1', $minutes);
            }

            var $hours = Math.round($minutes / 60);
            if ($hours < 24) {
                return $t("Today");
            }

            var $days = Math.round($hours / 24);
            if ($days == 1) {
                return $t("Yesterday");
            }
            if ($days < 7) {
                return $days + $t(" days");
            }

            format = 'MMM/DD/YYYY';
            date = date.isValid() ?
                date.format(format || this.dateFormat) :
                '';
            if (value['status'] == 1) {
                return "<strong>"+date+"</strong>";
            }
            return date;
        }
    });
});
