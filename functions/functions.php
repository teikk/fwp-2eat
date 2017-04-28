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

function fwpr_sortACFDates($a,$b){
	if( is_array($a) ){
		$a = $a['date'];
	}
	if( is_array($b) ){
		$b = $b['date'];
	}
	$a = DateTime::createFromFormat('Ymd',$a);
	$b = DateTime::createFromFormat('Ymd',$b);
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
		$date = DateTime::createFromFormat('d/m/Y',$date);
		$mDates[] = array('date' => $date->format( 'Ymd') );
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
	$types = FWPR_Payment::get_instance()->getTypes();
	return $types[$type];
}

function fwpr_getDisabledDates(){
	$disabled = get_field('fwpr_disabled_dates','option');

	$disabledDates = array();
	if( !empty( $disabled ) ) {
		foreach ($disabled as $key => $date) {
			$disabledDates[] = $date['data'];
		}
	}	
	return $disabledDates;
}

add_filter( 'acf/load_value/key=field_58c92b70760ff', 'fwpr_reformatDate', 10, 3 );
function fwpr_reformatDate($value,$post_id,$field){
	if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/",$value)) {
		$date = DateTime::createFromFormat( 'd/m/Y',$value );
		$value = $date->format('Ymd');
	}
	return $value;
}


function fwpr_getUserOrders(){
	$user = get_current_user_id();
	$args = array(
		'post_type' => 'fwpr_order',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_fwpr_userid',
				'value' => $user,
				'type' => 'NUMERIC',
				'compare' => '='
				)			
			)
		);
	$args = apply_filters( 'fwpr/user/orders/args', $args );

	return new WP_Query($args);
}


function fwpr_listOrderProducts($orderID){
	$products = get_field('order_products',$orderID);
	$totalPrice = 0;
	if( !empty($products) ){
		$productsHtml = apply_filters( 'fwpr/order/products/wrap/open', '<ul>' );
		foreach ($products as $key => $product) {
			$productItem = '<li>'.get_the_title($product['product']).': '.$product['variant'].'</li>';
			$productsHtml .= apply_filters( 'fwpr/order/products/item',$productItem );
			$dates = $product['dates'];
			if( !empty($dates) ) {
				$totalPrice += $product['price'] * sizeof($dates);
				usort($dates, 'fwpr_sortCartDates');

				$datesHtml = apply_filters( 'fwpr/order/date/wrap/open', '<ul>' );
				foreach ($dates as $key => $date) {
					$dateFormatted = $date['date'];
					$dateItem = '<li>'.$dateFormatted.'</li>';
					$datesHtml .= apply_filters( 'fwpr/order/date/item', $dateItem );
				}

				$datesHtml .= apply_filters( 'fwpr/order/date/wrap/end', '</ul>' );
				$productsHtml .= $datesHtml;
			}
		}
		$productsHtml .= apply_filters( 'fwpr/order/products/wrap/close', '</ul>' );	
	}
	return $productsHtml;
}



function fwpr_setupReminderDate($current_date){
	$reminder = get_field('fwpr_reminder_panel','option');
	if( empty($reminder) ) {
		$reminder = 1;
	}
	$current_date = DateTime::createFromFormat('d/m/Y', $current_date)->getTimestamp();
	$nextDay = strtotime('+'.$reminder.' day',$current_date);
	$nextDay = date( 'd/m/Y',$nextDay );
	return $nextDay;
}
function fwpr_getTimestamp($date){
	$date = DateTime::createFromFormat('d/m/Y', $date)->getTimestamp();
	return $date;
}


function fwpr_getPaymentStatus($orderID){
	$paymentID = get_post_meta($orderID, '_fwpr_payment_id', true);
	$statusField = get_field_object('payment_status',$paymentID);
	$status = get_field('payment_status',$paymentID);
	return $statusField['choices'][$status];
}


add_action( 'fwpr/payment/id/completed/cash','fwp_driver_payment',10,1 );
function fwp_driver_payment($paymentID){
	update_field('payment_status','completed',$paymentID);
}