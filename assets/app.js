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
	if( fwpr.enableWeekends ) {
		var daysDisabled = '';
	} else {
		daysDisabled = '0,6';
	}
	$('.js-bd').datepicker({
		format: "dd/mm/yyyy",
		container: '.fwpr-datepicker-container',
		weekStart: 1,
		todayBtn: false,
		language: 'pl',
		multidate: true,
		startDate: date,
		closeBtn:true,
		datesDisabled: fwpr.disabledDates,
		disableTouchKeyboard:true,
		showWeekDays:false,
		daysOfWeekDisabled: daysDisabled
	});
	$('.js-bd').click(function(event){
		event.stopPropagation();
	});
	$('body').click(function(){
		$('.js-bd').datepicker('hide');
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