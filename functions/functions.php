<?php 
function fwpr_returnUrl(){
	$options = FWPR_Options::get_instance()->get_options();
	return get_permalink( $options['global']['return_page'] );
}

function fwpr_sortCartDates($a,$b){
	if( is_array($a) ){
		$a = $a['date'];
	}
	if( is_array($b) ){
		$b = $b['date'];
	}
	$a = DateTime::createFromFormat('d/m/Y',$a);
	$b = DateTime::createFromFormat('d/m/Y',$b);
	$a = $a->getTimestamp();
	$b = $b->getTimestamp();
	return $a - $b;
}

function fwprSortByVariant($a, $b) {
  return strcmp($a['variant'], $b['variant']);
}

add_filter( 'fwpr/dates_to_repeater', 'fwpr_dates_to_repeater', 10, 1 );
function fwpr_dates_to_repeater($dates) {
	$mDates = array();
	foreach ($dates as $key => $date) {
		$mDates[] = array('date' => $date);
	}
	return $mDates;
}

function fwpr_sort_orders($date){
	$sorted_orders = array();
	$args = array(
		'post_type' => 'fwpr_order',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_fwpr_order_dates',
				'value' => $date,
				'compare' => 'LIKE'
				),
			)
		);
	$args = apply_filters( 'fwpr/panel/orders-args', $args );
	$orders = new WP_Query($args);
	if( $orders->have_posts() ){
		while( $orders->have_posts() ){
			$orders->the_post();
			$order_id = get_the_ID();
			$products = get_field('order_products');
			if( $products ) {
				foreach ($products as $key => $product) {
					$found = in_array(array('date'=>$date),$product['dates'] );
					if( $found ) {
						$product['order'] = $order_id;
						$sorted_orders[$product['product']][] = $product;
					} else {
						$found = false;
					}
				}
			}
		}
		wp_reset_postdata();
	}
	return $sorted_orders;
}


function fwpr_get_delivery_area(){
	$options = FWPR_Options::get_instance();
	$options = $options->get_options();
	return $options['delivery']['area'];
}

function fwpr_payment_label($type) {
	$types = FWPR_Payment::get_instance()->types;
	$types = apply_filters( 'fwpr/payment/types', $types );
	return $types[$type];
}