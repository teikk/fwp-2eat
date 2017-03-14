<?php 


add_action( 'plugins_loaded', array('FWPR_Cart','init') );
class FWPR_Cart {
	protected static $instance;
	public $items = array();
	function __construct(){}

	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}

	public static function init(){
		$instance = self::get_instance();
		add_action('init',array($instance,'onInit'),1);
		add_action('init',array($instance,'getItems'),1);
		add_action( 'wp_ajax_fwpr_cart', array($instance,'api') );
		add_action( 'wp_ajax_nopriv_fwpr_cart', array($instance,'api') );
	}

	public function onInit(){
		if( session_id() == '' && !isset($_SESSION) ) {
			session_start();
		}
		if( empty( $_SESSION['fwpr_cart'] ) ) {			
			$_SESSION['fwpr_cart'] = $this->items;
		}
	}

	public function api(){
		$method = $_POST['method'];
		switch ($method) {
			case 'addToCart':
				$data = fwpr_parse($_POST['data']);
				$this->addItem( $data['product_id'], $data['variant'] );
				break;
			case 'removeFromCart':
				$data = fwpr_parse($_POST['data']);
				$this->removeItem( $data['cart_item'] );
				break;
			case 'showCart':
				echo fwpr_cart();
				break;
			default:
				# code...
				break;
		}
		wp_die();
	}

	public function getItems(){
		$user = get_current_user_id();
		if( $user != 0 ) {
			$this->items = $_SESSION['fwpr_cart'];
		} else {
			$items = get_user_meta( $user, '_fwpr_cart', true );
			$this->items = $items;
		}
	}

	public function removeItem( $item_key ) {
		unset($this->items[$item_key]);
		$_SESSION['fwpr_cart'] = $this->items;
	}

	public function addItem($product_id, $variant, $user_id = 0 ){
		if( $user_id == 0 ) {
			$user_id = get_current_user_id();
		}
		$_SESSION['fwpr_cart'][] = array(
			'product' => $product_id,
			'variant' => $variant
			);
	}
}
