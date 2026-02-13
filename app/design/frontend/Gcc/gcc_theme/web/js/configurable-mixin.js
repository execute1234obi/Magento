define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';
    return function (ConfigurableWidget) {
        console.log('Mixin loaded for configurable widget');

        return $.widget('mage.configurable', ConfigurableWidget, {
            _create: function () {
                console.log('Mixin _create called');
                this._super();

                var self = this;

                // Bind click for your custom buttons
                this.element.on('click', '.option-btn', function () {
                    console.log('Option button clicked');
                    var $btn = $(this),
                        attributeCode = $btn.data('attribute-code'),
                        optionId = $btn.data('option-id');

                    // find corresponding select inside this.element
                    var $select = self.element.find('.super-attribute-select[name="super_attribute[' + attributeCode + ']"]');

                    if ($select.length) {
                        // visually update button states
                        $btn.siblings('.option-btn[data-attribute-code="' + attributeCode + '"]').removeClass('active');
                        $btn.addClass('active');
                        
                        // set select and trigger change
                        $select.val(optionId).trigger('change');
                    }
                });
            },

            _configureElement: function (element) {
                console.log('Mixin _configureElement:', element);
                var result = this._superApply(arguments);
                return result;
            },

            _changeProductImage: function () {
                console.log('Mixin _changeProductImage');
                return this._superApply(arguments);
            },

            _reloadPrice: function () {
                console.log('Mixin _reloadPrice');
                return this._superApply(arguments);
            }
        });
    };
});
