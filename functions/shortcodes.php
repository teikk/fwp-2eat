<?php 
	/**
	 * Setups shortcode displaying of product list
	 * @return void
	 */
	function fwpr_products_list(){
		ob_start();
		fwpr_template('products/products','list');
		return ob_get_clean();
	}
	add_shortcode( 'fwpr_products', 'fwpr_products_list' );

	function fwpr_cart(){
		ob_start();
		fwpr_template('cart/cart');
		return ob_get_clean();
	}
	add_shortcode( 'fwpr_cart', 'fwpr_cart' );

	function fwpr_order_form(){
		ob_start();
		fwpr_template('order-page/order-page');
		return ob_get_clean();
	}
	add_shortcode( 'fwpr_order_form', 'fwpr_order_form' );