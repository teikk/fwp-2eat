<?php 
add_action( 'plugins_loaded', array('FWPR_Payment','init'), 10, 1 );
class FWPR_Payment {
	protected static $instance;
	public $types = array();
	function __construct(){
		$this->types = array(
			'cash' => __('Gotówka', 'fwpr'),
			'transfer' => __('Przelew tradycyjny', 'fwpr')
		);
	}
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
		
	}
	public static function init(){
		$instance = self::get_instance();
		add_filter('acf/load_field/key=field_58c7e3a05f712', array($instance,'registerTypes') );
		add_filter('acf/load_field/key=field_58c7cd04e2960', array($instance,'registerTypes') );
		add_action( 'fwpr/payment-select', array( $instance,'select' ) );
		add_action( 'fwpr/payment/completed', array( $instance,'processCompleted' ) );
		add_filter( 'acf/update_value/name=payment_status', array($instance,'statusChanged'), 10, 3 );

		add_filter( 'fwpr/payment/make',array($instance,'make_payment'),10,1 );
		add_filter( 'fwpr/payment/pay/transfer', array( $instance,'default_payment' ) );
		add_filter( 'fwpr/payment/pay/cash', array( $instance,'default_payment' ) );

		add_action( 'wp_ajax_fwpr_pay', array($instance,'api') );
		add_action( 'wp_ajax_nopriv_fwpr_pay', array($instance,'api') );
	}


	public function registerTypes($field){
		$field['choices'] = apply_filters( 'fwpr/payment/types', $this->types );
		return $field;
	}
	public function getTypes(){
		return apply_filters( 'fwpr/payment/types', $this->types );
	}

	public function default_types(){
		$this->types = array(
		'cash' => __('Gotówka','fwpr'),
		'transfer' => __('Przelew','fwpr'),
		);
	}

	/**
	 * Hook into acf/update_value
	 * If payment status changed to completed create new order 
	 * @param  mixed $value   New status value
	 * @param  integer $post_id ID of the post being updated
	 * @param  array $field   ACF field array
	 * @return mixed          We do not do anything with the value so just return it
	 */
	public function statusChanged($value, $post_id, $field){
		$orderCreated = get_post_meta( $post_id, '_fwpr_payment_order_created', true );
		if( $orderCreated ) {
			return $value;
		}
		if( $value != 'completed' ) {
			return $value;
		}
		$before = get_field( 'payment_status',$post_id );		
		$payment_products = get_post_meta( $post_id,'_fwpr_payment_products',true );
		$order_data = array();
		$order_data['user'] = get_field( 'payment_user', $post_id );
		$order_data['address'] = get_field( 'payment_address', $post_id );
		$order_data['payment_type'] = get_field( 'payment_type', $post_id );
		$order_data['phone'] = get_field( 'payment_phone', $post_id );
		$order_data['mail'] = get_field( 'payment_mail', $post_id );
		$order_data['info'] = wp_strip_all_tags( get_field( 'payment_info', $post_id ) );
		$order_data['payment'] = $post_id;
		
		$order_products = array();
		foreach ($payment_products as $key => $product) {
			if( $product['variant'] !== 'false' ) {
				$variant = FWPR_Cart::get_instance()->getVariant($product['product'],$product['variant']); 
				$variant_text = $variant['name'].', posiłków:'. $variant['dinners'];
				$price = $variant['price'];
				$date = FWPR_Order::get_instance()->parseDates( $product['date'] );
				$date = apply_filters( 'fwpr/dates_to_repeater', $date );
			} else {
				$variant_text = 'Brak, produkt prosty';
				$isDiscounted = get_field('fwpr_product_discounted',$product['product']);
				$price = (!$isDiscounted) ? get_field( 'fwpr_product_price', $product['product']) : get_field( 'fwpr_product_price_discount', $product['product']);
				$date = array(
					array('date' => 'Jak najszybciej')
					);
			}
			$order_products[] = array(
				'product' => $product['product'],
				'variant' => $variant_text,
				'price' => $price,
				'dates' => $date
				);
		}
		$order_data = apply_filters( 'fwpr/payment/order-data', $order_data, $post_id );
		FWPR_Order::get_instance()->create($order_products,$order_data);
		update_post_meta( $post_id, '_fwpr_payment_order_created', true );
		return $value;
	}

	/**
	 * Fire when payment process is completed
	 * @return void Clear the cart
	 */
	public function processCompleted(){
		FWPR_Cart::get_instance()->clear();
	}
	/**
	 * Parses requests from AJAX. Only callable by AJAX.
	 */
	public function api(){
		$data = fwpr_parse($_POST['data']);
		$response = $this->pay($data);
		wp_send_json( $response );
	}

	/**
	 * Generate default payment types
	 * @return void Echoes radio buttons for payment type
	 */
	public function select(){
		$select = '';
		foreach ($this->types as $key => $type) {
			$select .= '<div class="radio"><label><input type="radio" name="payment_type" value="'.$key.'">'.$type.'</label></div>';
		}
		echo apply_filters( 'fwpr/payment/select/html', $select );
	}
	public function make_payment($data){
		$payment_uniqid = md5(uniqid());
		$payment_id = wp_insert_post( array(
			'post_type' => 'fwpr_payments',
			'post_title' => '['.current_time( 'd-m-Y H:i' ).'] '.$payment_uniqid,
			'post_status' => 'publish'
			) );		
		/**
		 * Sanitize all data before saving
		 */
		$data['firstname'] = sanitize_text_field( $data['firstname'] );
		$data['lastname'] = sanitize_text_field( $data['lastname'] );
		$data['payment_type'] = sanitize_text_field( $data['payment_type'] );
		$data['phone'] = sanitize_text_field( $data['phone'] );
		$data['email'] = sanitize_email( $data['email'] );
		$data['info'] = sanitize_textarea_field( $data['info'] );

		update_post_meta( $payment_id, '_fwpr_payment_id', $payment_uniqid );
		update_post_meta( $payment_id, '_fwpr_payment_products', FWPR_Cart::get_instance()->items );
		update_field( 'payment_user', $data['firstname'] .' '.$data['lastname'], $payment_id );
		update_field( 'payment_price', $data['price'], $payment_id );
		update_field( 'payment_type', $data['payment_type'], $payment_id );
		update_field( 'payment_status', 'new', $payment_id );
		update_field( 'payment_phone', $data['phone'], $payment_id );
		update_field( 'payment_mail', $data['email'], $payment_id );
		update_field( 'payment_info', $data['info'], $payment_id );

		if( !empty( $data['city'] ) ) {
			$address = $data['city'];
		}
		if( !empty( $data['street'] ) ) {
			$address .= ', '.$data['street'];
		}
		if( !empty( $data['block_number'] ) ) {
			$address .= ' '.$data['block_number'];
		}
		if( !empty( $data['flat_number'] ) ) {
			$address .= '/ '.$data['flat_number'];
		}
		if( !empty($address) ) {
			update_field( 'payment_address', $address, $payment_id );
		}
		do_action( 'fwpr/payment/create',$payment_id,$data );
		return $payment_id;
	}
	public function pay($data){
		$type = $data['payment_type'];
		$response = apply_filters( 'fwpr/payment/pay/'.$type, $data );
		do_action( 'fwpr/payment/completed', $data );
		return $response;
	}

	public function default_payment($data){
		$data['price'] = FWPR_Cart::get_instance()->getTotals();
		$payment_id = $this->make_payment($data);
		return $response = array(
			'redirect' => fwpr_returnUrl()
			);
	}
}