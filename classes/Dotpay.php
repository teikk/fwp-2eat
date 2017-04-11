<?php 
add_action( 'plugins_loaded',array('FWPR_Dotpay','init') );
class FWPR_Dotpay {
	protected static $instance;
	protected $allowed_ip = '195.150.9.37';
	function __construct(){}
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}
	public static function init(){
		$instance = self::get_instance();
		add_action( 'fwpr/payment-select', array( $instance,'select' ) );
		add_filter( 'fwpr/payment/pay/dotpay', array( $instance,'pay' ) );
		
		// add_filter('acf/load_field/key=field_58c7e3a05f712', array($instance,'registerType') );
		// add_filter('acf/load_field/key=field_58c7cd04e2960', array($instance,'registerType') );
		add_filter('fwpr/payment/types', array($instance,'registerType'),20,1 );

		add_action('wp',array($instance,'parse_response'));
	}
	public function select(){
		$select = '<div class="radio"><label><input type="radio" name="payment_type" value="dotpay">Przelew online</label></div>';
		echo apply_filters( 'fwpr/payment/select/html', $select );
	}

	/**
	 * Register new payment status for ACF payment type fields
	 * @param array $field ACF Field array
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
		return 'https://ssl.dotpay.pl/test_payment/?id=796548&api_version=dev&type=3&currency=PLN&description=Test-Payment&URLC='.$home_url;
	}
	private function paymentBaseUrl(){
		$home_url = urlencode(home_url('/'));
		return 'https://ssl.dotpay.pl/t2/?id=796548&api_version=dev&type=3&currency=PLN&description=Test-Payment';
	}
	public function payment_url($data){
		if( FWPR_DEV ) {
			$link = apply_filters( 'fwpr/dotpay/base_url', $this->test_url() );
		} else {
			$link = apply_filters( 'fwpr/dotpay/base_url', $this->paymentBaseUrl() );
		}
		if( !empty($data['first_name']) ) {
			$link .= '&firstname='.sanitize_text_field( $data['first_name'] );
		}
		if( !empty($data['last_name']) ) {
			$link .= '&lastname='.sanitize_text_field( $data['last_name'] );
		}
		if( !empty($data->data['email']) ) {
			$link .= '&email='.sanitize_text_field( $data['email'] );
		}
		
		$link .= '&amount='.$data['price'];
		$link .= '&control='.$data['control'];
		$link .= '&URL='.add_query_arg('control',$data['control'],home_url());
		return $link;
	}

	public function pay($data){
		$data['price'] = FWPR_Cart::get_instance()->getTotals();
		$payment_id = apply_filters( 'fwpr/payment/make', $data );
		$payment_uniqid = get_post_meta( $payment_id, '_fwpr_payment_id', true );
		$data['control'] = $payment_uniqid;		
		return $response = array(
			'payment' => $payment_uniqid,
			'redirect' => $this->payment_url($data)
			);
	}

	public function simulate_payment(){
		$control = $_GET['control'];
		$payment = get_posts( array(
			'post_type' => 'fwpr_payments',
			'meta_key' => '_fwpr_payment_id',
			'meta_value' => $control,
			'meta_compare' => '='
			) );
		$payment = $payment[0];
		update_field('payment_status','completed',$payment->ID);
	}
	public function parse_response(){
		if( !empty($_GET['control']) ) {
			$this->simulate_payment();
		}
		if( empty($_POST) ) {
			return;
		}
		
		$client_ip = $_SERVER['REMOTE_ADDR'];
		if( $client_ip != $this->allowed_ip ) {
			return;
		}
		error_log(print_r($_POST,true));
		if( $_POST['id'] != '796548' ) {
			return;
		}
		echo "OK";
		
		$sign=
		'ydNgiAGtf57SUARukZCGjUps8HhAwmZI'.
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
		// $order = get_posts(array(
		// 	'post_type' => 'quest_payments',
		// 	'meta_key' => 'payment_code',
		// 	'meta_value' => $control,
		// 	'meta_compare' => '=',
		// 	));
		// $order = $order[0];
		// $payment_type = get_field('payment_type',$order->ID);
		// $customer = get_field('payment_user',$order->ID);
		// if( is_array($customer) ) {
		// 	$customer = $customer['ID'];
		// }
		// $paid_for = get_field('payment_for',$order->ID);
		// // Post object for thing user is paying for
		// $paid_for = get_post($paid_for);
		
		// if( $payment_type == 'form' ) {
		// 	$code = get_field( 'payment_code_used',$order->ID );
		// 	$code = quest_check_code($paid_for->ID, $code);
		// 	if( $code ){
		// 		do_action( 'quest/code/deactivate', $code->ID );
		// 		/**
		// 		 * Get coach ID required for assigning the result
		// 		 */
		// 		$coach = get_field( 'quest_code_user', $code->ID );
		// 		if( is_array($coach) ) {
		// 			$coach = $coach['ID'];
		// 		}

		// 		$results_for_coach = get_user_meta($customer,'_quest_results_for_coach', true);
		// 		$results_for_coach[$paid_for->ID] = $coach;
		// 		update_user_meta( $customer, '_quest_results_for_coach', $results_for_coach );
		// 	}
		// 	do_action( 'quest/form/unlock', $paid_for->ID, $customer );
		// }
		// if( $payment_type == 'codes' ) {
		// 	$quantity = get_field('payment_quantity',$order->ID);
		// 	do_action( 'quest/codes/generate', $quantity, $paid_for->ID, $customer['ID'],100, true, '' );
		// }
	}
}
