define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';

    return Component.extend({

        title: 'Status',

        statusList: ko.observableArray([]),

        initialize: function () {
            this._super();
            this.populateStatus();
            return this;
        },

        populateStatus: function () {
            this.statusList([
                { name: 'Team Meeting', time: '19 Sep, 2019 10.30 AM' },
                { name: 'Vendor Visit', time: '22 Sep, 2018 11.20 AM' },
                { name: 'Lunch with Client', time: '25 Sep, 2018 2.00 PM' },
                { name: 'Client Visit', time: '30 Sep, 2018 7.30 PM' }
            ]);
        }
    });
});
