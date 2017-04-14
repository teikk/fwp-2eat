(function($){
	$('.fwpr-add-to-cart').submit(function(event) {
		event.preventDefault();
		var formdata = $(this).serialize();
		$('body').fwpr('addToCart',{data:formdata});
	});

	$('body').on('submit','.fwpr-remove-from-cart',function(event) {
		event.preventDefault();
		var formdata = $(this).serialize();
		console.log(formdata);
		$('body').fwpr('removeFromCart',{data:formdata});
	});
	
	$(document).on('fwpr/cart/itemAdded fwpr/cart/itemRemoved',function(){
		$('#fwpr-cart').fwpr('showCart');
	});


	/**
	 * Setup datepicker
	 * @type {Date}
	 */
	var date = new Date();
	if( date.getHours() < fwpr.maxForTomorrow ) {
		date.setDate(date.getDate() + 1);		
	} else {
		date.setDate(date.getDate() + 2);		
	}
	$('.js-bd').datepicker({
	    format: "dd/mm/yyyy",
	    weekStart: 1,
	    todayBtn: "linked",
	    language: "pl",
	    multidate: 14,
	    startDate: date,
	    daysOfWeekDisabled: "0,6"
	});
	$('.input-daterange input').each(function() {
	    $(this).datepicker('clearDates');
	});
	$('.fwpr-payment').parsley();
	$('.fwpr-payment').submit(function(event) {
		event.preventDefault();
		var formdata = $(this).serialize();
		$('body').fwpr('pay',{data:formdata});
	});

	$(document).on('fwpr/payment/completed',function(response){
		if( response.redirect != undefined && response != null) {
			window.location = response.redirect;
		}
	});

	/** Always do something if ajax request completes */
	$(document).ajaxSuccess(function( event, request, settings, data ) {

		if( data == undefined || data == null ) {
			return false;
		}
		if( data.redirect != undefined && data != null) {
			window.location = data.redirect;
		}
	});
})(jQuery);