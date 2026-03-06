define(
    [
        "jquery",
        'ko',
        'uiCollection'
    ],
    function (
        $,
        ko,
        Component
    ) {
        return Component.extend({
            defaults: {
                template: 'Vnecoms_VendorsMessage/message/count',
                count : false
            },

            /**
             * Calls 'initObservable' of parent
             *
             * @returns {Object} Chainable.
             */
            initObservable: function () {
                var self = this;
                this._super()
                    .observe([
                        'count'
                    ]);
                return this;
            },

            /**
             * Invokes initialize method of parent class,
             * contains initialization logic
             */
            initialize: function () {
                this._super();
                this.loadCount();
                return this;
            },

            /**
             *
             * @returns {*}
             */
            getMessageUrl: function() {
                return this.messageUrl;
            },

            /**
             * Retrieve data to authorized user.
             *
             * @return array
             */
            loadCount: function () {
                var self = this;
                $.ajax({
                    type: 'GET',
                    url: this.countUrl,
                    showLoader: false,
                    dataType: 'json',
                    context: this,

                    /**
                     * @param {Object} response
                     * @returns void
                     */
                    success: function (response) {
                        self.count(response.count);
                    },

                    /**
                     * @param {Object} response
                     * @returns {String}
                     */
                    error: function (response) {
                        self.count(0);
                    }
                });
            },

        });
    }
);
