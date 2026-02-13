/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated see Magento/Ui/view/base/web/js/grid/filters/elements/ui-select.js
 */
define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'underscore'
], function (Select, $, _) {
    'use strict';

    return Select.extend({
        defaults: {
            validationUrl: false,
            loadedOption: [],            
            validationLoading: false,
            
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.validateInitialValue();

            return this;
        },
        validateInitialValue: function () {			
			this.options = this.options;
			this.loadedOption = this.options;
		},
        onUpdate: function () {
			var productHasPrice =  this.value();
			if(productHasPrice == 0){
			   $('input[name="product[price]"]').val('').change();
			   $(".action-additional").hide();
		    }else if(productHasPrice == 1){
				$(".action-additional").show();
			}

            return this._super();
        }
    });
});
