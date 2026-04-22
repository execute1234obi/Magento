define([
    'ko',
    'Magento_Ui/js/grid/paging/paging'
], function (ko, Paging) {
    'use strict';

    return Paging.extend({
        defaults: {
            template: 'Business_VendorVisitorReport/grid/paging/paging'
        },

        initObservable: function () {
            this._super();

            this.visiblePages = ko.pureComputed(this.getVisiblePages, this);

            return this;
        },

        goToPage: function (page) {
            this.setPage(page);

            return this;
        },

        getVisiblePages: function () {
            var current = parseInt(ko.unwrap(this.current), 10) || 1;
            var total = parseInt(ko.unwrap(this.pages), 10) || 1;
            var items = [];
            var addPage = function (value) {
                items.push({
                    type: 'page',
                    value: value,
                    label: String(value),
                    active: value === current
                });
            };
            var addEllipsis = function () {
                items.push({
                    type: 'ellipsis',
                    label: '...'
                });
            };
            var start;
            var end;
            var page;

            if (total <= 7) {
                for (page = 1; page <= total; page++) {
                    addPage(page);
                }

                return items;
            }

            addPage(1);

            if (current <= 1) {
                addPage(2);

                if (total > 3) {
                    addEllipsis();
                }

                addPage(total);
                return items;
            }

            if (current === 2) {
                addPage(2);
                addPage(3);

                if (total > 4) {
                    addEllipsis();
                }

                addPage(total);
                return items;
            }

            if (current === 3) {
                addPage(2);
                addPage(3);
                addPage(4);

                if (total > 5) {
                    addEllipsis();
                }

                addPage(total);
                return items;
            }

            if (current >= total) {
                addEllipsis();
                addPage(total - 1);
                addPage(total);
                return items;
            }

            if (current >= total - 1) {
                addEllipsis();
                addPage(total - 2);
                addPage(total - 1);
                addPage(total);
                return items;
            }

            if (current >= total - 2) {
                addEllipsis();
                addPage(total - 3);
                addPage(total - 2);
                addPage(total - 1);
                addPage(total);
                return items;
            }

            addEllipsis();
            start = current - 1;
            end = current + 1;

            for (page = start; page <= end; page++) {
                addPage(page);
            }

            addEllipsis();
            addPage(total);

            return items;
        }
    });
});
