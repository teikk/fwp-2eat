<?php 
add_action( 'plugins_loaded', array('FWPR_Payment','init'), 10, 1 );
class FWPR_Payment {
	protected static $instance;
	public $types = array();
	function __construct(){
		$this->default_types();		
	}
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}
	public static function init(){
		$instance = self::get_instance();
		add_action( 'fwpr/payment-select', array( $instance,'select' ) );
	}
	public function default_types(){
		$this->types = array(
		'cash' => __('Gotówka','fwpr'),
		'transfer' => __('Przelew','fwpr'),
		);
	}
	public function select(){
		$select = '';
		foreach ($this->types as $key => $type) {
			$select .= '<div class="radio"><label><input type="radio" name="payment_type" value="'.$key.'">'.$type.'</label></div>';
		}
		echo apply_filters( 'fwpr/payment/select/html', $select );
	}
}