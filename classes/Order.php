<?php 


add_action( 'plugins_loaded', array('FWPR_Order','init') );
class FWPR_Order {
	protected static $instance;
	function __construct(){}

	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}

	public static function init(){
		$instance = self::get_instance();
	}

	public function api(){}

	public function create( $products, $data ) {
		$order_id = wp_insert_post( array(
			'post_type' => 'fwpr_order',
			'post_title' => '['.date('d-m-Y, H:i').'] '.$data['user'],
			'post_status' => 'publish'
			) );
		update_field( 'order_user',$data['user'],$order_id );
		update_field( 'order_address',$data['address'],$order_id );
		update_field( 'order_payment_type',$data['payment_type'],$order_id );
		update_post_meta( $order_id, '_order_linked_payment', $data['payment'] );
		update_field('field_58c7e58b81029',$products,$order_id);
	}
}
