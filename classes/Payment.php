<?php 
add_action( 'plugins_loaded', array('FWPR_Payment','init'), 10, 1 );
class FWPR_Payment {
	protected static $instance;
	public $types = array();
	function __construct(){
		$this->types = array(
			'cash' => __('Płatność u kierowcy', 'fwpr'),
			'transfer' => __('Przelew tradycyjny', 'fwpr')
		);
	}
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
		
	}

	/**
	 * Initialize class in wordpress
	 * @return void
	 * @hook plugins_loaded
	 */
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

	/**
	 * Register default payment types
	 * @param  array $field ACF Field object(array)
	 * @return array        Modified ACF Field
	 */
	public function registerTypes($field){
		$field['choices'] = $this->getTypes();
		return $field;
	}

	/**
	 * Get default payment types
	 * @return array Default payment types
	 */
	public function getTypes(){
		return apply_filters( 'fwpr/payment/types', $this->types );
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
		$order = FWPR_Order::get_instance()->prepareOrder($post_id);
		do_action('fwpr/payment/statusChanged',$post_id,$order['order'],$order['order_data']);
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
		$types = $this->getTypes();
		foreach ($types as $key => $type) {
			$input = '<div class="radio">
					<label>
						<input type="radio" name="payment_type" value="'.$key.'" data-parsley-error-message="'.__('Wybierz sposób płatności','fwpr').'" data-parsley-errors-container=".fwpr-parsleyerror" required>'.$type.'
					</label>
				</div>';
			$select .= apply_filters( 'fwpr/payment/select/html', $input, $key, $type );
		}
		$select .= '<div class="fwpr-parsleyerror"></div>';
		echo $select;
	}
	public function make_payment($data){
		$data['firstname'] = sanitize_text_field( $data['firstname'] );
		$data['lastname'] = sanitize_text_field( $data['lastname'] );

		$payment_uniqid = md5(uniqid());
		$payment_id = wp_insert_post( array(
			'post_type' => 'fwpr_payments',
			'post_title' => '['.current_time( 'd-m-Y H:i' ).'] '.$data['firstname'].' '.$data['lastname'],
			'post_status' => 'publish'
			) );

		/**
		 * Sanitize all data before saving
		 */
		$data['payment_type'] = sanitize_text_field( $data['payment_type'] );
		$data['phone'] = sanitize_text_field( $data['phone'] );
		$data['email'] = sanitize_email( $data['email'] );
		$data['info'] = sanitize_textarea_field( $data['info'] );

		update_post_meta( $payment_id, '_fwpr_payment_id', $payment_uniqid );
		update_post_meta( $payment_id, '_fwpr_payment_products', FWPR_Cart::get_instance()->items );

		$userName = $data['lastname'] .' '.$data['firstname'];
		$userName = apply_filters( 'fwpr/payment/username', $userName, $data['firstname'], $data['lastname'] );

		update_field( 'payment_user', $userName, $payment_id );
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
		return $response;
	}

	public function default_payment($data){
		$data['price'] = FWPR_Cart::get_instance()->getTotals();
		$payment_id = $this->make_payment( $data );

		do_action( 'fwpr/payment/completed/'.$data['payment_type'], $data );		
		do_action( 'fwpr/payment/completed', $data );
		do_action( 'fwpr/payment/id/completed/'.$data['payment_type'], $payment_id );
		return $response = array(
			'redirect' => fwpr_returnUrl()
			);
	}
}