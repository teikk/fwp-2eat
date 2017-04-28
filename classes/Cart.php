<?php 
add_action( 'plugins_loaded', array('FWPR_Cart','init') );
/**
 * Class responsible for handling the cart functionality
 * Starts the session if it is not started yet
 * @since  1.0 Introduction
 */
class FWPR_Cart {
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
		add_action('init',array($instance,'onInit'),0);
		add_action('init',array($instance,'getItems'),1);
		add_action( 'wp_ajax_fwpr_cart', array($instance,'api') );
		add_action( 'wp_ajax_nopriv_fwpr_cart', array($instance,'api') );
	}

	/**
	 * Start the session and assign the cart key to empty array as default
	 * @return void 
	 */
	public function onInit(){
		if( session_id() == '' && !isset($_SESSION) ) {
			session_start();
		}
		if( empty( $_SESSION['fwpr_cart'] ) ) {
			$_SESSION['fwpr_cart'] = $this->items;
		}
	}


	/**
	 * API for handling AJAX calls
	 * @return void wp_die if json is not sent earlier
	 */
	public function api(){
		/**
		 * Cart method to call
		 * @var string
		 */
		$method = $_POST['method'];
		switch ($method) {
			case 'addToCart':
				$data = fwpr_parse($_POST['data']);
				$this->addItem( $data['product_id'], $data['variant'], $data['date'],$data['quantity'] );
				break;
			case 'removeFromCart':
				$data = fwpr_parse($_POST['data']);
				$this->removeItem( $data['cart_item'] );
				break;
			case 'showCart':
				fwpr_template('cart/cart-content');
				break;
			case 'reorderItems';
				$products = get_post_meta($_POST['data'],'_fwpr_payment_products',true);
				$_SESSION['fwpr_cart'] = $products;
				$user_id = get_current_user_id();
				if( $user_id != 0 ) {
					update_user_meta( $user_id, '_fwpr_cart', $_SESSION['fwpr_cart'] );
				}
				break;
			default:
				# code...
				break;
		}
		wp_die();
	}

	public function getItems(){
		$user = get_current_user_id();
		if( $user == 0 ) {
			$this->items = $_SESSION['fwpr_cart'];
		} else {
			$userCart = get_user_meta($user,'_fwpr_cart',true);
			if( empty( $userCart ) ) {
				$userCart = array();
			}
			$this->items = $userCart;
		}
		
	}

	public function removeItem( $item_key ) {
		unset($this->items[$item_key]);
		$_SESSION['fwpr_cart'] = $this->items;
		$user_id = get_current_user_id();
		if( $user_id != 0 ) {
			update_user_meta( $user_id, '_fwpr_cart', $this->items );
		}
	}

	public function addItem($product_id, $variant, $date, $quantity = 1 ){
		if( empty( $quantity ) ) {
			$quantity = 1;
		}		
		for ($i=1; $i <= $quantity; $i++) { 
			$_SESSION['fwpr_cart'][] = array(
			'product' => $product_id,
			'variant' => $variant,
			'date' => $date
			);
		}
		$user_id = get_current_user_id();
		if( $user_id != 0 ) {
			update_user_meta( $user_id, '_fwpr_cart', $_SESSION['fwpr_cart'] );
		}
	}

	public function getVariant($product_id, $variant) {
		$variants = get_field('fwpr_product_variants',$product_id);
		return $variants[$variant];
	}
	public function getTotals() {
		$totals = 0;
		if( empty( $this->items ) ) {
			return $totals;
		}
		foreach ($this->items as $key => $item) {
			if( $item['variant'] !== 'false' ) {
				$dates = $item['date'];
				$dates = explode(',',$dates);
				$variant = $this->getVariant( $item['product'], $item['variant'] );
				$totals += sizeof($dates) * $variant['price'];
			} else {
				$isDiscounted = get_field('fwpr_product_discounted',$item['product']);
				$price = (!$isDiscounted) ? get_field( 'fwpr_product_price', $item['product']) : get_field( 'fwpr_product_price_discount', $item['product']);
				$totals += $price;
			}
		}
		return $totals;
	}

	public function clear(){
		$this->items = array();
		$_SESSION['fwpr_cart'] = $this->items;
		$user_id = get_current_user_id();
		if( $user_id != 0 ) {
			update_user_meta( $user_id, '_fwpr_cart', $_SESSION['fwpr_cart'] );
		}
	}
}