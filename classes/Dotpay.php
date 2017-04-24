<?php 
add_action( 'plugins_loaded',array('FWPR_Dotpay','init') );

/**
 * Handles Dotpay payments
 */
class FWPR_Dotpay {
	protected static $instance;
	protected $allowed_ip = '195.150.9.37';
	function __construct(){}
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

		add_filter( 'fwpr/payment/pay/dotpay', array( $instance,'pay' ) );
		add_filter('fwpr/payment/types', array($instance,'registerType'),20,1 );

		add_action('wp',array($instance,'parse_response'));
	}

	/**
	 * Register new payment status for ACF payment type fields
	 * @param array $field ACF Field array
	 * 
	 * @see  get_field_object() https://www.advancedcustomfields.com/resources/get_field_object/
	 * @return array Array of modified choices
	 */
	public function registerType($types){
		$types['dotpay'] = __('Przelew online','fwpr');
		return $types;
	}

	/**
	 * Generate base for DotPay test payments system
	 * @return string Base URL for DotPay Test System
	 */
	private function test_url(){
		$home_url = urlencode(home_url('/'));
		$options = get_option('fwpr_dotpay_options');
		$globalOptions = get_option('fwpr_global_options');
		return 'https://ssl.dotpay.pl/test_payment/?id='.$options['id'].'&api_version=dev&type=3&currency='.$globalOptions['currency'].'&URL='.$home_url.'&URLC='.$home_url;
	}

	/**
	 * Prepare base payment URL for production system
	 * @return string Base payment URL for production 
	 */
	private function paymentBaseUrl(){
		$home_url = urlencode(home_url('/'));
		$options = get_option('fwpr_dotpay_options');
		$globalOptions = get_option('fwpr_global_options');
		return 'https://ssl.dotpay.pl/t2/?id='.$options['id'].'&api_version=dev&type=3&currency='.$globalOptions['currency'].'&URL='.$home_url.'&URLC='.$home_url;
	}

	/**
	 * Populate payment URL with data from form
	 * @param  array $data $_POST data from payment form
	 * @return string       Populated URL ready to redirect user to payment process
	 */
	public function payment_url($data){
		if( FWPR_DEV ) {
			$link = apply_filters( 'fwpr/dotpay/base_url', $this->test_url() );
		} else {
			$link = apply_filters( 'fwpr/dotpay/base_url', $this->paymentBaseUrl() );
		}
		if( !empty($data['firstname']) ) {
			$link .= '&firstname='.sanitize_text_field( $data['firstname'] );
		}
		if( !empty($data['lastname']) ) {
			$link .= '&lastname='.sanitize_text_field( $data['lastname'] );
		}
		if( !empty($data['email']) ) {
			$link .= '&email='.sanitize_text_field( $data['email'] );
		}
		
		$link .= '&amount='.$data['price'];
		$link .= '&control='.$data['control'];
		$link .= '&description='.$data['firstname'].' '.$data['lastname'];
		return apply_filters( 'fwpr/payment/dotpay/url', $link );
	}

	/**
	 * Pay for products
	 * @param  array $data $_POST data from form
	 * @return array       Response containing payment unique id and redirect link
	 */
	public function pay($data){
		$data['price'] = FWPR_Cart::get_instance()->getTotals();
		$payment_id = apply_filters( 'fwpr/payment/make', $data );
		$payment_uniqid = get_post_meta( $payment_id, '_fwpr_payment_id', true );
		$data['control'] = $payment_uniqid;
		/**
		 * Clear cart before redirect to payment page
		 * @todo  Clear cart only after successfull payment
		 */
		do_action( 'fwpr/payment/completed', $data );
		return $response = array(
			'payment' => $payment_uniqid,
			'redirect' => $this->payment_url($data)
			);
	}

	/**
	 * Accepts payment based on passed code
	 * @param  string $control Payment code generated at the beginning of the process
	 * @return void          Updates payment_status field to 'complete'
	 */
	public function accept_payment($control){
		$payment = get_posts( array(
			'post_type' => 'fwpr_payments',
			'meta_key' => '_fwpr_payment_id',
			'meta_value' => $control,
			'meta_compare' => '='
			) );
		$payment = $payment[0];
		
		update_field('payment_status','completed',$payment->ID);		
		do_action('fwpr/payment/completed/dotpay',$payment->ID);
	}

	/**
	 * Parse response from DotPay
	 *
	 * Checks Dotpay IP address
	 * @return void Return if something is wrong
	 */
	public function parse_response(){
		// if( !empty($_GET['control']) ) {
		// 	$this->accept_payment( $_GET['control'] );
		// }
		if( empty($_POST) ) {
			return;
		}
		
		$client_ip = $_SERVER['REMOTE_ADDR'];
		if( $client_ip != $this->allowed_ip ) {
			return;
		}
		error_log(print_r($_POST,true));
		$options = get_option('fwpr_dotpay_options');
		if( $_POST['id'] != $options['id'] ) {
			return;
		}
		echo "OK";
		$sign=
		$options['pin'].
		$_POST['id'].
		$_POST['operation_number'].
		$_POST['operation_type'].
		$_POST['operation_status'].
		$_POST['operation_amount'].
		$_POST['operation_currency'].
		$_POST['operation_withdrawal_amount'].
		$_POST['operation_commission_amount'].
		$_POST['operation_original_amount'].
		$_POST['operation_original_currency'].
		$_POST['operation_datetime'].
		$_POST['operation_related_number'].
		$_POST['control'].
		$_POST['description'].
		$_POST['email'].
		$_POST['p_info'].
		$_POST['p_email'].
		$_POST['credit_card_issuer_identification_
		number'].
		$_POST['credit_card_masked_number'].
		$_POST['credit_card_brand_codename'].
		$_POST['credit_card_brand_code'].
		$_POST['credit_card_id'].
		$_POST['channel'].
		$_POST['channel_country'].
		$_POST['geoip_country'];
		$signature=hash('sha256', $sign);

		if( $signature != $_POST['signature'] ) {
			return;
		}
		//Check if payment status is success
		if( $_POST['operation_status'] != 'completed') {
			return;
		}

		$control = $_POST['control'];				
		$this->accept_payment( $control );
		do_action('fwpr/payment/after');
	}
}
