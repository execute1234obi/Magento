define([
    'jquery',
    'underscore',
    'uiRegistry',    
    'Magento_Ui/js/form/element/ui-select',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function ($, _, uiRegistry, select, modal, url) {
    'use strict';
    return select.extend({
        /**
         * On value change handler.
         *
         * @param {String} value
         */         
         initConfig:function (config) {
			 this._super();			 			 
			 if(this.selectedvalues){
				 var selectedOptionString =  this.selectedvalues;				 
				 var self = this;
				 if(selectedOptionString !==''){
					 var selectedArr = selectedOptionString.split(',');
					 if(selectedArr.length){
						 $.each(selectedArr, function(index, value){								 
							 self.value.push(value);
						 });
					 }
				 }
			}	 
		 return;
		}
			
    });
});
