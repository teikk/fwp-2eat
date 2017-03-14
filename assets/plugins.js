(function($){
	$.fn.fwpr = function( method,args ){
		var methods = {
			/**
			 * Adds item to cart
			 */
			addToCart : function(){
				console.log(args);
				$.post( fwpr.ajaxurl, {
					action: 'fwpr_cart',
					method: 'addToCart', 
					data:args.data
				},
				function(response){
					console.log(response);
					$(document).triggerHandler('fwpr/cart/itemAdded',response);
				});
			},
			/**
			 * Removes item from cart
			 */
			removeFromCart : function(){
				$.post( fwpr.ajaxurl, {
					action: 'fwpr_cart',
					method: 'removeFromCart',
					data:args.data
				},
				function(response){
					console.log(response);
					$(document).triggerHandler('fwpr/cart/itemRemoved',response);
				});
			},
			/**
			 * Displays cart HTML
			 */
			showCart : function(){
				$.post( fwpr.ajaxurl, {
					action: 'fwpr_cart',
					method: 'showCart',
				},
				function(response){
					$('#fwpr-cart').html(response);
					$(document).triggerHandler('fwpr/cart/cartRefreshed',response);
				});
			}
		}
		methods[method].call(this);
	}
})(jQuery);