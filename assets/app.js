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
})(jQuery);