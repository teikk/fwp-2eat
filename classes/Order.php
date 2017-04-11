<?php 
/**
 * @todo Zapisac wszystkie daty do meta zamowienia
 * @todo sprawdzic w wp query czy dane zamowienie ma wybrana date
 * @todo Pobrac repeater spelniajacy date
 * @todo 
 */

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
		add_filter( 'acf/update_value/name=order_products', array($instance,'datesChanged'), 50, 3 );
	}

	public function api(){}
	/**
	 * Explode and sort dates string for order saving
	 * @param  string $dates String of dates, separated by ","
	 * @return array       Sorted array of dates
	 * ["20\/03\/2017","22\/03\/2017","23\/03\/2017","24\/03\/2017"]
	 * ["27\/03\/2017","28\/03\/2017"]
	 */
	public function parseDates( $dates ) {
		$dates = explode(',', $dates);
		sort($dates);
		return $dates;
	}
	public function datesChanged($value, $post_id, $field){
		$this->saveDates($post_id);
		return $value;
	}
	public function saveDates($post_id){
		$order_dates = array();
		$rows = get_field('order_products',$post_id);
		if( $rows ) {
			foreach ($rows as $key => $row) {
				$dates = $row['dates'];
				if( $dates ) {
					foreach ($dates as $key => $date) {
						if( in_array($date['date'], $order_dates) ) {
							continue;
						}
						$order_dates[] = $date['date'];
					}
				}
			}
		}
		update_post_meta( $post_id, '_fwpr_order_dates', $order_dates );
	}
	public function create( $products, $data ) {
		$order_id = wp_insert_post( array(
			'post_type' => 'fwpr_order',
			'post_title' => '['.current_time( 'd-m-Y H:i' ).'] '.$data['user'],
			'post_status' => 'publish'
			) );
		update_field( 'order_user',$data['user'],$order_id );
		update_field( 'order_address',$data['address'],$order_id );
		update_field( 'order_payment_type',$data['payment_type'],$order_id );
		update_field( 'order_phone',$data['phone'],$order_id );
		update_field( 'order_mail',$data['mail'],$order_id );

		update_field( 'order_info',$data['info'],$order_id );

		update_post_meta( $order_id, '_order_linked_payment', $data['payment'] );
		update_field('field_58c7e58b81029',$products,$order_id);
		do_action('fwpr/order/create',$data,$order_id);
		$this->saveDates($order_id);
	}
}
