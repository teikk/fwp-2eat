<?php
add_action( 'plugins_loaded', array( 'FWPR_Users','init' ) );
/**
 * Handles user orders 
 * Assigns users to orders they make
 * Assigns payments to users
 */
class FWPR_Users {
	protected static $instance;
	public $items = array();
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

		add_action( 'fwpr/payment/create',array($instance,'assignToPayment'), 10, 2 );
		add_action( 'fwpr/payment/statusChanged',array($instance,'assignToOrder'), 10, 2 );
	}

	public function assignToPayment($paymentID, $paymentData) {
		$user = get_current_user_id();
		if( $user != 0 ) {
			update_post_meta( $paymentID, '_fwpr_userid', $user );
		}
	}

	public function assignToOrder($paymentID, $orderID ) {
		$paymentUser = get_post_meta( $paymentID, '_fwpr_userid', true );
		update_post_meta( $orderID, '_fwpr_userid', $paymentUser );
	}
}