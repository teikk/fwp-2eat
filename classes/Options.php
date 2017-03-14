<?php 
add_action('plugins_loaded',array('FWPR_Options','init'));
class FWPR_Options {
	protected static $instance;
	protected $page_slug = 'fwpr_settings';
	protected $options;
	public static function get_instance() {
		// create an object
		NULL === self::$instance and self::$instance = new self;

		return self::$instance; // return the object
	}
	function __construct(){
		
	}
	public static function init(){
		$instance = self::get_instance();
		$instance->options['global'] = get_option( 'fwpr_global_options' );
		$instance->options['dotpay'] = get_option( 'fwpr_dotpay_options' );
		$instance->options['delivery'] = get_option( 'fwpr_delivery_options' );
		$instance->options['opening_exceptions'] = get_option( 'fwpr_opening_exceptions' );
		add_action('admin_init',array($instance,'register_options'));
		add_action('admin_menu',array($instance,'options_page'));
		add_action('admin_menu',array($instance,'sub_options_page'));

		add_action( 'admin_enqueue_scripts',array($instance,'admin_scripts') );
	}
	public function admin_scripts(){

		// wp_register_script( 'fwpr-pickadate', FWPR_URI . 'dist/pickadate/picker.js', array( 'jquery' ), '1.0', true );
		// wp_register_script( 'fwpr-pickadate-time', FWPR_URI . 'dist/pickadate/picker.time.js', array( 'jquery' ), '1.0', true );
		// wp_register_script( 'fwpr-pickadate-date', FWPR_URI . 'dist/pickadate/picker.date.js', array( 'jquery' ), '1.0', true );
		// wp_register_script( 'fwpr-pickadate-pl', FWPR_URI . 'dist/pickadate/pl_PL.js', array( 'jquery' ), '1.0', true );
		// wp_register_script( 'fwpr-admin-scripts', FWPR_URI.'dist/admin/scripts.js', array( 'jquery' ), false, false );

		// wp_register_style( 'fwpr-pickadate-css', FWPR_URI . 'dist/pickadate/classic.css' );
		// wp_register_style( 'fwpr-pickadate-time-css', FWPR_URI . 'dist/pickadate/classic.time.css' );
		// wp_register_style( 'fwpr-pickadate-date-css', FWPR_URI . 'dist/pickadate/classic.date.css' );
	}
	public function get_options(){
		return $this->options;
	}
	public function options_page(){
		add_menu_page(
			__( 'FWPR Settings', 'fwpr' ),
			'FWPR Settings',
			'manage_options',
			$this->page_slug,
			array($this,'render_options'),
			'',
			76
		);	
	}
	public function sub_options_page(){
		// add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' )
		add_submenu_page( 
			$this->page_slug, 
			'Rejon dostawy', 
			'Rejon dostawy',
			'manage_options',
			'fwpr_delivery_page',
			array($this,'delivery') 
		);
	}
	public function register_options(){
		register_setting( 'fwpr_delivery_page', 'fwpr_delivery_options' );

		add_settings_section( 'fwpr_delivery', __('Ustawienia dostawy','fwpr'), array($this,'global_options'), 'fwpr_delivery_page' );
		add_settings_field( 'fwpr_delivery_area', '', array($this,'global_fields'), 'fwpr_delivery_page', 'fwpr_delivery', array(
			'label_for'         => 'fwpr_delivery_area',
			'name' => 'area',
			'group' => 'delivery',
			'type' => 'text',
			) );

		register_setting($this->page_slug, 'fwpr_global_options');

		register_setting($this->page_slug, 'fwpr_opening_exceptions');

		add_settings_section( 'fwpr_global', __('Ustawienia','fwpr'), array($this,'global_options'), $this->page_slug );

		add_settings_field( 'fwpr_delivery_time', __('Czas dostawy','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_delivery_time',
			'name' => 'delivery_time',
			'group' => 'global',
			'type' => 'text',
			'description' => __('Tekst wyświetlany użytkownikowi jako czas wykonania zamówienia','fwpr')
			) );

		add_settings_field( 'fwpr_return_url', __('Strona powrotu','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_return_url',
			'func' => 'page_select',
			'name' => 'return_page',
			'group' => 'global',
			'type' => 'select',
			'description' => __('Strona, na którą użytkownik zostanie przeniesiony po dokonaniu zamówienia','fwpr')
			) );

		add_settings_field( 'fwpr_order_page', __('Strona składania zamówienia','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_order_page',
			'func' => 'page_select',
			'name' => 'order_page',
			'group' => 'global',
			'type' => 'select',
			'description' => __('Strona, na którą użytkownik zostanie przekierowany po kliknięciu przycisku zatwierdzającego w koszyku','fwpr')
			) );

		add_settings_field( 'fwpr_gmap_api', __('Klucz API Google Maps','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_gmap_api',
			'name' => 'gmap_api',
			'group' => 'global',
			'type' => 'text'
			) );
		add_settings_field( 'fwpr_min_delivery_price', __('Minimalna kwota zamówienia','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_min_delivery_price',
			'name' => 'min_delivery_price',
			'group' => 'global',
			'type' => 'text'
			) );

		add_settings_field( 'fwpr_currency', __('Waluta','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_currency',
			'name' => 'currency',
			'group' => 'global',
			'type' => 'select',
			'options' => array(
				'PLN' => 'PLN',
				'EUR' => 'Euro',
				'GBP' => 'GBP',
				'USD' => 'USD',
				)
			) );
		add_settings_field( 'fwpr_cart_type', __('Rodzaj koszyka','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_global', array(
			'label_for'         => 'fwpr_cart_type',
			'name' => 'cart_type',
			'group' => 'global',
			'type' => 'select',
			'options' => array(
				'list' => 'Lista',
				'grouped' => 'Grupowany',
				)
			) );
		register_setting($this->page_slug, 'fwpr_dotpay_options');

		add_settings_section( 'fwpr_dotpay_settings', __('Ustawienia konta Dotpay','fwpr'), array($this,'dotpay_options'), $this->page_slug );

		add_settings_field( 'fwpr_dotpay_id', __('ID Konta dotpay','fwpr'), array($this,'global_fields'), $this->page_slug, 'fwpr_dotpay_settings', array(
			'label_for'         => 'fwpr_dotpay_id',
			'name' => 'id',
			'group' => 'dotpay',
			'type' => 'text'
			) );
	}
	public function global_options($args){
		//echo '<pre>'; print_r($this->options); echo '</pre>';
	}
	public function global_fields($args){
		if( !empty($args['func']) && $args['func'] == 'page_select' ){
			include FWPR_DIR . 'options-template/return-page-select.php';			
		} elseif( $args['type'] == 'select' ) {
			include FWPR_DIR . 'options-template/select-field.php';
		} else {
			include FWPR_DIR . 'options-template/global-fields.php';			
		}
		if( !empty( $args['description'] ) ){
			echo '<p class="description">'.$args['description'].'</p>';
		}
	}
	public function dotpay_options($args){		
		echo '<p class="description">Podstawowe ustawienia konta Dotpay</p>';
	}
	public function render_options(){
		include FWPR_DIR . 'options-template/options-page.php';
	}
	public function delivery(){
		include FWPR_DIR . 'options-template/delivery-page.php';
	}
}