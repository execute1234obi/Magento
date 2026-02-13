define([
    'jquery',
    'jquery/ui',
    'mage/calendar'],
    function($){
        $.widget('adbooking.calendar', $.mage.calendar, {
            /**
           * {@inheritdoc}
           */
           _create : function () {
			   alert("as");
			   this._super(); // Use super method for call parent functions 
	        },
	        onSelect: function(dateText, inst) {
				alert("aas");
				this._super(dateText, inst); // Use super method for call parent functions 
			},	        
            invalidDates: function (invalidDates) {
                alert("invalidDates");
            }
    });
    return $.adbooking.calendar;
    });
